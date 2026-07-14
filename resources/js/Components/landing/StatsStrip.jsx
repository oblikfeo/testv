import Reveal from '@/Components/landing/Reveal';

export default function StatsStrip() {
    return (
        <section className="stats-strip" aria-label="Ключевые свойства сервиса">
            <div className="container">
                <Reveal className="stats-row">
                    <span className="stat-word">Быстро</span>
                    <span className="stat-dot" aria-hidden="true" />
                    <span className="stat-word accent">Приватно</span>
                    <span className="stat-dot" aria-hidden="true" />
                    <span className="stat-word">Стабильно</span>
                    <span className="stat-dot" aria-hidden="true" />
                    <span className="stat-word accent">Просто</span>
                </Reveal>
            </div>
        </section>
    );
}
