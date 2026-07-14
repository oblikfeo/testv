import { Link, router } from '@inertiajs/react';

const NAV_GROUPS = [
    {
        label: 'Подключение',
        items: [
            {
                name: 'cabinet.subscription',
                label: 'Подписка',
                icon: <><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" /><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" /></>,
            },
            {
                name: 'cabinet.trial',
                label: 'Тест-драйв',
                icon: <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" />,
            },
        ],
    },
    {
        label: 'Аккаунт',
        items: [
            {
                name: 'cabinet.history',
                label: 'Покупки',
                icon: <><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" /><path d="M3 6h18" /><path d="M16 10a4 4 0 0 1-8 0" /></>,
            },
            {
                name: 'cabinet.profile',
                label: 'Профиль',
                icon: <><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" /><circle cx="12" cy="7" r="4" /></>,
            },
            {
                name: 'cabinet.security',
                label: 'Безопасность',
                icon: <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />,
            },
            {
                name: 'cabinet.support.index',
                label: 'Поддержка',
                icon: <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />,
            },
        ],
    },
];

function NavIcon({ children }) {
    return (
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round" className="h-[18px] w-[18px]">
            {children}
        </svg>
    );
}

function NavLink({ item, mobile }) {
    const active = route().current(item.name) || (item.name === 'cabinet.support.index' && route().current('cabinet.support.*'));

    const base = mobile
        ? 'flex shrink-0 items-center gap-2 rounded-full px-4 py-2 text-sm font-medium transition whitespace-nowrap'
        : 'flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium transition';

    const activeClass = active
        ? 'bg-gradient-to-r from-red-600/90 to-fuchsia-600/90 text-white shadow-glow'
        : 'text-white/55 hover:bg-white/[0.06] hover:text-white';

    return (
        <Link href={route(item.name)} className={`${base} ${activeClass}`}>
            <NavIcon>{item.icon}</NavIcon>
            <span>{item.label}</span>
        </Link>
    );
}

function LogoutButton({ mobile }) {
    function logout() {
        router.post(route('logout'));
    }

    return (
        <button
            type="button"
            onClick={logout}
            className={
                mobile
                    ? 'flex shrink-0 items-center gap-2 rounded-full border border-white/10 px-4 py-2 text-sm font-medium text-white/50 transition hover:text-white whitespace-nowrap'
                    : 'flex w-full items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium text-white/50 transition hover:bg-white/[0.06] hover:text-white'
            }
        >
            <NavIcon>
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                <polyline points="16 17 21 12 16 7" />
                <line x1="21" y1="12" x2="9" y2="12" />
            </NavIcon>
            <span>Выйти</span>
        </button>
    );
}

export default function Sidebar() {
    return (
        <>
            {/* Mobile: horizontal scrollable tab bar */}
            <nav className="-mx-5 mb-6 flex gap-2 overflow-x-auto px-5 pb-1 lg:hidden" aria-label="Навигация кабинета">
                {NAV_GROUPS.flatMap((g) => g.items).map((item) => (
                    <NavLink key={item.name} item={item} mobile />
                ))}
                <LogoutButton mobile />
            </nav>

            {/* Desktop: fixed sidebar */}
            <aside className="hidden w-60 shrink-0 lg:block" aria-label="Навигация кабинета">
                <div className="sticky top-24 flex flex-col gap-6 rounded-2xl border border-white/10 bg-white/[0.025] p-4">
                    <nav className="flex flex-col gap-1">
                        {NAV_GROUPS.map((group) => (
                            <div key={group.label} className="mb-1">
                                <p className="mb-1.5 px-3.5 text-xs font-semibold uppercase tracking-wider text-white/30">{group.label}</p>
                                <div className="flex flex-col gap-1">
                                    {group.items.map((item) => (
                                        <NavLink key={item.name} item={item} />
                                    ))}
                                </div>
                            </div>
                        ))}
                    </nav>
                    <div className="border-t border-white/10 pt-3">
                        <LogoutButton />
                    </div>
                </div>
            </aside>
        </>
    );
}
