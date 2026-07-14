import Reveal from '@/Components/landing/Reveal';

const FEATURES = [
    {
        title: 'Высокая скорость',
        text: <>Серверы 1&nbsp;Гбит/с в&nbsp;Европе и&nbsp;современный протокол VLESS Reality. Стримы 4K, видеозвонки и&nbsp;онлайн-игры — без лагов и&nbsp;«квадратов».</>,
        icon: <path d="M13 2 3 14h7l-1 8 11-14h-7l1-6z" />,
    },
    {
        title: 'Шифрование трафика',
        text: <>TLS&nbsp;1.3, маскировка под обычный HTTPS и&nbsp;современные алгоритмы. Ваш интернет-провайдер видит только зашифрованный поток.</>,
        icon: <><path d="M12 2 4 5v6c0 5 3.5 9.3 8 11 4.5-1.7 8-6 8-11V5l-8-3z" /><path d="m9 12 2 2 4-4" /></>,
    },
    {
        title: 'Обход блокировок',
        text: <>Доступ к&nbsp;Telegram, YouTube, Instagram*, ChatGPT, Discord и&nbsp;другим сервисам, которые ограничены или нестабильны в&nbsp;РФ.</>,
        icon: <><circle cx="12" cy="12" r="9" /><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18" /></>,
    },
    {
        title: 'Любые устройства',
        text: <>Android, iOS, Windows, macOS, Linux и&nbsp;роутеры. Один аккаунт — несколько устройств одновременно.</>,
        icon: <><rect x="3" y="3" width="18" height="14" rx="2" /><path d="M3 17h18M9 21h6" /></>,
    },
    {
        title: 'Подключение за минуту',
        text: <>Регистрация в&nbsp;один клик, готовая конфигурация в&nbsp;кабинете и&nbsp;понятные инструкции для каждой платформы.</>,
        icon: <><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" /></>,
    },
    {
        title: 'Поддержка в Telegram',
        text: <>Реальные люди отвечают быстро. Помогут с&nbsp;настройкой, оплатой и&nbsp;ответят на&nbsp;любые вопросы.</>,
        icon: <><path d="M3 8a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V8z" /><path d="M8 12h8M8 16h5" /></>,
    },
];

function FeatureCard({ title, text, icon, delay }) {
    function handlePointerMove(e) {
        const rect = e.currentTarget.getBoundingClientRect();
        const x = ((e.clientX - rect.left) / rect.width) * 100;
        const y = ((e.clientY - rect.top) / rect.height) * 100;
        e.currentTarget.style.setProperty('--mx', `${x}%`);
        e.currentTarget.style.setProperty('--my', `${y}%`);
    }

    return (
        <Reveal as="li" className="feature-card" delay={delay} onPointerMove={handlePointerMove}>
            <div className="feature-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">{icon}</svg>
            </div>
            <h3>{title}</h3>
            <p>{text}</p>
        </Reveal>
    );
}

export default function FeatureGrid() {
    return (
        <section className="section features" id="features" aria-labelledby="features-title">
            <div className="container">
                <Reveal as="header" className="section-head">
                    <span className="section-eyebrow">Возможности</span>
                    <h2 id="features-title" className="section-title">Почему выбирают AVA VPN</h2>
                    <p className="section-subtitle">
                        Мы сделали VPN таким, каким он должен быть в&nbsp;2026 году: быстрый, стабильный и&nbsp;понятный.
                        Никаких сложных настроек и&nbsp;ограничений по&nbsp;скорости.
                    </p>
                </Reveal>

                <ul className="features-grid">
                    {FEATURES.map((f, i) => (
                        <FeatureCard key={f.title} {...f} delay={i * 0.06} />
                    ))}
                </ul>
            </div>
        </section>
    );
}
