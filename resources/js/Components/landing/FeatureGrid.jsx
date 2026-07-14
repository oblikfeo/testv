import Reveal from '@/Components/landing/Reveal';
import SectionHeading from '@/Components/ui/SectionHeading';

const FEATURES = [
    {
        title: 'Высокая скорость',
        text: 'Серверы 1 Гбит/с в Европе и современный протокол VLESS Reality. Стримы 4K, видеозвонки и онлайн-игры — без лагов и «квадратов».',
        icon: <path d="M13 2 3 14h7l-1 8 11-14h-7l1-6z" />,
        span: 'md:col-span-2',
    },
    {
        title: 'Шифрование трафика',
        text: 'TLS 1.3, маскировка под обычный HTTPS. Провайдер видит только зашифрованный поток.',
        icon: <><path d="M12 2 4 5v6c0 5 3.5 9.3 8 11 4.5-1.7 8-6 8-11V5l-8-3z" /><path d="m9 12 2 2 4-4" /></>,
    },
    {
        title: 'Обход блокировок',
        text: 'Доступ к Telegram, YouTube, Instagram*, ChatGPT, Discord и другим ограниченным сервисам.',
        icon: <><circle cx="12" cy="12" r="9" /><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18" /></>,
    },
    {
        title: 'Любые устройства',
        text: 'Android, iOS, Windows, macOS, Linux и роутеры — один аккаунт, несколько устройств.',
        icon: <><rect x="3" y="3" width="18" height="14" rx="2" /><path d="M3 17h18M9 21h6" /></>,
    },
    {
        title: 'Подключение за минуту',
        text: 'Регистрация в один клик, готовая конфигурация в кабинете и понятные инструкции.',
        icon: <><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" /></>,
        span: 'md:col-span-2',
    },
    {
        title: 'Поддержка в Telegram',
        text: 'Реальные люди отвечают быстро и помогают с настройкой.',
        icon: <><path d="M3 8a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V8z" /><path d="M8 12h8M8 16h5" /></>,
    },
];

function FeatureCard({ title, text, icon, span, delay }) {
    function handlePointerMove(e) {
        const rect = e.currentTarget.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        e.currentTarget.style.setProperty('--mx', `${x}px`);
        e.currentTarget.style.setProperty('--my', `${y}px`);
    }

    return (
        <Reveal
            as="li"
            delay={delay}
            onPointerMove={handlePointerMove}
            className={`group relative overflow-hidden rounded-2xl border border-white/10 bg-white/[0.035] p-7
                transition-colors duration-300 hover:border-red-500/30 ${span ?? ''}`}
        >
            <div
                aria-hidden="true"
                className="pointer-events-none absolute inset-0 opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                style={{ background: 'radial-gradient(400px circle at var(--mx, 50%) var(--my, 50%), rgba(220,38,38,0.14), transparent 70%)' }}
            />
            <div className="relative z-10">
                <div className="mb-4 inline-flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-red-600/25 to-fuchsia-600/25 text-red-300">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" className="h-5 w-5">{icon}</svg>
                </div>
                <h3 className="mb-2 text-lg font-bold text-white">{title}</h3>
                <p className="text-sm leading-relaxed text-white/55">{text}</p>
            </div>
        </Reveal>
    );
}

export default function FeatureGrid() {
    return (
        <section className="relative bg-ink-900 py-24 sm:py-28" id="features" aria-labelledby="features-title">
            <div className="mx-auto max-w-6xl px-5 sm:px-8">
                <SectionHeading
                    eyebrow="Возможности"
                    id="features-title"
                    title="Почему выбирают AVA VPN"
                    subtitle="Мы сделали VPN таким, каким он должен быть в 2026 году: быстрый, стабильный и понятный."
                />

                <ul className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {FEATURES.map((f, i) => (
                        <FeatureCard key={f.title} {...f} delay={i * 0.06} />
                    ))}
                </ul>
            </div>
        </section>
    );
}
