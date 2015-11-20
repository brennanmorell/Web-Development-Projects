using System;
using System.Collections.ObjectModel;
using System.Linq;
using System.Web;
using System.Web.Mvc;
using Wolf.Models;
using Wolf.Services;
using Wolf.DataModel;

namespace Wolf.Controllers
{

    public class SearchController : Controller
    {
        DataFetchService _dataFetchService = new DataFetchService();

        [HttpGet]
        public ActionResult Search()
        {
            SearchModel searchModel = new SearchModel();
            searchModel.symbolSearch = "";
            return View(searchModel);
        }

        [HttpPost]
        public ActionResult Search(SearchModel searchModel, string submit)
        {
            if (submit == "Search")
            {
                ObservableCollection<QuoteModel> quotes = new ObservableCollection<QuoteModel>();
                QuoteModel q = new QuoteModel();
                q.symbol = searchModel.symbolSearch;
                quotes.Add(q);
                quotes = _dataFetchService.fetchData(quotes);
                TempData["quotes"] = quotes;
                return RedirectToAction("Results");
            }
            else
                return View(); //just to make the compiler happy
        }

        [HttpGet]
        public ActionResult Results()
        {
            QuoteListViewModel qlModel = new QuoteListViewModel();
            qlModel.list = (ObservableCollection<QuoteModel>)TempData["quotes"];
            return View(qlModel);
        }
    }
}