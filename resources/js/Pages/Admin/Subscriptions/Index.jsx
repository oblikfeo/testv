import { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Pagination from '@/Components/admin/Pagination';

function Row({ sub }) {
    const [days, setDays] = useState(30);

    function extend() {
        router.post(route('admin.subscriptions.extend', sub.id), { days }, { preserveScroll: true });
    }
    function expire() {
        if (!confirm(`Деактивировать подписку #${sub.id} (${sub.user?.label ?? ''})?`)) return;
        router.post(route('admin.subscriptions.expire', sub.id), {}, { preserveScroll: true });
    }

    return (
        <tr>
            <td className="py-2.5 pr-2 text-white/40">{sub.id}</td>
            <td className="py-2.5 pr-2">
                {sub.user
                    ? <Link href={route('admin.users.show', sub.user.id)} className="font-medium text-white transition hover:text-red-300">{sub.user.label}</Link>
                    : <span className="text-white/40">—</span>}
            </td>
            <td className="py-2.5 pr-2 text-white/70">{sub.plan}</td>
            <td className="py-2.5 pr-2 text-white/60">{sub.maxDevices}</td>
            <td className="py-2.5 pr-2">
                <span className={`rounded-full px-2 py-0.5 text-xs font-semibold ${sub.isActive ? 'bg-emerald-500/15 text-emerald-300' : 'bg-white/10 text-white/45'}`}>
                    {sub.isActive ? `активна · ${sub.daysLeft} дн.` : sub.status}
                </span>
            </td>
            <td className="py-2.5 pr-2 text-white/50">{sub.expiresAt}</td>
            <td className="py-2.5 text-right">
                <div className="flex items-center justify-end gap-1.5">
                    <input type="number" min="1" value={days} onChange={(e) => setDays(e.target.value)}
                        className="w-14 rounded-lg border border-white/10 bg-black/30 px-2 py-1 text-xs text-white outline-none focus:border-red-500/50" />
                    <button onClick={extend} className="rounded-lg border border-white/10 px-2.5 py-1 text-xs text-white/70 transition hover:text-white">+дн</button>
                    {sub.isActive && <button onClick={expire} className="rounded-lg border border-red-500/25 px-2.5 py-1 text-xs text-red-300 transition hover:bg-red-500/10">Стоп</button>}
                </div>
            </td>
        </tr>
    );
}

export default function SubscriptionsIndex({ subscriptions, filters, counts }) {
    const [q, setQ] = useState(filters.q ?? '');

    function apply(extra = {}) {
        router.get(route('admin.subscriptions'), { q, status: filters.status, ...extra }, { preserveState: true, replace: true });
    }

    const tab = (key, label) => (
        <button onClick={() => apply({ status: key })}
            className={`rounded-full px-4 py-1.5 text-sm font-medium transition ${filters.status === key ? 'bg-gradient-to-r from-red-600 to-fuchsia-600 text-white' : 'border border-white/10 text-white/55 hover:text-white'}`}>
            {label}
        </button>
    );

    return (
        <AdminLayout title="Подписки">
            <header className="mb-6 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-bold text-white sm:text-3xl">Подписки</h1>
                    <p className="mt-1 text-white/50">Активных: {counts.active} · всего: {counts.total}</p>
                </div>
                <form onSubmit={(e) => { e.preventDefault(); apply(); }} className="flex gap-2">
                    <input type="text" value={q} onChange={(e) => setQ(e.target.value)} placeholder="Пользователь…"
                        className="w-52 rounded-xl border border-white/10 bg-black/30 px-4 py-2 text-sm text-white placeholder-white/30 outline-none focus:border-red-500/50" />
                    <button type="submit" className="rounded-xl bg-gradient-to-r from-red-600 to-fuchsia-600 px-4 py-2 text-sm font-semibold text-white transition hover:brightness-110">Найти</button>
                </form>
            </header>

            <div className="mb-4 flex gap-2">
                {tab('active', 'Активные')}
                {tab('inactive', 'Истёкшие')}
                {tab('all', 'Все')}
            </div>

            <div className="rounded-2xl border border-white/10 bg-white/[0.035] p-5">
                <div className="overflow-x-auto">
                    <table className="w-full text-left text-sm">
                        <thead className="text-xs uppercase tracking-wider text-white/35">
                            <tr>
                                <th className="pb-3 font-medium">#</th>
                                <th className="pb-3 font-medium">Пользователь</th>
                                <th className="pb-3 font-medium">Тариф</th>
                                <th className="pb-3 font-medium">Устр.</th>
                                <th className="pb-3 font-medium">Статус</th>
                                <th className="pb-3 font-medium">Истекает</th>
                                <th className="pb-3 text-right font-medium">Действия</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-white/5">
                            {subscriptions.data.length === 0 && <tr><td colSpan={7} className="py-6 text-center text-white/40">Нет подписок</td></tr>}
                            {subscriptions.data.map((s) => <Row key={s.id} sub={s} />)}
                        </tbody>
                    </table>
                </div>
                <Pagination links={subscriptions.links} />
            </div>
        </AdminLayout>
    );
}
