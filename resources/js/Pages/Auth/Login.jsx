import { Link, useForm, usePage } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import GlowButton from '@/Components/ui/GlowButton';
import TelegramLoginWidget from '@/Components/auth/TelegramLoginWidget';

const inputClass =
    'w-full rounded-xl border border-white/10 bg-black/30 px-4 py-2.5 text-white placeholder-white/30 outline-none transition focus:border-red-500/50 focus:ring-2 focus:ring-red-500/20';

export default function Login() {
    const { props } = usePage();
    const status = props.flash?.status;
    const telegramError = props.errors?.telegram;
    const { botUsername, loginEnabled } = props.telegram ?? {};

    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    function submit(e) {
        e.preventDefault();
        post(route('login'));
    }

    return (
        <GuestLayout title="Войти в аккаунт">
            <h1 className="mb-6 text-center text-2xl font-bold text-white">Войти в аккаунт</h1>

            {status && (
                <p className="mb-5 rounded-xl border border-emerald-500/25 bg-emerald-500/10 px-4 py-2.5 text-sm text-emerald-300">
                    {status}
                </p>
            )}

            <form onSubmit={submit} className="space-y-4">
                <div className="space-y-1.5">
                    <label htmlFor="email" className="block text-sm font-medium text-white/70">Email</label>
                    <input
                        id="email" type="email" required autoFocus autoComplete="username" placeholder="name@mail.ru"
                        className={inputClass} value={data.email} onChange={(e) => setData('email', e.target.value)}
                    />
                    {errors.email && <p className="text-sm text-red-400">{errors.email}</p>}
                </div>

                <div className="space-y-1.5">
                    <label htmlFor="password" className="block text-sm font-medium text-white/70">Пароль</label>
                    <input
                        id="password" type="password" required autoComplete="current-password" placeholder="Введите пароль"
                        className={inputClass} value={data.password} onChange={(e) => setData('password', e.target.value)}
                    />
                    {errors.password && <p className="text-sm text-red-400">{errors.password}</p>}
                </div>

                <div className="flex items-center justify-between text-sm">
                    <label className="flex items-center gap-2 text-white/60">
                        <input
                            type="checkbox" checked={data.remember}
                            onChange={(e) => setData('remember', e.target.checked)}
                            className="h-4 w-4 rounded border-white/20 bg-black/30 text-red-500 focus:ring-red-500/40"
                        />
                        Не выходить
                    </label>
                    <Link href={route('password.request')} className="text-white/50 transition hover:text-white">
                        Не помню пароль
                    </Link>
                </div>

                <GlowButton as="button" type="submit" size="md" className="w-full" disabled={processing}>
                    Войти
                </GlowButton>
            </form>

            {loginEnabled && (
                <>
                    <div className="my-6 flex items-center gap-3 text-xs uppercase tracking-wider text-white/30">
                        <span className="h-px flex-1 bg-white/10" /> или <span className="h-px flex-1 bg-white/10" />
                    </div>
                    {telegramError && <p className="mb-3 text-center text-sm text-red-400">{telegramError}</p>}
                    <div className="flex justify-center">
                        <TelegramLoginWidget botUsername={botUsername} />
                    </div>
                </>
            )}

            <div className="mt-7 flex items-center justify-center gap-3 text-sm text-white/45">
                <Link href={route('register')} className="transition hover:text-white">Нет аккаунта? Создать</Link>
                <span className="h-1 w-1 rounded-full bg-white/20" />
                <Link href={route('home')} className="transition hover:text-white">Главная</Link>
            </div>
        </GuestLayout>
    );
}
