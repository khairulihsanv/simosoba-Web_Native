import { motion } from 'framer-motion';
import Link from 'next/link';
import { 
  FileText, 
  Printer, 
  Share2, 
  AlertTriangle,
  Clock,
  BarChart3,
  Sun
} from 'lucide-react';
import { useState } from 'react';

// Define the type for report data
type ReportData = {
  type: string;
  dateRange: string;
  generatedAt: string;
  data: {
    stock: {
      totalItems: number;
      lowStock: number;
      expiringSoon: number;
      totalValue: number;
    };
    usage: {
      totalDispensed: number;
      avgDailyUsage: number;
      peakUsageDay: string;
    };
    expiry: {
      expiringThisMonth: number;
      expiringNextMonth: number;
      expiredLastMonth: number;
    };
  };
};

export default function ReportsPage() {
  const [reportType, setReportType] = useState('stock');
  const [dateRange, setDateRange] = useState('month');
  const [loading, setLoading] = useState(false);
  const [reportData, setReportData] = useState<ReportData | null>(null);
  
  const isRainySeason = () => {
    const month = new Date().getMonth(); // 0-11, where 0 is January
    return month >= 9 || month <= 2; // Oct (9) to Mar (2)
  };

  const generateReport = async () => {
    setLoading(true);
    try {
      // Simulate API call
      await new Promise(resolve => setTimeout(resolve, 1500));
      
      // Mock report data
      setReportData({
        type: reportType,
        dateRange,
        generatedAt: new Date().toLocaleString(),
        data: {
          stock: {
            totalItems: 124,
            lowStock: 8,
            expiringSoon: 3,
            totalValue: 24500
          },
          usage: {
            totalDispensed: 450,
            avgDailyUsage: 15,
            peakUsageDay: 'Wednesday'
          },
          expiry: {
            expiringThisMonth: 3,
            expiringNextMonth: 7,
            expiredLastMonth: 1
          }
        }
      });
    } catch (error) {
      console.error('Error generating report:', error);
    } finally {
      setLoading(false);
    }
  };

  // WhatsApp share link format:
  // https://wa.me/?text=Antigravity%20Medication%20Stock%20Report%0A%0A
  //   Report%20Type:%20Stock%20Summary%0A
  //   Generated:%20May%2019,%202026%0A
  //   Total%20Medications:%20124%20items%0A
  //   Low%20Stock:%208%20items%0A
  //   Expiring%20Soon:%203%20items%0A
  //   Total%20Value:%20₱24,500%0A%0A
  //   Share%20this%20report%20to%20keep%20your%20team%20informed!
  const getWhatsAppShareLink = () => {
    if (!reportData) return '';
    
    const text = encodeURIComponent(
      `Antigravity Medication Stock Report%n%n` +
      `Report Type: ${reportData.type.charAt(0).toUpperCase() + reportData.type.slice(1)} Summary%n` +
      `Generated: ${reportData.generatedAt}%n` +
      `Total Medications: ${reportData.data.stock.totalItems} items%n` +
      `Low Stock: ${reportData.data.stock.lowStock} items%n` +
      `Expiring Soon: ${reportData.data.stock.expiringSoon} items%n` +
      `Total Value: ₱${reportData.data.stock.totalValue.toLocaleString()}%n%n` +
      `Share this report to keep your team informed!`
    );
    
    return `https://wa.me/?text=${text}`;
  };

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.8 }}
      className="min-h-screen bg-gradient-to-b from-slate-50 to-slate-100 p-6"
    >
      <header className="mb-8">
        <div className="flex justify-between items-center mb-4">
          <h1 className="text-3xl font-bold text-black">Reports & Analytics</h1>
          <div className="flex items-center space-x-3">
            <span className="text-sm text-gray-600">
              Last updated: {new Date().toLocaleTimeString()}
            </span>
            <div className="flex items-center space-x-2">
              {isRainySeason() ? (
                <AlertTriangle className="h-5 w-5 text-yellow-500 animate-pulse" />
              ) : (
                <Sun className="h-5 w-5 text-yellow-500" />
              )}
              <span className="text-sm font-medium text-black">
                {isRainySeason() ? 'Rainy Season Protocol Active' : 'Normal Operations'}
              </span>
            </div>
          </div>
        </div>
      </header>

      {/* Report Controls */}
      <div className="border-2 border-black p-6 mb-6 relative">
        <div className="absolute -top-4 -left-4 bg-white w-full h-full border-2 border-black"></div>
        <div className="relative z-10">
          <h2 className="text-xl font-bold text-black mb-4">Generate Report</h2>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
              <label className="block text-black font-medium mb-2">Report Type</label>
              <select
                value={reportType}
                onChange={(e) => setReportType(e.target.value)}
                className="border-2 border-black p-2 w-full rounded-none focus:outline-none focus:ring-2 focus:ring-black"
                aria-label="Report type"
                disabled={loading}
              >
                <option value="stock">Stock Summary</option>
                <option value="usage">Usage Analytics</option>
                <option value="expiry">Expiry Tracking</option>
                <option value="transactions">Transaction Log</option>
              </select>
            </div>
            
            <div>
              <label className="block text-black font-medium mb-2">Date Range</label>
              <select
                value={dateRange}
                onChange={(e) => setDateRange(e.target.value)}
                className="border-2 border-black p-2 w-full rounded-none focus:outline-none focus:ring-2 focus:ring-black"
                aria-label="Date range"
                disabled={loading}
              >
                <option value="week">Last Week</option>
                <option value="month">Last Month</option>
                <option value="quarter">Last Quarter</option>
                <option value="year">Last Year</option>
                <option value="custom">Custom Range</option>
              </select>
            </div>
            
            <div className="flex items-end">
              <button
                onClick={generateReport}
                disabled={loading}
                className={`px-6 py-3 font-semibold text-black border-2 border-black ${
                  loading ? 'bg-gray-200 cursor-not-allowed' : 'hover:bg-white hover:text-black transition-all duration-300 relative'
                }`}
              >
                <div className="absolute -top-2 -left-2 bg-white w-full h-full border-2 border-black"></div>
                <span className="relative z-10">
                  {loading ? 'Generating...' : 'Generate Report'}
                </span>
              </button>
            </div>
          </div>
        </div>
      </div>

      {/* Report Display */}
      {reportData && (
        <div className="border-2 border-black p-6 relative">
          <div className="absolute -top-4 -left-4 bg-white w-full h-full border-2 border-black"></div>
          <div className="relative z-10">
            <h2 className="text-xl font-bold text-black mb-4">
              {reportData.type.charAt(0).toUpperCase() + reportData.type.slice(1)} Report
            </h2>
            <div className="space-y-4">
              <div className="text-sm text-gray-600">
                <strong>Generated:</strong> {reportData.generatedAt}
              </div>
              
              <div className="space-y-2">
                <h3 className="font-semibold text-black mb-2">Key Metrics</h3>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div className="border-2 border-black p-3">
                    <div className="flex items-start space-x-2">
                      <FileText className="h-5 w-5 text-blue-500 mt-0.5" />
                      <div>
                        <p className="text-black font-medium">Total Medications</p>
                        <p className="text-2xl font-bold">{reportData.data.stock.totalItems}</p>
                      </div>
                    </div>
                  </div>
                  <div className="border-2 border-black p-3">
                    <div className="flex items-start space-x-2">
                      <AlertTriangle className="h-5 w-5 text-red-500 mt-0.5" />
                      <div>
                        <p className="text-black font-medium">Low Stock Alerts</p>
                        <p className="text-2xl font-bold">{reportData.data.stock.lowStock}</p>
                      </div>
                    </div>
                  </div>
                  <div className="border-2 border-black p-3">
                    <div className="flex items-start space-x-2">
                      <AlertTriangle className="h-5 w-5 text-orange-500 mt-0.5" />
                      <div>
                        <p className="text-black font-medium">Expiring Soon</p>
                        <p className="text-2xl font-bold">{reportData.data.stock.expiringSoon}</p>
                      </div>
                    </div>
                  </div>
                  <div className="border-2 border-black p-3">
                    <div className="flex items-start space-x-2">
                      <BarChart3 className="h-5 w-5 text-green-500 mt-0.5" />
                      <div>
                        <p className="text-black font-medium">Inventory Value</p>
                        <p className="text-2xl font-bold">₱{reportData.data.stock.totalValue.toLocaleString()}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Share Section */}
      {reportData && (
        <div className="border-2 border-black p-6 mt-6 relative">
          <div className="absolute -top-4 -left-4 bg-white w-full h-full border-2 border-black"></div>
          <div className="relative z-10">
            <h2 className="text-xl font-bold text-black mb-4">Share Report</h2>
            <div className="space-y-4">
              <p className="text-gray-600">
                Share this report instantly with your team via WhatsApp or download as PDF for
                offline sharing and printing.
              </p>
              
              <div className="flex flex-col sm:flex-row sm:space-y-0 sm:space-x-4">
                <a
                  href={getWhatsAppShareLink()}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="w-full sm:w-auto px-6 py-3 bg-green-500 text-white border-2 border-black hover:bg-green-600 transition-all duration-300 relative"
                >
                  <div className="absolute -top-2 -left-2 bg-white w-full h-full border-2 border-black"></div>
                  <span className="relative z-10 flex items-center space-x-2">
                    <Share2 className="h-5 w-5" />
                    Share via WhatsApp
                  </span>
                </a>
                
                <button
                  onClick={() => window.print()}
                  className="w-full sm:w-auto px-6 py-3 bg-black text-white border-2 border-black hover:bg-white hover:text-black transition-all duration-300 relative"
                >
                  <div className="absolute -top-2 -left-2 bg-white w-full h-full border-2 border-black"></div>
                  <span className="relative z-10 flex items-center space-x-2">
                    <Printer className="h-5 w-5" />
                    Download PDF
                  </span>
                </button>
              </div>
            </div>
          </div>
        </div>
      )}

      <footer className="mt-8 text-center text-gray-500">
        <p>
          Antigravity Medication Stock Monitoring System &copy; {new Date().getFullYear()}
        </p>
      </footer>
    </motion.div>
  );
}