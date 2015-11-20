using System;
using System.Collections.ObjectModel;
using System.Collections.Generic;
using System.Linq;
using System.Xml.Linq;
using Wolf.DataModel;


namespace Wolf.Services.Interfaces
{
    interface IDataFetchService
    {
         ObservableCollection<QuoteModel> fetchData(ObservableCollection<QuoteModel> quotes);
         ObservableCollection<QuoteModel> ParseData(ObservableCollection<QuoteModel> quotes, XDocument doc);
         IList<HistoricalModel> downloadHistoricalData(string stockSymbol);
         Double calculateOptimizedPercentage(IList<HistoricalModel> historicalData);

    }
}
