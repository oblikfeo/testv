import { Link, useForm } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import GlowButton from '@/Components/ui/GlowButton';

const inputClass =
    'w-full rounded-xl border border-white/10 bg-black/30 px-4 py-2.5 text-white placeholder-white/30 outline-none transition focus:border-red-500/50 focus:ring-2 focus:ring-red-500/20';

export default function ResetPassword({ email, token }) {
    const { data, setData, post, processing, errors } = useForm({
        token,
        email: email ?? '',
        password: '',
        password_confirmation: '',
    });

    function submit(e) {
        e.preventDefault();
        post(route('password.store'));
    }

    return (
        <GuestLayout title="Новый пароль">
            <h1 className="mb-6 text-center text-2xl font-bold text-white">Новый пароль</h1>

            <form onSubmit={submit} className="space-y-4">
                <div className="space-y-1.5">
                    <label htmlFor="email" className="block text-sm font-medium text-white/70">Email</label>
                    <input
                        id="email" type="email" required autoFocus autoComplete="username"
                        className={inputClass} value={data.email} onChange={(e) => setData('email', e.target.value)}
                    />
                    {errors.email && <p className="text-sm text-red-400">{errors.email}</p>}
                </div>

                <div className="space-y-1.5">
                    <label htmlFor="password" className="block text-sm font-medium text-white/70">Новый пароль</label>
                    <input
                        id="password" type="password" required autoComplete="new-password"
                        className={inputClass} value={data.password} onChange={(e) => setData('password', e.target.value)}
                    />
                    {errors.password && <p className="text-sm text-red-400">{errors.password}</p>}
                </div>

                <div className="space-y-1.5">
                    <label htmlFor="password_confirmation" className="block text-sm font-medium text-white/70">Повтор пароля</label>
                    <input
                        id="password_confirmation" type="password" required autoComplete="new-password"
                        className={inputClass} value={data.password_confirmation}
                        onChange={(e) => setData('password_confirmation', e.target.value)}
                    />
                    {errors.password_confirmation && <p className="text-sm text-red-400">{errors.password_confirmation}</p>}
                </div>

                <GlowButton as="button" type="submit" size="md" className="w-full" disabled={processing}>
                    Сохранить пароль
                </GlowButton>
            </form>

            <div className="mt-7 flex items-center justify-center text-sm text-white/45">
                <Link href={route('login')} className="transition hover:text-white">Войти</Link>
            </div>
        </GuestLayout>
    );
}
