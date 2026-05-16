import { NextRequest, NextResponse } from 'next/server';
import bcrypt from 'bcryptjs';
import pool from '@/lib/db';
import { signToken, setAuthCookie } from '@/lib/auth';

export async function POST(request: NextRequest) {
  try {
    const { name, email, password } = await request.json();

    if (!name || !email || !password) {
      return NextResponse.json({ success: false, error: 'All fields are required.' }, { status: 400 });
    }

    // Check if user already exists
    const [existing] = await pool.execute('SELECT id FROM users WHERE email = ?', [email]);
    if ((existing as { id: number }[]).length > 0) {
      return NextResponse.json({ success: false, error: 'Email already registered.' }, { status: 409 });
    }

    // Hash password
    const salt = await bcrypt.genSalt(10);
    const hashedPassword = await bcrypt.hash(password, salt);

    // Insert user
    const [result] = await pool.execute(
      'INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, "admin")',
      [name, email, hashedPassword]
    );
    
    const userId = (result as { insertId: number }).insertId;

    // Auto login after registration
    const token = signToken({
      id: userId,
      name,
      email,
      role: 'admin'
    });

    const response = NextResponse.json({
      success: true,
      data: { id: userId, name, role: 'admin' }
    });

    setAuthCookie(response, token);

    return response;
  } catch (error: any) {
    console.error('Registration Error:', error);
    return NextResponse.json({ success: false, error: 'Internal Server Error' }, { status: 500 });
  }
}
