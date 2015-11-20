var fullUserList = [];
fullUserList["lobby"] = [];
var fullSocketList = [];
fullSocketList["lobby"] = [];
var fullRoomList = [];
var roomCreatorList = [];
var permaBanRoomList = [];
permaBanRoomList["lobby"] = [];
var muteRoomList = [];
muteRoomList["lobby"] = [];
var passwordRoomList = []
passwordRoomList["lobby"] = null;

function removeFromArr(array, identifier) { //remove element from array based on identifier
	if (identifier != undefined && identifier != null) {
		var index = array.indexOf(identifier);
		if (index > -1) {
			array.splice(index, 1);
		}
	}
}

var http = require("http"), // Require the packages we will use:
	socketio = require("socket.io"),
	fs = require("fs"),
    url = require('url'),
	path = require('path'),
	mime = require('mime');

var app = http.createServer(function(req, resp){ // Listen for HTTP connections. This callback runs when a new connection is made to our HTTP server.
 
	var filename = path.join(__dirname, "static", url.parse(req.url).pathname); //parallel to static folder
	(fs.exists || path.exists)(filename, function(exists){
		if (exists) {
			fs.readFile(filename, function(err, data){
				if (err) {

					resp.writeHead(500, {
						"Content-Type": "text/plain"
					});
					resp.write("Internal server error: could not read file"); //can't read error
					resp.end();
					return;
				}
 
				var mimetype = mime.lookup(filename); //get mime type
				resp.writeHead(200, {
					"Content-Type": mimetype
				});
				resp.write(data);
				resp.end();
				return;
			});
		}else{
			
			resp.writeHead(404, {
				"Content-Type": "text/plain"
			});
			resp.write("Requested file not found: "+filename); //404 error
			resp.end();
			return;
		}
	});
});
app.listen(3456);



var io = socketio.listen(app);
io.sockets.on("connection", function(socket){ // This callback runs when a new Socket.IO connection is established.
    
	
    socket.on('message_to_server', function(data) { // This callback runs when the server receives a new message from the client.
       
        var typedCommand = false;
        if (data["user"] == roomCreatorList[socket.room]) {
            var muteMessageMatches = data["message"].match(/\/mute/g); //check for a MUTE command
            if (muteMessageMatches != null) {
                var muteUser = data["message"].split(/\s+/).slice(1,2);
                var muteUserString = muteUser[0];
                if (muteUser != null) {
                    muteRoomList[data["roomName"]].push(muteUserString);
                    typedCommand = true;
                }
            }

            var unmuteMessageMatches = data["message"].match(/\/unmute/g); //check for UNMUTE command
            if (unmuteMessageMatches != null) {
                var unmuteUser = data["message"].split(/\s+/).slice(1,2);
                var unmuteUserString = unmuteUser[0];
                if (unmuteUser != null) {
                    for (var i = 0; i < muteRoomList[data["roomName"]].length; i++) {
                        
                        if (muteRoomList[data["roomName"]][i] == unmuteUserString) {
                            muteRoomList[data["roomName"]].splice(i, 1);
                            typedCommand = true;
                        }
                    }
                }
            }
        }
        
        var privateMessageMatches = data["message"].match(/\/pm/g);//matches get stored in an array in order of appearance
       
        if (privateMessageMatches != null) {
            var recipientUser = data["message"].split(/\s+/).slice(1,2);//matches get stored in an array in order of appearance
           
            
            if (recipientUser != null) {
				var messageBody = data["message"].split(/\/pm\s\w+\s/g).slice(1,2);
                
                for (var i = 0; i < fullSocketList[data['roomName']].length; i++) //find recipient's socket
                {
                    if (fullSocketList[data["roomName"]][i].username == recipientUser || fullSocketList[data["roomName"]][i].username == data["user"]) {
                        fullSocketList[data["roomName"]][i].emit("message_to_client", { user:data["user"], message:"/pm " + messageBody }); //notify recipient
                    }
                }
            }    
        }
        else //its a public message
        {
            var notMuted = true;
            for (var i = 0; i < muteRoomList[data["roomName"]].length; i++) { //check for muted user
                if (data["user"] == muteRoomList[data["roomName"]][i]) {
                    notMuted = false;
                }
            }
            if (notMuted && !typedCommand) {
                io.sockets.in(data["roomName"]).emit("message_to_client", { user:data["user"], message:data["message"] }) // broadcast the message to other users
            }
        }
    });
    
	
    socket.on('username_to_server', function (data) { //received username from client
        socket.join("lobby");
        socket.username = data["user"]; //set socket username
        socket.room = "lobby"; //set socket current room
        fullRoomList.push("lobby"); //add lobby to room list
		
		fullSocketList["lobby"].push(socket);
        fullUserList["lobby"].push(data["user"]);
		
        io.sockets.in("lobby").emit("userList_to_client", {userList:fullUserList["lobby"], room:"lobby", changeRoom:false}); //send username array
        io.sockets.emit("roomList_to_client", {roomList:fullRoomList, thisRoom:"lobby", user:data["user"], creator:roomCreatorList});
    });
	
	socket.on('user_typing', function (data) { //received username from client
		
        for(i = 0; i < fullSocketList[data['roomName']].length; i++) //find recipient's socket
		{
			if (fullSocketList[data["roomName"]][i].username != data["user"]) {
				fullSocketList[data["roomName"]][i].emit("userTyping_to_client", { user:data["user"]}); 
			}
		}
    });
	
	socket.on('user_stopped_typing', function (data) { //received username from client
		
        for(i = 0; i < fullSocketList[data['roomName']].length; i++) //find recipient's socket
		{
			if (fullSocketList[data["roomName"]][i].username != data["user"]) {
				fullSocketList[data["roomName"]][i].emit("userStoppedTyping_to_client", { user:data["user"]}); 
			}
		}
    });
    
	
    socket.on('make_room', function(data) { //listen for when client wants to make a room
		var prevRoom = socket.room;
        socket.leave(socket.room); //leave current room
        socket.join(data["roomName"]); //join newly made room
		
		removeFromArr(fullUserList[socket.room], data["user"]);
		removeFromArr(fullSocketList[socket.room], socket);
		
        socket.room = data["roomName"]; //set current room
		
        fullRoomList.push(data["roomName"]); //add new room to room list
		
		roomCreatorList.push(data["user"]); //add room creator to creator list
		
        fullSocketList[data["roomName"]] = [];
		fullSocketList[data["roomName"]].push(socket);
        fullUserList[data["roomName"]] = [];
        fullUserList[data["roomName"]].push(data["user"]);
		
		passwordRoomList[data["roomName"]] = data["password"];
		permaBanRoomList[data["roomName"]] = []; //initialize the list of permanently banned users
		roomCreatorList[data["roomName"]] = data["user"];
		muteRoomList[data["roomName"]] = []; //initialize list of muted users in a room
		
		io.sockets.in(prevRoom).emit("userList_to_client", {userList:fullUserList[prevRoom], currentName:data["dataUser"], room:prevRoom, changeRoom:false});
        io.sockets.in(data["roomName"]).emit("userList_to_client", {userList:fullUserList[data["roomName"]], currentName:data["user"], room:data["roomName"], changeRoom:true, creator:data["user"]}); 
        io.sockets.emit("roomList_to_client", {roomList:fullRoomList, thisRoom:data["roomName"], user:data["user"], creator:roomCreatorList});
		socket.emit("check_client_creator", {creator:roomCreatorList[data["roomName"]]});
    });
	
	socket.on('get_password', function(data) { //listen for when client wants to change room
		socket.emit("room_password_to_client", {password: passwordRoomList[data["roomName"]], roomName:data["roomName"], username:data["username"]});
	});
	
    socket.on('change_rooms', function(data) { //listen for when client wants to change room
		
		var userAllowedEntry = true;
		var i = 0;
		for(i = 0; i < permaBanRoomList[data["roomName"]].length;i++) //loop through list of banned users for room
		{
			if (data["user"] == permaBanRoomList[data["roomName"]][i]) { //if found, set allowedEntry to false
				userAllowedEntry = false;
			}
		}
		
		if (userAllowedEntry) { //only let them in if they arent permanently banned
			var prevRoom = socket.room;
			socket.leave(socket.room); //leave current room
			socket.join(data["roomName"]); //join new room
			removeFromArr(fullUserList[socket.room], data["user"]);
			removeFromArr(fullSocketList[socket.room], socket);
			
			socket.room = data["roomName"]; //set current room
	
			fullSocketList[data["roomName"]].push(socket);
			fullUserList[data["roomName"]].push(data["user"]);
			
			io.sockets.in(prevRoom).emit("userList_to_client", {userList:fullUserList[prevRoom], currentName:data["dataUser"], room:prevRoom, changeRoom:false});
			io.sockets.in(data["roomName"]).emit("userList_to_client", {userList:fullUserList[data["roomName"]], currentName:data["dataUser"], room:data["roomName"], changeRoom:true});
			io.sockets.emit("roomList_to_client", {roomList:fullRoomList, thisRoom:data["roomName"], user:data["user"], creator:roomCreatorList});
			socket.emit("check_client_creator", {creator:roomCreatorList[data["roomName"]]});
		}
	});
	
	socket.on("kick_person", function(data) { //listen when client wants to kick someone (temporary kick)
		userToKick = data["kickUser"];
		theRoom = socket.room;
		for (var i = 0; i < fullSocketList[theRoom].length; i++) {
			if (userToKick == fullSocketList[theRoom][i].username) {
				fullSocketList[theRoom][i].leave(theRoom); //kick out of this room	
				fullSocketList[theRoom][i].join("lobby"); //go into lobby
				
				fullSocketList["lobby"].push(fullSocketList[theRoom][i]);
				var alreadyIn = false;
				for (var j = 0; j < fullUserList["lobby"].length; j++) { //check if user is in list already
					alreadyIn = true;
				}
				if (!alreadyIn) {
					fullUserList["lobby"].push(fullSocketList[theRoom][i].username); //otherwise add to list
				}
				alreadyIn = false;
				
				removeFromArr(fullUserList[theRoom], fullSocketList[theRoom][i].username);
				fullSocketList[theRoom][i].room = "lobby";
				removeFromArr(fullSocketList[theRoom], fullSocketList[theRoom][i]);
				
				
				io.sockets.in(theRoom).emit("userList_to_client", {userList:fullUserList[theRoom], currentName:data["kickUser"], room:theRoom, changeRoom:false});
				io.sockets.in("lobby").emit("userList_to_client", {userList:fullUserList["lobby"], currentName:data["kickUser"], room:"lobby", changeRoom:true});
				io.sockets.emit("roomList_to_client", {roomList:fullRoomList, thisRoom:"lobby", user:data["kickUser"], creator:roomCreatorList});
			}
		}
	});
	
	socket.on("permaBan_person", function(data) {
		userToPermaBan = data["permaBanUser"];
		
		theRoom = socket.room
		for (var i = 0; i < fullSocketList[theRoom].length; i++) {
			if (userToPermaBan == fullSocketList[theRoom][i].username) {
				
				permaBanRoomList[theRoom].push(userToPermaBan); //add them to list of permanently banned users for room.
				fullSocketList[theRoom][i].leave(theRoom); //kick out of this room	
				fullSocketList[theRoom][i].join("lobby"); //go into lobby
				
				fullSocketList["lobby"].push(fullSocketList[theRoom][i]);
				fullUserList["lobby"].push(fullSocketList[theRoom][i].username);
				
				removeFromArr(fullUserList[theRoom], fullSocketList[theRoom][i].username);
				fullSocketList[theRoom][i].room = "lobby";
				removeFromArr(fullSocketList[theRoom], fullSocketList[theRoom][i]);
				
				io.sockets.in(theRoom).emit("userList_to_client", {userList:fullUserList[theRoom], currentName:data["permaBanUser"], room:theRoom, changeRoom:false});
				io.sockets.in("lobby").emit("userList_to_client", {userList:fullUserList["lobby"], currentName:data["permaBanUser"], room:"lobby", changeRoom:true});
				io.sockets.emit("roomList_to_client", {roomList:fullRoomList, thisRoom:"lobby", user:data["permaBanUser"], creator:roomCreatorList});
			}
		}
	});
	
	socket.on("kick_all", function(data) { //kick everyone from the room at once
		theRoom = socket.room;
		for (var i = 0; i < fullSocketList[theRoom].length; i++) {
			if (socket.username != fullSocketList[theRoom][i].username) {
				
				fullSocketList[theRoom][i].leave(theRoom); //kick out of this room	
				fullSocketList[theRoom][i].join("lobby"); //go into lobby
				
				fullSocketList["lobby"].push(fullSocketList[theRoom][i]);
				fullUserList["lobby"].push(fullSocketList[theRoom][i].username);
				thisUser = fullSocketList[theRoom][i].username;
				removeFromArr(fullUserList[theRoom], fullSocketList[theRoom][i].username);
				fullSocketList[theRoom][i].room = "lobby";
				removeFromArr(fullSocketList[theRoom], fullSocketList[theRoom][i]);
				
				io.sockets.in(theRoom).emit("userList_to_client", {userList:fullUserList[theRoom], currentName:thisUser, room:theRoom, changeRoom:false});
				io.sockets.in("lobby").emit("userList_to_client", {userList:fullUserList["lobby"], currentName:thisUser, room:"lobby", changeRoom:true});
				io.sockets.emit("roomList_to_client", {roomList:fullRoomList, thisRoom:"lobby", user:thisUser, creator:roomCreatorList});
			}
		}
	});
	
	
	socket.on("disconnect", function(data){
		var roomName = socket.room;
		var username = socket.username;
		
		removeFromArr(fullUserList[roomName], username);
		
		io.sockets.emit("userList_to_client", {userList:fullUserList[roomName], currentName:username, room:roomName, changeRoom:false});
	});
});

