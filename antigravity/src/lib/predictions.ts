import type { Medicine, PredictionResult, SeasonalTag } from '@/types';

/**
 * src/lib/predictions.ts
 * ─────────────────────────────────────────────────────────────
 * Antigravity Business Intelligence & Seasonal Predictive Engine
 * Handles seasonal logic and expiration classification.
 * ─────────────────────────────────────────────────────────────
 */

export type Season = 'Rainy' | 'Dry';

/**
 * Identifies the current season based on the calendar month.
 * Indonesian pattern: Oct-Mar (Rainy), Apr-Sep (Dry).
 */
export function getCurrentSeason(): Season {
  const month = new Date().getMonth() + 1; // 1-indexed (Jan=1, Dec=12)
  // Rainy: 10, 11, 12, 1, 2, 3
  if ([10, 11, 12, 1, 2, 3].includes(month)) {
    return 'Rainy';
  }
  return 'Dry';
}

/**
 * Core Intelligence Engine: getSeasonalInsight()
 * Evaluates the inventory catalogue against current seasonal thresholds
 * and generates restocking recommendations.
 * 
 * Logic:
 * If a medicine matches the current season and its stock falls below
 * its demand threshold (2x safety stock), we trigger a recommendation.
 */
export function getSeasonalInsight(medicines: Medicine[]): PredictionResult[] {
  const currentSeason = getCurrentSeason();
  const recommendations: PredictionResult[] = [];

  for (const med of medicines) {
    if (med.seasonal_tag === 'None') continue;

    const isMatchingSeason = med.seasonal_tag === currentSeason;
    
    // Seasonal Demand Threshold: During high season, stock must stay above 2x safety_stock
    const demandThreshold = med.safety_stock * 2;
    const isVulnerable = med.stock_current < demandThreshold;

    if (isMatchingSeason && isVulnerable) {
      // Calculate how many items to order to exceed the demand threshold comfortably
      const recommendedQty = demandThreshold - med.stock_current + Math.floor(med.safety_stock / 2);
      
      // Categorize urgency
      let urgency: 'high' | 'medium' | 'low' = 'low';
      if (med.stock_current <= med.safety_stock) urgency = 'high';
      else if (med.stock_current < (demandThreshold * 0.8)) urgency = 'medium';

      recommendations.push({
        medicine_id: med.id,
        medicine_name: med.name,
        current_stock: med.stock_current,
        safety_stock: med.safety_stock,
        seasonal_tag: med.seasonal_tag,
        current_season: currentSeason,
        recommended_qty: recommendedQty,
        urgency,
        reason: `High demand expected for ${med.seasonal_tag} season. Current stock (${med.stock_current}) is vulnerable. Suggest increasing inventory.`,
      });
    }
  }

  // Sort by urgency: High -> Medium -> Low
  const priority: Record<string, number> = { high: 0, medium: 1, low: 2 };
  return recommendations.sort((a, b) => priority[a.urgency] - priority[b.urgency]);
}

/**
 * Profit Calculation Engine
 * NetProfit = (SellPrice - BuyPrice) * Quantity
 */
export function calculateNetProfit(sellPrice: number, buyPrice: number, quantity: number): number {
  return (sellPrice - buyPrice) * quantity;
}

export type ExpiryStatus = 'expired' | 'critical' | 'warning' | 'notice' | 'ok';

/**
 * Expiry Classification logic.
 * Used heavily on the Dashboard & Stock view to color-code medicine rows.
 */
export function getExpiryStatus(expiredAt: string | Date | null): {
  status: ExpiryStatus;
  daysLeft: number | null;
  label: string;
  colorClass: string;
  bgClass: string;
} {
  if (!expiredAt) {
    return { status: 'ok', daysLeft: null, label: 'No Expiry', colorClass: 'text-gray-400', bgClass: 'bg-gray-800' };
  }

  const now = new Date();
  const expiry = new Date(expiredAt);
  const msPerDay = 1000 * 60 * 60 * 24;
  const daysLeft = Math.ceil((expiry.getTime() - now.getTime()) / msPerDay);

  if (daysLeft <= 0)  return { status: 'expired',  daysLeft, label: 'Expired', colorClass: 'text-red-400', bgClass: 'bg-red-950' };
  if (daysLeft <= 30) return { status: 'critical', daysLeft, label: `${daysLeft}d left`, colorClass: 'text-red-400', bgClass: 'bg-red-950' };
  if (daysLeft <= 60) return { status: 'warning',  daysLeft, label: `${daysLeft}d left`, colorClass: 'text-amber-400', bgClass: 'bg-amber-950' };
  if (daysLeft <= 90) return { status: 'notice',   daysLeft, label: `${daysLeft}d left`, colorClass: 'text-yellow-400', bgClass: 'bg-yellow-950' };
  
  return { status: 'ok', daysLeft, label: `${daysLeft}d left`, colorClass: 'text-emerald-400', bgClass: 'bg-emerald-950' };
}
