using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using Wolf.DataModel;

namespace Wolf.Models
{
    public class PortfolioModel
    {
        public string symbol1 { get; set; }
        public string symbol2 { get; set; }

        public Double optimizedPercentage1 { get; set; }
        public Double optimizedPercentage2 { get; set; }

        public IList<HistoricalModel> historicalData1 { get; set; }
        public IList<HistoricalModel> historicalData2 { get; set; }
    }
}