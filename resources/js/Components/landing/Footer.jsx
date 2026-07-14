import { Link } from '@inertiajs/react';

export default function Footer({ variant = 'default' }) {
    return (
        <footer className="footer">
            <div className="container">
                <p>
                    {variant === 'legal' ? (
                        <>
                            AVA VPN — сервис для защиты и ускорения вашего интернет-соединения.<br />
                            Используя сервис, вы принимаете на себя ответственность за его применение
                            в соответствии с действующим законодательством.<br /><br />
                        </>
                    ) : (
                        <>
                            AVA VPN — сервис для защиты и приватности вашего интернет-соединения.<br /><br />
                        </>
                    )}
                    <Link href={route('privacy')}>Политика конфиденциальности</Link> ·{' '}
                    <Link href={route('offer')}>Оферта</Link> ·{' '}
                    <Link href={route('personal-data')}>Обработка персональных данных</Link>
                </p>
            </div>
        </footer>
    );
}
