'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { motion, AnimatePresence } from 'framer-motion';

/**
 * src/app/page.tsx
 * ─────────────────────────────────────────────────────────────
 * Splash Screen (Landing Page)
 * Displays a 4-second premium animated sequence before redirecting
 * the user to the Authentication screen.
 * ─────────────────────────────────────────────────────────────
 */

export default function SplashPage() {
  const router = useRouter();
  const [exiting, setExiting] = useState(false);

  useEffect(() => {
    // Wait 4 seconds, trigger exit animation, then route to Auth
    const timer = setTimeout(() => {
      setExiting(true);
      setTimeout(() => router.push('/auth'), 800); // Route after exit anim completes
    }, 4000);

    return () => clearTimeout(timer);
  }, [router]);

  return (
    <AnimatePresence>
      {!exiting && (
        <motion.div
          key="splash"
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          exit={{ opacity: 0, scale: 1.05, filter: 'blur(10px)' }}
          transition={{ duration: 0.8, ease: 'easeInOut' }}
          className="fixed inset-0 flex flex-col items-center justify-center overflow-hidden bg-[#0a0a14]"
        >
          {/* Ambient Lighting Orbs */}
          <motion.div
            animate={{ scale: [1, 1.2, 1], opacity: [0.5, 0.8, 0.5] }}
            transition={{ duration: 4, repeat: Infinity, ease: 'easeInOut' }}
            className="absolute top-[20%] left-1/2 -translate-x-1/2 w-[600px] h-[600px] rounded-full pointer-events-none"
            style={{ background: 'radial-gradient(circle, rgba(99,102,241,0.15) 0%, transparent 70%)' }}
          />

          {/* Icon Animation */}
          <motion.div
            initial={{ scale: 0, rotate: -45 }}
            animate={{ scale: 1, rotate: 0 }}
            transition={{ duration: 1, type: 'spring', bounce: 0.5 }}
            className="w-20 h-20 rounded-2xl flex items-center justify-center text-4xl mb-6 shadow-[0_0_60px_rgba(99,102,241,0.5)] z-10"
            style={{ background: 'linear-gradient(135deg, #6366f1, #4f46e5)' }}
          >
            💊
          </motion.div>

          {/* Typography */}
          <motion.h1
            initial={{ y: 20, opacity: 0 }}
            animate={{ y: 0, opacity: 1 }}
            transition={{ delay: 0.5, duration: 0.8 }}
            className="text-4xl md:text-5xl font-extrabold tracking-[0.2em] gradient-text z-10"
          >
            ANTIGRAVITY
          </motion.h1>

          <motion.p
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            transition={{ delay: 1.2, duration: 1 }}
            className="text-slate-400 text-sm tracking-[0.3em] uppercase mt-4 z-10"
          >
            Pharmacy Intelligence
          </motion.p>
        </motion.div>
      )}
    </AnimatePresence>
  );
}
