using System;
using System.Collections.ObjectModel;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.Mvc;
using Wolf.Models;
using Wolf.Services;
using Wolf.DataModel;

namespace Wolf.Controllers
{
    public class PortfolioController : Controller
    {
        PorfolioOptimizationService _portfolioOptimizationService = new PorfolioOptimizationService();
        DataFetchService _dataFetchService = new DataFetchService();

        [HttpGet]
        public ActionResult Portfolio()
        {
            PortfolioModel portfolioModel = new PortfolioModel();
            return View(portfolioModel);
            
        }

        [HttpPost]
        public ActionResult Portfolio(PortfolioModel portfolioModel)
        {
            portfolioModel.historicalData1 = _dataFetchService.downloadHistoricalData(portfolioModel.symbol1);
            portfolioModel.historicalData2 = _dataFetchService.downloadHistoricalData(portfolioModel.symbol2);
            //use data fetch service to fetch past returns
            //make collections of past returns and add field to quotemodel that can support this
            //IList<Double> percentages = _portfolioOptimizationService.optimizePortfolio(portfolioModel); //figure out what to put in 
            //get list of doubles, set fields, return view
            portfolioModel.optimizedPercentage1 = _dataFetchService.calculateOptimizedPercentage(portfolioModel.historicalData1);   //percentages[0];
            portfolioModel.optimizedPercentage2 = _dataFetchService.calculateOptimizedPercentage(portfolioModel.historicalData2);


            return View(portfolioModel);
        }
    }
}