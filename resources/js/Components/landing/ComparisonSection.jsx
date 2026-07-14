import Reveal from '@/Components/landing/Reveal';

export default function ComparisonSection() {
    return (
        <section className="section compare" id="compare" aria-labelledby="compare-title">
            <div className="container">
                <Reveal as="header" className="section-head">
                    <span className="section-eyebrow">Сравнение</span>
                    <h2 id="compare-title" className="section-title">AVA VPN против бесплатных и&nbsp;«серых» сервисов</h2>
                    <p className="section-subtitle">Почему платный VPN с&nbsp;собственной инфраструктурой надёжнее, чем «бесплатные» решения.</p>
                </Reveal>

                <div className="compare-grid">
                    <Reveal as="article" className="compare-card compare-card--bad">
                        <h3>Бесплатные VPN</h3>
                        <ul>
                            <li>Низкая скорость и ограничения по трафику</li>
                            <li>Часто продают данные пользователей и показывают рекламу</li>
                            <li>Нестабильно работают, серверы перегружены</li>
                            <li>Поддержки нет — пишите на форум и ждите</li>
                        </ul>
                    </Reveal>
                    <Reveal as="article" className="compare-card compare-card--good" delay={0.08}>
                        <h3>AVA VPN</h3>
                        <ul>
                            <li>Без лимитов скорости и трафика</li>
                            <li>Не продаём данные, не показываем рекламу</li>
                            <li>Собственные серверы, мониторинг 24/7</li>
                            <li>Живая поддержка в Telegram, ответ — минуты</li>
                        </ul>
                    </Reveal>
                </div>
            </div>
        </section>
    );
}
