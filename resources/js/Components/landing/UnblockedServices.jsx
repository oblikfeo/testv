import Reveal from '@/Components/landing/Reveal';
import SectionHeading from '@/Components/ui/SectionHeading';
import GlassCard from '@/Components/ui/GlassCard';

const CATEGORIES = [
    {
        title: 'Мессенджеры и соцсети',
        text: 'Стабильные голосовые и видеозвонки в Telegram, доступ к Instagram* и X (Twitter).',
        icon: <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z" />,
    },
    {
        title: 'Видео и стриминг',
        text: 'YouTube в полном качестве без вечной буферизации и зарубежные стриминговые сервисы.',
        icon: <><rect x="2" y="4" width="20" height="16" rx="2" /><path d="m10 9 5 3-5 3z" /></>,
    },
    {
        title: 'Нейросети и рабочие сервисы',
        text: 'ChatGPT и другие ИИ-сервисы, часть Google-инструментов, Notion, Figma.',
        icon: <><path d="M12 2a5 5 0 0 0-5 5v1a4 4 0 0 0-2 7v2a3 3 0 0 0 3 3h1a3 3 0 0 0 3-3" /><path d="M12 2a5 5 0 0 1 5 5v1a4 4 0 0 1 2 7v2a3 3 0 0 1-3 3h-1a3 3 0 0 1-3-3v-9" /></>,
    },
    {
        title: 'Игры и голосовые чаты',
        text: 'Discord, игровые серверы и матчмейкинг без скачков пинга и разрывов соединения.',
        icon: <><rect x="2" y="7" width="20" height="10" rx="4" /><circle cx="8" cy="12" r="1.5" fill="currentColor" /><circle cx="16" cy="12" r="1.5" fill="currentColor" /></>,
    },
];

export default function UnblockedServices() {
    return (
        <section className="relative bg-ink-900 py-24 sm:py-28" id="unblocked" aria-labelledby="unblocked-title">
            <div className="mx-auto max-w-6xl px-5 sm:px-8">
                <SectionHeading
                    eyebrow="Доступ"
                    id="unblocked-title"
                    title="Какие сервисы возвращает VPN"
                    subtitle="Речь не про «магию» — просто стабильное шифрованное соединение туда, где обычный интернет работает нестабильно."
                />

                <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    {CATEGORIES.map((c, i) => (
                        <Reveal key={c.title} delay={i * 0.08}>
                            <GlassCard className="h-full">
                                <div className="mb-4 inline-flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-red-600/25 to-fuchsia-600/25 text-red-300">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round" className="h-5 w-5">{c.icon}</svg>
                                </div>
                                <h3 className="mb-2 text-base font-bold text-white">{c.title}</h3>
                                <p className="text-sm leading-relaxed text-white/55">{c.text}</p>
                            </GlassCard>
                        </Reveal>
                    ))}
                </div>
            </div>
        </section>
    );
}
