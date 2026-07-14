import { Link, useForm, usePage } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import GlowButton from '@/Components/ui/GlowButton';

const inputClass =
    'w-full rounded-xl border border-white/10 bg-black/30 px-4 py-2.5 text-white placeholder-white/30 outline-none transition focus:border-red-500/50 focus:ring-2 focus:ring-red-500/20';

export default function ForgotPassword() {
    const { props } = usePage();
    const status = props.flash?.status;

    const { data, setData, post, processing, errors } = useForm({ email: '' });

    function submit(e) {
        e.preventDefault();
        post(route('password.email'));
    }

    return (
        <GuestLayout title="Восстановление доступа">
            <h1 className="mb-3 text-center text-2xl font-bold text-white">Восстановление доступа</h1>
            <p className="mb-6 text-center text-sm leading-relaxed text-white/50">
                Введите email, на который зарегистрирован аккаунт. Мы пришлём ссылку для смены пароля.
            </p>

            {status && (
                <p className="mb-5 rounded-xl border border-emerald-500/25 bg-emerald-500/10 px-4 py-2.5 text-sm text-emerald-300">
                    {status}
                </p>
            )}

            <form onSubmit={submit} className="space-y-4">
                <div className="space-y-1.5">
                    <label htmlFor="email" className="block text-sm font-medium text-white/70">Email</label>
                    <input
                        id="email" type="email" required autoFocus placeholder="name@mail.ru"
                        className={inputClass} value={data.email} onChange={(e) => setData('email', e.target.value)}
                    />
                    {errors.email && <p className="text-sm text-red-400">{errors.email}</p>}
                </div>

                <GlowButton as="button" type="submit" size="md" className="w-full" disabled={processing}>
                    Восстановить
                </GlowButton>
            </form>

            <div className="mt-7 flex items-center justify-center gap-3 text-sm text-white/45">
                <Link href={route('login')} className="transition hover:text-white">Назад ко входу</Link>
                <span className="h-1 w-1 rounded-full bg-white/20" />
                <Link href={route('home')} className="transition hover:text-white">Главная</Link>
            </div>
        </GuestLayout>
    );
}
