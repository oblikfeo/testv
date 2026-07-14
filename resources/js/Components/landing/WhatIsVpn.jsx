import Reveal from '@/Components/landing/Reveal';
import SectionHeading from '@/Components/ui/SectionHeading';
import GlassCard from '@/Components/ui/GlassCard';

const COLUMNS = [
    {
        title: 'Как это работает',
        icon: <><path d="M12 2 4 5v6c0 5 3.5 9.3 8 11 4.5-1.7 8-6 8-11V5l-8-3z" /><path d="m9 12 2 2 4-4" /></>,
        points: [
            'VPN шифрует весь трафик между вашим устройством и сервером — провайдер видит только зашифрованный поток, а не то, какие сайты вы открываете.',
            'Ваш реальный IP-адрес скрывается за адресом сервера, поэтому сайты и сервисы видят подключение из другой страны.',
            'VLESS, который использует AVA VPN, дополнительно маскирует трафик под обычный HTTPS, что усложняет его блокировку по протоколу.',
        ],
    },
    {
        title: 'Когда он реально нужен',
        icon: <><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" /></>,
        points: [
            'Сервисы, ограниченные или нестабильно работающие в России, — от мессенджеров до стриминга и рабочих инструментов.',
            'Публичный Wi-Fi в кафе, аэропортах и отелях — без шифрования там легко перехватить незащищённый трафик.',
            'Ситуации, когда провайдер замедляет отдельные сервисы или протоколы — маскировка трафика снижает вероятность такого throttling.',
        ],
    },
];

export default function WhatIsVpn() {
    return (
        <section className="relative bg-ink-950 py-20 sm:py-24" id="what-is-vpn" aria-labelledby="what-is-vpn-title">
            <div className="mx-auto max-w-6xl px-5 sm:px-8">
                <SectionHeading
                    id="what-is-vpn-title"
                    title="Что такое VPN и зачем он нужен в России"
                    subtitle="Короткое и честное объяснение, без маркетингового тумана."
                />

                <div className="grid gap-6 md:grid-cols-2">
                    {COLUMNS.map((col, i) => (
                        <Reveal key={col.title} delay={i * 0.1} className="h-full">
                            <GlassCard className="h-full">
                                <div className="mb-5 inline-flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-red-600/25 to-fuchsia-600/25 text-red-300">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" className="h-5 w-5">{col.icon}</svg>
                                </div>
                                <h3 className="mb-4 text-xl font-bold text-white">{col.title}</h3>
                                <ul className="space-y-4">
                                    {col.points.map((point) => (
                                        <li key={point} className="flex items-start gap-3 text-sm leading-relaxed text-white/60">
                                            <span className="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-white/10 text-[10px] font-bold text-red-300">✓</span>
                                            <span>{point}</span>
                                        </li>
                                    ))}
                                </ul>
                            </GlassCard>
                        </Reveal>
                    ))}
                </div>
            </div>
        </section>
    );
}
