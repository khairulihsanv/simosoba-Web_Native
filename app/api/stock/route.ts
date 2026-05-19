import { NextRequest, NextResponse } from 'next/server';
import pool from '@/lib/db';
import { z } from 'zod';

// Validation schema for medicine
const medicineSchema = z.object({
  name: z.string().min(1, 'Medicine name is required'),
  genericName: z.string().optional(),
  category: z.string().optional(),
  strength: z.string().optional(),
  unit: z.string().optional(),
  quantity: z.number().min(0, 'Quantity must be non-negative'),
  reorderLevel: z.number().min(0, 'Reorder level must be non-negative').default(10),
  expiryDate: z.string().optional(),
  supplier: z.string().optional(),
  price: z.number().nonnegative().optional(),
});

// GET all medicines with optional filtering
export async function GET(request: NextRequest) {
  try {
    const { searchParams } = new URL(request.url);
    const category = searchParams.get('category');
    const search = searchParams.get('search');
    const lowStock = searchParams.get('lowStock') === 'true';
    
    let query = 'SELECT * FROM medicines';
    const params: any[] = [];
    const conditions: string[] = [];
    
    if (category) {
      conditions.push('category = ?');
      params.push(category);
    }
    
    if (search) {
      conditions.push('(name LIKE ? OR genericName LIKE ?)');
      params.push(`%${search}%`, `%${search}%`);
    }
    
    if (lowStock) {
      conditions.push('quantity <= reorderLevel');
    }
    
    if (conditions.length > 0) {
      query += ' WHERE ' + conditions.join(' AND ');
    }
    
    query += ' ORDER BY name';
    
    const [rows] = await pool.query(query, params);
    
    return NextResponse.json(rows);
  } catch (error) {
    console.error('Error fetching medicines:', error);
    return NextResponse.json(
      { error: 'Failed to fetch medicines' },
      { status: 500 }
    );
  }
}

// CREATE a new medicine
export async function POST(request: NextRequest) {
  try {
    const body = await request.json();
    const validatedData = medicineSchema.parse(body);
    
    const result = await pool.query(
      `INSERT INTO medicines (
        name, genericName, category, strength, unit, 
        quantity, reorderLevel, expiryDate, supplier, price
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        validatedData.name,
        validatedData.genericName || null,
        validatedData.category || null,
        validatedData.strength || null,
        validatedData.unit || null,
        validatedData.quantity,
        validatedData.reorderLevel,
        validatedData.expiryDate || null,
        validatedData.supplier || null,
        validatedData.price || null
      ]
    );
    
    const newMedicine = {
      id: (result as any).insertId,
      ...validatedData
    };
    
    return NextResponse.json(newMedicine, { status: 201 });
  } catch (error) {
    if (error instanceof z.ZodError) {
      return NextResponse.json(
        { error: 'Validation failed', details: error.errors },
        { status: 400 }
      );
    }
    
    console.error('Error creating medicine:', error);
    return NextResponse.json(
      { error: 'Failed to create medicine' },
      { status: 500 }
    );
  }
}

// UPDATE a medicine
export async function PUT(request: NextRequest) {
  try {
    const { searchParams } = new URL(request.url);
    const id = searchParams.get('id');
    
    if (!id) {
      return NextResponse.json(
        { error: 'Medicine ID is required' },
        { status: 400 }
      );
    }
    
    const body = await request.json();
    const validatedData = medicineSchema.partial().parse(body);
    
    // Build dynamic update query
    const fields = Object.keys(validatedData);
    if (fields.length === 0) {
      return NextResponse.json(
        { error: 'No fields to update' },
        { status: 400 }
      );
    }
    
    const setClause = fields.map((field, index) => `${field} = ?`).join(', ');
    const values = [...fields.map(field => validatedData[field as keyof typeof validatedData]), id];
    
    const result = await pool.query(
      `UPDATE medicines SET ${setClause} WHERE id = ?`,
      values
    );
    
    if ((result as any).affectedRows === 0) {
      return NextResponse.json(
        { error: 'Medicine not found' },
        { status: 404 }
      );
    }
    
    const updatedMedicine = {
      id: Number(id),
      ...validatedData
    };
    
    return NextResponse.json(updatedMedicine);
  } catch (error) {
    if (error instanceof z.ZodError) {
      return NextResponse.json(
        { error: 'Validation failed', details: error.errors },
        { status: 400 }
      );
    }
    
    console.error('Error updating medicine:', error);
    return NextResponse.json(
      { error: 'Failed to update medicine' },
      { status: 500 }
    );
  }
}

// DELETE a medicine
export async function DELETE(request: NextRequest) {
  try {
    const { searchParams } = new URL(request.url);
    const id = searchParams.get('id');
    
    if (!id) {
      return NextResponse.json(
        { error: 'Medicine ID is required' },
        { status: 400 }
      );
    }
    
    const result = await pool.query(
      'DELETE FROM medicines WHERE id = ?',
      [id]
    );
    
    if ((result as any).affectedRows === 0) {
      return NextResponse.json(
        { error: 'Medicine not found' },
        { status: 404 }
      );
    }
    
    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('Error deleting medicine:', error);
    return NextResponse.json(
      { error: 'Failed to delete medicine' },
      { status: 500 }
    );
  }
}