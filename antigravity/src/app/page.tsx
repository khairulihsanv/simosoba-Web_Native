'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { motion, AnimatePresence } from 'framer-motion';

/**
 * src/app/page.tsx
 * ─────────────────────────────────────────────────────────────
 * Splash Screen (Landing Branding)
 * Displays a 4-second premium animated sequence.
 * ─────────────────────────────────────────────────────────────
 */

export default function SplashPage() {
  const router = useRouter();
  const [exiting, setExiting] = useState(false);

  useEffect(() => {
    const timer = setTimeout(() => {
      setExiting(true);
      setTimeout(() => router.push('/login'), 800);
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
          transition={{ duration: 0.8 }}
          className="fixed inset-0 flex flex-col items-center justify-center overflow-hidden bg-[#0a0a14]"
        >
          {/* Pulsing Branding Background */}
          <motion.div
            animate={{ 
              scale: [1, 1.1, 1],
              opacity: [0.3, 0.5, 0.3] 
            }}
            transition={{ duration: 3, repeat: Infinity, ease: 'easeInOut' }}
            className="absolute w-[500px] h-[500px] rounded-full"
            style={{ background: 'radial-gradient(circle, #6366f1 0%, transparent 70%)' }}
          />

          {/* Logo Pulse Effect */}
          <motion.div
            initial={{ scale: 0.8, opacity: 0 }}
            animate={{ scale: 1, opacity: 1 }}
            transition={{ duration: 1, type: 'spring' }}
            className="w-24 h-24 rounded-3xl flex items-center justify-center text-4xl mb-8 z-10 shadow-[0_0_50px_rgba(99,102,241,0.6)]"
            style={{ background: 'linear-gradient(135deg, #6366f1, #4f46e5)' }}
          >
            💊
          </motion.div>

          <motion.h1
            initial={{ letterSpacing: '0.1em', opacity: 0 }}
            animate={{ letterSpacing: '0.4em', opacity: 1 }}
            transition={{ duration: 1.5, ease: 'easeOut' }}
            className="text-4xl md:text-5xl font-black gradient-text z-10"
          >
            ANTIGRAVITY
          </motion.h1>

          <motion.div
            initial={{ width: 0 }}
            animate={{ width: 100 }}
            transition={{ delay: 1, duration: 2 }}
            className="h-0.5 bg-brand-500 mt-6 rounded-full opacity-50 z-10"
          />
        </motion.div>
      )}
    </AnimatePresence>
  );
}
