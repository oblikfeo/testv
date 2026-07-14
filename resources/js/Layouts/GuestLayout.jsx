import { Head, Link } from '@inertiajs/react';
import AnimatedBackground from '@/Components/ui/AnimatedBackground';

export default function GuestLayout({ title, children }) {
    return (
        <>
            <Head title={title ? `${title} — AVA VPN` : 'AVA VPN'} />
            <div className="relative isolate flex min-h-screen items-center justify-center overflow-hidden bg-ink-950 px-5 py-16">
                <AnimatedBackground variant="guest" />

                <div className="relative z-10 w-full max-w-md">
                    <Link href={route('home')} className="mb-8 flex items-center justify-center gap-2.5">
                        <img src="/assets/logo.png" alt="AVA VPN" className="h-9 w-9" />
                        <span className="text-xl font-extrabold tracking-tight text-white">
                            AVA <em className="bg-gradient-to-r from-red-500 to-fuchsia-500 bg-clip-text not-italic text-transparent">VPN</em>
                        </span>
                    </Link>

                    <div className="rounded-3xl border border-white/10 bg-white/[0.035] p-8 shadow-glow-lg backdrop-blur-sm sm:p-10">
                        {children}
                    </div>
                </div>
            </div>
        </>
    );
}
