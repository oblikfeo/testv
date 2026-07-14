import { Link, useForm } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import GlowButton from '@/Components/ui/GlowButton';

const inputClass =
    'w-full rounded-xl border border-white/10 bg-black/30 px-4 py-2.5 text-white placeholder-white/30 outline-none transition focus:border-red-500/50 focus:ring-2 focus:ring-red-500/20';

export default function Register() {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        terms: false,
    });

    function submit(e) {
        e.preventDefault();
        post(route('register'));
    }

    return (
        <GuestLayout title="Создать аккаунт">
            <h1 className="mb-6 text-center text-2xl font-bold text-white">Создать аккаунт</h1>

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
                    <label htmlFor="password" className="block text-sm font-medium text-white/70">Придумайте пароль</label>
                    <input
                        id="password" type="password" required minLength={8} autoComplete="new-password" placeholder="От 8 символов"
                        className={inputClass} value={data.password} onChange={(e) => setData('password', e.target.value)}
                    />
                    <p className="text-xs text-white/35">Если забудете — можно восстановить через почту.</p>
                    {errors.password && <p className="text-sm text-red-400">{errors.password}</p>}
                </div>

                <label className="flex items-start gap-2.5 text-sm text-white/60">
                    <input
                        type="checkbox" required checked={data.terms}
                        onChange={(e) => setData('terms', e.target.checked)}
                        className="mt-0.5 h-4 w-4 rounded border-white/20 bg-black/30 text-red-500 focus:ring-red-500/40"
                    />
                    <span>
                        Принимаю{' '}
                        <a href={route('offer')} target="_blank" rel="noopener" className="text-red-300 underline hover:text-red-200">
                            условия использования
                        </a>
                    </span>
                </label>
                {errors.terms && <p className="text-sm text-red-400">{errors.terms}</p>}

                <GlowButton as="button" type="submit" size="md" className="w-full" disabled={processing}>
                    Создать
                </GlowButton>
            </form>

            <div className="mt-7 flex items-center justify-center gap-3 text-sm text-white/45">
                <Link href={route('login')} className="transition hover:text-white">Уже есть аккаунт? Войти</Link>
                <span className="h-1 w-1 rounded-full bg-white/20" />
                <Link href={route('home')} className="transition hover:text-white">Главная</Link>
            </div>
        </GuestLayout>
    );
}
