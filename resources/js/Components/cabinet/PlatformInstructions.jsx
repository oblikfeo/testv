import { useState } from 'react';
import GlassCard from '@/Components/ui/GlassCard';

const PLATFORMS = {
    ios: {
        label: 'iOS',
        install: [
            { href: 'https://apps.apple.com/app/happ-proxy-utility/id6504287215', label: 'Happ — App Store' },
            { href: 'https://apps.apple.com/app/v2raytun/id6476628951', label: 'v2RayTun — App Store' },
        ],
        showImport: true,
        connectText: 'На главном экране приложения включите VPN. При необходимости выберите сервер из списка. Разрешите создание VPN-профиля при первом запуске.',
    },
    android: {
        label: 'Android',
        install: [
            { href: 'https://play.google.com/store/apps/details?id=com.happproxy', label: 'Happ — Google Play' },
            { href: 'https://play.google.com/store/apps/details?id=com.v2raytun.android', label: 'v2RayTun — Google Play' },
        ],
        showImport: true,
        connectText: 'Нажмите кнопку подключения на главном экране. При первом запуске разрешите приложению создать VPN-соединение.',
    },
    windows: {
        label: 'Windows',
        install: [
            { href: 'https://github.com/Happ-proxy/happ-desktop/releases/latest', label: 'Happ — Скачать' },
            { href: 'https://v2raytun.com/', label: 'v2RayTun — Скачать' },
        ],
        showImport: false,
        connectText: 'Выберите сервер из списка и нажмите кнопку подключения.',
    },
    macos: {
        label: 'macOS',
        install: [
            { href: 'https://apps.apple.com/app/happ-proxy-utility/id6504287215', label: 'Happ — App Store' },
            { href: 'https://apps.apple.com/app/v2raytun/id6476628951', label: 'v2RayTun — App Store' },
        ],
        showImport: true,
        connectText: 'Выберите сервер и включите VPN. При первом запуске macOS попросит разрешение на установку VPN-профиля — подтвердите паролем.',
    },
};

function StepCard({ n, title, children }) {
    return (
        <div className="flex gap-4 rounded-xl border border-white/10 bg-black/25 p-4 transition hover:border-red-500/25">
            <div className="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-red-500/25 bg-red-500/10 text-xs font-bold text-red-300">
                {n}
            </div>
            <div className="min-w-0 flex-1">
                <h3 className="mb-1 text-sm font-semibold text-white">{title}</h3>
                <div className="text-sm leading-relaxed text-white/55">{children}</div>
            </div>
        </div>
    );
}

export default function PlatformInstructions({ connectionUri, title = 'Как подключиться', description }) {
    const [platform, setPlatform] = useState('ios');
    const [copied, setCopied] = useState(false);
    const current = PLATFORMS[platform];

    function importTo(app) {
        if (!connectionUri) return;
        const scheme = app === 'happ' ? 'happ://import/' : 'v2raytun://import/';
        window.location.href = scheme + encodeURIComponent(connectionUri);
    }

    function copyLink() {
        if (!connectionUri) return;
        navigator.clipboard.writeText(connectionUri).then(() => {
            setCopied(true);
            setTimeout(() => setCopied(false), 1500);
        });
    }

    return (
        <GlassCard className="p-0 overflow-hidden">
            <div className="px-6 pt-6 sm:px-7">
                <h2 className="mb-1.5 text-lg font-bold text-white">{title}</h2>
                <p className="max-w-md text-sm leading-relaxed text-white/50">{description}</p>
            </div>

            <div role="tablist" className="mx-6 my-5 flex gap-1.5 overflow-x-auto rounded-xl border border-white/10 bg-black/30 p-1.5 sm:mx-7">
                {Object.entries(PLATFORMS).map(([key, p]) => (
                    <button
                        key={key} type="button" role="tab" aria-selected={platform === key}
                        onClick={() => setPlatform(key)}
                        className={`flex-1 shrink-0 whitespace-nowrap rounded-lg px-3.5 py-2 text-sm font-medium transition ${
                            platform === key ? 'bg-red-600 text-white shadow-glow' : 'text-white/55 hover:bg-white/[0.05] hover:text-white'
                        }`}
                    >
                        {p.label}
                    </button>
                ))}
            </div>

            <div className="flex flex-col gap-3 px-6 pb-6 sm:px-7">
                <StepCard n={1} title="Установите приложение">
                    <p>Скачайте Happ или v2RayTun.</p>
                    <div className="mt-3 flex flex-wrap gap-2">
                        {current.install.map((btn) => (
                            <a
                                key={btn.href} href={btn.href} target="_blank" rel="noopener"
                                className="rounded-lg border border-white/15 bg-white/[0.05] px-3 py-1.5 text-xs font-medium text-white/80 transition hover:bg-white/[0.1]"
                            >
                                {btn.label}
                            </a>
                        ))}
                    </div>
                </StepCard>

                <StepCard n={2} title="Добавьте подписку">
                    <p>Откройте приложение, нажмите «+» и вставьте скопированную ссылку подключения.</p>
                    {connectionUri && (
                        <div className="mt-3 flex flex-wrap gap-2">
                            {current.showImport && (
                                <>
                                    <button type="button" onClick={() => importTo('happ')} className="rounded-lg border border-white/15 bg-white/[0.05] px-3 py-1.5 text-xs font-medium text-white/80 transition hover:bg-white/[0.1]">
                                        + Добавить в Happ
                                    </button>
                                    <button type="button" onClick={() => importTo('v2raytun')} className="rounded-lg border border-white/15 bg-white/[0.05] px-3 py-1.5 text-xs font-medium text-white/80 transition hover:bg-white/[0.1]">
                                        + Добавить в v2RayTun
                                    </button>
                                </>
                            )}
                            <button type="button" onClick={copyLink} className="rounded-lg border border-white/15 bg-white/[0.05] px-3 py-1.5 text-xs font-medium text-white/80 transition hover:bg-white/[0.1]">
                                {copied ? 'Скопировано' : 'Копировать ссылку'}
                            </button>
                        </div>
                    )}
                </StepCard>

                <StepCard n={3} title="Подключитесь">
                    <p>{current.connectText}</p>
                </StepCard>
            </div>
        </GlassCard>
    );
}
