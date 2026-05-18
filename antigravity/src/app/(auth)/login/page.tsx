'use client';

import { useState, FormEvent, Suspense } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import { motion, AnimatePresence } from 'framer-motion';
import { Eye, EyeOff, LogIn, UserPlus, Loader2, AlertCircle } from 'lucide-react';

/**
 * src/app/(auth)/login/page.tsx
 * ─────────────────────────────────────────────────────────────
 * Professional Glassmorphism Login Interface.
 * Implements smooth tab switching and secure session entry.
 * ─────────────────────────────────────────────────────────────
 */

function LoginForm() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const redirectTo = searchParams.get('redirect') || '/dashboard';

  const [tab, setTab] = useState<'login' | 'register'>('login');
  const [showPass, setShowPass] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');

  const handleLogin = async (e: FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError('');

    try {
      const res = await fetch('/api/auth/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password }),
      });
      const json = await res.json();
      if (!res.ok) throw new Error(json.error || 'Login failed.');
      
      window.location.replace(redirectTo);
    } catch (err: any) {
      setError(err.message);
      setLoading(false);
    }
  };

  const handleRegister = async (e: FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError('');
    setSuccess('');

    try {
      const res = await fetch('/api/auth/register', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name, email, password }),
      });
      const json = await res.json();
      if (!res.ok) throw new Error(json.error || 'Registration failed.');
      
      setSuccess('Account created! Logging you in...');
      setTimeout(() => window.location.replace('/dashboard'), 1200);
    } catch (err: any) {
      setError(err.message);
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center p-4 bg-[#0a0a14]">
      {/* Ambient background glows */}
      <div className="absolute top-[-10%] left-1/2 -translate-x-1/2 w-[600px] h-[600px] rounded-full pointer-events-none opacity-20" style={{ background: 'radial-gradient(circle, #6366f1 0%, transparent 70%)' }} />

      <motion.div
        initial={{ opacity: 0, scale: 0.95 }}
        animate={{ opacity: 1, scale: 1 }}
        className="w-full max-w-md z-10"
      >
        <div className="glass-card p-8 shadow-2xl border-white/10 backdrop-blur-3xl">
          
          <div className="flex flex-col items-center mb-8">
            <div className="w-16 h-16 rounded-2xl flex items-center justify-center text-3xl mb-4 shadow-[0_0_40px_rgba(99,102,241,0.4)]" style={{ background: 'linear-gradient(135deg, #6366f1, #4f46e5)' }}>
              💊
            </div>
            <h1 className="text-2xl font-bold gradient-text tracking-widest">ANTIGRAVITY</h1>
            <p className="text-[10px] text-slate-500 uppercase tracking-[0.4em] mt-1">Intelligence Portal</p>
          </div>

          <div className="flex rounded-xl p-1 mb-6 bg-black/40 border border-white/5">
            <button onClick={() => setTab('login')} className={`flex-1 py-2.5 rounded-lg text-xs font-bold transition-all ${tab === 'login' ? 'bg-brand-500 text-white' : 'text-slate-500'}`}>
              LOG IN
            </button>
            <button onClick={() => setTab('register')} className={`flex-1 py-2.5 rounded-lg text-xs font-bold transition-all ${tab === 'register' ? 'bg-brand-500 text-white' : 'text-slate-500'}`}>
              REGISTER
            </button>
          </div>

          <AnimatePresence mode="popLayout">
            {error && (
              <motion.div initial={{ opacity: 0, y: -10 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0, y: -10 }} className="flex items-center gap-2 p-3 rounded-xl mb-4 text-xs text-red-300 bg-red-500/10 border border-red-500/20">
                <AlertCircle className="w-4 h-4 shrink-0" /> {error}
              </motion.div>
            )}
            {success && (
              <motion.div initial={{ opacity: 0, y: -10 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0, y: -10 }} className="flex items-center gap-2 p-3 rounded-xl mb-4 text-xs text-emerald-300 bg-emerald-500/10 border border-emerald-500/20">
                ✓ {success}
              </motion.div>
            )}
          </AnimatePresence>

          {tab === 'login' ? (
            <form key="login" onSubmit={handleLogin} className="space-y-4">
              <div>
                <label htmlFor="login-email" className="block text-[10px] font-bold text-slate-400 mb-1.5 uppercase tracking-wider">Email Address or Username</label>
                <input id="login-email" type="text" required value={email} onChange={e => setEmail(e.target.value)} placeholder="Email or username..." className="input-field bg-white/5 border-white/10" />
              </div>
              <div>
                <label htmlFor="login-password" className="block text-[10px] font-bold text-slate-400 mb-1.5 uppercase tracking-wider">Password</label>
                <div className="relative">
                  <input id="login-password" type={showPass ? 'text' : 'password'} required value={password} onChange={e => setPassword(e.target.value)} placeholder="••••••••" className="input-field bg-white/5 border-white/10 pr-11" />
                  <button 
                    type="button" 
                    onClick={() => setShowPass(!showPass)} 
                    aria-label={showPass ? "Hide password" : "Show password"}
                    className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500"
                  >
                    {showPass ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                  </button>
                </div>
              </div>
              <button type="submit" disabled={loading} className="btn-primary w-full h-11 mt-2 uppercase text-xs tracking-widest font-bold">
                {loading ? <Loader2 className="w-4 h-4 animate-spin" /> : <LogIn className="w-4 h-4" />}
                {loading ? 'Authenticating...' : 'Sign In'}
              </button>
            </form>
          ) : (
            <form key="register" onSubmit={handleRegister} className="space-y-4">
              <div>
                <label htmlFor="reg-name" className="block text-[10px] font-bold text-slate-400 mb-1.5 uppercase tracking-wider">Full Name</label>
                <input id="reg-name" type="text" required value={name} onChange={e => setName(e.target.value)} placeholder="Dr. Sarah Connor" className="input-field bg-white/5 border-white/10" />
              </div>
              <div>
                <label htmlFor="reg-email" className="block text-[10px] font-bold text-slate-400 mb-1.5 uppercase tracking-wider">Email Address</label>
                <input id="reg-email" type="email" required value={email} onChange={e => setEmail(e.target.value)} placeholder="sarah@antigravity.io" className="input-field bg-white/5 border-white/10" />
              </div>
              <div>
                <label htmlFor="reg-password" className="block text-[10px] font-bold text-slate-400 mb-1.5 uppercase tracking-wider">Password</label>
                <div className="relative">
                  <input id="reg-password" type={showPass ? 'text' : 'password'} required minLength={6} value={password} onChange={e => setPassword(e.target.value)} placeholder="Min. 6 characters" className="input-field bg-white/5 border-white/10 pr-11" />
                  <button 
                    type="button" 
                    onClick={() => setShowPass(!showPass)} 
                    aria-label={showPass ? "Hide password" : "Show password"}
                    className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500"
                  >
                    {showPass ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                  </button>
                </div>
              </div>
              <button type="submit" disabled={loading} className="btn-primary w-full h-11 mt-2 uppercase text-xs tracking-widest font-bold">
                {loading ? <Loader2 className="w-4 h-4 animate-spin" /> : <UserPlus className="w-4 h-4" />}
                {loading ? 'Creating Account...' : 'Register'}
              </button>
            </form>
          )}

        </div>
      </motion.div>
    </div>
  );
}

export default function LoginPage() {
  return (
    <Suspense fallback={<div className="min-h-screen bg-[#0a0a14]" />}>
      <LoginForm />
    </Suspense>
  );
}
