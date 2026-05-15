'use client';
/**
 * app/auth/page.tsx — Login / Register Page
 * ─────────────────────────────────────────────────────────────
 * Glassmorphism auth page with animated tab switching between
 * Login and Register forms. Handles API calls, loading states,
 * and error display inline.
 * ─────────────────────────────────────────────────────────────
 */

import { useState, FormEvent } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import { motion, AnimatePresence } from 'framer-motion';
import { Eye, EyeOff, LogIn, UserPlus, Loader2, AlertCircle } from 'lucide-react';
import type { Metadata } from 'next';

type Tab = 'login' | 'register';

export default function AuthPage() {
  const router       = useRouter();
  const searchParams = useSearchParams();
  const redirectTo   = searchParams.get('redirect') || '/dashboard';

  // ── UI State ───────────────────────────────────────────────
  const [tab,         setTab]         = useState<Tab>('login');
  const [showPass,    setShowPass]    = useState(false);
  const [loading,     setLoading]     = useState(false);
  const [error,       setError]       = useState('');
  const [successMsg,  setSuccessMsg]  = useState('');

  // ── Form Fields ────────────────────────────────────────────
  const [name,     setName]     = useState('');
  const [email,    setEmail]    = useState('');
  const [password, setPassword] = useState('');

  // ── Tab Switch ─────────────────────────────────────────────
  function switchTab(next: Tab) {
    setTab(next);
    setError('');
    setSuccessMsg('');
  }

  // ── Login Handler ──────────────────────────────────────────
  async function handleLogin(e: FormEvent) {
    e.preventDefault();
    setLoading(true);
    setError('');
    try {
      const res  = await fetch('/api/auth/login', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ email, password }),
      });
      const json = await res.json();
      if (!res.ok) throw new Error(json.error || 'Login failed.');
      router.push(redirectTo);
    } catch (err: unknown) {
      setError(err instanceof Error ? err.message : 'An error occurred.');
    } finally {
      setLoading(false);
    }
  }

  // ── Register Handler ───────────────────────────────────────
  async function handleRegister(e: FormEvent) {
    e.preventDefault();
    setLoading(true);
    setError('');
    setSuccessMsg('');
    try {
      const res  = await fetch('/api/auth/register', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ name, email, password }),
      });
      const json = await res.json();
      if (!res.ok) throw new Error(json.error || 'Registration failed.');
      setSuccessMsg('Account created! Logging you in…');
      setTimeout(() => router.push('/dashboard'), 1200);
    } catch (err: unknown) {
      setError(err instanceof Error ? err.message : 'An error occurred.');
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="min-h-dvh flex items-center justify-center p-4 relative overflow-hidden">
      {/* ── Background ambient orbs ───────────────────────── */}
      <div
        className="absolute top-[-200px] left-1/2 -translate-x-1/2 w-[700px] h-[700px] rounded-full pointer-events-none"
        style={{ background: 'radial-gradient(circle, rgba(99,102,241,0.12) 0%, transparent 65%)' }}
      />
      <div
        className="absolute bottom-[-100px] right-[-100px] w-[400px] h-[400px] rounded-full pointer-events-none"
        style={{ background: 'radial-gradient(circle, rgba(139,92,246,0.08) 0%, transparent 65%)' }}
      />

      {/* ── Auth Card ─────────────────────────────────────── */}
      <motion.div
        initial={{ opacity: 0, y: 24 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.6, ease: 'easeOut' }}
        className="w-full max-w-md relative"
      >
        {/* Glass card */}
        <div
          className="rounded-2xl p-8"
          style={{
            background: 'rgba(22, 22, 40, 0.85)',
            backdropFilter: 'blur(24px)',
            border: '1px solid rgba(255,255,255,0.07)',
            boxShadow: '0 25px 50px rgba(0,0,0,0.5)',
          }}
        >
          {/* ── Logo + Brand ─────────────────────────────── */}
          <div className="flex flex-col items-center mb-8">
            <div
              className="w-14 h-14 rounded-xl flex items-center justify-center text-2xl mb-4"
              style={{
                background: 'linear-gradient(135deg, #6366f1, #4f46e5)',
                boxShadow: '0 8px 30px rgba(99,102,241,0.4)',
              }}
            >
              💊
            </div>
            <h1 className="text-2xl font-display font-extrabold gradient-text">ANTIGRAVITY</h1>
            <p className="text-xs text-slate-500 tracking-widest mt-1 uppercase">
              Pharmacy Intelligence
            </p>
          </div>

          {/* ── Tab Switcher ──────────────────────────────── */}
          <div
            className="flex rounded-xl p-1 mb-6"
            style={{ background: 'rgba(0,0,0,0.3)' }}
          >
            {(['login', 'register'] as Tab[]).map((t) => (
              <button
                key={t}
                onClick={() => switchTab(t)}
                className={`flex-1 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 capitalize ${
                  tab === t
                    ? 'text-white'
                    : 'text-slate-500 hover:text-slate-300'
                }`}
                style={tab === t ? {
                  background: 'linear-gradient(135deg, #6366f1, #4f46e5)',
                  boxShadow: '0 4px 12px rgba(99,102,241,0.35)',
                } : {}}
              >
                {t === 'login' ? 'Sign In' : 'Create Account'}
              </button>
            ))}
          </div>

          {/* ── Error / Success Banner ────────────────────── */}
          <AnimatePresence>
            {error && (
              <motion.div
                initial={{ opacity: 0, y: -8 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0, y: -8 }}
                className="flex items-center gap-2 p-3 rounded-xl mb-4 text-sm text-red-300"
                style={{ background: 'rgba(239,68,68,0.1)', border: '1px solid rgba(239,68,68,0.2)' }}
              >
                <AlertCircle className="w-4 h-4 shrink-0" />
                {error}
              </motion.div>
            )}
            {successMsg && (
              <motion.div
                initial={{ opacity: 0, y: -8 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0, y: -8 }}
                className="flex items-center gap-2 p-3 rounded-xl mb-4 text-sm text-emerald-300"
                style={{ background: 'rgba(16,185,129,0.1)', border: '1px solid rgba(16,185,129,0.2)' }}
              >
                ✓ {successMsg}
              </motion.div>
            )}
          </AnimatePresence>

          {/* ── Forms ─────────────────────────────────────── */}
          <AnimatePresence mode="wait">
            {tab === 'login' ? (
              <motion.form
                key="login"
                initial={{ opacity: 0, x: -20 }}
                animate={{ opacity: 1, x: 0 }}
                exit={{ opacity: 0, x: 20 }}
                transition={{ duration: 0.25 }}
                onSubmit={handleLogin}
                className="flex flex-col gap-4"
              >
                <div>
                  <label className="block text-xs text-slate-400 mb-1.5 font-medium">Email Address</label>
                  <input
                    type="email"
                    required
                    autoComplete="email"
                    value={email}
                    onChange={e => setEmail(e.target.value)}
                    placeholder="admin@pharmacy.com"
                    className="input-field"
                  />
                </div>

                <div>
                  <label className="block text-xs text-slate-400 mb-1.5 font-medium">Password</label>
                  <div className="relative">
                    <input
                      type={showPass ? 'text' : 'password'}
                      required
                      autoComplete="current-password"
                      value={password}
                      onChange={e => setPassword(e.target.value)}
                      placeholder="••••••••"
                      className="input-field pr-11"
                    />
                    <button
                      type="button"
                      onClick={() => setShowPass(!showPass)}
                      className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 transition-colors"
                    >
                      {showPass ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                    </button>
                  </div>
                </div>

                <button type="submit" disabled={loading} className="btn-primary w-full mt-2 h-11">
                  {loading
                    ? <Loader2 className="w-4 h-4 animate-spin" />
                    : <LogIn className="w-4 h-4" />
                  }
                  {loading ? 'Signing in…' : 'Sign In'}
                </button>

                <p className="text-center text-xs text-slate-600 mt-2">
                  Default: admin@antigravity.app / Admin@123
                </p>
              </motion.form>
            ) : (
              <motion.form
                key="register"
                initial={{ opacity: 0, x: 20 }}
                animate={{ opacity: 1, x: 0 }}
                exit={{ opacity: 0, x: -20 }}
                transition={{ duration: 0.25 }}
                onSubmit={handleRegister}
                className="flex flex-col gap-4"
              >
                <div>
                  <label className="block text-xs text-slate-400 mb-1.5 font-medium">Full Name</label>
                  <input
                    type="text"
                    required
                    autoComplete="name"
                    value={name}
                    onChange={e => setName(e.target.value)}
                    placeholder="Dr. Siti Rahayu"
                    className="input-field"
                  />
                </div>
                <div>
                  <label className="block text-xs text-slate-400 mb-1.5 font-medium">Email Address</label>
                  <input
                    type="email"
                    required
                    autoComplete="email"
                    value={email}
                    onChange={e => setEmail(e.target.value)}
                    placeholder="staff@pharmacy.com"
                    className="input-field"
                  />
                </div>
                <div>
                  <label className="block text-xs text-slate-400 mb-1.5 font-medium">Password</label>
                  <div className="relative">
                    <input
                      type={showPass ? 'text' : 'password'}
                      required
                      minLength={6}
                      autoComplete="new-password"
                      value={password}
                      onChange={e => setPassword(e.target.value)}
                      placeholder="Min. 6 characters"
                      className="input-field pr-11"
                    />
                    <button
                      type="button"
                      onClick={() => setShowPass(!showPass)}
                      className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 transition-colors"
                    >
                      {showPass ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                    </button>
                  </div>
                </div>

                <button type="submit" disabled={loading} className="btn-primary w-full mt-2 h-11">
                  {loading
                    ? <Loader2 className="w-4 h-4 animate-spin" />
                    : <UserPlus className="w-4 h-4" />
                  }
                  {loading ? 'Creating account…' : 'Create Account'}
                </button>

                <p className="text-center text-xs text-slate-600 mt-1">
                  New accounts are assigned <strong className="text-slate-500">admin</strong> role.
                  <br />Superadmin can promote via User Management.
                </p>
              </motion.form>
            )}
          </AnimatePresence>
        </div>

        {/* ── Footer note ───────────────────────────────────── */}
        <p className="text-center text-xs text-slate-700 mt-5">
          Antigravity v1.0 — Pharmacy Intelligence Platform
        </p>
      </motion.div>
    </div>
  );
}
