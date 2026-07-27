import { Link, router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Pagination from '@/Components/admin/Pagination';

const STATUS = {
    open: { label: 'Открыт', cls: 'bg-red-500/15 text-red-300' },
    pending_user: { label: 'Ждёт юзера', cls: 'bg-amber-500/15 text-amber-300' },
    closed: { label: 'Закрыт', cls: 'bg-white/10 text-white/45' },
};

export default function SupportIndex({ tickets, filter, counters }) {
    function setFilter(status) {
        router.get(route('admin.support.index'), { status }, { preserveState: true, replace: true });
    }

    const tab = (key, label) => (
        <button onClick={() => setFilter(key)}
            className={`rounded-full px-4 py-1.5 text-sm font-medium transition ${filter === key ? 'bg-gradient-to-r from-red-600 to-fuchsia-600 text-white' : 'border border-white/10 text-white/55 hover:text-white'}`}>
            {label}
        </button>
    );

    return (
        <AdminLayout title="Поддержка">
            <header className="mb-6">
                <h1 className="text-2xl font-bold text-white sm:text-3xl">Поддержка</h1>
                <p className="mt-1 text-white/50">Открытых: {counters.open} · ждут юзера: {counters.pending_user} · закрытых: {counters.closed}</p>
            </header>

            <div className="mb-4 flex flex-wrap gap-2">
                {tab('active', 'Активные')}
                {tab('open', 'Открытые')}
                {tab('pending_user', 'Ждут юзера')}
                {tab('closed', 'Закрытые')}
            </div>

            <div className="rounded-2xl border border-white/10 bg-white/[0.035] p-5">
                <div className="flex flex-col divide-y divide-white/5">
                    {tickets.data.length === 0 && <p className="py-6 text-center text-white/40">Нет тикетов</p>}
                    {tickets.data.map((t) => {
                        const s = STATUS[t.status] ?? { label: t.statusLabel, cls: 'bg-white/10 text-white/45' };
                        return (
                            <Link key={t.id} href={route('admin.support.show', t.id)} className="flex items-center justify-between gap-4 py-3 transition hover:bg-white/[0.02]">
                                <div className="min-w-0">
                                    <div className="flex items-center gap-2">
                                        <span className={`rounded-full px-2 py-0.5 text-xs font-semibold ${s.cls}`}>{s.label}</span>
                                        <span className="truncate font-medium text-white">{t.subject}</span>
                                    </div>
                                    <div className="mt-0.5 text-xs text-white/40">
                                        #{t.id} · {t.category} · {t.user?.label ?? '—'} · {t.messagesCount} сообщ.
                                    </div>
                                </div>
                                <div className="shrink-0 text-xs text-white/35">{t.lastMessageAt}</div>
                            </Link>
                        );
                    })}
                </div>
                <Pagination links={tickets.links} />
            </div>
        </AdminLayout>
    );
}
