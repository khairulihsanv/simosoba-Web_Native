import { useState } from 'react';
import { motion } from 'framer-motion';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { 
  LogIn, 
  UserPlus, 
  Mail, 
  Lock, 
  CheckCircle, 
  X 
} from 'lucide-react';
import { z } from 'zod';

// Validation schemas
const loginSchema = z.object({
  email: z.string().email('Invalid email address'),
  password: z.string().min(6, 'Password must be at least 6 characters'),
});

const registerSchema = z.object({
  name: z.string().min(2, 'Name must be at least 2 characters'),
  email: z.string().email('Invalid email address'),
  password: z.string().min(6, 'Password must be at least 6 characters'),
  confirmPassword: z.string().min(6, 'Password must be at least 6 characters'),
}).refine((data) => data.password === data.confirmPassword, {
  message: "Passwords don't match",
  path: ["confirmPassword"],
});

export default function LoginPage() {
  const [isLogin, setIsLogin] = useState(true);
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [name, setName] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState<string | null>(null);
  const router = useRouter();

  const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError(null);
    setSuccess(null);
    
    try {
      // Validate with Zod
      loginSchema.parse({ email, password });
      
      // Simulate API call
      await new Promise(resolve => setTimeout(resolve, 1000));
      
      // In a real app, you would call your auth API here
      // For now, we'll simulate successful login
      setSuccess('Login successful!');
      setTimeout(() => {
        router.push('/dashboard');
      }, 1500);
    } catch (err: any) {
      setError(err.message || 'Login failed');
    } finally {
      setLoading(false);
    }
  };

  const handleRegister = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError(null);
    setSuccess(null);
    
    try {
      // Validate with Zod
      registerSchema.parse({ name, email, password, confirmPassword });
      
      // Simulate API call
      await new Promise(resolve => setTimeout(resolve, 1500));
      
      // In a real app, you would call your auth API here
      setSuccess('Registration successful! Please login.');
      setIsLogin(true);
      // Clear form
      setName('');
      setEmail('');
      setPassword('');
      setConfirmPassword('');
    } catch (err: any) {
      setError(err.message || 'Registration failed');
    } finally {
      setLoading(false);
    }
  };

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.8 }}
      className="min-h-screen bg-gradient-to-b from-slate-50 to-slate-100 p-6"
    >
      <div className="max-w-md mx-auto">
        <header className="text-center mb-8">
          <h1 className="text-3xl font-bold text-black mb-4">
            Antigravity Medication Stock
          </h1>
          <p className="text-gray-600">
            {isLogin ? 'Welcome back' : 'Create your account'}
          </p>
        </header>

        {/* Glassmorphism Card */}
        <motion.div
          whileHover={{ scale: 1.02 }}
          whileTap={{ scale: 0.98 }}
          className="border-2 border-black p-8 relative backdrop-blur-lg bg-white/20"
        >
          {/* Neo-Brutalism offset */}
          <div className="absolute -top-4 -left-4 bg-white w-full h-full border-2 border-black"></div>
          <div className="relative z-10">
            {/* Form Tabs */}
            <div className="flex mb-6">
              <button
                onClick={() => setIsLogin(true)}
                className={`flex-1 px-4 py-3 font-semibold text-black ${
                  isLogin ? 'border-2 border-black bg-white' : 'border-2 border-black bg-transparent'
                } relative`}
              >
                <div className="absolute -top-2 -left-2 bg-white w-full h-full border-2 border-black"></div>
                <span className="relative z-10">Login</span>
              </button>
              <button
                onClick={() => setIsLogin(false)}
                className={`flex-1 px-4 py-3 font-semibold text-black ${
                  !isLogin ? 'border-2 border-black bg-white' : 'border-2 border-black bg-transparent'
                } relative`}
              >
                <div className="absolute -top-2 -left-2 bg-white w-full h-full border-2 border-black"></div>
                <span className="relative z-10">Register</span>
              </button>
            </div>

            {/* Error Message */}
            {error && (
              <div className="mb-4 p-3 bg-red-50 border-2 border-red-500 text-red-700 relative">
                <div className="absolute -top-2 -left-2 bg-white w-full h-full border-2 border-red-500"></div>
                <span className="relative z-10">{error}</span>
              </div>
            )}

            {/* Success Message */}
            {success && (
              <div className="mb-4 p-3 bg-green-50 border-2 border-green-500 text-green-700 relative">
                <div className="absolute -top-2 -left-2 bg-white w-full h-full border-2 border-green-500"></div>
                <span className="relative z-10">{success}</span>
              </div>
            )}

            <form onSubmit={isLogin ? handleLogin : handleRegister} className="space-y-4">
              {!isLogin && (
                <div>
                  <label className="flex items-start space-x-3">
                    <UserPlus className="h-5 w-5 text-gray-500 mt-0.5" />
                    <div>
                      <label className="text-black font-medium mb-1">Full Name</label>
                      <input
                        type="text"
                        value={name}
                        onChange={(e) => setName(e.target.value)}
                        placeholder="Enter your full name"
                        className="border-2 border-black p-2 w-full rounded-none focus:outline-none focus:ring-2 focus:ring-black"
                        disabled={loading}
                      />
                    </div>
                  </label>
                </div>
              )}

              <div>
                <label className="flex items-start space-x-3">
                  <Mail className="h-5 w-5 text-gray-500 mt-0.5" />
                  <div>
                    <label className="text-black font-medium mb-1">Email Address</label>
                    <input
                      type="email"
                      value={email}
                      onChange={(e) => setEmail(e.target.value)}
                      placeholder="Enter your email"
                      className="border-2 border-black p-2 w-full rounded-none focus:outline-none focus:ring-2 focus:ring-black"
                      disabled={loading}
                    />
                  </div>
                </div>
              </div>

              <div>
                <label className="flex items-start space-x-3">
                  <Lock className="h-5 w-5 text-gray-500 mt-0.5" />
                  <div>
                    <label className="text-black font-medium mb-1">Password</label>
                    <input
                      type="password"
                      value={password}
                      onChange={(e) => setPassword(e.target.value)}
                      placeholder="Enter your password"
                      className="border-2 border-black p-2 w-full rounded-none focus:outline-none focus:ring-2 focus:ring-black"
                      disabled={loading}
                    />
                  </div>
                </div>
              </div>

              {isLogin ? null : (
                <div>
                  <label className="flex items-start space-x-3">
                    <Lock className="h-5 w-5 text-gray-500 mt-0.5" />
                    <div>
                      <label className="text-black font-medium mb-1">Confirm Password</label>
                      <input
                        type="password"
                        value={confirmPassword}
                        onChange={(e) => setConfirmPassword(e.target.value)}
                        placeholder="Confirm your password"
                        className="border-2 border-black p-2 w-full rounded-none focus:outline-none focus:ring-2 focus:ring-black"
                        disabled={loading}
                      />
                    </div>
                  </label>
                </div>
              )}

              <button
                type="submit"
                disabled={loading}
                className={`w-full px-6 py-3 font-semibold text-black border-2 border-black ${
                  loading ? 'bg-gray-200 cursor-not-allowed' : 'hover:bg-white hover:text-black transition-all duration-300 relative'
                }`}
              >
                <div className="absolute -top-2 -left-2 bg-white w-full h-full border-2 border-black"></div>
                <span className="relative z-10">
                  {loading ? 'Processing...' : isLogin ? 'Login' : 'Register'}
                </span>
              </button>
            </form>

            <div className="mt-4 text-center text-sm text-gray-600">
              {isLogin ? (
                <>
                  Don't have an account?{' '}
                  <button
                    onClick={() => setIsLogin(false)}
                    className="font-semibold text-black hover:underline"
                  >
                    Sign up
                  </button>
                </>
              ) : (
                <>
                  Already have an account?{' '}
                  <button
                    onClick={() => setIsLogin(true)}
                    className="font-semibold text-black hover:underline"
                  >
                    Login
                  </button>
                </>
              )}
            </div>
          </div>
        </div>

        <footer className="mt-8 text-center text-gray-500">
          <p>
            By continuing, you agree to our Terms of Service and Privacy Policy
          </p>
        </footer>
      </div>
    </motion.div>
  );
}