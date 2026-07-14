import { useState } from 'react';

export default function SubscriptionLinkBox({ connectionUri }) {
    const [copied, setCopied] = useState(false);

    if (!connectionUri) return null;

    function copy() {
        navigator.clipboard.writeText(connectionUri).then(() => {
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        });
    }

    return (
        <div className="mt-5 rounded-2xl border border-white/10 bg-black/25 p-4">
            <div className="mb-2 flex items-center justify-between">
                <span className="text-xs font-semibold uppercase tracking-wider text-white/40">Подписочная ссылка</span>
                <span className="text-xs text-white/30">Happ · v2RayTun</span>
            </div>
            <div className="flex items-center gap-2">
                <input
                    type="text" readOnly value={connectionUri} aria-label="Подписочная ссылка"
                    className="min-w-0 flex-1 truncate rounded-lg border border-white/10 bg-black/30 px-3 py-2 text-sm text-white/70"
                />
                <button
                    type="button" onClick={copy}
                    className="shrink-0 rounded-lg border border-white/15 bg-white/[0.06] px-3 py-2 text-sm font-medium text-white/80 transition hover:bg-white/[0.1]"
                >
                    {copied ? 'Скопировано' : 'Копировать'}
                </button>
            </div>
            <p className="mt-2.5 text-xs leading-relaxed text-white/35">
                Добавьте как подписку в приложении — в списке появятся два сервера: Wi-Fi и «Обход блокировок».
            </p>
        </div>
    );
}
