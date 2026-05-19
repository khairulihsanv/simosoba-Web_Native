import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { 
  Menu, 
  X, 
  Home, 
  BarChart3, 
  FileText, 
  User, 
  LogOut 
} from 'lucide-react';

export default function Navbar() {
  const pathname = usePathname();

  return (
    <nav className="border-2 border-black">
      {/* Neo-Brutalism offset */}
      <div className="absolute -top-4 -left-4 bg-white w-full h-full border-2 border-black"></div>
      <div className="relative z-10">
        <div className="hidden md:flex items-center justify-between px-6 py-3">
          {/* Left Side - Logo & Nav Links */}
          <div className="flex items-center space-x-6">
            <Link href="/" className="flex items-center space-x-2">
              <Home className="h-5 w-5 text-black" />
              <span className="font-bold text-black text-lg">Antigravity</span>
            </Link>
            
            <div className="hidden md:flex space-x-4">
              <Link
                href="/"
                className={`px-3 py-2 font-medium text-black rounded border-2 border-black ${
                  pathname === '/' ? 'bg-white' : 'bg-transparent'
                } hover:bg-white hover:text-black transition-all duration-300 relative`}
              >
                <div className="absolute -top-2 -left-2 bg-white w-full h-full border-2 border-black"></div>
                <span className="relative z-10">Home</span>
              </Link>
              
              <Link
                href="/dashboard"
                className={`px-3 py-2 font-medium text-black rounded border-2 border-black ${
                  pathname === '/dashboard' ? 'bg-white' : 'bg-transparent'
                } hover:bg-white hover:text-black transition-all duration-300 relative`}
              >
                <div className="absolute -top-2 -left-2 bg-white w-full h-full border-2 border-black"></div>
                <span className="relative z-10">Dashboard</span>
              </Link>
              
              <Link
                href="/reports"
                className={`px-3 py-2 font-medium text-black rounded border-2 border-black ${
                  pathname === '/reports' ? 'bg-white' : 'bg-transparent'
                } hover:bg-white hover:text-black transition-all duration-300 relative`}
              >
                <div className="absolute -top-2 -left-2 bg-white w-full h-full border-2 border-black"></div>
                <span className="relative z-10">Reports</span>
              </Link>
              
              <Link
                href="/admin"
                className={`px-3 py-2 font-medium text-black rounded border-2 border-black ${
                  pathname.startsWith('/admin') ? 'bg-white' : 'bg-transparent'
                } hover:bg-white hover:text-black transition-all duration-300 relative`}
              >
                <div className="absolute -top-2 -left-2 bg-white w-full h-full border-2 border-black"></div>
                <span className="relative z-10">Admin</span>
              </Link>
            </div>
          </div>
          
          {/* Right Side - Auth & Menu */}
          <div className="flex items-center space-x-4">
            <Link
              href="/login"
              className="px-4 py-2 font-medium text-black border-2 border-black rounded hover:bg-white hover:text-black transition-all duration-300 relative"
            >
              <div className="absolute -top-2 -left-2 bg-white w-full h-full border-2 border-black"></div>
              <span className="relative z-10">Login</span>
            </Link>
            
            <button
              className="p-2 text-black hover:bg-white hover:text-black transition-all duration-300 relative"
              aria-label="User profile"
            >
              <div className="absolute -top-2 -left-2 bg-white w-full h-full border-2 border-black"></div>
              <span className="relative z-10">
                <User className="h-5 w-5" />
              </span>
            </button>
          </div>
        </div>
        
        {/* Mobile Menu */}
        <div className="md:hidden px-4 py-3">
          <div className="flex justify-between items-center">
            <div className="flex items-center space-x-2">
              <Home className="h-5 w-5 text-black" />
              <span className="font-bold text-black text-lg">Antigravity</span>
            </div>
            
            <button
              id="mobile-menu-button"
              className="p-2 text-black hover:bg-white hover:text-black transition-all duration-300 relative"
              aria-label="Open mobile menu"
            >
              <div className="absolute -top-2 -left-2 bg-white w-full h-full border-2 border-black"></div>
              <span className="relative z-10" id="mobile-menu-icon">
                <Menu className="h-5 w-5" />
              </span>
            </button>
          </div>
          
          {/* Mobile Menu Dropdown */}
          <div id="mobile-menu" className="hidden mt-4 space-y-2">
            <Link
              href="/"
              className="block px-4 py-3 font-medium text-black border-2 border-black rounded hover:bg-white hover:text-black transition-all duration-300 relative"
            >
              <div className="absolute -top-2 -left-2 bg-white w-full h-full border-2 border-black"></div>
              <span className="relative z-10">Home</span>
            </Link>
            
            <Link
              href="/dashboard"
              className="block px-4 py-3 font-medium text-black border-2 border-black rounded hover:bg-white hover:text-black transition-all duration-300 relative"
            >
              <div className="absolute -top-2 -left-2 bg-white w-full h-full border-2 border-black"></div>
              <span className="relative z-10">Dashboard</span>
            </Link>
            
            <Link
              href="/reports"
              className="block px-4 py-3 font-medium text-black border-2 border-black rounded hover:bg-white hover:text-black transition-all duration-300 relative"
            >
              <div className="absolute -top-2 -left-2 bg-white w-full h-full border-2 border-black"></div>
              <span className="relative z-10">Reports</span>
            </Link>
            
            <Link
              href="/admin"
              className="block px-4 py-3 font-medium text-black border-2 border-black rounded hover:bg-white hover:text-black transition-all duration-300 relative"
            >
              <div className="absolute -top-2 -left-2 bg-white w-full h-full border-2 border-black"></div>
              <span className="relative z-10">Admin</span>
            </Link>
            
            <div className="border-t-2 border-black pt-4">
              <Link
                href="/login"
                className="block px-4 py-3 font-medium text-black border-2 border-black rounded hover:bg-white hover:text-black transition-all duration-300 relative"
              >
                <div className="absolute -top-2 -left-2 bg-white w-full h-full border-2 border-black"></div>
                <span className="relative z-10">Login</span>
              </Link>
              
              <button
                className="block w-full text-left px-4 py-3 font-medium text-black border-2 border-black rounded hover:bg-white hover:text-black transition-all duration-300 relative"
              >
                <div className="absolute -top-2 -left-2 bg-white w-full h-full border-2 border-black"></div>
                <span className="relative z-10">
                  <LogOut className="h-5 w-5 mr-2" /> Logout
                </span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </nav>
  );
}