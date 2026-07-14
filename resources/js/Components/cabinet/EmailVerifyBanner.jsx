import { useForm } from '@inertiajs/react';

export default function EmailVerifyBanner() {
    const form = useForm({});

    function resend(e) {
        e.preventDefault();
        form.post(route('verification.send'));
    }

    return (
        <div
            role="status"
            className="mb-5 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3.5 text-sm leading-relaxed text-red-200"
        >
            <strong className="text-red-300">Подтвердите email.</strong>{' '}
            Проверьте почту и перейдите по ссылке из письма. Не пришло —{' '}
            <button type="button" onClick={resend} disabled={form.processing} className="underline hover:text-red-100">
                отправить ещё раз
            </button>{' '}
            или{' '}
            <a href={route('verification.notice')} className="underline hover:text-red-100">
                страница подтверждения
            </a>
            .
        </div>
    );
}
