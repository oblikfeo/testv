import Reveal from '@/Components/landing/Reveal';
import SectionHeading from '@/Components/ui/SectionHeading';

export default function ComparisonSection() {
    return (
        <section className="relative bg-ink-900 py-20 sm:py-24" id="compare" aria-labelledby="compare-title">
            <div className="mx-auto max-w-5xl px-5 sm:px-8">
                <SectionHeading
                    eyebrow="Сравнение"
                    id="compare-title"
                    title="AVA VPN против бесплатных и «серых» сервисов"
                    subtitle="Почему платный VPN с собственной инфраструктурой надёжнее, чем «бесплатные» решения."
                />

                <div className="grid gap-6 md:grid-cols-2">
                    <Reveal className="rounded-2xl border border-white/10 bg-white/[0.02] p-7">
                        <h3 className="mb-4 text-lg font-bold text-white/70">Бесплатные VPN</h3>
                        <ul className="space-y-3 text-sm text-white/45">
                            {[
                                'Низкая скорость и ограничения по трафику',
                                'Часто продают данные пользователей и показывают рекламу',
                                'Нестабильно работают, серверы перегружены',
                                'Поддержки нет — пишите на форум и ждите',
                            ].map((item) => (
                                <li key={item} className="flex gap-3">
                                    <span className="text-white/25">✕</span> {item}
                                </li>
                            ))}
                        </ul>
                    </Reveal>

                    <Reveal delay={0.1} className="relative rounded-2xl border border-red-500/30 bg-gradient-to-b from-red-600/[0.08] to-fuchsia-600/[0.04] p-7 shadow-glow">
                        <h3 className="mb-4 text-lg font-bold text-white">AVA VPN</h3>
                        <ul className="space-y-3 text-sm text-white/75">
                            {[
                                'Без лимитов скорости и трафика',
                                'Не продаём данные, не показываем рекламу',
                                'Собственные серверы, мониторинг 24/7',
                                'Живая поддержка в Telegram, ответ — минуты',
                            ].map((item) => (
                                <li key={item} className="flex gap-3">
                                    <span className="text-red-400">✓</span> {item}
                                </li>
                            ))}
                        </ul>
                    </Reveal>
                </div>
            </div>
        </section>
    );
}
