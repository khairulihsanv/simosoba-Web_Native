import Navigation from '@/components/Navigation';

/**
 * src/app/(app)/layout.tsx
 * ─────────────────────────────────────────────────────────────
 * Route Group Layout for Authenticated Pages.
 * Injects the Navigation component and handles spacing.
 * ─────────────────────────────────────────────────────────────
 */

export default function AppLayout({ children }: { children: React.ReactNode }) {
  return (
    <div className="flex flex-col min-h-dvh">
      <Navigation />
      {/* 
        Main content wrapper. 
        Note the pb-32 on mobile to ensure the bottom content isn't 
        hidden behind the floating navigation bar.
      */}
      <main className="flex-1 w-full max-w-7xl mx-auto p-4 md:p-6 pb-32 md:pb-8">
        {children}
      </main>
    </div>
  );
}
