import type { Metadata, Viewport } from 'next';
import './globals.css';

/**
 * src/app/layout.tsx
 * ─────────────────────────────────────────────────────────────
 * Root application layout. Injects fonts, CSS, and SEO config.
 * ─────────────────────────────────────────────────────────────
 */

export const metadata: Metadata = {
  title: 'Antigravity | Pharmacy Intelligence',
  description: 'Production-ready pharmacy stock and financial monitoring system.',
};

export const viewport: Viewport = {
  width: 'device-width',
  initialScale: 1,
  maximumScale: 1, // Prevents unintended zoom on input focus in mobile
  themeColor: '#0a0a14',
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="en" suppressHydrationWarning>
      <body>{children}</body>
    </html>
  );
}
