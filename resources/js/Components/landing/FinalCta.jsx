import { Link, usePage } from '@inertiajs/react';
import Reveal from '@/Components/landing/Reveal';

export default function FinalCta() {
    const { props } = usePage();
    const user = props.auth?.user;
    const supportUrl = props.telegram?.supportUrl;

    return (
        <section className="section cta-final" aria-labelledby="cta-title">
            <div className="container">
                <Reveal className="cta-card">
                    <div className="cta-glow" aria-hidden="true" />
                    <h2 id="cta-title">Попробуйте AVA&nbsp;VPN прямо сейчас</h2>
                    <p>3&nbsp;часа бесплатного тестового доступа без банковской карты.<br />Если понравится — продолжите с&nbsp;любого тарифа.</p>
                    <div className="cta-buttons">
                        {user ? (
                            <Link href={route('cabinet.subscription')} className="btn btn-primary btn-lg">Перейти в&nbsp;кабинет →</Link>
                        ) : (
                            <Link href={route('register')} className="btn btn-primary btn-lg">Получить тест бесплатно →</Link>
                        )}
                        {supportUrl && (
                            <a href={supportUrl} target="_blank" rel="noopener" className="btn btn-secondary btn-lg">Задать вопрос в&nbsp;Telegram</a>
                        )}
                    </div>
                </Reveal>
            </div>
        </section>
    );
}
