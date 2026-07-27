import { Head, Link, router, usePage } from '@inertiajs/react';

const NAV_MAIN = [
    { name: 'admin.dashboard', label: 'Дашборд', icon: <><rect x="3" y="3" width="7" height="9" rx="1" /><rect x="14" y="3" width="7" height="5" rx="1" /><rect x="14" y="12" width="7" height="9" rx="1" /><rect x="3" y="16" width="7" height="5" rx="1" /></> },
    { name: 'admin.users', label: 'Пользователи', icon: <><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M22 21v-2a4 4 0 0 0-3-3.87" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /></> },
    { name: 'admin.subscriptions', label: 'Подписки', icon: <><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" /><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" /></> },
    { name: 'admin.orders', label: 'Заказы', icon: <><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" /><path d="M3 6h18" /><path d="M16 10a4 4 0 0 1-8 0" /></> },
    { name: 'admin.plans', label: 'Тарифы', icon: <><path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4" /><path d="M4 6v12c0 1.1.9 2 2 2h14v-4" /><path d="M18 12a2 2 0 0 0 0 4h4v-4Z" /></> },
    { name: 'admin.support.index', match: 'admin.support.*', label: 'Поддержка', icon: <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" /> },
];

const NAV_LEGACY = [
    { href: '/admin/trials', label: 'Триалы' },
    { href: '/admin/trial-feedback', label: 'Отзывы' },
];

function NavIcon({ children }) {
    return (
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round" className="h-[18px] w-[18px]">
            {children}
        </svg>
    );
}

function NavLink({ item }) {
    const active = route().current(item.name) || (item.match && route().current(item.match));
    const cls = active
        ? 'bg-gradient-to-r from-red-600/90 to-fuchsia-600/90 text-white shadow-glow'
        : 'text-white/55 hover:bg-white/[0.06] hover:text-white';

    return (
        <Link href={route(item.name)} className={`flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium transition ${cls}`}>
            <NavIcon>{item.icon}</NavIcon>
            <span>{item.label}</span>
        </Link>
    );
}

function FlashBanner() {
    const { props } = usePage();
    const success = props.flash?.success;
    const error = props.flash?.error;

    if (!success && !error) return null;

    return (
        <div className={`mb-6 rounded-xl border px-4 py-3 text-sm ${error ? 'border-red-500/25 bg-red-500/10 text-red-300' : 'border-emerald-500/25 bg-emerald-500/10 text-emerald-300'}`}>
            {error || success}
        </div>
    );
}

export default function AdminLayout({ title, children }) {
    const { props } = usePage();
    const adminLogin = props.admin?.login;

    function logout() {
        router.post(route('admin.logout'));
    }

    return (
        <>
            <Head title={title ? `${title} — Админка AVA VPN` : 'Админка AVA VPN'} />
            <div className="min-h-screen bg-ink-950 text-white">
                {/* Topbar */}
                <header className="sticky top-0 z-20 border-b border-white/10 bg-ink-950/85 backdrop-blur">
                    <div className="mx-auto flex max-w-7xl items-center justify-between gap-4 px-5 py-3.5 sm:px-8">
                        <Link href={route('admin.dashboard')} className="flex items-center gap-2.5">
                            <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-red-600 to-fuchsia-600 text-sm font-black">A</span>
                            <span className="font-bold">AVA VPN <span className="text-white/40">· админка</span></span>
                        </Link>
                        <div className="flex items-center gap-3 text-sm">
                            {adminLogin && <span className="hidden text-white/45 sm:inline">{adminLogin}</span>}
                            <button type="button" onClick={logout} className="rounded-full border border-white/10 px-3.5 py-1.5 font-medium text-white/60 transition hover:text-white">
                                Выйти
                            </button>
                        </div>
                    </div>
                </header>

                <div className="mx-auto flex max-w-7xl flex-col gap-6 px-5 py-6 sm:px-8 lg:flex-row lg:gap-8">
                    {/* Mobile nav */}
                    <nav className="-mx-5 flex gap-2 overflow-x-auto px-5 pb-1 sm:-mx-8 sm:px-8 lg:hidden" aria-label="Навигация админки">
                        {NAV_MAIN.map((item) => {
                            const active = route().current(item.name);
                            return (
                                <Link key={item.name} href={route(item.name)}
                                    className={`flex shrink-0 items-center gap-2 rounded-full px-4 py-2 text-sm font-medium whitespace-nowrap transition ${active ? 'bg-gradient-to-r from-red-600/90 to-fuchsia-600/90 text-white shadow-glow' : 'border border-white/10 text-white/55 hover:text-white'}`}>
                                    {item.label}
                                </Link>
                            );
                        })}
                    </nav>

                    {/* Desktop sidebar */}
                    <aside className="hidden w-56 shrink-0 lg:block" aria-label="Навигация админки">
                        <div className="sticky top-20 flex flex-col gap-5 rounded-2xl border border-white/10 bg-white/[0.025] p-4">
                            <nav className="flex flex-col gap-1">
                                {NAV_MAIN.map((item) => <NavLink key={item.name} item={item} />)}
                            </nav>
                            <div className="border-t border-white/10 pt-3">
                                <p className="mb-1.5 px-3.5 text-xs font-semibold uppercase tracking-wider text-white/30">Пока старое</p>
                                <div className="flex flex-col gap-1">
                                    {NAV_LEGACY.map((item) => (
                                        <a key={item.href} href={item.href}
                                            className="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium text-white/45 transition hover:bg-white/[0.06] hover:text-white">
                                            <span>{item.label}</span>
                                        </a>
                                    ))}
                                </div>
                            </div>
                        </div>
                    </aside>

                    <main className="min-w-0 flex-1">
                        <FlashBanner />
                        {children}
                    </main>
                </div>
            </div>
        </>
    );
}
