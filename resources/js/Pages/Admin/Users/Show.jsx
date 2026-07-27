import { useState } from 'react';
import { Link, router, useForm } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import AccessBadge from '@/Components/admin/AccessBadge';

function money(v) {
    return new Intl.NumberFormat('ru-RU').format(v ?? 0) + ' ₽';
}

const ORDER_STATUS = {
    fulfilled: { label: 'Оплачен', cls: 'bg-emerald-500/15 text-emerald-300' },
    pending: { label: 'Ожидает', cls: 'bg-amber-500/15 text-amber-300' },
    cancelled: { label: 'Отменён', cls: 'bg-white/10 text-white/50' },
};

function Card({ title, action, children }) {
    return (
        <section className="rounded-2xl border border-white/10 bg-white/[0.035] p-5">
            {title && (
                <div className="mb-4 flex items-center justify-between gap-3">
                    <h2 className="text-lg font-bold text-white">{title}</h2>
                    {action}
                </div>
            )}
            {children}
        </section>
    );
}

function SubscriptionRow({ userId, sub }) {
    const [days, setDays] = useState(30);

    function extend() {
        router.post(route('admin.users.subscriptions.extend', [userId, sub.id]), { days }, { preserveScroll: true });
    }

    function expire() {
        if (!confirm(`Деактивировать подписку #${sub.id}?`)) return;
        router.post(route('admin.users.subscriptions.expire', [userId, sub.id]), {}, { preserveScroll: true });
    }

    return (
        <div className="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-white/10 bg-black/20 px-4 py-3">
            <div>
                <div className="flex items-center gap-2">
                    <span className="font-semibold text-white">{sub.plan}</span>
                    <span className={`rounded-full px-2 py-0.5 text-xs font-semibold ${sub.isActive ? 'bg-emerald-500/15 text-emerald-300' : 'bg-white/10 text-white/45'}`}>
                        {sub.isActive ? `активна · ${sub.daysLeft} дн.` : sub.status}
                    </span>
                </div>
                <div className="mt-0.5 text-xs text-white/45">
                    #{sub.id} · {sub.maxDevices} устр. · до {sub.expiresAt} · {sub.source}
                </div>
            </div>
            <div className="flex items-center gap-2">
                <input type="number" min="1" value={days} onChange={(e) => setDays(e.target.value)}
                    className="w-16 rounded-lg border border-white/10 bg-black/30 px-2 py-1 text-sm text-white outline-none focus:border-red-500/50" />
                <button onClick={extend} className="rounded-lg border border-white/10 px-3 py-1 text-sm text-white/70 transition hover:text-white">+ дней</button>
                {sub.isActive && (
                    <button onClick={expire} className="rounded-lg border border-red-500/25 px-3 py-1 text-sm text-red-300 transition hover:bg-red-500/10">Стоп</button>
                )}
            </div>
        </div>
    );
}

export default function UserShow({ user, subscriptions, orders, trial, tickets, plans }) {
    const grant = useForm({ plan_id: plans[0]?.id ?? '' });
    const trialForm = useForm({ hours: 3, gb: 0 });
    const [copied, setCopied] = useState(false);

    function submitGrant(e) {
        e.preventDefault();
        grant.post(route('admin.users.subscriptions.grant', user.id), { preserveScroll: true });
    }

    function submitTrial(e) {
        e.preventDefault();
        trialForm.post(route('admin.users.trial.issue', user.id), { preserveScroll: true });
    }

    function revokeTrial() {
        if (!confirm('Отозвать триал?')) return;
        router.delete(route('admin.users.trial.revoke', user.id), { preserveScroll: true });
    }

    function copyLink() {
        if (!user.subLink) return;
        navigator.clipboard?.writeText(user.subLink).then(() => {
            setCopied(true);
            setTimeout(() => setCopied(false), 1500);
        });
    }

    return (
        <AdminLayout title={user.name}>
            <div className="mb-6">
                <Link href={route('admin.users')} className="text-sm text-white/45 transition hover:text-white">← К списку</Link>
                <div className="mt-2 flex flex-wrap items-center gap-3">
                    <h1 className="text-2xl font-bold text-white sm:text-3xl">{user.name}</h1>
                    <AccessBadge status={user.access} />
                </div>
            </div>

            <div className="grid gap-6 lg:grid-cols-3">
                {/* Left column */}
                <div className="flex flex-col gap-6 lg:col-span-2">
                    {/* Subscriptions */}
                    <Card title="Подписки">
                        <form onSubmit={submitGrant} className="mb-4 flex flex-wrap items-center gap-2">
                            <select value={grant.data.plan_id} onChange={(e) => grant.setData('plan_id', e.target.value)}
                                className="flex-1 rounded-xl border border-white/10 bg-black/30 px-3 py-2 text-sm text-white outline-none focus:border-red-500/50">
                                {plans.map((p) => <option key={p.id} value={p.id} className="bg-ink-900">{p.label}</option>)}
                            </select>
                            <button type="submit" disabled={grant.processing}
                                className="rounded-xl bg-gradient-to-r from-red-600 to-fuchsia-600 px-4 py-2 text-sm font-semibold text-white transition hover:brightness-110 disabled:opacity-60">
                                Выдать подписку
                            </button>
                        </form>
                        <div className="flex flex-col gap-2">
                            {subscriptions.length === 0 && <p className="text-sm text-white/40">Нет подписок</p>}
                            {subscriptions.map((s) => <SubscriptionRow key={s.id} userId={user.id} sub={s} />)}
                        </div>
                    </Card>

                    {/* Orders */}
                    <Card title="Заказы">
                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-sm">
                                <thead className="text-xs uppercase tracking-wider text-white/35">
                                    <tr>
                                        <th className="pb-2 font-medium">#</th>
                                        <th className="pb-2 font-medium">Тариф</th>
                                        <th className="pb-2 text-right font-medium">Сумма</th>
                                        <th className="pb-2 text-right font-medium">Статус</th>
                                        <th className="pb-2 text-right font-medium">Дата</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-white/5">
                                    {orders.length === 0 && <tr><td colSpan={5} className="py-4 text-center text-white/40">Нет заказов</td></tr>}
                                    {orders.map((o) => {
                                        const s = ORDER_STATUS[o.status] ?? { label: o.status ?? '—', cls: 'bg-white/10 text-white/50' };
                                        return (
                                            <tr key={o.id}>
                                                <td className="py-2.5 pr-2 text-white/40">{o.id}</td>
                                                <td className="py-2.5 pr-2 text-white/70">{o.plan}</td>
                                                <td className="py-2.5 pr-2 text-right font-semibold text-white">{money(o.amount)}</td>
                                                <td className="py-2.5 pr-2 text-right"><span className={`rounded-full px-2 py-0.5 text-xs font-semibold ${s.cls}`}>{s.label}</span></td>
                                                <td className="py-2.5 text-right text-white/45">{o.createdAt}</td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </table>
                        </div>
                    </Card>

                    {/* Tickets */}
                    {tickets.length > 0 && (
                        <Card title="Тикеты">
                            <div className="flex flex-col gap-2">
                                {tickets.map((t) => (
                                    <a key={t.id} href={`/admin/support/${t.id}`}
                                        className="flex items-center justify-between gap-3 rounded-xl border border-white/10 bg-black/20 px-4 py-2.5 text-sm transition hover:bg-white/[0.04]">
                                        <span className="text-white">{t.subject}</span>
                                        <span className="text-xs text-white/40">{t.category} · {t.status} · {t.lastMessageAt}</span>
                                    </a>
                                ))}
                            </div>
                        </Card>
                    )}
                </div>

                {/* Right column */}
                <div className="flex flex-col gap-6">
                    <Card title="Профиль">
                        <dl className="flex flex-col gap-3 text-sm">
                            <div className="flex justify-between gap-2"><dt className="text-white/40">ID</dt><dd className="font-medium text-white">#{user.id}</dd></div>
                            <div className="flex justify-between gap-2"><dt className="text-white/40">Email</dt><dd className="text-right font-medium text-white">{user.email ?? '—'}{user.email && !user.emailVerified && <span className="ml-1 text-amber-300">(не подтв.)</span>}</dd></div>
                            {user.telegramId && <div className="flex justify-between gap-2"><dt className="text-white/40">Telegram</dt><dd className="font-medium text-white">{user.telegramUsername ? '@' + user.telegramUsername : user.telegramId}</dd></div>}
                            <div className="flex justify-between gap-2"><dt className="text-white/40">Регистрация</dt><dd className="font-medium text-white">{user.createdAt}</dd></div>
                            <div className="flex justify-between gap-2"><dt className="text-white/40">Триал использован</dt><dd className="font-medium text-white">{user.trialUsed ? 'да' : 'нет'}</dd></div>
                        </dl>
                        {user.subLink && (
                            <div className="mt-4 border-t border-white/10 pt-4">
                                <div className="mb-1.5 text-xs uppercase tracking-wider text-white/35">Ссылка подписки</div>
                                <div className="flex items-center gap-2">
                                    <code className="min-w-0 flex-1 truncate rounded-lg bg-black/30 px-3 py-2 text-xs text-white/70">{user.subLink}</code>
                                    <button onClick={copyLink} className="shrink-0 rounded-lg border border-white/10 px-3 py-2 text-xs text-white/70 transition hover:text-white">{copied ? '✓' : 'Копир.'}</button>
                                </div>
                            </div>
                        )}
                    </Card>

                    {/* Trial */}
                    <Card title="Триал">
                        {trial ? (
                            <div className="mb-4 rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-sm">
                                <div className="flex items-center justify-between">
                                    <span className={`font-semibold ${trial.isActive ? 'text-sky-300' : 'text-white/50'}`}>{trial.isActive ? 'Активен' : 'Истёк'}</span>
                                    <button onClick={revokeTrial} className="rounded-lg border border-red-500/25 px-2.5 py-1 text-xs text-red-300 transition hover:bg-red-500/10">Отозвать</button>
                                </div>
                                <div className="mt-1.5 text-xs text-white/45">
                                    До {trial.expiresAt} · осталось {trial.remaining}
                                    {trial.totalGb > 0 && <> · {trial.usedGb}/{trial.totalGb} ГБ ({trial.percent}%)</>}
                                </div>
                            </div>
                        ) : (
                            <p className="mb-4 text-sm text-white/40">Триал не выдавался.</p>
                        )}
                        <form onSubmit={submitTrial} className="flex flex-wrap items-end gap-2">
                            <label className="text-xs text-white/50">Часы
                                <input type="number" min="1" max="168" value={trialForm.data.hours} onChange={(e) => trialForm.setData('hours', e.target.value)}
                                    className="mt-1 w-20 rounded-lg border border-white/10 bg-black/30 px-2 py-1.5 text-sm text-white outline-none focus:border-red-500/50" />
                            </label>
                            <label className="text-xs text-white/50">ГБ (0=∞)
                                <input type="number" min="0" max="100" value={trialForm.data.gb} onChange={(e) => trialForm.setData('gb', e.target.value)}
                                    className="mt-1 w-20 rounded-lg border border-white/10 bg-black/30 px-2 py-1.5 text-sm text-white outline-none focus:border-red-500/50" />
                            </label>
                            <button type="submit" disabled={trialForm.processing}
                                className="rounded-xl border border-white/10 px-4 py-2 text-sm font-semibold text-white/80 transition hover:bg-white/[0.06] hover:text-white disabled:opacity-60">
                                {trial ? 'Перевыдать' : 'Выдать'}
                            </button>
                        </form>
                    </Card>
                </div>
            </div>
        </AdminLayout>
    );
}
