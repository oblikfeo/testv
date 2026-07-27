export default function StatCard({ label, value, sub, accent = false }) {
    return (
        <div className={`rounded-2xl border p-5 ${accent ? 'border-red-500/30 bg-gradient-to-br from-red-600/15 to-fuchsia-600/10' : 'border-white/10 bg-white/[0.035]'}`}>
            <div className="text-xs font-semibold uppercase tracking-wider text-white/40">{label}</div>
            <div className="mt-2 text-2xl font-bold text-white">{value}</div>
            {sub && <div className="mt-1 text-xs text-white/45">{sub}</div>}
        </div>
    );
}
