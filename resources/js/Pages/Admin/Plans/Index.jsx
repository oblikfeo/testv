import { useState } from 'react';
import { router, useForm } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Modal from '@/Components/admin/Modal';

const inputCls = 'w-full rounded-xl border border-white/10 bg-black/30 px-3 py-2 text-sm text-white outline-none focus:border-red-500/50';

const EMPTY = {
    name: '', slug: '', devices: 2, days: 30, price: 0,
    discount: 0, traffic_gb: 0, is_popular: false, is_active: true, sort_order: 0,
};

function Field({ label, children }) {
    return (
        <label className="block">
            <span className="mb-1 block text-xs font-medium text-white/60">{label}</span>
            {children}
        </label>
    );
}

export default function PlansIndex({ plans }) {
    const [open, setOpen] = useState(false);
    const [editId, setEditId] = useState(null);
    const form = useForm({ ...EMPTY });

    function openCreate() {
        setEditId(null);
        form.setData({ ...EMPTY });
        form.clearErrors();
        setOpen(true);
    }

    function openEdit(p) {
        setEditId(p.id);
        form.setData({
            name: p.name, slug: p.slug, devices: p.devices, days: p.days, price: p.price,
            discount: p.discount, traffic_gb: p.trafficGb, is_popular: p.isPopular, is_active: p.isActive, sort_order: p.sortOrder,
        });
        form.clearErrors();
        setOpen(true);
    }

    function submit(e) {
        e.preventDefault();
        const opts = { preserveScroll: true, onSuccess: () => setOpen(false) };
        if (editId) form.put(route('admin.plans.update', editId), opts);
        else form.post(route('admin.plans.store'), opts);
    }

    function toggle(p) {
        router.post(route('admin.plans.toggle', p.id), {}, { preserveScroll: true });
    }

    function remove(p) {
        if (!confirm(`Удалить тариф «${p.name}»?`)) return;
        router.delete(route('admin.plans.destroy', p.id), { preserveScroll: true });
    }

    return (
        <AdminLayout title="Тарифы">
            <header className="mb-6 flex items-center justify-between gap-4">
                <h1 className="text-2xl font-bold text-white sm:text-3xl">Тарифы</h1>
                <button onClick={openCreate} className="rounded-full bg-gradient-to-r from-red-600 to-fuchsia-600 px-5 py-2.5 text-sm font-semibold text-white shadow-glow transition hover:brightness-110">+ Тариф</button>
            </header>

            <div className="rounded-2xl border border-white/10 bg-white/[0.035] p-4 sm:p-5">
                {/* Mobile: cards */}
                <div className="flex flex-col gap-2 sm:hidden">
                    {plans.map((p) => (
                        <div key={p.id} className={`rounded-xl border border-white/10 bg-black/20 px-3.5 py-3 ${p.isActive ? '' : 'opacity-50'}`}>
                            <div className="flex items-center justify-between gap-2">
                                <div className="min-w-0">
                                    <div className="font-medium text-white">{p.name} {p.isPopular && <span className="ml-1 rounded bg-fuchsia-500/15 px-1.5 py-0.5 text-[9px] font-semibold text-fuchsia-300">POPULAR</span>}</div>
                                    <div className="truncate text-xs text-white/40"><code>{p.slug}</code> · {p.devices} устр. · {p.days} дн. · {p.subscriptionsCount} подп.</div>
                                </div>
                                <div className="shrink-0 text-right font-semibold text-white">{p.price} ₽{p.discount > 0 && <span className="ml-1 text-xs text-emerald-300">−{p.discount}%</span>}</div>
                            </div>
                            <div className="mt-2.5 flex items-center justify-end gap-1.5">
                                <button onClick={() => toggle(p)} className={`mr-auto rounded px-2 py-1 text-[11px] font-semibold ${p.isActive ? 'bg-emerald-500/15 text-emerald-300' : 'bg-white/10 text-white/45'}`}>{p.isActive ? 'ON' : 'OFF'}</button>
                                <button onClick={() => openEdit(p)} className="rounded-lg border border-white/10 px-2.5 py-1 text-xs text-white/70">Ред.</button>
                                <button onClick={() => remove(p)} className="rounded-lg border border-red-500/25 px-2.5 py-1 text-xs text-red-300">Удл.</button>
                            </div>
                        </div>
                    ))}
                </div>

                {/* Desktop: table */}
                <div className="hidden overflow-x-auto sm:block">
                    <table className="w-full text-left text-sm">
                        <thead className="text-xs uppercase tracking-wider text-white/35">
                            <tr>
                                <th className="pb-3 font-medium">Название</th>
                                <th className="pb-3 font-medium">slug</th>
                                <th className="pb-3 font-medium">Устр.</th>
                                <th className="pb-3 font-medium">Дней</th>
                                <th className="pb-3 text-right font-medium">Цена</th>
                                <th className="pb-3 font-medium">Флаги</th>
                                <th className="pb-3 text-right font-medium">Подписки</th>
                                <th className="pb-3 text-right font-medium"></th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-white/5">
                            {plans.map((p) => (
                                <tr key={p.id} className={p.isActive ? '' : 'opacity-50'}>
                                    <td className="py-2.5 pr-2 font-medium text-white">{p.name}</td>
                                    <td className="py-2.5 pr-2"><code className="text-xs text-white/50">{p.slug}</code></td>
                                    <td className="py-2.5 pr-2 text-white/70">{p.devices}</td>
                                    <td className="py-2.5 pr-2 text-white/70">{p.days}</td>
                                    <td className="py-2.5 pr-2 text-right font-semibold text-white">{p.price} ₽{p.discount > 0 && <span className="ml-1 text-xs text-emerald-300">−{p.discount}%</span>}</td>
                                    <td className="py-2.5 pr-2">
                                        {p.isPopular && <span className="mr-1 rounded bg-fuchsia-500/15 px-1.5 py-0.5 text-[10px] font-semibold text-fuchsia-300">POPULAR</span>}
                                        <button onClick={() => toggle(p)} className={`rounded px-1.5 py-0.5 text-[10px] font-semibold transition ${p.isActive ? 'bg-emerald-500/15 text-emerald-300' : 'bg-white/10 text-white/45'}`}>
                                            {p.isActive ? 'ON' : 'OFF'}
                                        </button>
                                    </td>
                                    <td className="py-2.5 pr-2 text-right text-white/50">{p.subscriptionsCount}</td>
                                    <td className="py-2.5 text-right">
                                        <div className="flex items-center justify-end gap-1.5">
                                            <button onClick={() => openEdit(p)} className="rounded-lg border border-white/10 px-2.5 py-1 text-xs text-white/70 transition hover:text-white">Ред.</button>
                                            <button onClick={() => remove(p)} className="rounded-lg border border-red-500/25 px-2.5 py-1 text-xs text-red-300 transition hover:bg-red-500/10">Удл.</button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>

            <Modal open={open} onClose={() => setOpen(false)} title={editId ? 'Редактировать тариф' : 'Новый тариф'}>
                <form onSubmit={submit} className="space-y-3">
                    <div className="grid grid-cols-2 gap-3">
                        <Field label="Название"><input className={inputCls} value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} /></Field>
                        <Field label="slug"><input className={inputCls} value={form.data.slug} onChange={(e) => form.setData('slug', e.target.value)} /></Field>
                    </div>
                    {(form.errors.name || form.errors.slug) && <p className="text-xs text-red-400">{form.errors.name || form.errors.slug}</p>}
                    <div className="grid grid-cols-3 gap-3">
                        <Field label="Устройства"><input type="number" className={inputCls} value={form.data.devices} onChange={(e) => form.setData('devices', e.target.value)} /></Field>
                        <Field label="Дней"><input type="number" className={inputCls} value={form.data.days} onChange={(e) => form.setData('days', e.target.value)} /></Field>
                        <Field label="Цена ₽"><input type="number" className={inputCls} value={form.data.price} onChange={(e) => form.setData('price', e.target.value)} /></Field>
                    </div>
                    <div className="grid grid-cols-3 gap-3">
                        <Field label="Скидка %"><input type="number" className={inputCls} value={form.data.discount} onChange={(e) => form.setData('discount', e.target.value)} /></Field>
                        <Field label="Трафик ГБ (0=∞)"><input type="number" className={inputCls} value={form.data.traffic_gb} onChange={(e) => form.setData('traffic_gb', e.target.value)} /></Field>
                        <Field label="Сортировка"><input type="number" className={inputCls} value={form.data.sort_order} onChange={(e) => form.setData('sort_order', e.target.value)} /></Field>
                    </div>
                    <div className="flex gap-5 pt-1">
                        <label className="flex items-center gap-2 text-sm text-white/70">
                            <input type="checkbox" checked={form.data.is_active} onChange={(e) => form.setData('is_active', e.target.checked)} className="h-4 w-4 rounded border-white/20 bg-black/30 text-red-500" /> Активен
                        </label>
                        <label className="flex items-center gap-2 text-sm text-white/70">
                            <input type="checkbox" checked={form.data.is_popular} onChange={(e) => form.setData('is_popular', e.target.checked)} className="h-4 w-4 rounded border-white/20 bg-black/30 text-red-500" /> Популярный
                        </label>
                    </div>
                    <div className="flex justify-end gap-2 pt-3">
                        <button type="button" onClick={() => setOpen(false)} className="rounded-full border border-white/10 px-4 py-2 text-sm text-white/60 transition hover:text-white">Отмена</button>
                        <button type="submit" disabled={form.processing} className="rounded-full bg-gradient-to-r from-red-600 to-fuchsia-600 px-5 py-2 text-sm font-semibold text-white transition hover:brightness-110 disabled:opacity-60">
                            {editId ? 'Сохранить' : 'Создать'}
                        </button>
                    </div>
                </form>
            </Modal>
        </AdminLayout>
    );
}
