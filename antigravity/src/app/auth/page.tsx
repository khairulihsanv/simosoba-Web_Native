'use client';

import { useState, FormEvent, Suspense } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import { motion, AnimatePresence } from 'framer-motion';
import { Eye, EyeOff, LogIn, UserPlus, Loader2, AlertCircle } from 'lucide-react';

/**
 * src/app/auth/page.tsx
 * ─────────────────────────────────────────────────────────────
 * Authentication View (Login & Register).
 * Uses Framer Motion for smooth tab transitions and feedback.
 * ─────────────────────────────────────────────────────────────
 */

function AuthForm() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const redirectTo = searchParams.get('redirect') || '/dashboard';

  const [tab, setTab] = useState<'login' | 'register'>('login');
  const [showPass, setShowPass] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  // Form states
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');

  const switchTab = (next: 'login' | 'register') => {
    setTab(next);
    setError('');
    setSuccess('');
  };

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
      
      router.push(redirectTo);
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
      setTimeout(() => router.push('/dashboard'), 1200);
    } catch (err: any) {
      setError(err.message);
      setLoading(false);
    }
  };

  return (
    <div className="min-h-dvh flex items-center justify-center p-4 relative overflow-hidden bg-surface">
      {/* Ambient background glows */}
      <div className="absolute top-[-20%] left-1/2 -translate-x-1/2 w-[600px] h-[600px] rounded-full pointer-events-none" style={{ background: 'radial-gradient(circle, rgba(99,102,241,0.1) 0%, transparent 60%)' }} />
      <div className="absolute bottom-[-10%] right-[-10%] w-[400px] h-[400px] rounded-full pointer-events-none" style={{ background: 'radial-gradient(circle, rgba(139,92,246,0.1) 0%, transparent 60%)' }} />

      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.5 }}
        className="w-full max-w-md z-10"
      >
        <div className="glass-card p-8 shadow-2xl">
          
          <div className="flex flex-col items-center mb-8">
            <div className="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl mb-4 shadow-[0_8px_30px_rgba(99,102,241,0.4)]" style={{ background: 'linear-gradient(135deg, #6366f1, #4f46e5)' }}>
              💊
            </div>
            <h1 className="text-2xl font-bold gradient-text tracking-wider">ANTIGRAVITY</h1>
            <p className="text-xs text-slate-500 uppercase tracking-[0.2em] mt-1">Intelligence Platform</p>
          </div>

          <div className="flex rounded-xl p-1 mb-6 bg-black/40 border border-white/5">
            <button onClick={() => switchTab('login')} className={`flex-1 py-2.5 rounded-lg text-sm font-bold transition-all ${tab === 'login' ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/25' : 'text-slate-500'}`}>
              Sign In
            </button>
            <button onClick={() => switchTab('register')} className={`flex-1 py-2.5 rounded-lg text-sm font-bold transition-all ${tab === 'register' ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/25' : 'text-slate-500'}`}>
              Create Account
            </button>
          </div>

          <AnimatePresence mode="popLayout">
            {error && (
              <motion.div initial={{ opacity: 0, y: -10 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0, y: -10 }} className="flex items-center gap-2 p-3 rounded-xl mb-4 text-sm text-red-300 bg-red-500/10 border border-red-500/20">
                <AlertCircle className="w-4 h-4 shrink-0" /> {error}
              </motion.div>
            )}
            {success && (
              <motion.div initial={{ opacity: 0, y: -10 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0, y: -10 }} className="flex items-center gap-2 p-3 rounded-xl mb-4 text-sm text-emerald-300 bg-emerald-500/10 border border-emerald-500/20">
                ✓ {success}
              </motion.div>
            )}
          </AnimatePresence>

          {tab === 'login' ? (
            <motion.form key="login" initial={{ opacity: 0, x: -20 }} animate={{ opacity: 1, x: 0 }} exit={{ opacity: 0, x: 20 }} onSubmit={handleLogin} className="space-y-4">
              <div>
                <label className="block text-xs font-bold text-slate-400 mb-1.5 uppercase">Email</label>
                <input type="email" required value={email} onChange={e => setEmail(e.target.value)} placeholder="admin@pharmacy.com" className="input-field" />
              </div>
              <div>
                <label className="block text-xs font-bold text-slate-400 mb-1.5 uppercase">Password</label>
                <div className="relative">
                  <input type={showPass ? 'text' : 'password'} required value={password} onChange={e => setPassword(e.target.value)} placeholder="••••••••" className="input-field pr-11" />
                  <button type="button" onClick={() => setShowPass(!showPass)} className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-white">
                    {showPass ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                  </button>
                </div>
              </div>
              <button type="submit" disabled={loading} className="btn-primary w-full h-11 mt-2">
                {loading ? <Loader2 className="w-4 h-4 animate-spin" /> : <LogIn className="w-4 h-4" />}
                {loading ? 'Authenticating...' : 'Sign In'}
              </button>
            </motion.form>
          ) : (
            <motion.form key="register" initial={{ opacity: 0, x: 20 }} animate={{ opacity: 1, x: 0 }} exit={{ opacity: 0, x: -20 }} onSubmit={handleRegister} className="space-y-4">
              <div>
                <label className="block text-xs font-bold text-slate-400 mb-1.5 uppercase">Full Name</label>
                <input type="text" required value={name} onChange={e => setName(e.target.value)} placeholder="Dr. John Doe" className="input-field" />
              </div>
              <div>
                <label className="block text-xs font-bold text-slate-400 mb-1.5 uppercase">Email</label>
                <input type="email" required value={email} onChange={e => setEmail(e.target.value)} placeholder="staff@pharmacy.com" className="input-field" />
              </div>
              <div>
                <label className="block text-xs font-bold text-slate-400 mb-1.5 uppercase">Password</label>
                <div className="relative">
                  <input type={showPass ? 'text' : 'password'} required minLength={6} value={password} onChange={e => setPassword(e.target.value)} placeholder="Min. 6 chars" className="input-field pr-11" />
                  <button type="button" onClick={() => setShowPass(!showPass)} className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-white">
                    {showPass ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                  </button>
                </div>
              </div>
              <button type="submit" disabled={loading} className="btn-primary w-full h-11 mt-2">
                {loading ? <Loader2 className="w-4 h-4 animate-spin" /> : <UserPlus className="w-4 h-4" />}
                {loading ? 'Creating...' : 'Create Account'}
              </button>
            </motion.form>
          )}

        </div>
      </motion.div>
    </div>
  );
}

export default function AuthPage() {
  return (
    <Suspense fallback={
      <div className="min-h-dvh flex items-center justify-center bg-surface">
        <Loader2 className="w-8 h-8 animate-spin text-brand-500" />
      </div>
    }>
      <AuthForm />
    </Suspense>
  );
}
