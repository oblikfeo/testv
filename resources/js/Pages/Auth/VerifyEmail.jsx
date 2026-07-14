import { Link, useForm, usePage } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import GlowButton from '@/Components/ui/GlowButton';

export default function VerifyEmail() {
    const { props } = usePage();
    const status = props.flash?.status;

    const resendForm = useForm({});
    const logoutForm = useForm({});

    function resend(e) {
        e.preventDefault();
        resendForm.post(route('verification.send'));
    }

    function logout(e) {
        e.preventDefault();
        logoutForm.post(route('logout'));
    }

    return (
        <GuestLayout title="Подтвердите email">
            <h1 className="mb-3 text-center text-2xl font-bold text-white">Почта</h1>
            <p className="mb-6 text-center text-sm leading-relaxed text-white/50">
                Спасибо за регистрацию! Перед началом работы подтвердите email, перейдя по ссылке из письма.
                Если письмо не пришло, мы отправим его повторно.
            </p>

            {status === 'verification-link-sent' && (
                <p className="mb-5 rounded-xl border border-emerald-500/25 bg-emerald-500/10 px-4 py-2.5 text-center text-sm text-emerald-300">
                    Новая ссылка для подтверждения отправлена на email, указанный при регистрации.
                </p>
            )}

            <div className="flex flex-col gap-3">
                <GlowButton as="button" type="button" size="md" className="w-full" onClick={resend} disabled={resendForm.processing}>
                    Отправить ссылку ещё раз
                </GlowButton>
                <Link
                    href={route('cabinet.subscription')}
                    className="inline-flex w-full items-center justify-center rounded-full border border-white/15 bg-white/[0.06] px-6 py-3 text-sm font-semibold text-white/90 backdrop-blur transition hover:border-white/25 hover:bg-white/[0.1]"
                >
                    Личный кабинет
                </Link>
                <button
                    type="button" onClick={logout} disabled={logoutForm.processing}
                    className="inline-flex w-full items-center justify-center rounded-full border border-white/15 bg-white/[0.06] px-6 py-3 text-sm font-semibold text-white/90 backdrop-blur transition hover:border-white/25 hover:bg-white/[0.1]"
                >
                    Выйти
                </button>
            </div>

            <div className="mt-7 flex items-center justify-center text-sm text-white/45">
                <Link href={route('home')} className="transition hover:text-white">Главная</Link>
            </div>

            <p className="mt-6 text-center text-sm leading-relaxed text-red-400/90">
                Пока почта не подтверждена, часть функций недоступна. Проверьте входящие и папку «Спам», затем перейдите по ссылке из письма.
            </p>
        </GuestLayout>
    );
}
