'use client';

import { useState, useEffect } from 'react';
import { Download, Share2, FileText, Loader2 } from 'lucide-react';
import jsPDF from 'jspdf';
import autoTable from 'jspdf-autotable';
import { format } from 'date-fns';
import type { DashboardStats, Medicine } from '@/types';

/**
 * src/app/(app)/reports/page.tsx
 * ─────────────────────────────────────────────────────────────
 * Financial & Stock Reporting Interface
 * Generates client-side PDF reports using jsPDF.
 * Embeds a WhatsApp Bridge for sharing monthly summaries.
 * ─────────────────────────────────────────────────────────────
 */

export default function ReportsPage() {
  const [stats, setStats] = useState<DashboardStats | null>(null);
  const [medicines, setMedicines] = useState<Medicine[]>([]);
  const [loading, setLoading] = useState(true);
  const [generating, setGenerating] = useState(false);

  useEffect(() => {
    Promise.all([
      fetch('/api/dashboard').then(res => res.json()),
      fetch('/api/medicines').then(res => res.json())
    ]).then(([dashJson, medJson]) => {
      if (dashJson.success) setStats(dashJson.data.stats);
      if (medJson.success) setMedicines(medJson.data);
      setLoading(false);
    }).catch(console.error);
  }, []);

  const formatIDR = (num: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(num);

  const handleGeneratePDF = () => {
    if (!stats || medicines.length === 0) return;
    setGenerating(true);

    try {
      const doc = new jsPDF('p', 'pt', 'a4');
      const currentMonth = format(new Date(), 'MMMM yyyy');

      // ── PDF Styling & Header ──
      doc.setFontSize(24);
      doc.setTextColor(30, 27, 75); // Dark Indigo
      doc.text('ANTIGRAVITY', 40, 50);
      
      doc.setFontSize(12);
      doc.setTextColor(100);
      doc.text(`Monthly Stock & Financial Report - ${currentMonth}`, 40, 70);
      doc.text(`Generated: ${format(new Date(), 'dd MMM yyyy, HH:mm')}`, 40, 85);

      // ── KPI Section ──
      doc.setFontSize(14);
      doc.setTextColor(40);
      doc.text('Financial Summary', 40, 130);
      
      doc.setFontSize(10);
      doc.text(`Total Revenue: ${formatIDR(stats.monthly_revenue)}`, 40, 150);
      doc.text(`Total Cost: ${formatIDR(stats.monthly_cost)}`, 40, 165);
      doc.text(`Net Profit: ${formatIDR(stats.monthly_profit)}`, 40, 180);

      doc.setFontSize(14);
      doc.text('Inventory Health', 300, 130);
      
      doc.setFontSize(10);
      doc.text(`Active Catalog Items: ${stats.total_medicines}`, 300, 150);
      doc.text(`Low Stock Warnings: ${stats.low_stock_count}`, 300, 165);
      doc.text(`Expiring Soon (<90 Days): ${stats.expiring_soon}`, 300, 180);

      // ── Data Table ──
      doc.setFontSize(14);
      doc.text('Current Inventory State', 40, 220);
      
      const tableData = medicines.map(m => [
        m.name,
        m.category,
        `${m.stock_current} ${m.unit}`,
        m.safety_stock.toString(),
        formatIDR(m.sell_price)
      ]);

      autoTable(doc, {
        startY: 235,
        head: [['Medicine Name', 'Category', 'Current Stock', 'Safety Threshold', 'Sell Price']],
        body: tableData,
        theme: 'striped',
        headStyles: { fillColor: [99, 102, 241] }, // Matches brand-500
        styles: { fontSize: 9 },
      });

      doc.save(`Antigravity_Report_${format(new Date(), 'yyyy-MM-dd')}.pdf`);
    } catch (err) {
      console.error(err);
      alert('Failed to generate PDF. Check console.');
    } finally {
      setGenerating(false);
    }
  };

  const handleShareWhatsApp = () => {
    if (!stats) return;
    
    const currentMonth = format(new Date(), 'MMMM yyyy');
    const text = `*📊 ANTIGRAVITY REPORT - ${currentMonth}*
    
*Financial Overview:*
💰 Revenue: ${formatIDR(stats.monthly_revenue)}
📉 Cost: ${formatIDR(stats.monthly_cost)}
📈 *Net Profit: ${formatIDR(stats.monthly_profit)}*

*Inventory Health:*
📦 Total Items: ${stats.total_medicines}
⚠️ Low Stock Alerts: ${stats.low_stock_count}
⏰ Expiring Soon: ${stats.expiring_soon}

_Generated via Antigravity System_`;

    const encodedText = encodeURIComponent(text);
    window.open(`https://wa.me/?text=${encodedText}`, '_blank');
  };

  if (loading) {
    return <div className="py-20 flex justify-center"><Loader2 className="w-8 h-8 animate-spin text-brand-500" /></div>;
  }

  return (
    <div className="space-y-6 max-w-5xl mx-auto">
      <div>
        <h1 className="text-2xl font-bold gradient-text">Reporting & Export</h1>
        <p className="text-slate-400 text-sm mt-1">Export structured PDF reports or share executive summaries.</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        {/* PDF EXPORT CARD */}
        <div className="glass-card p-6 flex flex-col items-center text-center">
          <div className="w-16 h-16 rounded-2xl bg-brand-500/10 flex items-center justify-center mb-4 border border-brand-500/20">
            <FileText className="w-8 h-8 text-brand-400" />
          </div>
          <h2 className="text-lg font-bold mb-2">Detailed PDF Report</h2>
          <p className="text-sm text-slate-400 mb-6">
            A comprehensive, paginated PDF detailing exact stock levels, financial KPIs, and safety thresholds. Ideal for archiving.
          </p>
          <button 
            onClick={handleGeneratePDF} 
            disabled={generating}
            className="btn-primary w-full mt-auto"
          >
            {generating ? <Loader2 className="w-4 h-4 animate-spin" /> : <Download className="w-4 h-4" />}
            {generating ? 'Compiling PDF...' : 'Download PDF Document'}
          </button>
        </div>

        {/* WHATSAPP BRIDGE CARD */}
        <div className="glass-card p-6 flex flex-col items-center text-center">
          <div className="w-16 h-16 rounded-2xl bg-emerald-500/10 flex items-center justify-center mb-4 border border-emerald-500/20">
            <Share2 className="w-8 h-8 text-emerald-400" />
          </div>
          <h2 className="text-lg font-bold mb-2">WhatsApp Executive Brief</h2>
          <p className="text-sm text-slate-400 mb-6">
            Generates a high-level text summary of net profit and urgent stock warnings. Instantly forwards to management via WhatsApp.
          </p>
          <button 
            onClick={handleShareWhatsApp}
            className="w-full mt-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-sm text-white transition-all duration-300"
            style={{
              background: 'linear-gradient(135deg, #10b981, #059669)',
              boxShadow: '0 4px 15px rgba(16, 185, 129, 0.25)'
            }}
          >
            <Share2 className="w-4 h-4" /> Forward to WhatsApp
          </button>
        </div>

      </div>
    </div>
  );
}
