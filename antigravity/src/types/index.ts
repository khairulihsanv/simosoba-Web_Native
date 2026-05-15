/**
 * src/types/index.ts
 * ─────────────────────────────────────────────────────────────
 * Centralized TypeScript Interfaces.
 * Ensures consistent data shaping across the application.
 * ─────────────────────────────────────────────────────────────
 */

// ── Auth & Users ─────────────────────────────────────────────────

export interface JWTPayload {
  id: number;
  name: string;
  email: string;
  role: 'superadmin' | 'admin';
  iat?: number;
  exp?: number;
}

export interface AuthUser {
  id: number;
  name: string;
  email: string;
  role: 'superadmin' | 'admin';
}

export interface User {
  id: number;
  name: string;
  email: string;
  role: 'superadmin' | 'admin';
  created_at: string | Date;
}

// ── Medicines ────────────────────────────────────────────────────

export type SeasonalTag = 'Rainy' | 'Dry' | 'None';

export interface Medicine {
  id: number;
  name: string;
  category: string;
  unit: string;
  buy_price: number;
  sell_price: number;
  stock_current: number;
  safety_stock: number;
  expired_at: string | Date | null;
  seasonal_tag: SeasonalTag;
  notes: string | null;
  created_at?: string | Date;
  updated_at?: string | Date;
}

export type MedicineInput = Omit<Medicine, 'id' | 'created_at' | 'updated_at'>;

// ── Transactions (Inventory Logs) ────────────────────────────────

export type LogType = 'in' | 'out';

export interface InventoryLog {
  id: number;
  medicine_id: number;
  medicine_name?: string;  // Joined from medicines table
  user_id: number;
  user_name?: string;      // Joined from users table
  type: LogType;
  quantity: number;
  unit_price: number;
  total_price: number;
  notes: string | null;
  created_at: string | Date;
}

// ── Analytics & Dashboard Metrics ────────────────────────────────

export interface DashboardStats {
  total_medicines: number;
  low_stock_count: number;
  expiring_soon: number;
  expired_count: number;
  monthly_revenue: number;
  monthly_cost: number;
  monthly_profit: number; // Evaluated dynamically (Sell Price - Buy Price)
}

export interface MonthlyChartData {
  month: string;
  revenue: number;
  cost: number;
  profit: number;
}

export interface PredictionResult {
  medicine_id: number;
  medicine_name: string;
  current_stock: number;
  safety_stock: number;
  seasonal_tag: SeasonalTag;
  current_season: 'Rainy' | 'Dry';
  recommended_qty: number;
  urgency: 'high' | 'medium' | 'low';
  reason: string;
}

// ── Standard API Response ────────────────────────────────────────

export interface ApiSuccess<T = unknown> {
  success: true;
  data: T;
  message?: string;
}

export interface ApiError {
  success: false;
  error: string;
}

export type ApiResponse<T = unknown> = ApiSuccess<T> | ApiError;
