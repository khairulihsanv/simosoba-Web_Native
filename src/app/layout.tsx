import './globals.css';
import type { Metadata } from 'next';
import { Inter } from 'next/font/google';
import Navbar from '@/components/Navbar';

const inter = Inter({ subsets: ['latin'] });

export const metadata: Metadata = {
  title: 'Antigravity Medication Stock',
  description: 'Monitor and manage medication inventory with real-time alerts',
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="en" className="scroll-smooth">
      <body className={inter.className} min-h-screen bg-gradient-to-b from-slate-50 to-slate-100>
        <Navbar />
        <main className="pt-16 pb-20">{children}</main>
      </body>
    </html>
  );
}