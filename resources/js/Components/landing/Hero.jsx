import { motion } from 'framer-motion';
import { usePage } from '@inertiajs/react';
import AnimatedBackground from '@/Components/ui/AnimatedBackground';
import Spotlight from '@/Components/ui/Spotlight';
import GlowButton from '@/Components/ui/GlowButton';

function TelegramIcon() {
    return (
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0h-.056zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z" />
        </svg>
    );
}

const fadeUp = {
    hidden: { opacity: 0, y: 26 },
    show: (delay = 0) => ({
        opacity: 1,
        y: 0,
        transition: { duration: 0.65, delay, ease: [0.16, 1, 0.3, 1] },
    }),
};

export default function Hero() {
    const { props } = usePage();
    const user = props.auth?.user;
    const { botUrl, channelUrl } = props.telegram ?? {};

    return (
        <section className="relative isolate overflow-hidden bg-ink-950 pb-28 pt-32 sm:pt-40">
            <AnimatedBackground variant="hero" />
            <Spotlight />

            <div className="relative z-10 mx-auto flex max-w-4xl flex-col items-center px-5 text-center sm:px-8">
                <motion.div
                    initial="hidden" animate="show" variants={fadeUp}
                    className="mb-7 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/[0.05] px-4 py-1.5 text-xs font-medium text-white/70 backdrop-blur"
                >
                    <span className="relative flex h-2 w-2">
                        <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-500 opacity-75" />
                        <span className="relative inline-flex h-2 w-2 rounded-full bg-red-500" />
                    </span>
                    Серверы в ЕС · Подключение за 1 минуту
                </motion.div>

                <motion.h1
                    initial="hidden" animate="show" custom={0.08} variants={fadeUp}
                    className="text-4xl font-extrabold leading-[1.08] tracking-tight text-white sm:text-6xl lg:text-7xl"
                >
                    Быстрый VPN для России —
                    <br />
                    <span className="bg-gradient-to-r from-red-500 via-rose-400 to-fuchsia-500 bg-[length:200%_auto] bg-clip-text text-transparent animate-gradient-x">
                        без блокировок и тормозов
                    </span>
                </motion.h1>

                <motion.p
                    initial="hidden" animate="show" custom={0.16} variants={fadeUp}
                    className="mx-auto mt-6 max-w-xl text-lg leading-relaxed text-white/55"
                >
                    AVA VPN — это шифрование трафика, защита приватности и стабильная работа любимых сервисов.
                    Подключите смартфон, компьютер или роутер за минуту и пользуйтесь интернетом без ограничений.
                </motion.p>

                <motion.div initial="hidden" animate="show" custom={0.24} variants={fadeUp} className="mt-9 flex flex-col items-center gap-3">
                    {user ? (
                        <GlowButton href={route('cabinet.subscription')} size="lg">Перейти в кабинет →</GlowButton>
                    ) : (
                        <GlowButton href={route('register')} size="lg">Получить тест бесплатно →</GlowButton>
                    )}
                    <span className="text-sm text-white/40">3 часа без оплаты. Карта не нужна.</span>
                </motion.div>

                {(channelUrl || botUrl) && (
                    <motion.div initial="hidden" animate="show" custom={0.3} variants={fadeUp} className="mt-4 flex flex-wrap justify-center gap-3">
                        {channelUrl && (
                            <GlowButton as="a" href={channelUrl} target="_blank" rel="noopener" variant="secondary" size="md">
                                <TelegramIcon /> ТГ-канал
                            </GlowButton>
                        )}
                        {botUrl && (
                            <GlowButton as="a" href={botUrl} target="_blank" rel="noopener" variant="secondary" size="md">
                                <TelegramIcon /> ТГ-бот
                            </GlowButton>
                        )}
                    </motion.div>
                )}

                <motion.ul
                    initial="hidden" animate="show" custom={0.38} variants={fadeUp}
                    className="mt-10 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-sm text-white/45"
                    aria-label="Преимущества"
                >
                    {['Без логов', 'Оплата ЮKassa', 'Поддержка 24/7', 'Возврат при сбое'].map((item) => (
                        <li key={item} className="flex items-center gap-1.5">
                            <span className="text-red-400">✓</span> {item}
                        </li>
                    ))}
                </motion.ul>
            </div>
        </section>
    );
}
