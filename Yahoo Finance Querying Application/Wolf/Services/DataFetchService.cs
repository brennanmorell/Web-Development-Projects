using System;
using System.Collections.ObjectModel;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Net;
using System.Xml.Linq;
using Wolf.DataModel;

namespace Wolf.Services
{
    public class DataFetchService
    {
        private const string BASE_URL = "https://query.yahooapis.com/v1/public/yql?q=" + "select%20*%20from%20yahoo.finance.quote%20where%20symbol%20%3D%20";
        //historical string  http://query.yahooapis.com/v1/public/yql?q=select * from yahoo.finance.historicaldata where symbol = "YHOO" and startDate = "2014-02-11" and endDate = "2014-02-18"&diagnostics=true&env=store://datatables.org/alltableswithkeys                       
        

        public ObservableCollection<QuoteModel> fetchData(ObservableCollection<QuoteModel> quotes)
        {
            

            string url = BASE_URL + "%22" + quotes.First().symbol + "%22" + "&diagnostics=true&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys";

            XDocument doc = XDocument.Load(url);
            return ParseData(quotes, doc);
        }

        public ObservableCollection<QuoteModel> ParseData(ObservableCollection<QuoteModel> quotes, XDocument doc)
        {
            XElement results = doc.Root.Element("results");

            foreach (QuoteModel quote in quotes) 
            {

                XElement q = results.Elements("quote").First(w => w.Attribute("symbol").Value == quote.symbol);
                quote.name = q.Element("Name").Value;
                quote.exchange = q.Element("StockExchange").Value;
                quote.averageDailyVolume = GetDecimal(q.Element("AverageDailyVolume").Value);
                quote.change = GetDecimal(q.Element("Change").Value);
                quote.dailyHigh = GetDecimal(q.Element("DaysHigh").Value);
                quote.dailyLow = GetDecimal(q.Element("DaysLow").Value);

            }

            return quotes;

        }

        public IList<HistoricalModel> downloadHistoricalData(string stockSymbol)
        {
            IList<HistoricalModel> historicalData = new List<HistoricalModel>();

            using (WebClient web = new WebClient())
            {
                string data = web.DownloadString(string.Format("http://ichart.finance.yahoo.com/table.csv?s={0}&c={1}", stockSymbol, 2000));

                data = data.Replace("r", "");

                string[] rows = data.Split('\n');

                //First row is headers so Ignore it
                for (int i = 1; i < rows.Length; i++)
                {
                    if (rows[i].Replace("n", "").Trim() == "") continue;

                    string[] cols = rows[i].Split(',');

                    
                    HistoricalModel hs = new HistoricalModel();
                    Double output;
                    if (Double.TryParse(cols[1], out output) && Double.TryParse(cols[4], out output))
                    {
                        hs.Date = Convert.ToDateTime(cols[0]);
                        hs.Open = Convert.ToDouble(cols[1]);
                       //hs.High = Convert.ToDouble(cols[2]);
                       // hs.Low = Convert.ToDouble(cols[3]);
                        hs.Close = Convert.ToDouble(cols[4]);
                       // hs.Volume = Convert.ToDouble(cols[5]);
                       // hs.AdjClose = Convert.ToDouble(cols[6]);

                        historicalData.Add(hs);
                    }
                }

                return historicalData;
            }
        }

        public Double calculateOptimizedPercentage(IList<HistoricalModel> historicalData)
        {
            IList<Double> dailyChanges = new List<Double>();
            Double optimizedPercentage = 0.0;

            foreach(HistoricalModel h in historicalData)
            {
                Double dailyPercentageChange = ((h.Open-h.Close)/h.Open);
                dailyChanges.Add(dailyPercentageChange);
            }

            if (dailyChanges.Count() > 0)
            {
                Double sumPercentChanges = 0;
                foreach (Double percentChange in dailyChanges)
                {
                    sumPercentChanges = sumPercentChanges + percentChange;
                }

                optimizedPercentage = (sumPercentChanges / dailyChanges.Count()) * 100;

            }
            
            return optimizedPercentage;
            
        }
        

        private static decimal? GetDecimal(string input)
        {
            if (input == null) return null;

            input = input.Replace("%", "");

            decimal value;

            if (Decimal.TryParse(input, out value)) return value;
            return null;
        }

        private static DateTime? GetDateTime(string input)
        {
            if (input == null) return null;

            DateTime value;

            if (DateTime.TryParse(input, out value)) return value;
            return null;
        }
    }
}