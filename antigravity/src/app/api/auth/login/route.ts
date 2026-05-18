import { NextRequest, NextResponse } from 'next/server';
import bcrypt from 'bcryptjs';
import pool from '@/lib/db';
import { signToken, setAuthCookie } from '@/lib/auth';
import type { User } from '@/types';

/**
 * src/app/api/auth/login/route.ts
 * ─────────────────────────────────────────────────────────────
 * Validates credentials against TiDB and issues a secure JWT.
 * ─────────────────────────────────────────────────────────────
 */

export async function POST(request: NextRequest) {
  try {
    const { email, password } = await request.json();

    if (!email || !password) {
      return NextResponse.json({ success: false, error: 'Email and password are required.' }, { status: 400 });
    }

    // Fetch user from database
    const [rows] = await pool.execute('SELECT * FROM users WHERE email = ? OR username = ?', [email, email]);
    const users = rows as (User & { password_hash: string })[];

    if (users.length === 0) {
      return NextResponse.json({ success: false, error: 'Invalid credentials.' }, { status: 401 });
    }

    const user = users[0];

    // Verify password hash
    const isMatch = await bcrypt.compare(password, user.password_hash);
    if (!isMatch) {
      return NextResponse.json({ success: false, error: 'Invalid credentials.' }, { status: 401 });
    }

    // Sign JWT
    const token = signToken({
      id: user.id,
      name: user.name,
      email: user.email,
      role: user.role as 'admin' | 'superadmin'
    });

    const response = NextResponse.json({
      success: true,
      data: { id: user.id, name: user.name, role: user.role }
    });

    // Set HttpOnly Cookie
    setAuthCookie(response, token);

    return response;
  } catch (error: any) {
    console.error('Login Error:', error);
    return NextResponse.json({ success: false, error: 'Internal Server Error' }, { status: 500 });
  }
}
