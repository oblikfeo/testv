import { useEffect, useRef } from 'react';

export default function TelegramLoginWidget({ botUsername }) {
    const containerRef = useRef(null);

    useEffect(() => {
        if (!botUsername || !containerRef.current) return undefined;

        const script = document.createElement('script');
        script.src = 'https://telegram.org/js/telegram-widget.js?22';
        script.async = true;
        script.setAttribute('data-telegram-login', botUsername);
        script.setAttribute('data-size', 'large');
        script.setAttribute('data-radius', '10');
        script.setAttribute('data-request-access', 'write');
        script.setAttribute('data-auth-url', route('auth.telegram.callback'));

        const container = containerRef.current;
        container.appendChild(script);

        return () => {
            container.innerHTML = '';
        };
    }, [botUsername]);

    return <div ref={containerRef} />;
}
