'use client';
/**
 * app/(app)/medicines/page.tsx
 * ─────────────────────────────────────────────────────────────
 * Medicine Inventory Management
 * Displays list of medicines with search/filters.
 * Allows adding, editing, and deleting medicines.
 * Integrates expiry calculation for color-coded status.
 * ─────────────────────────────────────────────────────────────
 */

import { useState, useEffect, FormEvent } from 'react';
import { Plus, Search, Edit2, Trash2, Loader2, AlertTriangle, AlertCircle } from 'lucide-react';
import type { Medicine, MedicineInput, AuthUser } from '@/types';
import { getExpiryStatus } from '@/lib/predictions';
import { format } from 'date-fns';

export default function MedicinesPage() {
  const [medicines, setMedicines] = useState<Medicine[]>([]);
  const [loading, setLoading] = useState(true);
  const [user, setUser] = useState<AuthUser | null>(null);

  // Filters
  const [search, setSearch] = useState('');
  const [categoryFilter, setCategoryFilter] = useState('');
  
  // Modal State
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [currentMedicine, setCurrentMedicine] = useState<Partial<Medicine> | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');

  // Fetch Session & Data
  useEffect(() => {
    fetch('/api/auth/me').then(r => r.json()).then(j => setUser(j.data)).catch(console.error);
    loadMedicines();
  }, []);

  const loadMedicines = async () => {
    setLoading(true);
    try {
      const res = await fetch('/api/medicines');
      const json = await res.json();
      if (json.success) setMedicines(json.data);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  // Form Handlers
  const handleOpenModal = (med?: Medicine) => {
    setErrorMsg('');
    if (med) {
      // Format date for input type="date"
      const formattedMed = { ...med };
      if (formattedMed.expired_at) {
        formattedMed.expired_at = new Date(formattedMed.expired_at).toISOString().split('T')[0];
      }
      setCurrentMedicine(formattedMed);
    } else {
      setCurrentMedicine({ category: 'General', unit: 'pcs', seasonal_tag: 'None' });
    }
    setIsModalOpen(true);
  };

  const handleCloseModal = () => {
    setIsModalOpen(false);
    setCurrentMedicine(null);
  };

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);
    setErrorMsg('');

    const method = currentMedicine?.id ? 'PATCH' : 'POST';
    const url = currentMedicine?.id ? `/api/medicines/${currentMedicine.id}` : '/api/medicines';

    try {
      const res = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(currentMedicine),
      });
      const json = await res.json();
      if (!res.ok) throw new Error(json.error);
      
      await loadMedicines();
      handleCloseModal();
    } catch (err: any) {
      setErrorMsg(err.message);
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleDelete = async (id: number) => {
    if (!confirm('Are you sure you want to delete this medicine? This will also delete related inventory logs.')) return;
    try {
      const res = await fetch(`/api/medicines/${id}`, { method: 'DELETE' });
      if (!res.ok) throw new Error('Delete failed');
      await loadMedicines();
    } catch (err) {
      console.error(err);
      alert('Failed to delete.');
    }
  };

  // Filtered Data
  const filteredMedicines = medicines.filter(m => {
    const matchSearch = m.name.toLowerCase().includes(search.toLowerCase()) || m.category.toLowerCase().includes(search.toLowerCase());
    const matchCat = categoryFilter ? m.category === categoryFilter : true;
    return matchSearch && matchCat;
  });

  const categories = Array.from(new Set(medicines.map(m => m.category)));

  const formatIDR = (num: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num);

  return (
    <div className="space-y-6">
      <div className="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
          <h1 className="text-2xl font-display font-bold gradient-text">Stock Monitoring</h1>
          <p className="text-slate-400 text-sm mt-1">Manage medicines, pricing, and stock limits.</p>
        </div>
        <button onClick={() => handleOpenModal()} className="btn-primary shrink-0">
          <Plus className="w-4 h-4" /> Add Medicine
        </button>
      </div>

      <div className="glass-card p-5">
        <div className="flex flex-col md:flex-row gap-4 mb-6">
          <div className="relative flex-1">
            <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-500" />
            <input 
              type="text" 
              placeholder="Search by name or category..." 
              aria-label="Search medicines"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              className="input-field pl-10"
            />
          </div>
          <select 
            aria-label="Filter by category"
            value={categoryFilter}
            onChange={(e) => setCategoryFilter(e.target.value)}
            className="input-field md:w-48 appearance-none bg-surface-card"
          >
            <option value="">All Categories</option>
            {categories.map(c => <option key={c} value={c}>{c}</option>)}
          </select>
        </div>

        {loading ? (
          <div className="py-12 flex justify-center"><Loader2 className="w-8 h-8 animate-spin text-brand-500" /></div>
        ) : (
          <div className="overflow-x-auto">
            <table className="data-table">
              <thead>
                <tr>
                  <th>Name & Category</th>
                  <th>Stock Info</th>
                  <th>Pricing</th>
                  <th>Expiry & Season</th>
                  <th className="text-right">Actions</th>
                </tr>
              </thead>
              <tbody>
                {filteredMedicines.length === 0 ? (
                  <tr><td colSpan={5} className="text-center py-8 text-slate-500">No medicines found.</td></tr>
                ) : (
                  filteredMedicines.map(med => {
                    const expiry = getExpiryStatus(med.expired_at);
                    const isLowStock = med.stock_current <= med.safety_stock;
                    
                    return (
                      <tr key={med.id}>
                        <td>
                          <p className="font-semibold text-slate-200">{med.name}</p>
                          <p className="text-xs text-slate-500">{med.category}</p>
                        </td>
                        <td>
                          <div className="flex items-center gap-2">
                            <span className={`font-bold ${isLowStock ? 'text-red-400' : 'text-slate-200'}`}>
                              {med.stock_current} {med.unit}
                            </span>
                            {isLowStock && <span title="Low Stock"><AlertTriangle className="w-4 h-4 text-red-400" /></span>}
                          </div>
                          <p className="text-xs text-slate-500">Safe: {med.safety_stock}</p>
                        </td>
                        <td>
                          <p className="text-sm font-mono">Sell: <span className="text-brand-400">{formatIDR(med.sell_price)}</span></p>
                          <p className="text-xs text-slate-500 font-mono">Buy: {formatIDR(med.buy_price)}</p>
                        </td>
                        <td>
                          <div className="flex flex-col gap-1 items-start">
                            <span className={`text-[10px] uppercase font-bold px-2 py-0.5 rounded-full ${expiry.bgClass} ${expiry.colorClass}`}>
                              {expiry.label}
                            </span>
                            {med.seasonal_tag !== 'None' && (
                              <span className="text-[10px] uppercase text-slate-400 bg-white/5 border border-white/10 px-2 py-0.5 rounded-full">
                                {med.seasonal_tag} Season
                              </span>
                            )}
                          </div>
                        </td>
                        <td className="text-right">
                          <div className="flex items-center justify-end gap-2">
                            <button 
                              onClick={() => handleOpenModal(med)} 
                              aria-label={`Edit ${med.name}`}
                              className="p-2 rounded-lg bg-white/5 text-slate-400 hover:text-brand-400 hover:bg-brand-500/10 transition-colors"
                            >
                              <Edit2 className="w-4 h-4" />
                            </button>
                            {user?.role === 'superadmin' && (
                              <button 
                                onClick={() => handleDelete(med.id)} 
                                aria-label={`Delete ${med.name}`}
                                className="p-2 rounded-lg bg-white/5 text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition-colors"
                              >
                                <Trash2 className="w-4 h-4" />
                              </button>
                            )}
                          </div>
                        </td>
                      </tr>
                    );
                  })
                )}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {/* ── Modal Overlay ────────────────────────────────────── */}
      {isModalOpen && (
        <div className="fixed inset-0 z-[100] flex items-center justify-center p-4">
          <div className="absolute inset-0 bg-black/60 backdrop-blur-sm" onClick={handleCloseModal} />
          
          <div className="relative glass-card w-full max-w-2xl max-h-[90vh] overflow-y-auto custom-scrollbar p-6 shadow-2xl">
            <h2 className="text-xl font-bold mb-4">{currentMedicine?.id ? 'Edit Medicine' : 'Add New Medicine'}</h2>
            
            {errorMsg && (
              <div className="flex items-center gap-2 p-3 rounded-lg bg-red-500/10 text-red-400 text-sm mb-4 border border-red-500/20">
                <AlertCircle className="w-4 h-4" /> {errorMsg}
              </div>
            )}

            <form onSubmit={handleSubmit} className="space-y-4">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="md:col-span-2">
                  <label htmlFor="med-name" className="block text-xs text-slate-400 mb-1">Name *</label>
                  <input id="med-name" required type="text" className="input-field" value={currentMedicine?.name || ''} onChange={e => setCurrentMedicine({...currentMedicine, name: e.target.value})} />
                </div>
                
                <div>
                  <label htmlFor="med-category" className="block text-xs text-slate-400 mb-1">Category</label>
                  <input id="med-category" type="text" className="input-field" value={currentMedicine?.category || ''} onChange={e => setCurrentMedicine({...currentMedicine, category: e.target.value})} placeholder="e.g. Analgesic" />
                </div>
                
                <div>
                  <label htmlFor="med-unit" className="block text-xs text-slate-400 mb-1">Unit</label>
                  <input id="med-unit" type="text" className="input-field" value={currentMedicine?.unit || ''} onChange={e => setCurrentMedicine({...currentMedicine, unit: e.target.value})} placeholder="e.g. pcs, tablet, syrup" />
                </div>

                <div>
                  <label htmlFor="med-buy-price" className="block text-xs text-slate-400 mb-1">Buy Price (IDR)</label>
                  <input id="med-buy-price" type="number" min="0" className="input-field font-mono" value={currentMedicine?.buy_price || ''} onChange={e => setCurrentMedicine({...currentMedicine, buy_price: Number(e.target.value)})} />
                </div>

                <div>
                  <label htmlFor="med-sell-price" className="block text-xs text-slate-400 mb-1">Sell Price (IDR)</label>
                  <input id="med-sell-price" type="number" min="0" className="input-field font-mono" value={currentMedicine?.sell_price || ''} onChange={e => setCurrentMedicine({...currentMedicine, sell_price: Number(e.target.value)})} />
                </div>

                <div>
                  <label htmlFor="med-stock" className="block text-xs text-slate-400 mb-1">Current Stock</label>
                  <input id="med-stock" type="number" min="0" className="input-field font-mono" value={currentMedicine?.stock_current || ''} onChange={e => setCurrentMedicine({...currentMedicine, stock_current: Number(e.target.value)})} />
                </div>

                <div>
                  <label htmlFor="med-safety" className="block text-xs text-slate-400 mb-1">Safety Stock Level</label>
                  <input id="med-safety" type="number" min="0" className="input-field font-mono" value={currentMedicine?.safety_stock || ''} onChange={e => setCurrentMedicine({...currentMedicine, safety_stock: Number(e.target.value)})} />
                </div>

                <div>
                  <label htmlFor="med-expiry" className="block text-xs text-slate-400 mb-1">Expiry Date (Optional)</label>
                  <input id="med-expiry" type="date" className="input-field [color-scheme:dark]" value={currentMedicine?.expired_at?.toString() || ''} onChange={e => setCurrentMedicine({...currentMedicine, expired_at: e.target.value || null})} />
                </div>

                <div>
                  <label htmlFor="med-season" className="block text-xs text-slate-400 mb-1">Seasonal Tag</label>
                  <select id="med-season" className="input-field appearance-none bg-surface-card" value={currentMedicine?.seasonal_tag || 'None'} onChange={e => setCurrentMedicine({...currentMedicine, seasonal_tag: e.target.value as 'Rainy' | 'Dry' | 'None'})}>
                    <option value="None">None</option>
                    <option value="Rainy">Rainy Season</option>
                    <option value="Dry">Dry Season</option>
                  </select>
                </div>
              </div>

              <div className="flex justify-end gap-3 mt-6 pt-4 border-t border-white/10">
                <button type="button" onClick={handleCloseModal} className="btn-ghost">Cancel</button>
                <button type="submit" disabled={isSubmitting} className="btn-primary">
                  {isSubmitting && <Loader2 className="w-4 h-4 animate-spin" />}
                  Save Medicine
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
