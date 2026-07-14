import Reveal from '@/Components/landing/Reveal';

const DEVICES = [
    { title: 'Android', apps: 'Happ, V2RayTun, Hiddify, NekoBox', icon: <><rect x="6" y="2" width="12" height="20" rx="2" /><path d="M11 18h2" /></> },
    { title: <>iPhone&nbsp;и&nbsp;iPad</>, apps: 'Happ, Streisand, V2Box, Foxray', icon: <path d="M16.5 3a4 4 0 0 1-3.5 4M9 5a5 5 0 0 1 5 4 5 5 0 0 1 5 5c0 4-3 8-5 8s-2.5-1-5-1-3 1-5 1-5-4-5-8a5 5 0 0 1 5-5 5 5 0 0 1 5-4z" /> },
    { title: 'Windows', apps: 'Hiddify, Nekoray, V2RayN, FlClash', icon: <><rect x="2" y="4" width="20" height="14" rx="2" /><path d="M8 22h8M12 18v4" /></> },
    { title: 'macOS', apps: 'Hiddify, V2RayU, Streisand, Happ', icon: <><rect x="3" y="4" width="18" height="13" rx="2" /><path d="M2 21h20" /></> },
    { title: 'Роутер', apps: 'OpenWrt, Keenetic, Padavan', icon: <><path d="M3 6h18v6H3zM3 14h18v6H3z" /><circle cx="7" cy="9" r=".5" fill="currentColor" /><circle cx="7" cy="17" r=".5" fill="currentColor" /></> },
    { title: 'Linux', apps: 'Hiddify, Nekoray, sing-box CLI', icon: <><circle cx="12" cy="12" r="9" /><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18" /></> },
];

export default function DeviceGrid() {
    return (
        <section className="section devices" id="devices" aria-labelledby="devices-title">
            <div className="container">
                <Reveal as="header" className="section-head">
                    <span className="section-eyebrow">Платформы</span>
                    <h2 id="devices-title" className="section-title">Работает на&nbsp;всех ваших устройствах</h2>
                    <p className="section-subtitle">Один ключ — несколько устройств. Подключайте телефон, компьютер, планшет и&nbsp;роутер.</p>
                </Reveal>

                <div className="devices-grid">
                    {DEVICES.map((d, i) => (
                        <Reveal as="article" key={d.apps} className="device-card" delay={i * 0.06}>
                            <div className="device-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round">{d.icon}</svg>
                            </div>
                            <h3>{d.title}</h3>
                            <p>{d.apps}</p>
                        </Reveal>
                    ))}
                </div>
            </div>
        </section>
    );
}
