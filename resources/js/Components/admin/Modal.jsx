import { useEffect } from 'react';

export default function Modal({ open, onClose, title, children }) {
    useEffect(() => {
        function onKey(e) {
            if (e.key === 'Escape') onClose();
        }
        if (open) document.addEventListener('keydown', onKey);
        return () => document.removeEventListener('keydown', onKey);
    }, [open, onClose]);

    if (!open) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/60 p-4 backdrop-blur-sm" onClick={onClose}>
            <div className="mt-16 w-full max-w-lg rounded-2xl border border-white/10 bg-ink-900 p-6 shadow-glow" onClick={(e) => e.stopPropagation()}>
                <div className="mb-4 flex items-center justify-between">
                    <h2 className="text-lg font-bold text-white">{title}</h2>
                    <button onClick={onClose} className="text-white/40 transition hover:text-white">✕</button>
                </div>
                {children}
            </div>
        </div>
    );
}
