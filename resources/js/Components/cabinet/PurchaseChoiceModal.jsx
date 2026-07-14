import { useState } from 'react';
import { router } from '@inertiajs/react';
import { AnimatePresence, motion } from 'framer-motion';

export default function PurchaseChoiceModal({ choice, onClose }) {
    const [pending, setPending] = useState(false);

    if (!choice) return null;

    function submit(action, targetSubscriptionId) {
        setPending(true);
        router.post(
            route('payment.create'),
            {
                plan_id: choice.plan.id,
                purchase_action: action,
                target_subscription_id: targetSubscriptionId ?? null,
            },
            { onFinish: () => setPending(false) },
        );
    }

    return (
        <AnimatePresence>
            <motion.div
                initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }}
                className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-5 backdrop-blur-sm"
                onClick={onClose}
            >
                <motion.div
                    initial={{ opacity: 0, scale: 0.95, y: 12 }}
                    animate={{ opacity: 1, scale: 1, y: 0 }}
                    exit={{ opacity: 0, scale: 0.95, y: 12 }}
                    transition={{ duration: 0.2, ease: [0.16, 1, 0.3, 1] }}
                    onClick={(e) => e.stopPropagation()}
                    className="w-full max-w-md rounded-2xl border border-white/10 bg-ink-900 p-6 shadow-glow-lg"
                    role="dialog" aria-modal="true"
                >
                    <button
                        type="button" onClick={onClose} aria-label="Закрыть"
                        className="float-right text-white/40 transition hover:text-white"
                    >
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M18 6 6 18M6 6l12 12" /></svg>
                    </button>

                    <h3 className="mb-2 text-lg font-bold text-white">Продлить или купить новую?</h3>
                    <p className="mb-5 text-sm leading-relaxed text-white/55">
                        Тариф <strong className="text-white">{choice.plan.name}</strong> ({choice.plan.period_label}, {choice.plan.devices} устр.) — {choice.plan.price}
                    </p>

                    <div className="flex flex-col gap-2.5">
                        <button
                            type="button" disabled={pending} onClick={() => submit('new_purchase')}
                            className="rounded-xl bg-gradient-to-r from-red-600 to-fuchsia-600 px-4 py-3 text-sm font-semibold text-white shadow-glow transition hover:brightness-110 disabled:opacity-60"
                        >
                            Купить новую подписку
                        </button>
                        {choice.subscriptions.map((sub) => (
                            <button
                                key={sub.id} type="button" disabled={pending}
                                onClick={() => submit('renew_subscription', sub.id)}
                                className="rounded-xl border border-white/15 bg-white/[0.06] px-4 py-3 text-sm font-semibold text-white/90 transition hover:bg-white/[0.1] disabled:opacity-60"
                            >
                                Продлить «{sub.plan_name}» до {sub.expires_at ?? '—'}
                            </button>
                        ))}
                    </div>
                </motion.div>
            </motion.div>
        </AnimatePresence>
    );
}
