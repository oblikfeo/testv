import { useForm } from '@inertiajs/react';

export default function TrialFeedbackBanner() {
    const { data, setData, post, processing, reset } = useForm({ message: '' });

    function submit(e) {
        e.preventDefault();
        post(route('cabinet.trial-feedback.submit'), { onSuccess: () => reset() });
    }

    return (
        <div className="mb-5 rounded-xl border border-sky-500/25 bg-sky-500/10 px-4 py-4 text-sm text-sky-100">
            <p>
                <strong className="text-sky-200">Помогите нам стать лучше.</strong>{' '}
                Ваш тестовый период завершился — поделитесь, пожалуйста, обратной связью по скорости и стабильности.
            </p>
            <form onSubmit={submit} className="mt-3 space-y-2.5">
                <textarea
                    value={data.message}
                    onChange={(e) => setData('message', e.target.value)}
                    rows={3}
                    maxLength={4000}
                    required
                    placeholder="Напишите в свободной форме: где всё хорошо, а где были проблемы"
                    className="w-full resize-y rounded-lg border border-white/15 bg-black/30 px-3 py-2.5 text-white placeholder-white/30 outline-none focus:border-sky-400/50 focus:ring-2 focus:ring-sky-400/20"
                />
                <button
                    type="submit"
                    disabled={processing}
                    className="rounded-lg bg-sky-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-sky-400 disabled:opacity-60"
                >
                    Отправить отзыв
                </button>
            </form>
        </div>
    );
}
