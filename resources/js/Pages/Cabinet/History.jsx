import { useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import CabinetLayout from '@/Layouts/CabinetLayout';
import GlassCard from '@/Components/ui/GlassCard';
import Alert from '@/Components/cabinet/Alert';
import TariffTier from '@/Components/cabinet/TariffTier';
import PurchaseChoiceModal from '@/Components/cabinet/PurchaseChoiceModal';

const STATUS_STYLES = {
    pending: { label: 'Ожидает оплаты', className: 'bg-amber-500/15 text-amber-300' },
    fulfilled: { label: 'Оплачен', className: 'bg-emerald-500/15 text-emerald-300' },
    cancelled: { label: 'Отменён', className: 'bg-white/10 text-white/45' },
};

export default function History({ tiers, orders }) {
    const { props } = usePage();
    const errorList = Object.values(props.errors ?? {});
    const [dismissed, setDismissed] = useState(false);
    const purchaseChoice = dismissed ? null : props.purchaseChoice;

    return (
        <CabinetLayout title="Покупки">
            <header className="mb-6">
                <h1 className="text-2xl font-bold text-white sm:text-3xl">Покупки</h1>
                <p className="mt-1 text-white/50">Выберите тариф, оплатите онлайн — подписка активируется автоматически.</p>
            </header>

            <Alert variant="success">{props.flash?.success}</Alert>
            {errorList.length > 0 && (
                <Alert variant="error">
                    {errorList.map((err) => (
                        <p key={err}>{err}</p>
                    ))}
                </Alert>
            )}

            <section className="mb-8">
                <div className="mb-4">
                    <h2 className="text-lg font-bold text-white">Тарифы</h2>
                    <p className="text-sm text-white/45">Скидка растёт с длительностью периода</p>
                </div>
                <div className="grid gap-5 md:grid-cols-3">
                    {tiers.map((tier) => (
                        <TariffTier key={tier.name} tier={tier} />
                    ))}
                </div>
            </section>

            <GlassCard>
                <div className="mb-5 flex items-center justify-between">
                    <div>
                        <h2 className="text-lg font-bold text-white">История покупок</h2>
                        <p className="text-sm text-white/45">Последние заказы и статусы оплаты</p>
                    </div>
                    {orders.total > 0 && (
                        <span className="rounded-full bg-white/10 px-3 py-1 text-sm font-semibold text-white/60">{orders.total}</span>
                    )}
                </div>

                {orders.data.length > 0 ? (
                    <div className="flex flex-col gap-3">
                        {orders.data.map((order) => {
                            const status = STATUS_STYLES[order.status] ?? { label: order.status, className: 'bg-white/10 text-white/45' };
                            return (
                                <article key={order.id} className="rounded-xl border border-white/10 bg-black/20 p-4">
                                    <div className="flex flex-wrap items-start justify-between gap-3">
                                        <div>
                                            <div className="font-semibold text-white">{order.planName ?? order.note ?? 'Заказ'}</div>
                                            {order.planName && (
                                                <div className="text-sm text-white/40">{order.periodLabel} · {order.devices} устр.</div>
                                            )}
                                        </div>
                                        <div className="text-right">
                                            <div className="font-semibold text-white">{order.amount ?? '—'}</div>
                                            <time className="text-sm text-white/40">{order.createdAt}</time>
                                        </div>
                                    </div>
                                    <span className={`mt-3 inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold ${status.className}`}>{status.label}</span>
                                </article>
                            );
                        })}
                    </div>
                ) : (
                    <p className="text-white/45">Покупок пока нет — выберите тариф выше.</p>
                )}

                {orders.last_page > 1 && (
                    <div className="mt-5 flex flex-wrap gap-2">
                        {Array.from({ length: orders.last_page }, (_, i) => i + 1).map((page) => (
                            <Link
                                key={page}
                                href={route('cabinet.history', { page })}
                                preserveScroll
                                className={`rounded-lg px-3 py-1.5 text-sm font-medium transition ${
                                    page === orders.current_page ? 'bg-red-600 text-white' : 'border border-white/10 text-white/55 hover:bg-white/[0.06]'
                                }`}
                            >
                                {page}
                            </Link>
                        ))}
                    </div>
                )}
            </GlassCard>

            <PurchaseChoiceModal choice={purchaseChoice} onClose={() => setDismissed(true)} />
        </CabinetLayout>
    );
}
