using System;
using System.Collections.ObjectModel;
using System.Linq;
using System.Web;
using Wolf.DataModel;

namespace Wolf.Models
{
    public class QuoteListViewModel
    {
        public ObservableCollection<QuoteModel> list { get; set; }
    }
}