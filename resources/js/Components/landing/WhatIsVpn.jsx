import Reveal from '@/Components/landing/Reveal';

export default function WhatIsVpn() {
    return (
        <section className="section" id="what-is-vpn" aria-labelledby="what-is-vpn-title">
            <div className="container">
                <Reveal as="header" className="section-head">
                    <span className="section-eyebrow">Ликбез</span>
                    <h2 id="what-is-vpn-title" className="section-title">Что такое VPN и зачем он нужен в&nbsp;России</h2>
                    <p className="section-subtitle">
                        Если вы впервые разбираетесь в теме — вот короткое и честное объяснение, без маркетингового тумана.
                    </p>
                </Reveal>

                <div className="compare-grid">
                    <Reveal as="article" className="compare-card">
                        <h3>Как это работает</h3>
                        <ul>
                            <li>VPN (Virtual Private Network) шифрует весь трафик между вашим устройством и сервером — провайдер видит только зашифрованный поток, а не то, какие сайты вы открываете.</li>
                            <li>Ваш реальный IP-адрес скрывается за адресом сервера, поэтому сайты и сервисы видят подключение из другой страны.</li>
                            <li>Современные протоколы (в том числе VLESS, который использует AVA VPN) дополнительно маскируют трафик под обычный HTTPS-трафик, что усложняет его блокировку по протоколу.</li>
                        </ul>
                    </Reveal>
                    <Reveal as="article" className="compare-card" delay={0.08}>
                        <h3>Когда он реально нужен</h3>
                        <ul>
                            <li>Сервисы, ограниченные или нестабильно работающие в России, — от мессенджеров до стриминга и рабочих инструментов.</li>
                            <li>Публичный Wi-Fi в кафе, аэропортах и отелях — без шифрования там легко перехватить незащищённый трафик.</li>
                            <li>Ситуации, когда провайдер замедляет отдельные сервисы или протоколы — маскировка трафика снижает вероятность такого throttling.</li>
                        </ul>
                    </Reveal>
                </div>
            </div>
        </section>
    );
}
