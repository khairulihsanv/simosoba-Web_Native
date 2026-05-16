import type { Metadata, Viewport } from 'next';
import './globals.css';

/**
 * src/app/layout.tsx
 * ─────────────────────────────────────────────────────────────
 * Root layout with high-value aesthetic configuration.
 * Sets the dark theme foundation and typography.
 * ─────────────────────────────────────────────────────────────
 */

export const metadata: Metadata = {
  title: 'Antigravity | Pharmacy Intelligence',
  description: 'Next-generation pharmacy stock and financial intelligence system.',
};

export const viewport: Viewport = {
  width: 'device-width',
  initialScale: 1,
  maximumScale: 1,
  themeColor: '#020617', // slate-950
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="en" className="dark" suppressHydrationWarning>
      <body className="bg-slate-950 text-slate-200 selection:bg-brand-500/30">
        {children}
      </body>
    </html>
  );
}
