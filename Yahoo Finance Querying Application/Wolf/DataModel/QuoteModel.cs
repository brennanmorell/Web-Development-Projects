using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;

namespace Wolf.DataModel
{
    public class QuoteModel
    {
        public string name { get; set; }
        public string exchange { get; set; }
        public string symbol { get; set; }
        public decimal? ask { get; set; }
        public decimal? bid { get; set; }
        public decimal? bookValue { get; set; }
        public decimal? averageDailyVolume { get; set; }
        public decimal? change { get; set; }
        public decimal? dailyHigh { get; set; }
        public decimal? dailyLow { get; set; }
        public DateTime? lastUpdate { get; set; }
    }
}