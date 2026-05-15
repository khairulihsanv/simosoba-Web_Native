import jwt from 'jsonwebtoken';
import { NextRequest, NextResponse } from 'next/server';
import type { JWTPayload } from '@/types';

/**
 * src/lib/auth.ts
 * ─────────────────────────────────────────────────────────────
 * Provides secure JWT utility functions (Sign, Verify) and
 * HttpOnly Cookie management for Edge/Node environments.
 * ─────────────────────────────────────────────────────────────
 */

const JWT_SECRET: string = process.env.JWT_SECRET as string;
const COOKIE_NAME = 'ag_session';
const TOKEN_TTL = '7d';

/**
 * Signs a payload to generate a JWT token.
 * Validates the existence of the secret to prevent silent failures.
 */
export function signToken(payload: JWTPayload): string {
  if (!JWT_SECRET) {
    throw new Error('FATAL: JWT_SECRET environment variable is missing.');
  }
  // Remove existing iat/exp so jwt.sign can regenerate them safely
  const { iat, exp, ...cleanPayload } = payload;
  return jwt.sign(cleanPayload, JWT_SECRET, { expiresIn: TOKEN_TTL });
}

/**
 * Verifies a JWT token. Returns the decoded payload if valid.
 * Fails gracefully (returns null) on expiration or tampering.
 */
export function verifyToken(token: string): JWTPayload | null {
  try {
    if (!JWT_SECRET) return null;
    // Explicitly cast to unknown then to JWTPayload to satisfy strict TS
    const decoded = jwt.verify(token, JWT_SECRET) as unknown;
    return decoded as JWTPayload;
  } catch (error) {
    return null;
  }
}

/**
 * Attaches an HttpOnly, secure cookie to the response object.
 * This completely isolates the token from XSS (Cross-Site Scripting) vectors.
 */
export function setAuthCookie(response: NextResponse, token: string): void {
  response.cookies.set(COOKIE_NAME, token, {
    httpOnly: true, // Prevents document.cookie access in the browser
    secure: process.env.NODE_ENV === 'production', // Requires HTTPS on Vercel
    sameSite: 'lax', // CSRF mitigation
    maxAge: 60 * 60 * 24 * 7, // 7 days in seconds
    path: '/',
  });
}

/**
 * Immediately invalidates the auth cookie.
 */
export function clearAuthCookie(response: NextResponse): void {
  response.cookies.set(COOKIE_NAME, '', {
    httpOnly: true,
    secure: process.env.NODE_ENV === 'production',
    sameSite: 'lax',
    maxAge: 0, // Immediately expire
    path: '/',
  });
}

/**
 * Parses the incoming request for the HttpOnly session cookie,
 * verifies the JWT signature, and returns the payload.
 * Used across `/app/api/` routes to authenticate users.
 */
export function getSession(request: NextRequest): JWTPayload | null {
  const token = request.cookies.get(COOKIE_NAME)?.value;
  if (!token) return null;
  return verifyToken(token);
}

/**
 * Returns a standardized 401 Unauthorized API response.
 */
export function unauthorized(): NextResponse {
  return NextResponse.json({ success: false, error: 'Unauthorized. Please log in.' }, { status: 401 });
}

/**
 * Returns a standardized 403 Forbidden API response.
 */
export function forbidden(): NextResponse {
  return NextResponse.json({ success: false, error: 'Forbidden. Insufficient privileges.' }, { status: 403 });
}
