import { usePage } from '@inertiajs/react';
import Reveal from '@/Components/landing/Reveal';
import GlowButton from '@/Components/ui/GlowButton';
import AnimatedBackground from '@/Components/ui/AnimatedBackground';

export default function FinalCta() {
    const { props } = usePage();
    const user = props.auth?.user;
    const supportUrl = props.telegram?.supportUrl;

    return (
        <section className="relative overflow-hidden bg-ink-900 py-20 sm:py-24">
            <div className="mx-auto max-w-4xl px-5 sm:px-8">
                <Reveal className="relative overflow-hidden rounded-3xl border border-red-500/30 bg-gradient-to-br from-red-600/15 via-ink-900 to-fuchsia-600/15 px-8 py-16 text-center shadow-glow-lg sm:px-16">
                    <AnimatedBackground variant="section" />
                    <div className="relative z-10">
                        <h2 className="bg-gradient-to-b from-white to-white/70 bg-clip-text text-3xl font-extrabold text-transparent sm:text-4xl">
                            Попробуйте AVA VPN прямо сейчас
                        </h2>
                        <p className="mx-auto mt-4 max-w-md text-white/55">
                            3 часа бесплатного тестового доступа без банковской карты. Если понравится — продолжите с любого тарифа.
                        </p>
                        <div className="mt-9 flex flex-wrap items-center justify-center gap-4">
                            {user ? (
                                <GlowButton href={route('cabinet.subscription')} size="lg">Перейти в кабинет →</GlowButton>
                            ) : (
                                <GlowButton href={route('register')} size="lg">Получить тест бесплатно →</GlowButton>
                            )}
                            {supportUrl && (
                                <GlowButton as="a" href={supportUrl} target="_blank" rel="noopener" variant="secondary" size="lg">
                                    Задать вопрос в Telegram
                                </GlowButton>
                            )}
                        </div>
                    </div>
                </Reveal>
            </div>
        </section>
    );
}
