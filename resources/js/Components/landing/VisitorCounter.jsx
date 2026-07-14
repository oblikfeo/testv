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
                className="visitor-counter-btn"
                onClick={handleOpen}
                aria-label="Статистика заходов на главную"
                title="Нажми — откуда приходят гости"
            >
                <table className="visitor-counter"><tbody><tr><td>{visitorCount.toLocaleString('ru-RU')}</td></tr></tbody></table>
            </button>

            <AnimatePresence>
                {open && (
                    <div className="traffic-modal-root" role="presentation">
                        <motion.div
                            className="traffic-modal-backdrop"
                            onClick={handleClose}
                            initial={{ opacity: 0 }}
                            animate={{ opacity: 1 }}
                            exit={{ opacity: 0 }}
                        />
                        <motion.div
                            className="traffic-modal-shell"
                            role="dialog"
                            aria-modal="true"
                            aria-labelledby="traffic-modal-title"
                            initial={{ opacity: 0, scale: 0.96, y: 12 }}
                            animate={{ opacity: 1, scale: 1, y: 0 }}
                            exit={{ opacity: 0, scale: 0.96, y: 12 }}
                            transition={{ duration: 0.2 }}
                        >
                            <button type="button" className="traffic-modal-x" onClick={handleClose} aria-label="Закрыть">&times;</button>
                            <div className="traffic-modal-inner">
                                <h2 id="traffic-modal-title" className="traffic-modal-title">Откуда приходят гости</h2>
                                <div className="traffic-modal-body">
                                    {status === 'loading' && <p className="traffic-modal-loading">Загрузка…</p>}
                                    {status === 'error' && <p className="traffic-modal-error">Не удалось загрузить. Попробуйте позже.</p>}
                                    {status === 'ready' && stats && (
                                        <>
                                            <div className="traffic-stat-grid">
                                                <div className="traffic-stat-card">
                                                    <span>Визитов главной</span>
                                                    <strong>{stats.total_visits ?? 0}</strong>
                                                </div>
                                                <div className="traffic-stat-card traffic-stat-card--accent">
                                                    <span>Открыли эту панель</span>
                                                    <strong>{stats.modal_opens ?? 0}</strong>
                                                </div>
                                            </div>
                                            {!(stats.sources ?? []).length ? (
                                                <p className="traffic-modal-empty">Источники ещё копятся — загляни позже.</p>
                                            ) : (
                                                <div className="traffic-bars">
                                                    {stats.sources.map((s) => {
                                                        const pct = typeof s.pct === 'number' ? s.pct : 0;
                                                        const width = Math.min(100, Math.max(6, pct));
                                                        return (
                                                            <div className="traffic-bar-row" key={s.label}>
                                                                <span className="traffic-bar-label">{s.label}</span>
                                                                <div className="traffic-bar-track"><div className="traffic-bar-fill" style={{ width: `${width}%` }} /></div>
                                                                <span className="traffic-bar-meta">{s.hits} · {pct}%</span>
                                                            </div>
                                                        );
                                                    })}
                                                </div>
                                            )}
                                        </>
                                    )}
                                </div>
                            </div>
                        </motion.div>
                    </div>
                )}
            </AnimatePresence>
        </>
    );
}
