import { motion } from 'framer-motion';
import Link from 'next/link';
import { Sun, Moon, CloudRain, Zap } from 'lucide-react';

export default function Home() {
  const isRainySeason = () => {
    const month = new Date().getMonth(); // 0-11, where 0 is January
    return month >= 9 || month <= 2; // Oct (9) to Mar (2)
  };

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.8 }}
      className="min-h-screen bg-gradient-to-b from-slate-50 to-slate-100 p-6"
    >
      <header className="text-center mb-12">
        <h1 className="text-4xl font-bold text-black mb-4">
          Antigravity Medication Stock
        </h1>
        <p className="text-lg text-gray-600">
          Monitor and manage your medication inventory with real-time alerts
        </p>
      </header>

      <main className="space-y-12">
        {/* Features Section */}
        <section className="space-y-8">
          <div className="border-2 border-black p-6 relative">
            {/* Neo-Brutalism style: 2px sharp black border, 4px offset */}
            <div className="absolute -top-4 -left-4 bg-white w-full h-full border-2 border-black"></div>
            <div className="relative z-10">
              <h2 className="text-2xl font-bold text-black mb-4">
                Key Features
              </h2>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div className="flex items-start space-x-4">
                  <motion.div
                    whileHover={{ scale: 1.1 }}
                    whileTap={{ scale: 0.9 }}
                  >
                    <Sun className="h-6 w-6 text-yellow-500" />
                  </motion.div>
                  <div>
                    <h3 className="font-semibold text-black">Real-time Dashboard</h3>
                    <p className="text-gray-600">
                      View medication stock levels, expiry dates, and usage patterns
                    </p>
                  </div>
                </div>
                <div className="flex items-start space-x-4">
                  <motion.div
                    whileHover={{ scale: 1.1 }}
                    whileTap={{ scale: 0.9 }}
                  >
                    <CloudRain className="h-6 w-6 text-blue-500" />
                  </motion.div>
                  <div>
                    <h3 className="font-semibold text-black">Rainy Season Protocol</h3>
                    <p className="text-gray-600">
                      Automatic alerts for increased disease prevention needs during
                      {isRainySeason() ? 'active' : 'off'} season
                    </p>
                  </div>
                </div>
                <div className="flex items-start space-x-4">
                  <motion.div
                    whileHover={{ scale: 1.1 }}
                    whileTap={{ scale: 0.9 }}
                  >
                    <Zap className="h-6 w-6 text-red-500" />
                  </motion.div>
                  <div>
                    <h3 className="font-semibold text-black">Expiry Alerts</h3>
                    <p className="text-gray-600">
                      Get notified before medications expire to reduce waste
                    </p>
                  </div>
                </div>
                <div className="flex items-start space-x-4">
                  <motion.div
                    whileHover={{ scale: 1.1 }}
                    whileTap={{ scale: 0.9 }}
                  >
                    <Link href="/reports" className="flex items-center space-x-2">
                      <a className="text-blue-600 hover:underline">
                        Generate Reports
                      </a>
                    </Link>
                  </motion.div>
                  <div>
                    <h3 className="font-semibold text-black">PDF & WhatsApp Reports</h3>
                    <p className="text-gray-600">
                      Share stock reports instantly via WhatsApp or download as PDF
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {/* Call to Action */}
          <div className="border-2 border-black p-6 relative">
            <div className="absolute -top-4 -left-4 bg-white w-full h-full border-2 border-black"></div>
            <div className="relative z-10 text-center">
              <h2 className="text-2xl font-bold text-black mb-6">
                Ready to take control of your medication inventory?
              </h2>
              <div className="flex flex-col sm:flex-row sm:justify-center space-y-4 sm:space-y-0 sm:space-x-4">
                <Link
                  href="/login"
                  className="flex-1 px-6 py-3 bg-black text-white border-2 border-black hover:bg-white hover:text-black transition-all duration-300 relative"
                >
                  <div className="absolute -top-2 -left-2 bg-white w-full h-full border-2 border-black"></div>
                  <span className="relative z-10">Get Started</span>
                </Link>
                <Link
                  href="/dashboard"
                  className="flex-1 px-6 py-3 border-2 border-black text-black hover:bg-black hover:text-white transition-all duration-300 relative"
                >
                  <div className="absolute -top-2 -left-2 bg-white w-full h-full border-2 border-black"></div>
                  <span className="relative z-10">View Dashboard</span>
                </Link>
              </div>
            </div>
          </div>
        </section>
      </main>

      <footer className="text-center text-gray-500 mt-12">
        <p>
          Antigravity Medication Stock Monitoring System &copy; {new Date().getFullYear()}
        </p>
      </footer>
    </motion.div>
  );
}