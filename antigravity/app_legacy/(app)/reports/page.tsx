'use client';
/**
 * app/(app)/reports/page.tsx
 * ─────────────────────────────────────────────────────────────
 * Financial & Stock Reporting
 * Generates client-side PDF reports using jsPDF and allows
 * sharing summaries via WhatsApp.
 * ─────────────────────────────────────────────────────────────
 */

import { useState, useEffect } from 'react';
import { Download, Share2, FileText, Loader2, Calendar } from 'lucide-react';
import jsPDF from 'jspdf';
import autoTable from 'jspdf-autotable';
import { format } from 'date-fns';
import type { DashboardStats, Medicine } from '@/types';

export default function ReportsPage() {
  const [stats, setStats] = useState<DashboardStats | null>(null);
  const [medicines, setMedicines] = useState<Medicine[]>([]);
  const [loading, setLoading] = useState(true);
  const [generatingPDF, setGeneratingPDF] = useState(false);

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

  const generatePDF = () => {
    if (!stats || medicines.length === 0) return;
    setGeneratingPDF(true);

    try {
      const doc = new jsPDF('p', 'pt', 'a4');
      const currentMonth = format(new Date(), 'MMMM yyyy');

      // Header
      doc.setFontSize(22);
      doc.setTextColor(30, 27, 75); // brand.950 equivalent
      doc.text('ANTIGRAVITY', 40, 50);
      
      doc.setFontSize(12);
      doc.setTextColor(100);
      doc.text(`Monthly Stock & Financial Report - ${currentMonth}`, 40, 70);
      doc.text(`Generated on: ${format(new Date(), 'dd MMM yyyy, HH:mm')}`, 40, 85);

      // Summary Cards (Text format for PDF)
      doc.setFontSize(14);
      doc.setTextColor(40);
      doc.text('Financial Summary', 40, 120);
      
      doc.setFontSize(10);
      doc.text(`Total Revenue: ${formatIDR(stats.monthly_revenue)}`, 40, 140);
      doc.text(`Total Cost: ${formatIDR(stats.monthly_cost)}`, 40, 155);
      doc.text(`Net Profit: ${formatIDR(stats.monthly_profit)}`, 40, 170);

      doc.setFontSize(14);
      doc.text('Inventory Status', 300, 120);
      
      doc.setFontSize(10);
      doc.text(`Total Items: ${stats.total_medicines}`, 300, 140);
      doc.text(`Low Stock Warnings: ${stats.low_stock_count}`, 300, 155);
      doc.text(`Expiring Soon (<90d): ${stats.expiring_soon}`, 300, 170);

      // Data Table
      doc.text('Current Stock Levels', 40, 210);
      
      const tableData = medicines.map(m => [
        m.name,
        m.category,
        `${m.stock_current} ${m.unit}`,
        m.safety_stock.toString(),
        formatIDR(m.sell_price)
      ]);

      autoTable(doc, {
        startY: 220,
        head: [['Medicine Name', 'Category', 'Current Stock', 'Safety Threshold', 'Sell Price']],
        body: tableData,
        theme: 'striped',
        headStyles: { fillColor: [99, 102, 241] }, // brand primary
        styles: { fontSize: 9 },
      });

      doc.save(`Antigravity_Report_${format(new Date(), 'yyyy-MM-dd')}.pdf`);
    } catch (err) {
      console.error(err);
      alert('Failed to generate PDF');
    } finally {
      setGeneratingPDF(false);
    }
  };

  const shareToWhatsApp = () => {
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
    // Opens WhatsApp Web or App
    window.open(`https://wa.me/?text=${encodedText}`, '_blank');
  };

  if (loading) {
    return <div className="py-20 flex justify-center"><Loader2 className="w-8 h-8 animate-spin text-brand-500" /></div>;
  }

  return (
    <div className="space-y-6 max-w-4xl mx-auto">
      <div>
        <h1 className="text-2xl font-display font-bold gradient-text">Reports & Exports</h1>
        <p className="text-slate-400 text-sm mt-1">Generate professional PDF reports and share insights.</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        {/* PDF Export Card */}
        <div className="glass-card p-6 flex flex-col items-center text-center">
          <div className="w-16 h-16 rounded-2xl bg-brand-500/10 flex items-center justify-center mb-4">
            <FileText className="w-8 h-8 text-brand-400" />
          </div>
          <h2 className="text-lg font-bold mb-2">Monthly Stock Report</h2>
          <p className="text-sm text-slate-400 mb-6">
            Detailed PDF document containing financial summaries, current stock levels, and safety warnings for the current month.
          </p>
          <button 
            onClick={generatePDF} 
            disabled={generatingPDF}
            className="btn-primary w-full max-w-xs mt-auto"
          >
            {generatingPDF ? <Loader2 className="w-4 h-4 animate-spin" /> : <Download className="w-4 h-4" />}
            {generatingPDF ? 'Generating PDF...' : 'Download PDF'}
          </button>
        </div>

        {/* WhatsApp Share Card */}
        <div className="glass-card p-6 flex flex-col items-center text-center">
          <div className="w-16 h-16 rounded-2xl bg-emerald-500/10 flex items-center justify-center mb-4">
            <Share2 className="w-8 h-8 text-emerald-400" />
          </div>
          <h2 className="text-lg font-bold mb-2">Executive Summary</h2>
          <p className="text-sm text-slate-400 mb-6">
            Quick, text-based overview of net profit and urgent alerts. Instantly forwardable to management via WhatsApp.
          </p>
          <button 
            onClick={shareToWhatsApp}
            className="w-full max-w-xs mt-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-sm text-white transition-all duration-200"
            style={{
              background: 'linear-gradient(135deg, #10b981, #059669)',
              boxShadow: '0 4px 15px rgba(16, 185, 129, 0.3)'
            }}
          >
            <Share2 className="w-4 h-4" /> Share via WhatsApp
          </button>
        </div>
      </div>

      {/* Preview Section */}
      {stats && (
        <div className="glass-card p-6 mt-8">
          <h3 className="section-title flex items-center gap-2 mb-6">
            <Calendar className="w-5 h-5 text-brand-400" /> Preview: {format(new Date(), 'MMMM yyyy')}
          </h3>
          
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4 bg-black/20 p-4 rounded-xl border border-white/5">
            <div>
              <p className="text-xs text-slate-500">Revenue</p>
              <p className="font-mono text-sm text-slate-200 mt-1">{formatIDR(stats.monthly_revenue)}</p>
            </div>
            <div>
              <p className="text-xs text-slate-500">Cost</p>
              <p className="font-mono text-sm text-slate-200 mt-1">{formatIDR(stats.monthly_cost)}</p>
            </div>
            <div>
              <p className="text-xs text-emerald-500/70 font-semibold">Net Profit</p>
              <p className="font-mono text-lg font-bold text-emerald-400 mt-1">{formatIDR(stats.monthly_profit)}</p>
            </div>
            <div>
              <p className="text-xs text-red-500/70 font-semibold">Low Stock Alerts</p>
              <p className="font-mono text-lg font-bold text-red-400 mt-1">{stats.low_stock_count}</p>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
