import { Link } from '@inertiajs/react';

export default function Footer({ variant = 'default' }) {
    return (
        <footer className="relative border-t border-white/10 bg-ink-950 py-10">
            <div className="mx-auto max-w-7xl px-5 text-center sm:px-8">
                <p className="text-sm leading-relaxed text-white/45">
                    {variant === 'legal' ? (
                        <>
                            AVA VPN — сервис для защиты и ускорения вашего интернет-соединения.
                            Используя сервис, вы принимаете на себя ответственность за его применение
                            в соответствии с действующим законодательством.
                        </>
                    ) : (
                        <>AVA VPN — сервис для защиты и приватности вашего интернет-соединения.</>
                    )}
                </p>
                <div className="mt-4 flex flex-wrap items-center justify-center gap-x-2 gap-y-1 text-sm text-white/40">
                    <Link href={route('privacy')} className="transition-colors hover:text-white">Политика конфиденциальности</Link>
                    <span className="text-white/20">·</span>
                    <Link href={route('offer')} className="transition-colors hover:text-white">Оферта</Link>
                    <span className="text-white/20">·</span>
                    <Link href={route('personal-data')} className="transition-colors hover:text-white">Обработка персональных данных</Link>
                </div>
            </div>
        </footer>
    );
}
