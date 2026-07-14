import Reveal from '@/Components/landing/Reveal';
import SectionHeading from '@/Components/ui/SectionHeading';
import GlassCard from '@/Components/ui/GlassCard';

const DEVICES = [
    { title: 'Android', apps: 'Happ, V2RayTun, Hiddify, NekoBox', icon: <><rect x="6" y="2" width="12" height="20" rx="2" /><path d="M11 18h2" /></> },
    { title: 'iPhone и iPad', apps: 'Happ, Streisand, V2Box, Foxray', icon: <path d="M16.5 3a4 4 0 0 1-3.5 4M9 5a5 5 0 0 1 5 4 5 5 0 0 1 5 5c0 4-3 8-5 8s-2.5-1-5-1-3 1-5 1-5-4-5-8a5 5 0 0 1 5-5 5 5 0 0 1 5-4z" /> },
    { title: 'Windows', apps: 'Hiddify, Nekoray, V2RayN, FlClash', icon: <><rect x="2" y="4" width="20" height="14" rx="2" /><path d="M8 22h8M12 18v4" /></> },
    { title: 'macOS', apps: 'Hiddify, V2RayU, Streisand, Happ', icon: <><rect x="3" y="4" width="18" height="13" rx="2" /><path d="M2 21h20" /></> },
    { title: 'Роутер', apps: 'OpenWrt, Keenetic, Padavan', icon: <><path d="M3 6h18v6H3zM3 14h18v6H3z" /><circle cx="7" cy="9" r=".5" fill="currentColor" /><circle cx="7" cy="17" r=".5" fill="currentColor" /></> },
    { title: 'Linux', apps: 'Hiddify, Nekoray, sing-box CLI', icon: <><circle cx="12" cy="12" r="9" /><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18" /></> },
];

export default function DeviceGrid() {
    return (
        <section className="relative bg-ink-950 py-20 sm:py-24" id="devices" aria-labelledby="devices-title">
            <div className="mx-auto max-w-6xl px-5 sm:px-8">
                <SectionHeading
                    eyebrow="Платформы"
                    id="devices-title"
                    title="Работает на всех ваших устройствах"
                    subtitle="Один ключ — несколько устройств. Подключайте телефон, компьютер, планшет и роутер."
                />

                <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    {DEVICES.map((d, i) => (
                        <Reveal key={d.title} delay={i * 0.06}>
                            <GlassCard className="flex h-full items-start gap-4">
                                <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-red-600/25 to-fuchsia-600/25 text-red-300">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round" className="h-5 w-5">{d.icon}</svg>
                                </div>
                                <div>
                                    <h3 className="mb-1 text-base font-bold text-white">{d.title}</h3>
                                    <p className="text-sm leading-relaxed text-white/55">{d.apps}</p>
                                </div>
                            </GlassCard>
                        </Reveal>
                    ))}
                </div>
            </div>
        </section>
    );
}
