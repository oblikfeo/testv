import { useEffect, useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { motion } from 'framer-motion';
import VisitorCounter from '@/Components/landing/VisitorCounter';
import GlowButton from '@/Components/ui/GlowButton';

function TelegramIcon() {
    return (
        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0h-.056zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z" />
        </svg>
    );
}

export default function Navbar({ visitorCount }) {
    const { props } = usePage();
    const user = props.auth?.user;
    const { botUrl, channelUrl } = props.telegram ?? {};
    const [scrolled, setScrolled] = useState(false);

    useEffect(() => {
        function onScroll() {
            setScrolled(window.scrollY > 8);
        }
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
        return () => window.removeEventListener('scroll', onScroll);
    }, []);

    return (
        <motion.nav
            initial={{ y: -32, opacity: 0 }}
            animate={{ y: 0, opacity: 1 }}
            transition={{ duration: 0.6, ease: [0.16, 1, 0.3, 1] }}
            className={`sticky top-0 z-50 border-b transition-all duration-300 ${
                scrolled
                    ? 'border-white/10 bg-ink-950/80 shadow-[0_8px_30px_-20px_rgba(0,0,0,0.9)] backdrop-blur-xl'
                    : 'border-transparent bg-transparent'
            }`}
        >
            <div className="mx-auto flex max-w-7xl items-center justify-between gap-4 px-5 py-3 sm:px-8">
                <Link href={route('home')} className="group flex items-center gap-2.5">
                    <img src="/assets/logo.png" alt="AVA VPN" className="h-8 w-8 transition-transform duration-300 group-hover:scale-110" />
                    <span className="text-lg font-extrabold tracking-tight text-white">
                        AVA <em className="bg-gradient-to-r from-red-500 to-fuchsia-500 bg-clip-text not-italic text-transparent">VPN</em>
                    </span>
                </Link>

                <div className="flex items-center gap-2 sm:gap-3">
                    <div className="hidden sm:block">
                        <VisitorCounter visitorCount={visitorCount} />
                    </div>
                    {channelUrl && (
                        <a
                            href={channelUrl}
                            target="_blank"
                            rel="noopener"
                            className="hidden items-center gap-2 rounded-full border border-white/15 bg-white/[0.04] px-4 py-2 text-sm font-medium text-white/80 backdrop-blur transition-colors hover:border-white/25 hover:bg-white/[0.08] hover:text-white md:inline-flex"
                        >
                            <TelegramIcon />
                            <span>Канал</span>
                        </a>
                    )}
                    {botUrl && (
                        <a
                            href={botUrl}
                            target="_blank"
                            rel="noopener"
                            className="hidden items-center gap-2 rounded-full border border-white/15 bg-white/[0.04] px-4 py-2 text-sm font-medium text-white/80 backdrop-blur transition-colors hover:border-white/25 hover:bg-white/[0.08] hover:text-white md:inline-flex"
                        >
                            <TelegramIcon />
                            <span>Бот</span>
                        </a>
                    )}
                    <GlowButton
                        href={user ? route('cabinet.subscription') : route('login')}
                        size="sm"
                        variant="primary"
                    >
                        {user ? 'Кабинет' : 'Войти'}
                    </GlowButton>
                </div>
            </div>
        </motion.nav>
    );
}
