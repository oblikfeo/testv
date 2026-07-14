import { useEffect, useState } from 'react';
import { router } from '@inertiajs/react';

export default function PaymentStatusBanner({ orderId }) {
    const [state, setState] = useState({ variant: 'info', text: 'Проверяем статус оплаты…' });

    useEffect(() => {
        if (!orderId) return undefined;

        let attempts = 0;
        let timer = null;
        let cancelled = false;
        const maxAttempts = 30;

        function cleanUrl() {
            const url = new URL(window.location.href);
            url.searchParams.delete('order_id');
            window.history.replaceState({}, '', url.pathname + url.search);
        }

        function poll() {
            attempts += 1;
            fetch(`${route('payment.status')}?order_id=${encodeURIComponent(orderId)}`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            })
                .then((r) => r.json())
                .then((data) => {
                    if (cancelled) return;
                    if (data.status === 'fulfilled') {
                        setState({ variant: 'success', text: 'Оплата прошла успешно. Обновляем кабинет…' });
                        cleanUrl();
                        timer = setTimeout(() => router.reload(), 1200);
                        return;
                    }
                    if (data.status === 'cancelled') {
                        setState({ variant: 'error', text: 'Платёж отменён. Можно попробовать снова в разделе «Покупки».' });
                        cleanUrl();
                        return;
                    }
                    if (attempts >= maxAttempts) {
                        setState({ variant: 'info', text: 'Оплата ещё обрабатывается. Обновите страницу через минуту.' });
                        cleanUrl();
                        return;
                    }
                    timer = setTimeout(poll, 2000);
                })
                .catch(() => {
                    if (cancelled) return;
                    if (attempts >= maxAttempts) {
                        setState({ variant: 'info', text: 'Не удалось проверить оплату. Обновите страницу.' });
                        cleanUrl();
                        return;
                    }
                    timer = setTimeout(poll, 2000);
                });
        }

        poll();

        return () => {
            cancelled = true;
            if (timer) clearTimeout(timer);
        };
    }, [orderId]);

    if (!orderId) return null;

    const variants = {
        info: 'border-sky-500/25 bg-sky-500/10 text-sky-200',
        success: 'border-emerald-500/25 bg-emerald-500/10 text-emerald-300',
        error: 'border-red-500/25 bg-red-500/10 text-red-300',
    };

    return (
        <div role="status" className={`mb-5 rounded-xl border px-4 py-3 text-sm ${variants[state.variant]}`}>
            {state.text}
        </div>
    );
}
