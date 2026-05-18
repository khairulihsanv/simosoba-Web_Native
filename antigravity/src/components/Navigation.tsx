'use client';

import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { LayoutDashboard, Pill, ArrowRightLeft, FileText, Settings, LogOut, Loader2 } from 'lucide-react';
import { useState, useEffect } from 'react';
import type { AuthUser } from '@/types';

/**
 * src/components/Navigation.tsx
 * ─────────────────────────────────────────────────────────────
 * Adaptive Navigation:
 * - Desktop: Sticky glassmorphism header.
 * - Mobile: Floating Safe-Area bottom navigation.
 * ─────────────────────────────────────────────────────────────
 */

export default function Navigation() {
  const pathname = usePathname();
  const [user, setUser] = useState<AuthUser | null>(null);
  const [loggingOut, setLoggingOut] = useState(false);

  // Fetch the current session to determine role-based access
  useEffect(() => {
    fetch('/api/auth/me')
      .then(res => res.json())
      .then(json => {
        if (json.success) setUser(json.data);
      })
      .catch(console.error);
  }, []);

  const handleLogout = async () => {
    setLoggingOut(true);
    try {
      await fetch('/api/auth/logout', { method: 'POST' });
      // Use replace to prevent back-button hijacking
      window.location.replace('/login');
    } catch (err) {
      console.error(err);
      setLoggingOut(false);
    }
  };

  const navItems = [
    { label: 'Dashboard', icon: LayoutDashboard, href: '/dashboard' },
    { label: 'Stock', icon: Pill, href: '/medicines' },
    { label: 'In/Out', icon: ArrowRightLeft, href: '/transactions' },
    { label: 'Reports', icon: FileText, href: '/reports' },
    ...(user?.role === 'superadmin' ? [{ label: 'Admin', icon: Settings, href: '/admin' }] : []),
  ];

  return (
    <>
      {/* ── DESKTOP STICKY HEADER ── */}
      <header className="hidden md:block sticky top-0 z-50 w-full glass-card border-x-0 border-t-0 rounded-none h-16 shadow-lg shadow-black/20">
        <div className="max-w-7xl mx-auto px-6 h-full flex items-center justify-between">
          
          <div className="flex items-center gap-8">
            <Link href="/dashboard" className="flex items-center gap-2">
              <span className="text-xl drop-shadow-[0_0_10px_rgba(99,102,241,0.5)]">💊</span>
              <span className="font-extrabold gradient-text tracking-wider">ANTIGRAVITY</span>
            </Link>
            
            <nav className="flex items-center gap-1">
              {navItems.map((item) => {
                const isActive = pathname.startsWith(item.href);
                return (
                  <Link
                    key={item.href}
                    href={item.href}
                    className={`flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all ${
                      isActive 
                        ? 'bg-brand-500/10 text-brand-400 border border-brand-500/20' 
                        : 'text-slate-400 hover:text-slate-200 hover:bg-white/5'
                    }`}
                  >
                    <item.icon className="w-4 h-4" />
                    {item.label}
                  </Link>
                );
              })}
            </nav>
          </div>

          <div className="flex items-center gap-4">
            {user && (
              <div className="text-right hidden lg:block">
                <p className="text-sm font-bold text-slate-200">{user.name}</p>
                <p className="text-xs text-brand-400 uppercase tracking-widest">{user.role}</p>
              </div>
            )}
            <button
              onClick={handleLogout}
              disabled={loggingOut}
              aria-label="Logout"
              className="p-2.5 rounded-xl text-slate-400 bg-white/5 hover:bg-red-500/20 hover:text-red-400 transition-colors border border-white/5"
            >
              {loggingOut ? <Loader2 className="w-4 h-4 animate-spin" /> : <LogOut className="w-4 h-4" />}
            </button>
          </div>
        </div>
      </header>

      {/* ── MOBILE FLOATING BOTTOM NAV ── */}
      <div className="md:hidden fixed bottom-0 left-0 right-0 z-50 bottom-nav-safe">
        <div className="mx-4 mb-4 glass-card p-2 flex items-center justify-around shadow-[0_-10px_40px_rgba(0,0,0,0.8)] backdrop-blur-3xl border-white/10">
          {navItems.map((item) => {
            const isActive = pathname.startsWith(item.href);
            return (
              <Link
                key={item.href}
                href={item.href}
                aria-label={item.label}
                className="relative flex flex-col items-center gap-1 p-2 min-w-[64px]"
              >
                {isActive && (
                  <div className="absolute inset-0 bg-brand-500/20 rounded-xl border border-brand-500/30" />
                )}
                <item.icon className={`w-5 h-5 relative z-10 ${isActive ? 'text-brand-400' : 'text-slate-400'}`} />
                <span className={`text-[10px] font-bold relative z-10 ${isActive ? 'text-brand-300' : 'text-slate-500'}`}>
                  {item.label}
                </span>
              </Link>
            );
          })}
        </div>
      </div>
    </>
  );
}
