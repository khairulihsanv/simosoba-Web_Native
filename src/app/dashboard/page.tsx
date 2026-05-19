import { motion } from 'framer-motion';
import { 
  BarChart3, 
  AlertTriangle, 
  Clock, 
  Package, 
  TrendingUp, 
  AlertCircle,
  Moon,
  Sun
} from 'lucide-react';
import { useEffect, useState } from 'react';

export default function Dashboard() {
  const [stats, setStats] = useState({
    totalMedicines: 0,
    lowStock: 0,
    expiringSoon: 0,
    totalValue: 0
  });
  
  const [chartData, setChartData] = useState({
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
    datasets: [{
      label: 'Stock Levels',
      data: [65, 59, 80, 81, 56, 55],
      backgroundColor: 'rgba(54, 162, 235, 0.5)',
      borderColor: 'rgba(54, 162, 235, 1)',
      borderWidth: 2
    }]
  });
  
  const [alerts, setAlerts] = useState([
    { id: 1, medicine: 'Paracetamol 500mg', issue: 'Low Stock', severity: 'high', time: '2 hours ago' },
    { id: 2, medicine: 'Amoxicillin 250mg', issue: 'Expiring Soon', severity: 'medium', time: '5 hours ago' },
    { id: 3, medicine: 'Ibuprofen 400mg', issue: 'Low Stock', severity: 'high', time: '1 day ago' }
  ]);
  
  const isRainySeason = () => {
    const month = new Date().getMonth(); // 0-11, where 0 is January
    return month >= 9 || month <= 2; // Oct (9) to Mar (2)
  };

  useEffect(() => {
    // Simulate fetching stats from API
    const fetchStats = async () => {
      // In a real app, this would be an API call
      setStats({
        totalMedicines: 124,
        lowStock: 8,
        expiringSoon: 3,
        totalValue: 24500
      });
      
      // Simulate chart data
      setChartData({
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
          label: 'Medication Stock Levels',
          data: [65, 59, 80, 81, 56, 55, 40, 60, 70, 75, 80, 85],
          backgroundColor: 'rgba(54, 162, 235, 0.5)',
          borderColor: 'rgba(54, 162, 235, 1)',
          borderWidth: 2
        }]
      });
    };
    
    fetchStats();
  }, []);

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.8 }}
      className="min-h-screen bg-gradient-to-b from-slate-50 to-slate-100 p-6"
    >
      <header className="mb-8">
        <div className="flex justify-between items-center mb-4">
          <h1 className="text-3xl font-bold text-black">Dashboard</h1>
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
        
        {/* Rainy Season Alert */}
        {isRainySeason() && (
          <div className="border-2 border-black p-4 mb-6 relative">
            <div className="absolute -top-2 -left-2 bg-white w-full h-full border-2 border-yellow-500"></div>
            <div className="relative z-10 flex items-start space-x-3">
              <AlertTriangle className="h-6 w-6 text-yellow-500 mt-0.5" />
              <div>
                <h2 className="font-semibold text-black">Rainy Season Protocol Active</h2>
                <p className="text-gray-700">
                  Increased monitoring for disease prevention medications. Stock levels for 
                  flu, cold, and waterborne disease treatments are being prioritized.
                </p>
              </div>
            </div>
          </div>
        )}
      </header>

      {/* Stats Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div className="border-2 border-black p-6 relative">
          <div className="absolute -top-4 -left-4 bg-white w-full h-full border-2 border-black"></div>
          <div className="relative z-10">
            <div className="flex items-start space-x-3">
              <Package className="h-6 w-6 text-blue-500" />
              <div>
                <h3 className="text-black font-semibold">Total Medications</h3>
                <p className="text-2xl font-bold text-black">{stats.totalMedicines}</p>
                <p className="text-sm text-gray-600">Items in inventory</p>
              </div>
            </div>
          </div>
        </div>
        
        <div className="border-2 border-black p-6 relative">
          <div className="absolute -top-4 -left-4 bg-white w-full h-full border-2 border-black"></div>
          <div className="relative z-10">
            <div className="flex items-start space-x-3">
              <AlertTriangle className="h-6 w-6 text-red-500" />
              <div>
                <h3 className="text-black font-semibold">Low Stock Alerts</h3>
                <p className="text-2xl font-bold text-black">{stats.lowStock}</p>
                <p className="text-sm text-gray-600">Items needing restock</p>
              </div>
            </div>
          </div>
        </div>
        
        <div className="border-2 border-black p-6 relative">
          <div className="absolute -top-4 -left-4 bg-white w-full h-full border-2 border-black"></div>
          <div className="relative z-10">
            <div className="flex items-start space-x-3">
              <AlertCircle className="h-6 w-6 text-orange-500" />
              <div>
                <h3 className="text-black font-semibold">Expiring Soon</h3>
                <p className="text-2xl font-bold text-black">{stats.expiringSoon}</p>
                <p className="text-sm text-gray-600">Within 30 days</p>
              </div>
            </div>
          </div>
        </div>
        
        <div className="border-2 border-black p-6 relative">
          <div className="absolute -top-4 -left-4 bg-white w-full h-full border-2 border-black"></div>
          <div className="relative z-10">
            <div className="flex items-start space-x-3">
              <TrendingUp className="h-6 w-6 text-green-500" />
              <div>
                <h3 className="text-black font-semibold">Inventory Value</h3>
                <p className="text-2xl font-bold text-black">₱{stats.totalValue.toLocaleString()}</p>
                <p className="text-sm text-gray-600">Total stock value</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Charts Section */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        {/* Stock Chart */}
        <div className="border-2 border-black p-6 relative">
          <div className="absolute -top-4 -left-4 bg-white w-full h-full border-2 border-black"></div>
          <div className="relative z-10">
            <h2 className="text-xl font-bold text-black mb-4">Medication Stock Trends</h2>
            <div className="h-96">
              {/* In a real app, we'd use a chart library like Chart.js or Recharts here */}
              <div className="bg-white/50 border-2 border-black p-4 h-full flex items-center justify-center">
                <p className="text-gray-500 text-center">
                  Chart.js would be integrated here to show stock trends over time
                </p>
              </div>
            </div>
          </div>
        </div>
        
        {/* Category Distribution */}
        <div className="border-2 border-black p-6 relative">
          <div className="absolute -top-4 -left-4 bg-white w-full h-full border-2 border-black"></div>
          <div className="relative z-10">
            <h2 className="text-xl font-bold text-black mb-4">Medication Categories</h2>
            <div className="space-y-4">
              <div className="flex items-start space-x-3">
                <div className="h-4 w-4 bg-blue-500 rounded" />
                <div>
                  <span className="font-medium text-black">Analgesics</span>
                  <span className="ml-auto text-gray-600">32 items</span>
                </div>
              </div>
              <div className="flex items-start space-x-3">
                <div className="h-4 w-4 bg-green-500 rounded" />
                <div>
                  <span className="font-medium text-black">Antibiotics</span>
                  <span className="ml-auto text-gray-600">28 items</span>
                </div>
              </div>
              <div className="flex items-start space-x-3">
                <div className="h-4 w-4 bg-yellow-500 rounded" />
                <div>
                  <span className="font-medium text-black">Antipyretics</span>
                  <span className="ml-auto text-gray-600">24 items</span>
                </div>
              </div>
              <div className="flex items-start space-x-3">
                <div className="h-4 w-4 bg-purple-500 rounded" />
                <div>
                  <span className="font-medium text-black">Antihistamines</span>
                  <span className="ml-auto text-gray-600">18 items</span>
                </div>
              </div>
              <div className="flex items-start space-x-3">
                <div className="h-4 w-4 bg-red-500 rounded" />
                <div>
                  <span className="font-medium text-black">Cardiovascular</span>
                  <span className="ml-auto text-gray-600">12 items</span>
                </div>
              </div>
              <div className="flex items-start space-x-3">
                <div className="h-4 w-4 bg-gray-500 rounded" />
                <div>
                  <span className="font-medium text-black">Others</span>
                  <span className="ml-auto text-gray-600">10 items</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Alerts Section */}
      <div className="border-2 border-black p-6 relative">
        <div className="absolute -top-4 -left-4 bg-white w-full h-full border-2 border-black"></div>
        <div className="relative z-10">
          <h2 className="text-xl font-bold text-black mb-4">Recent Alerts</h2>
          <div className="space-y-3">
            {alerts.map(alert => (
              <div key={alert.id} className="border-2 border-black p-4 relative">
                <div className="absolute -top-2 -left-2 bg-white w-full h-full border-2 border-black"></div>
                <div className="relative z-10 flex items-start space-x-3">
                  {alert.severity === 'high' && (
                    <AlertTriangle className="h-5 w-5 text-red-500 mt-0.5" />
                  )}
                  {alert.severity === 'medium' && (
                    <AlertTriangle className="h-5 w-5 text-yellow-500 mt-0.5" />
                  )}
                  {alert.severity === 'low' && (
                    <AlertTriangle className="h-5 w-5 text-blue-500 mt-0.5" />
                  )}
                  <div>
                    <h3 className="font-semibold text-black">{alert.medicine}</h3>
                    <p className="text-sm text-gray-600">{alert.issue}</p>
                    <p className="text-xs text-gray-500">{alert.time}</p>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>

      <footer className="mt-8 text-center text-gray-500">
        <p>
          Antigravity Medication Stock Monitoring System &copy; {new Date().getFullYear()}
        </p>
      </footer>
    </motion.div>
  );
}