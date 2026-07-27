import AdminLayout from '@/Layouts/AdminLayout';
import StatCard from '@/Components/admin/StatCard';

function money(v) {
    return new Intl.NumberFormat('ru-RU').format(v ?? 0) + ' ₽';
}

const ORDER_STATUS = {
    fulfilled: { label: 'Оплачен', cls: 'bg-emerald-500/15 text-emerald-300' },
    pending: { label: 'Ожидает', cls: 'bg-amber-500/15 text-amber-300' },
    cancelled: { label: 'Отменён', cls: 'bg-white/10 text-white/50' },
};

function OrderStatusBadge({ status }) {
    const s = ORDER_STATUS[status] ?? { label: status ?? '—', cls: 'bg-white/10 text-white/50' };
    return <span className={`rounded-full px-2.5 py-0.5 text-xs font-semibold ${s.cls}`}>{s.label}</span>;
}

export default function Dashboard({ stats, recentOrders, recentUsers, nodes }) {
    return (
        <AdminLayout title="Дашборд">
            <header className="mb-6">
                <h1 className="text-2xl font-bold text-white sm:text-3xl">Дашборд</h1>
                <p className="mt-1 text-white/50">Обзор ключевых показателей сервиса.</p>
            </header>

            <div className="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                <StatCard label="Активные подписки" value={stats.activeSubscriptions} accent />
                <StatCard label="Выручка сегодня" value={money(stats.revenueToday)} sub={`За месяц: ${money(stats.revenueMonth)}`} />
                <StatCard label="Новые за 7 дней" value={stats.newUsersWeek} sub={`Сегодня: ${stats.newUsersToday} · всего ${stats.totalUsers}`} />
                <StatCard label="Активные триалы" value={stats.activeTrials} />
                <StatCard label="Заказы в ожидании" value={stats.pendingOrders} />
                <StatCard label="Открытые тикеты" value={stats.openTickets} />
                <div className="col-span-2 rounded-2xl border border-white/10 bg-white/[0.035] p-5">
                    <div className="text-xs font-semibold uppercase tracking-wider text-white/40">Узлы в подписке</div>
                    <div className="mt-2 flex flex-col gap-1.5">
                        {nodes.length === 0 && <span className="text-sm text-white/45">Не настроены</span>}
                        {nodes.map((n, i) => (
                            <div key={i} className="flex items-center gap-2 text-sm">
                                <span className="h-1.5 w-1.5 rounded-full bg-emerald-400" />
                                <span className="font-medium text-white">{n.host}</span>
                                <span className="text-white/35">· {n.scheme}</span>
                            </div>
                        ))}
                    </div>
                </div>
            </div>

            <div className="mt-6 grid gap-6 lg:grid-cols-2">
                {/* Recent orders */}
                <section className="rounded-2xl border border-white/10 bg-white/[0.035] p-5">
                    <h2 className="mb-4 text-lg font-bold text-white">Последние заказы</h2>
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="text-xs uppercase tracking-wider text-white/35">
                                <tr>
                                    <th className="pb-2 font-medium">Пользователь</th>
                                    <th className="pb-2 font-medium">Тариф</th>
                                    <th className="pb-2 text-right font-medium">Сумма</th>
                                    <th className="pb-2 text-right font-medium">Статус</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-white/5">
                                {recentOrders.length === 0 && (
                                    <tr><td colSpan={4} className="py-4 text-center text-white/40">Пока нет заказов</td></tr>
                                )}
                                {recentOrders.map((o) => (
                                    <tr key={o.id}>
                                        <td className="py-2.5 pr-2">
                                            <div className="font-medium text-white">{o.user}</div>
                                            <div className="text-xs text-white/35">{o.createdAt} · {o.source}</div>
                                        </td>
                                        <td className="py-2.5 pr-2 text-white/70">{o.plan}</td>
                                        <td className="py-2.5 pr-2 text-right font-semibold text-white">{money(o.amount)}</td>
                                        <td className="py-2.5 text-right"><OrderStatusBadge status={o.status} /></td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </section>

                {/* Recent users */}
                <section className="rounded-2xl border border-white/10 bg-white/[0.035] p-5">
                    <h2 className="mb-4 text-lg font-bold text-white">Новые пользователи</h2>
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="text-xs uppercase tracking-wider text-white/35">
                                <tr>
                                    <th className="pb-2 font-medium">Имя</th>
                                    <th className="pb-2 font-medium">Контакт</th>
                                    <th className="pb-2 text-right font-medium">Регистрация</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-white/5">
                                {recentUsers.length === 0 && (
                                    <tr><td colSpan={3} className="py-4 text-center text-white/40">Пока нет пользователей</td></tr>
                                )}
                                {recentUsers.map((u) => (
                                    <tr key={u.id}>
                                        <td className="py-2.5 pr-2 font-medium text-white">{u.name}</td>
                                        <td className="py-2.5 pr-2 text-white/70">
                                            {u.isTelegram && <span className="mr-1.5 rounded bg-sky-500/15 px-1.5 py-0.5 text-[10px] font-semibold text-sky-300">TG</span>}
                                            {u.contact}
                                        </td>
                                        <td className="py-2.5 text-right text-white/50">{u.createdAt}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </AdminLayout>
    );
}
