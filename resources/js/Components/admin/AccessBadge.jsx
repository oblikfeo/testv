const MAP = {
    active: { label: 'Активна', cls: 'bg-emerald-500/15 text-emerald-300' },
    trial: { label: 'Триал', cls: 'bg-sky-500/15 text-sky-300' },
    expired: { label: 'Истекла', cls: 'bg-amber-500/15 text-amber-300' },
    none: { label: 'Нет', cls: 'bg-white/10 text-white/45' },
};

export default function AccessBadge({ status }) {
    const s = MAP[status] ?? MAP.none;
    return <span className={`inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold ${s.cls}`}>{s.label}</span>;
}
