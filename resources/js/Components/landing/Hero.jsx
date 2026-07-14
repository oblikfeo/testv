import { motion } from 'framer-motion';
import { Link, usePage } from '@inertiajs/react';

function TelegramIcon() {
    return (
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0h-.056zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z" />
        </svg>
    );
}

const fadeUp = {
    hidden: { opacity: 0, y: 22 },
    show: (delay = 0) => ({
        opacity: 1,
        y: 0,
        transition: { duration: 0.55, delay, ease: [0.16, 1, 0.3, 1] },
    }),
};

export default function Hero() {
    const { props } = usePage();
    const user = props.auth?.user;
    const { botUrl, channelUrl } = props.telegram ?? {};

    return (
        <section className="hero" aria-labelledby="hero-title">
            <div className="hero-bg" aria-hidden="true">
                <motion.div
                    className="hero-orb hero-orb--1"
                    animate={{ x: [0, 30, -10, 0], y: [0, -20, 15, 0] }}
                    transition={{ duration: 22, repeat: Infinity, ease: 'easeInOut' }}
                />
                <motion.div
                    className="hero-orb hero-orb--2"
                    animate={{ x: [0, -25, 15, 0], y: [0, 20, -10, 0] }}
                    transition={{ duration: 26, repeat: Infinity, ease: 'easeInOut' }}
                />
                <div className="hero-grid" />
            </div>

            <div className="container hero-inner">
                <motion.div className="hero-badge" initial="hidden" animate="show" variants={fadeUp}>
                    <span className="hero-badge-dot" />
                    Серверы в ЕС · Подключение за 1 минуту
                </motion.div>

                <motion.h1
                    id="hero-title"
                    initial="hidden"
                    animate="show"
                    custom={0.06}
                    variants={fadeUp}
                >
                    Быстрый VPN&nbsp;для&nbsp;России —<br />
                    <span className="accent">без блокировок и&nbsp;тормозов</span>
                </motion.h1>

                <motion.p className="hero-subtitle" initial="hidden" animate="show" custom={0.12} variants={fadeUp}>
                    AVA VPN — это шифрование трафика, защита приватности и стабильная работа любимых сервисов.
                    Подключите смартфон, компьютер или роутер за минуту и пользуйтесь интернетом без ограничений.
                </motion.p>

                <motion.div className="hero-cta" initial="hidden" animate="show" custom={0.18} variants={fadeUp}>
                    {user ? (
                        <>
                            <Link href={route('cabinet.subscription')} className="btn btn-primary btn-lg">Перейти в кабинет →</Link>
                            <span className="hint">Ваши ключи и активные подписки</span>
                        </>
                    ) : (
                        <>
                            <Link href={route('register')} className="btn btn-primary btn-lg">Получить тест бесплатно →</Link>
                            <span className="hint">3 часа без оплаты. Карта не нужна.</span>
                        </>
                    )}
                </motion.div>

                {(channelUrl || botUrl) && (
                    <motion.div
                        className="hero-cta hero-cta-social"
                        style={{ display: 'flex', flexDirection: 'row', flexWrap: 'wrap', justifyContent: 'center', gap: 12 }}
                        initial="hidden"
                        animate="show"
                        custom={0.21}
                        variants={fadeUp}
                    >
                        {channelUrl && (
                            <a href={channelUrl} target="_blank" rel="noopener" className="btn btn-secondary btn-lg" style={{ display: 'inline-flex', alignItems: 'center', gap: 10 }}>
                                <TelegramIcon />
                                <span>ТГ-канал</span>
                            </a>
                        )}
                        {botUrl && (
                            <a href={botUrl} target="_blank" rel="noopener" className="btn btn-secondary btn-lg" style={{ display: 'inline-flex', alignItems: 'center', gap: 10 }}>
                                <TelegramIcon />
                                <span>ТГ-бот</span>
                            </a>
                        )}
                    </motion.div>
                )}

                <motion.ul className="hero-trust" initial="hidden" animate="show" custom={0.24} variants={fadeUp} aria-label="Преимущества">
                    <li><span className="trust-check">✓</span> Без логов</li>
                    <li><span className="trust-check">✓</span> Оплата ЮKassa</li>
                    <li><span className="trust-check">✓</span> Поддержка 24/7</li>
                    <li><span className="trust-check">✓</span> Возврат при сбое</li>
                </motion.ul>
            </div>
        </section>
    );
}
