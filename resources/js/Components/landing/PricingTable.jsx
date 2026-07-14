import { useState } from 'react';
import { motion } from 'framer-motion';
import { usePage } from '@inertiajs/react';
import Reveal from '@/Components/landing/Reveal';
import SectionHeading from '@/Components/ui/SectionHeading';
import GlowButton from '@/Components/ui/GlowButton';

function findPlan(plans, days) {
    return plans?.find((p) => p.days === days) ?? null;
}

function pluralDevices(n) {
    const mod10 = n % 10;
    const mod100 = n % 100;
    if (mod10 === 1 && mod100 !== 11) return 'устройство';
    if ([2, 3, 4].includes(mod10) && ![12, 13, 14].includes(mod100)) return 'устройств';
    return 'устройств';
}

export default function PricingTable({ planGroups = [] }) {
    const { props } = usePage();
    const user = props.auth?.user;
    const [period, setPeriod] = useState(90);

    const standard = planGroups.find((g) => g[0]?.devices === 2) ?? [];
    const extended = planGroups.find((g) => g[0]?.devices === 5) ?? [];
    const premium = planGroups.find((g) => g[0]?.devices === 10) ?? [];
    const premiumPlan = premium[0] ?? null;

    const availablePeriods = [...new Set([...standard, ...extended].map((p) => p.days))].sort((a, b) => a - b);
    const activePeriod = availablePeriods.includes(period) ? period : availablePeriods[0];

    const cards = [
        { key: 'standard', label: 'Стандартный', plan: findPlan(standard, activePeriod), featured: false },
        { key: 'extended', label: 'Расширенный', plan: findPlan(extended, activePeriod), featured: true },
    ];

    return (
        <section className="relative bg-ink-950 py-20 sm:py-24" id="pricing" aria-labelledby="pricing-title">
            <div className="mx-auto max-w-5xl px-5 sm:px-8">
                <SectionHeading
                    eyebrow="Тарифы"
                    id="pricing-title"
                    title="Честные цены без мелкого шрифта"
                    subtitle="Прозрачные условия и скидки за долгий период. Без скрытых платежей и автосписаний."
                />

                <Reveal className="mb-10 flex justify-center">
                    <div className="inline-flex gap-1 rounded-full border border-white/10 bg-white/[0.03] p-1">
                        {availablePeriods.map((days) => (
                            <button
                                key={days}
                                type="button"
                                onClick={() => setPeriod(days)}
                                className={`relative rounded-full px-4 py-2 text-sm font-semibold transition-colors sm:px-5 ${
                                    activePeriod === days ? 'text-white' : 'text-white/45 hover:text-white/70'
                                }`}
                            >
                                {activePeriod === days && (
                                    <motion.span
                                        layoutId="period-pill"
                                        className="absolute inset-0 rounded-full bg-gradient-to-r from-red-600 to-fuchsia-600"
                                        transition={{ type: 'spring', stiffness: 400, damping: 30 }}
                                    />
                                )}
                                <span className="relative z-10">{days} дней</span>
                            </button>
                        ))}
                    </div>
                </Reveal>

                <div className="grid gap-6 sm:grid-cols-2">
                    {cards.map(({ key, label, plan, featured }, i) => (
                        <Reveal key={key} delay={i * 0.08}>
                            <div
                                className={`relative flex h-full flex-col rounded-2xl border p-8 transition-all ${
                                    featured
                                        ? 'border-red-500/40 bg-gradient-to-b from-red-600/[0.08] to-fuchsia-600/[0.04] shadow-glow'
                                        : 'border-white/10 bg-white/[0.03]'
                                }`}
                            >
                                {featured && (
                                    <span className="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-gradient-to-r from-red-600 to-fuchsia-600 px-4 py-1 text-xs font-bold uppercase tracking-wide text-white shadow-glow">
                                        Выгодно
                                    </span>
                                )}
                                <h3 className="text-lg font-bold text-white">{label}</h3>
                                <p className="mt-1 text-sm text-white/45">До {plan?.devices ?? '—'} {pluralDevices(plan?.devices ?? 0)}</p>

                                <div className="mt-6 flex items-baseline gap-2">
                                    <span className="text-4xl font-extrabold text-white">{plan?.formattedPrice ?? '—'}</span>
                                    {plan?.discount > 0 && (
                                        <span className="rounded-full bg-red-500/15 px-2 py-0.5 text-xs font-bold text-red-300">−{plan.discount}%</span>
                                    )}
                                </div>
                                <p className="mt-1 text-sm text-white/40">{plan?.periodLabel ?? `${activePeriod} дней`}</p>

                                <div className="mt-8">
                                    <GlowButton
                                        href={user ? route('cabinet.subscription') : route('register')}
                                        variant={featured ? 'primary' : 'secondary'}
                                        size="md"
                                        className="w-full"
                                    >
                                        {user ? 'В кабинет →' : 'Оформить →'}
                                    </GlowButton>
                                </div>
                            </div>
                        </Reveal>
                    ))}
                </div>

                {premiumPlan && (
                    <Reveal delay={0.16} className="mt-6">
                        <div className="flex flex-col items-start justify-between gap-6 rounded-2xl border border-white/10 bg-white/[0.03] p-7 sm:flex-row sm:items-center">
                            <div>
                                <h3 className="text-base font-bold text-white">Премиум — для тех, кому мало пяти устройств</h3>
                                <p className="mt-1 text-sm text-white/55">
                                    До {premiumPlan.devices} устройств одновременно, {premiumPlan.periodLabel.toLowerCase()}
                                    {premiumPlan.trafficGb > 0 && <>, лимит трафика {premiumPlan.trafficGb} ГБ</>}.
                                </p>
                            </div>
                            <div className="flex shrink-0 items-center gap-4">
                                <span className="text-2xl font-extrabold text-white">{premiumPlan.formattedPrice}</span>
                                <GlowButton href={user ? route('cabinet.history') : route('register')} variant="secondary" size="md">
                                    Подробнее →
                                </GlowButton>
                            </div>
                        </div>
                    </Reveal>
                )}

                <Reveal delay={0.2} className="mt-8 flex flex-wrap justify-center gap-x-6 gap-y-2 text-sm text-white/45">
                    <span className="flex items-center gap-1.5"><span className="text-red-400">✓</span> Оплата ЮKassa: карта, СБП, СберPay</span>
                    <span className="flex items-center gap-1.5"><span className="text-red-400">✓</span> Без автосписаний</span>
                    <span className="flex items-center gap-1.5"><span className="text-red-400">✓</span> Возврат при технических проблемах</span>
                </Reveal>
            </div>
        </section>
    );
}
