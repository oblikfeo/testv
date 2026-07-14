import { Link, usePage } from '@inertiajs/react';
import Reveal from '@/Components/landing/Reveal';

function findPlan(plans, days) {
    return plans?.find((p) => p.days === days) ?? null;
}

function pluralDevices(n) {
    const mod10 = n % 10;
    const mod100 = n % 100;
    if (mod10 === 1 && mod100 !== 11) return 'устройство';
    if ([2, 3, 4].includes(mod10) && ![12, 13, 14].includes(mod100)) return 'устройства';
    return 'устройств';
}

export default function PricingTable({ planGroups = [] }) {
    const { props } = usePage();
    const user = props.auth?.user;

    const standard = planGroups.find((g) => g[0]?.devices === 2) ?? [];
    const extended = planGroups.find((g) => g[0]?.devices === 5) ?? [];
    const premium = planGroups.find((g) => g[0]?.devices === 10) ?? [];
    const premiumPlan = premium[0] ?? null;

    const periods = [30, 90, 180];

    return (
        <section className="section pricing" id="pricing" aria-labelledby="pricing-title">
            <div className="container">
                <Reveal as="header" className="section-head">
                    <span className="section-eyebrow">Тарифы</span>
                    <h2 id="pricing-title" className="section-title">Честные цены без&nbsp;мелкого шрифта</h2>
                    <p className="section-subtitle">Прозрачные условия и&nbsp;скидки за&nbsp;долгий период. Без скрытых платежей и&nbsp;автосписаний.</p>
                </Reveal>

                <Reveal className="pricing-table-wrap">
                    <table className="pricing-table" aria-describedby="pricing-title">
                        <caption className="visually-hidden">Стоимость подписки AVA VPN по&nbsp;тарифам и&nbsp;периодам</caption>
                        <thead>
                            <tr>
                                <th scope="col">Период</th>
                                <th scope="col">
                                    <div className="plan-name">Стандартный</div>
                                    <div className="plan-desc">2 устройства</div>
                                </th>
                                <th scope="col" className="plan-highlight">
                                    <span className="plan-badge">выгодно</span>
                                    <div className="plan-name">Расширенный</div>
                                    <div className="plan-desc">5 устройств</div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {periods.map((days) => {
                                const s = findPlan(standard, days);
                                const e = findPlan(extended, days);
                                return (
                                    <tr key={days}>
                                        <th scope="row">{s?.periodLabel ?? e?.periodLabel ?? `${days} дней`}</th>
                                        <td>
                                            {s ? (
                                                <>
                                                    <strong>{s.formattedPrice}</strong>
                                                    {s.discount > 0 && <span className="table-badge"> −{s.discount}%</span>}
                                                </>
                                            ) : '—'}
                                        </td>
                                        <td className="plan-highlight">
                                            {e ? (
                                                <>
                                                    <strong>{e.formattedPrice}</strong>
                                                    {e.discount > 0 && <span className="table-badge"> −{e.discount}%</span>}
                                                </>
                                            ) : '—'}
                                        </td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </Reveal>

                {premiumPlan && (
                    <Reveal className="cta-card" style={{ maxWidth: 640, margin: '28px auto 0' }} delay={0.08}>
                        <h3 style={{ marginTop: 0 }}>Премиум — для тех, кому мало пяти устройств</h3>
                        <p>
                            До&nbsp;{premiumPlan.devices}&nbsp;{pluralDevices(premiumPlan.devices)} одновременно,
                            {' '}{premiumPlan.periodLabel.toLowerCase()},
                            {premiumPlan.trafficGb > 0 && <> лимит трафика {premiumPlan.trafficGb}&nbsp;ГБ,</>} всё то же шифрование и поддержка.
                        </p>
                        <div className="cta-buttons">
                            <strong style={{ fontSize: '1.5rem' }}>{premiumPlan.formattedPrice}</strong>
                            {user ? (
                                <Link href={route('cabinet.history')} className="btn btn-primary btn-lg">Перейти к тарифам →</Link>
                            ) : (
                                <Link href={route('register')} className="btn btn-primary btn-lg">Оформить →</Link>
                            )}
                        </div>
                    </Reveal>
                )}

                <Reveal className="pricing-cta" delay={0.12}>
                    {user ? (
                        <Link href={route('cabinet.subscription')} className="btn btn-primary btn-lg">Перейти в&nbsp;кабинет →</Link>
                    ) : (
                        <Link href={route('register')} className="btn btn-primary btn-lg">Оформить подписку →</Link>
                    )}
                </Reveal>

                <Reveal as="ul" className="pricing-notes" delay={0.16}>
                    <li className="pricing-note"><span className="check">✓</span> Оплата ЮKassa: карта, СБП, СберPay</li>
                    <li className="pricing-note"><span className="check">✓</span> Без автосписаний</li>
                    <li className="pricing-note"><span className="check">✓</span> Возврат при технических проблемах</li>
                </Reveal>
            </div>
        </section>
    );
}
