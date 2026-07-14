import { useState } from 'react';
import { motion } from 'framer-motion';
import Reveal from '@/Components/landing/Reveal';
import SectionHeading from '@/Components/ui/SectionHeading';

const FEATURES = [
    {
        title: 'Высокая скорость',
        text: 'Серверы 1 Гбит/с в Европе и протокол VLESS Reality. Стримы 4K, звонки и игры — без лагов.',
        icon: <path d="M13 2 3 14h7l-1 8 11-14h-7l1-6z" />,
    },
    {
        title: 'Шифрование трафика',
        text: 'TLS 1.3 и маскировка под обычный HTTPS. Провайдер видит только зашифрованный поток.',
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
    },
    {
        title: 'Поддержка в Telegram',
        text: 'Реальные люди отвечают быстро и помогают с настройкой в любое время.',
        icon: <><path d="M3 8a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V8z" /><path d="M8 12h8M8 16h5" /></>,
    },
];

function FlipCard({ title, text, icon, delay }) {
    const [pinned, setPinned] = useState(false);
    const [hovered, setHovered] = useState(false);
    const flipped = pinned || hovered;

    return (
        <Reveal
            delay={delay}
            className="h-52 [perspective:1200px]"
            onMouseEnter={() => setHovered(true)}
            onMouseLeave={() => setHovered(false)}
            onClick={() => setPinned((p) => !p)}
        >
            <motion.div
                animate={{ rotateY: flipped ? 180 : 0 }}
                transition={{ duration: 0.55, ease: [0.16, 1, 0.3, 1] }}
                className="relative h-full w-full cursor-pointer [transform-style:preserve-3d]"
            >
                {/* Front */}
                <div className="absolute inset-0 flex flex-col justify-between rounded-2xl border border-white/10 bg-white/[0.035] p-6 [backface-visibility:hidden]">
                    <div className="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-red-600/25 to-fuchsia-600/25 text-red-300">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" className="h-5 w-5">{icon}</svg>
                    </div>
                    <div>
                        <h3 className="text-lg font-bold text-white">{title}</h3>
                        <span className="mt-1 inline-block text-xs text-white/35">Наведите или нажмите →</span>
                    </div>
                </div>

                {/* Back */}
                <div
                    className="absolute inset-0 flex flex-col justify-center rounded-2xl border border-red-500/30 bg-gradient-to-br from-red-600/15 to-fuchsia-600/10 p-6 [backface-visibility:hidden]"
                    style={{ transform: 'rotateY(180deg)' }}
                >
                    <h3 className="mb-2 text-sm font-bold text-white">{title}</h3>
                    <p className="text-sm leading-relaxed text-white/70">{text}</p>
                </div>
            </motion.div>
        </Reveal>
    );
}

export default function FeatureGrid() {
    return (
        <section className="relative bg-ink-900 py-20 sm:py-24" id="features" aria-labelledby="features-title">
            <div className="mx-auto max-w-6xl px-5 sm:px-8">
                <SectionHeading
                    eyebrow="Возможности"
                    id="features-title"
                    title="Почему выбирают AVA VPN"
                    subtitle="Мы сделали VPN таким, каким он должен быть в 2026 году: быстрый, стабильный и понятный."
                />

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {FEATURES.map((f, i) => (
                        <FlipCard key={f.title} {...f} delay={i * 0.06} />
                    ))}
                </div>
            </div>
        </section>
    );
}
