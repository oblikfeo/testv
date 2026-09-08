function deviceWord(devices) {
    const mod10 = devices % 10;
    const mod100 = devices % 100;
    if (mod10 === 1 && mod100 !== 11) return 'устройство';
    if ([2, 3, 4].includes(mod10) && ![12, 13, 14].includes(mod100)) return 'устройства';
    return 'устройств';
}

export default function TariffTier({ tier, onBuy, purchasingPlanId }) {
    const busy = purchasingPlanId !== null;

    return (
        <article
            className={`relative flex flex-col gap-4 rounded-2xl border p-6 ${
                tier.featured ? 'border-red-500/30 bg-gradient-to-b from-red-500/[0.07] to-transparent' : 'border-white/10 bg-white/[0.025]'
            }`}
        >
            {tier.featured && (
                <span className="absolute -top-3 right-6 rounded-full bg-gradient-to-r from-red-600 to-fuchsia-600 px-3 py-1 text-xs font-bold text-white shadow-glow">
                    Выгодно
                </span>
            )}

            <header className="flex items-center gap-3">
                <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/[0.06] text-white/70">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" className="h-5 w-5">
                        <rect x="2" y="3" width="20" height="14" rx="2" /><path d="M8 21h8" /><path d="M12 17v4" />
                    </svg>
                </div>
                <div>
                    <h3 className="font-bold text-white">{tier.name}</h3>
                    <p className="text-sm text-white/45">{tier.devices} {deviceWord(tier.devices)}</p>
                </div>
            </header>

            <div className="flex flex-col gap-2.5">
                {tier.plans.length === 0 && <p className="text-sm text-white/40">Тарифы временно недоступны</p>}
                {tier.plans.map((plan) => {
                    const pending = purchasingPlanId === plan.id;
                    return (
                        <div key={plan.id} className="flex flex-col gap-3 rounded-xl border border-white/10 bg-black/20 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <div className="text-sm font-medium text-white">{plan.periodLabel}</div>
                                <div className="flex flex-wrap items-center gap-2 text-xs text-white/40">
                                    {plan.trafficGb > 0 && <span>{plan.trafficGb} ГБ</span>}
                                    <span className="font-semibold text-white/80">{plan.formattedPrice}</span>
                                    {plan.discount > 0 && <span className="text-emerald-400">−{plan.discount}%</span>}
                                </div>
                            </div>
                            <button
                                type="button" disabled={busy} onClick={() => onBuy(plan.id)}
                                aria-busy={pending}
                                className={`flex w-full shrink-0 items-center justify-center gap-2 rounded-full px-4 py-2 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto ${
                                    tier.featured
                                        ? 'bg-gradient-to-r from-red-600 to-fuchsia-600 text-white shadow-glow hover:brightness-110'
                                        : 'border border-white/15 bg-white/[0.06] text-white/90 hover:bg-white/[0.1]'
                                }`}
                            >
                                {pending && (
                                    <svg className="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <circle cx="12" cy="12" r="9" stroke="currentColor" strokeWidth="3" className="opacity-25" />
                                        <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" strokeWidth="3" strokeLinecap="round" />
                                    </svg>
                                )}
                                {pending ? 'Открываем оплату…' : 'Купить'}
                            </button>
                        </div>
                    );
                })}
            </div>
        </article>
    );
}
