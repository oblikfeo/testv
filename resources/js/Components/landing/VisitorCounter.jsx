import { useState } from 'react';
import { AnimatePresence, motion } from 'framer-motion';
import axios from 'axios';

export default function VisitorCounter({ visitorCount }) {
    const [open, setOpen] = useState(false);
    const [status, setStatus] = useState('loading');
    const [stats, setStats] = useState(null);

    async function handleOpen() {
        setOpen(true);
        setStatus('loading');
        try {
            await axios.post(route('landing.traffic-modal'), { opened: true }).catch(() => {});
            const { data } = await axios.get(route('landing.traffic-stats'));
            setStats(data);
            setStatus('ready');
        } catch {
            setStatus('error');
        }
    }

    function handleClose() {
        setOpen(false);
    }

    if (typeof visitorCount !== 'number') return null;

    return (
        <>
            <button
                type="button"
                onClick={handleOpen}
                aria-label="Статистика заходов на главную"
                title="Нажми — откуда приходят гости"
                className="flex items-center gap-2 rounded-full border border-white/15 bg-white/[0.04] px-3.5 py-2 text-xs font-semibold tabular-nums text-white/70 backdrop-blur transition-colors hover:border-red-500/40 hover:bg-red-500/10 hover:text-white"
            >
                <span className="h-1.5 w-1.5 animate-pulse-glow rounded-full bg-red-500" />
                {visitorCount.toLocaleString('ru-RU')}
            </button>

            <AnimatePresence>
                {open && (
                    <div className="fixed inset-0 z-[100] flex items-center justify-center p-4">
                        <motion.div
                            className="absolute inset-0 bg-black/70 backdrop-blur-sm"
                            onClick={handleClose}
                            initial={{ opacity: 0 }}
                            animate={{ opacity: 1 }}
                            exit={{ opacity: 0 }}
                        />
                        <motion.div
                            role="dialog"
                            aria-modal="true"
                            initial={{ opacity: 0, scale: 0.95, y: 12 }}
                            animate={{ opacity: 1, scale: 1, y: 0 }}
                            exit={{ opacity: 0, scale: 0.95, y: 12 }}
                            transition={{ duration: 0.2 }}
                            className="relative w-full max-w-md rounded-2xl border border-white/10 bg-ink-900/95 p-6 shadow-glow backdrop-blur-xl"
                        >
                            <button
                                type="button"
                                onClick={handleClose}
                                aria-label="Закрыть"
                                className="absolute right-4 top-4 flex h-8 w-8 items-center justify-center rounded-full text-white/50 transition-colors hover:bg-white/10 hover:text-white"
                            >
                                &times;
                            </button>
                            <h2 className="mb-4 text-lg font-bold text-white">Откуда приходят гости</h2>

                            {status === 'loading' && <p className="text-sm text-white/50">Загрузка…</p>}
                            {status === 'error' && <p className="text-sm text-red-400">Не удалось загрузить. Попробуйте позже.</p>}
                            {status === 'ready' && stats && (
                                <>
                                    <div className="mb-5 grid grid-cols-2 gap-3">
                                        <div className="rounded-xl border border-white/10 bg-white/[0.03] p-3">
                                            <span className="block text-xs text-white/45">Визитов главной</span>
                                            <strong className="text-xl font-extrabold text-white">{stats.total_visits ?? 0}</strong>
                                        </div>
                                        <div className="rounded-xl border border-red-500/20 bg-red-500/10 p-3">
                                            <span className="block text-xs text-white/45">Открыли эту панель</span>
                                            <strong className="text-xl font-extrabold text-white">{stats.modal_opens ?? 0}</strong>
                                        </div>
                                    </div>
                                    {!(stats.sources ?? []).length ? (
                                        <p className="text-sm text-white/45">Источники ещё копятся — загляни позже.</p>
                                    ) : (
                                        <div className="space-y-2.5">
                                            {stats.sources.map((s) => {
                                                const pct = typeof s.pct === 'number' ? s.pct : 0;
                                                const width = Math.min(100, Math.max(6, pct));
                                                return (
                                                    <div key={s.label} className="flex items-center gap-3 text-xs">
                                                        <span className="w-24 shrink-0 truncate text-white/60">{s.label}</span>
                                                        <div className="h-1.5 flex-1 overflow-hidden rounded-full bg-white/10">
                                                            <div className="h-full rounded-full bg-gradient-to-r from-red-600 to-fuchsia-500" style={{ width: `${width}%` }} />
                                                        </div>
                                                        <span className="w-16 shrink-0 text-right text-white/45">{s.hits} · {pct}%</span>
                                                    </div>
                                                );
                                            })}
                                        </div>
                                    )}
                                </>
                            )}
                        </motion.div>
                    </div>
                )}
            </AnimatePresence>
        </>
    );
}
