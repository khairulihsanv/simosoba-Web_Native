/**
 * middleware.ts
 * ─────────────────────────────────────────────────────────────
 * Next.js Edge Middleware — runs on every matched request BEFORE
 * the page/API handler, at the CDN edge (zero cold start).
 *
 * Responsibilities:
 *  1. Protect dashboard routes — redirect to /login if unauthenticated
 *  2. Protect admin routes — redirect to /dashboard if not superadmin
 *  3. Redirect authenticated users away from /login to /dashboard
 *
 * Note: We manually decode the JWT here because the `jsonwebtoken`
 * library uses Node.js crypto APIs not available in Edge runtime.
 * We do a lightweight base64 decode of the payload instead.
 * ─────────────────────────────────────────────────────────────
 */

import { NextRequest, NextResponse } from 'next/server';

// Cookie name must match lib/auth.ts
const COOKIE_NAME = 'ag_session';

/**
 * Lightweight JWT payload decoder for the Edge runtime.
 * Does NOT verify the signature — that happens in API routes
 * which run in Node.js runtime and can use jsonwebtoken.
 */
function decodeJWTPayload(token: string): { role?: string } | null {
  try {
    const parts = token.split('.');
    if (parts.length !== 3) return null;
    // Base64url → Base64 → JSON
    const base64 = parts[1].replace(/-/g, '+').replace(/_/g, '/');
    const json   = atob(base64);
    return JSON.parse(json);
  } catch {
    return null;
  }
}

export function middleware(request: NextRequest) {
  const { pathname } = request.nextUrl;
  const token        = request.cookies.get(COOKIE_NAME)?.value;
  const payload      = token ? decodeJWTPayload(token) : null;
  const isAuthed     = !!payload;
  const isSuperAdmin = payload?.role === 'superadmin';

  // ── Route: /login (Login / Register) ───────────────────────
  // Redirect already-authenticated users to the dashboard
  if (pathname.startsWith('/login') && isAuthed) {
    return NextResponse.redirect(new URL('/dashboard', request.url));
  }

  // ── Route: /dashboard/** ───────────────────────────────────
  // Require authentication
  if (pathname.startsWith('/dashboard') && !isAuthed) {
    const loginUrl = new URL('/login', request.url);
    loginUrl.searchParams.set('redirect', pathname); // preserve intended destination
    return NextResponse.redirect(loginUrl);
  }

  // ── Route: /medicines, /transactions, /reports ────────────
  // Require authentication
  const protectedRoutes = ['/medicines', '/transactions', '/reports'];
  if (protectedRoutes.some(r => pathname.startsWith(r)) && !isAuthed) {
    return NextResponse.redirect(new URL('/login', request.url));
  }

  // ── Route: /admin/** ───────────────────────────────────────
  // Require superadmin role
  if (pathname.startsWith('/admin')) {
    if (!isAuthed) {
      return NextResponse.redirect(new URL('/login', request.url));
    }
    if (!isSuperAdmin) {
      return NextResponse.redirect(new URL('/dashboard?error=forbidden', request.url));
    }
  }

  return NextResponse.next();
}

export const config = {
  // Only run middleware on these paths (skip static files, API routes)
  matcher: [
    '/dashboard/:path*',
    '/medicines/:path*',
    '/transactions/:path*',
    '/reports/:path*',
    '/admin/:path*',
    '/login',
  ],
};
