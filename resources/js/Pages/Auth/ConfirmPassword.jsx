import { useForm } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import GlowButton from '@/Components/ui/GlowButton';

const inputClass =
    'w-full rounded-xl border border-white/10 bg-black/30 px-4 py-2.5 text-white placeholder-white/30 outline-none transition focus:border-red-500/50 focus:ring-2 focus:ring-red-500/20';

export default function ConfirmPassword() {
    const { data, setData, post, processing, errors } = useForm({ password: '' });

    function submit(e) {
        e.preventDefault();
        post(route('password.confirm'));
    }

    return (
        <GuestLayout title="Подтверждение пароля">
            <h1 className="mb-3 text-center text-2xl font-bold text-white">Подтверждение</h1>
            <p className="mb-6 text-center text-sm leading-relaxed text-white/50">
                Это защищённый раздел приложения. Подтвердите пароль перед тем, как продолжить.
            </p>

            <form onSubmit={submit} className="space-y-4">
                <div className="space-y-1.5">
                    <label htmlFor="password" className="block text-sm font-medium text-white/70">Пароль</label>
                    <input
                        id="password" type="password" required autoFocus autoComplete="current-password"
                        className={inputClass} value={data.password} onChange={(e) => setData('password', e.target.value)}
                    />
                    {errors.password && <p className="text-sm text-red-400">{errors.password}</p>}
                </div>

                <GlowButton as="button" type="submit" size="md" className="w-full" disabled={processing}>
                    Подтвердить
                </GlowButton>
            </form>
        </GuestLayout>
    );
}
