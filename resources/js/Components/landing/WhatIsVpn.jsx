import Reveal from '@/Components/landing/Reveal';
import SectionHeading from '@/Components/ui/SectionHeading';
import GlassCard from '@/Components/ui/GlassCard';

const COLUMNS = [
    {
        title: 'Как это работает',
        points: [
            'VPN (Virtual Private Network) шифрует весь трафик между вашим устройством и сервером — провайдер видит только зашифрованный поток, а не то, какие сайты вы открываете.',
            'Ваш реальный IP-адрес скрывается за адресом сервера, поэтому сайты и сервисы видят подключение из другой страны.',
            'Современные протоколы (в том числе VLESS, который использует AVA VPN) дополнительно маскируют трафик под обычный HTTPS-трафик, что усложняет его блокировку по протоколу.',
        ],
    },
    {
        title: 'Когда он реально нужен',
        points: [
            'Сервисы, ограниченные или нестабильно работающие в России, — от мессенджеров до стриминга и рабочих инструментов.',
            'Публичный Wi-Fi в кафе, аэропортах и отелях — без шифрования там легко перехватить незащищённый трафик.',
            'Ситуации, когда провайдер замедляет отдельные сервисы или протоколы — маскировка трафика снижает вероятность такого throttling.',
        ],
    },
];

export default function WhatIsVpn() {
    return (
        <section className="relative bg-ink-950 py-24 sm:py-28" id="what-is-vpn" aria-labelledby="what-is-vpn-title">
            <div className="mx-auto max-w-6xl px-5 sm:px-8">
                <SectionHeading
                    eyebrow="Ликбез"
                    id="what-is-vpn-title"
                    title="Что такое VPN и зачем он нужен в России"
                    subtitle="Если вы впервые разбираетесь в теме — вот короткое и честное объяснение, без маркетингового тумана."
                />

                <div className="grid gap-6 md:grid-cols-2">
                    {COLUMNS.map((col, i) => (
                        <Reveal key={col.title} delay={i * 0.1}>
                            <GlassCard className="h-full">
                                <h3 className="mb-4 text-xl font-bold text-white">{col.title}</h3>
                                <ul className="space-y-3">
                                    {col.points.map((point) => (
                                        <li key={point} className="flex gap-3 text-sm leading-relaxed text-white/60">
                                            <span className="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-gradient-to-r from-red-500 to-fuchsia-500" />
                                            {point}
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
