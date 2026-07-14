import Reveal from '@/Components/landing/Reveal';

const STEPS = [
    {
        num: '01',
        title: 'Создайте аккаунт',
        text: 'Только email и пароль — больше ничего не нужно. Никаких паспортов и номеров телефона.',
    },
    {
        num: '02',
        title: 'Получите конфигурацию',
        text: 'В личном кабинете появится готовый ключ. Скопируйте ссылку или скачайте файл — всё в один клик.',
    },
    {
        num: '03',
        title: 'Подключите устройство',
        text: 'Импортируйте конфигурацию в приложение Happ, V2RayTun или любое совместимое — и интернет работает без ограничений.',
    },
];

export default function HowItWorks() {
    return (
        <section className="section how" id="how" aria-labelledby="how-title">
            <div className="container">
                <Reveal as="header" className="section-head">
                    <span className="section-eyebrow">Подключение</span>
                    <h2 id="how-title" className="section-title">Три шага — и&nbsp;вы&nbsp;в&nbsp;деле</h2>
                    <p className="section-subtitle">Не&nbsp;нужно разбираться в&nbsp;настройках. Всё работает «из&nbsp;коробки».</p>
                </Reveal>

                <ol className="steps">
                    {STEPS.map((s, i) => (
                        <Reveal as="li" key={s.num} className="step" delay={i * 0.08}>
                            <div className="step-num">{s.num}</div>
                            <div className="step-body">
                                <h3>{s.title}</h3>
                                <p>{s.text}</p>
                            </div>
                        </Reveal>
                    ))}
                </ol>
            </div>
        </section>
    );
}
