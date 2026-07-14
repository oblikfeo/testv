import { Head, Link } from '@inertiajs/react';

export default function AgreementLayout({ title, dateLabel, children }) {
    return (
        <>
            <Head>
                <link rel="stylesheet" href="/css/landing.css" />
                <link rel="stylesheet" href="/css/agreement.css" />
            </Head>

            <main className="agreement-page">
                <div className="container">
                    <div className="agreement-content">
                        <h1>{title}</h1>
                        <p className="agreement-date">{dateLabel}</p>

                        {children}

                        <Link href={route('home')} className="agreement-back">← Вернуться на главную</Link>
                    </div>
                </div>
            </main>
        </>
    );
}
