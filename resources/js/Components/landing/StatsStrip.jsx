const WORDS = ['Быстро', 'Приватно', 'Стабильно', 'Просто', 'Без логов', 'Без границ'];

function Track() {
    return (
        <div className="flex shrink-0 items-center gap-10 pr-10">
            {WORDS.map((word, i) => (
                <span
                    key={word}
                    className={`flex items-center gap-10 text-lg font-bold tracking-tight sm:text-xl ${
                        i % 2 === 0 ? 'text-white/85' : 'bg-gradient-to-r from-red-500 to-fuchsia-500 bg-clip-text text-transparent'
                    }`}
                >
                    {word}
                    <span className="text-white/15">◆</span>
                </span>
            ))}
        </div>
    );
}

export default function StatsStrip() {
    return (
        <section className="relative overflow-hidden border-y border-white/10 bg-ink-900 py-5">
            <div className="pointer-events-none absolute inset-y-0 left-0 z-10 w-24 bg-gradient-to-r from-ink-900 to-transparent" />
            <div className="pointer-events-none absolute inset-y-0 right-0 z-10 w-24 bg-gradient-to-l from-ink-900 to-transparent" />
            <div className="flex w-max animate-marquee whitespace-nowrap will-change-transform">
                <Track />
                <Track />
            </div>
        </section>
    );
}
