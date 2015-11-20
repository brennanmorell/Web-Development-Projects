using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using Wolf.Models;

namespace Wolf.Services.Interfaces
{
    interface IPortfolioOptimizationService
    {
        IList<Double> optimizePercentages(PortfolioModel portfolioModel);
    }
}