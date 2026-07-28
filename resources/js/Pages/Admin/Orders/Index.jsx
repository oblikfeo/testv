import { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import StatCard from '@/Components/admin/StatCard';
import Pagination from '@/Components/admin/Pagination';

function money(v) {
    return new Intl.NumberFormat('ru-RU').format(v ?? 0) + ' ₽';
}

const STATUS = {
    fulfilled: { label: 'Оплачен', cls: 'bg-emerald-500/15 text-emerald-300' },
    pending: { label: 'Ожидает', cls: 'bg-amber-500/15 text-amber-300' },
    cancelled: { label: 'Отменён', cls: 'bg-white/10 text-white/50' },
};

export default function OrdersIndex({ orders, filters, revenue, sources }) {
    const [q, setQ] = useState(filters.q ?? '');

    function apply(extra = {}) {
        router.get(route('admin.orders'), { q, status: filters.status, source: filters.source, ...extra }, { preserveState: true, replace: true });
    }

    function sync(id) {
        router.post(route('admin.orders.sync', id), {}, { preserveScroll: true });
    }

    const selectCls = 'rounded-xl border border-white/10 bg-black/30 px-3 py-2 text-sm text-white outline-none focus:border-red-500/50';

    return (
        <AdminLayout title="Заказы">
            <header className="mb-6">
                <h1 className="text-2xl font-bold text-white sm:text-3xl">Заказы / платежи</h1>
            </header>

            <div className="mb-6 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3">
                <StatCard label="Выручка сегодня" value={money(revenue.today)} accent />
                <StatCard label="Выручка за месяц" value={money(revenue.month)} />
                <StatCard label="Оплачено (по фильтру)" value={money(revenue.filtered)} />
            </div>

            <div className="rounded-2xl border border-white/10 bg-white/[0.035] p-5">
                {/* Filters */}
                <div className="mb-4 flex flex-wrap items-center gap-2">
                    <form onSubmit={(e) => { e.preventDefault(); apply(); }} className="flex gap-2">
                        <input type="text" value={q} onChange={(e) => setQ(e.target.value)} placeholder="Юзер, payment_id, id…"
                            className="w-52 rounded-xl border border-white/10 bg-black/30 px-4 py-2 text-sm text-white placeholder-white/30 outline-none focus:border-red-500/50 sm:w-64" />
                        <button type="submit" className="rounded-xl bg-gradient-to-r from-red-600 to-fuchsia-600 px-4 py-2 text-sm font-semibold text-white transition hover:brightness-110">Найти</button>
                    </form>
                    <select value={filters.status} onChange={(e) => apply({ status: e.target.value })} className={selectCls}>
                        <option value="" className="bg-ink-900">Все статусы</option>
                        <option value="fulfilled" className="bg-ink-900">Оплачен</option>
                        <option value="pending" className="bg-ink-900">Ожидает</option>
                        <option value="cancelled" className="bg-ink-900">Отменён</option>
                    </select>
                    <select value={filters.source} onChange={(e) => apply({ source: e.target.value })} className={selectCls}>
                        <option value="" className="bg-ink-900">Все источники</option>
                        {sources.map((s) => <option key={s} value={s} className="bg-ink-900">{s}</option>)}
                    </select>
                    {(filters.q || filters.status || filters.source) && (
                        <button onClick={() => { setQ(''); router.get(route('admin.orders'), {}, { preserveState: true, replace: true }); }}
                            className="rounded-xl border border-white/10 px-4 py-2 text-sm text-white/60 transition hover:text-white">Сброс</button>
                    )}
                </div>

                {/* Mobile: cards */}
                <div className="flex flex-col gap-2 sm:hidden">
                    {orders.data.length === 0 && <p className="py-6 text-center text-white/40">Ничего не найдено</p>}
                    {orders.data.map((o) => {
                        const s = STATUS[o.status] ?? { label: o.status ?? '—', cls: 'bg-white/10 text-white/50' };
                        const refunded = o.paymentStatus === 'refunded';
                        return (
                            <div key={o.id} className="rounded-xl border border-white/10 bg-black/20 px-3.5 py-3">
                                <div className="flex items-start justify-between gap-2">
                                    <div className="min-w-0">
                                        {o.user
                                            ? <Link href={route('admin.users.show', o.user.id)} className="block truncate font-medium text-white">{o.user.label}</Link>
                                            : <span className="text-white/40">—</span>}
                                        <div className="truncate text-xs text-white/35">#{o.id} · {o.plan} · {o.createdAt}</div>
                                    </div>
                                    <div className="shrink-0 text-right">
                                        <div className="font-semibold text-white">{money(o.amount)}</div>
                                        <span className={`rounded-full px-2 py-0.5 text-[11px] font-semibold ${s.cls}`}>{s.label}</span>
                                    </div>
                                </div>
                                <div className="mt-2 flex items-center justify-between gap-2">
                                    <span className="truncate text-xs text-white/40">
                                        {refunded ? 'возврат' : (o.paymentStatus ?? '—')}{o.method ? ` · ${o.method}` : ''} · {o.source}
                                    </span>
                                    {o.canSync && <button onClick={() => sync(o.id)} className="shrink-0 rounded-lg border border-white/10 px-2.5 py-1 text-xs text-white/60">Синхр.</button>}
                                </div>
                            </div>
                        );
                    })}
                </div>

                {/* Desktop: table */}
                <div className="hidden overflow-x-auto sm:block">
                    <table className="w-full text-left text-sm">
                        <thead className="text-xs uppercase tracking-wider text-white/35">
                            <tr>
                                <th className="pb-3 font-medium">#</th>
                                <th className="pb-3 font-medium">Пользователь</th>
                                <th className="pb-3 font-medium">Тариф</th>
                                <th className="pb-3 text-right font-medium">Сумма</th>
                                <th className="pb-3 font-medium">Статус</th>
                                <th className="pb-3 font-medium">Оплата</th>
                                <th className="pb-3 text-right font-medium">Дата</th>
                                <th className="pb-3 text-right font-medium"></th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-white/5">
                            {orders.data.length === 0 && <tr><td colSpan={8} className="py-6 text-center text-white/40">Ничего не найдено</td></tr>}
                            {orders.data.map((o) => {
                                const s = STATUS[o.status] ?? { label: o.status ?? '—', cls: 'bg-white/10 text-white/50' };
                                const refunded = o.paymentStatus === 'refunded';
                                return (
                                    <tr key={o.id}>
                                        <td className="py-2.5 pr-2 text-white/40">{o.id}</td>
                                        <td className="py-2.5 pr-2">
                                            {o.user ? (
                                                <Link href={route('admin.users.show', o.user.id)} className="font-medium text-white transition hover:text-red-300">{o.user.label}</Link>
                                            ) : <span className="text-white/40">—</span>}
                                            <div className="text-xs text-white/30">{o.source}{o.action === 'renew_subscription' ? ' · продление' : ''}</div>
                                        </td>
                                        <td className="py-2.5 pr-2 text-white/70">{o.plan}</td>
                                        <td className="py-2.5 pr-2 text-right font-semibold text-white">{money(o.amount)}</td>
                                        <td className="py-2.5 pr-2"><span className={`rounded-full px-2 py-0.5 text-xs font-semibold ${s.cls}`}>{s.label}</span></td>
                                        <td className="py-2.5 pr-2">
                                            {refunded
                                                ? <span className="rounded-full bg-fuchsia-500/15 px-2 py-0.5 text-xs font-semibold text-fuchsia-300">возврат</span>
                                                : <span className="text-xs text-white/45">{o.paymentStatus ?? '—'}{o.method ? ` · ${o.method}` : ''}</span>}
                                        </td>
                                        <td className="py-2.5 text-right text-white/45">{o.createdAt}</td>
                                        <td className="py-2.5 text-right">
                                            {o.canSync && (
                                                <button onClick={() => sync(o.id)} className="rounded-lg border border-white/10 px-2.5 py-1 text-xs text-white/60 transition hover:text-white">Синхр.</button>
                                            )}
                                        </td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>
                <Pagination links={orders.links} />
            </div>
        </AdminLayout>
    );
}
