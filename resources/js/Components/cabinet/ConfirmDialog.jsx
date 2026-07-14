import { AnimatePresence, motion } from 'framer-motion';

export default function ConfirmDialog({ open, title, description, confirmLabel = 'Подтвердить', danger = false, onConfirm, onCancel, processing = false }) {
    return (
        <AnimatePresence>
            {open && (
                <motion.div
                    initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }}
                    className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-5"
                    onClick={onCancel}
                >
                    <motion.div
                        initial={{ opacity: 0, scale: 0.95, y: 12 }}
                        animate={{ opacity: 1, scale: 1, y: 0 }}
                        exit={{ opacity: 0, scale: 0.95, y: 12 }}
                        transition={{ duration: 0.2, ease: [0.16, 1, 0.3, 1] }}
                        onClick={(e) => e.stopPropagation()}
                        className="w-full max-w-sm rounded-2xl border border-white/10 bg-ink-900 p-6 shadow-glow-lg"
                        role="alertdialog" aria-modal="true"
                    >
                        <h3 className="mb-2 text-lg font-bold text-white">{title}</h3>
                        {description && <p className="mb-5 text-sm leading-relaxed text-white/55">{description}</p>}
                        <div className="flex justify-end gap-3">
                            <button
                                type="button" onClick={onCancel}
                                className="rounded-full border border-white/15 bg-white/[0.06] px-4 py-2 text-sm font-semibold text-white/80 transition hover:bg-white/[0.1]"
                            >
                                Отмена
                            </button>
                            <button
                                type="button" onClick={onConfirm} disabled={processing}
                                className={`rounded-full px-4 py-2 text-sm font-semibold text-white transition disabled:opacity-60 ${
                                    danger ? 'bg-red-600 hover:bg-red-500' : 'bg-gradient-to-r from-red-600 to-fuchsia-600 hover:brightness-110'
                                }`}
                            >
                                {confirmLabel}
                            </button>
                        </div>
                    </motion.div>
                </motion.div>
            )}
        </AnimatePresence>
    );
}
