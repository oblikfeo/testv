import Reveal from '@/Components/landing/Reveal';

export default function SpeedSecurity() {
    return (
        <section className="section" id="speed-security" aria-labelledby="speed-security-title">
            <div className="container">
                <Reveal as="header" className="section-head">
                    <span className="section-eyebrow">Под капотом</span>
                    <h2 id="speed-security-title" className="section-title">Какой протокол и почему это быстро</h2>
                    <p className="section-subtitle">
                        Коротко о том, что делает соединение одновременно быстрым и устойчивым к блокировкам —
                        без лишнего технического жаргона.
                    </p>
                </Reveal>

                <Reveal className="agreement-content" style={{ maxWidth: 760, margin: '0 auto', textAlign: 'left' }}>
                    <p>
                        AVA VPN работает на протоколе <strong>VLESS</strong> с маскировкой трафика Reality — она делает
                        зашифрованное соединение неотличимым от обычного HTTPS-трафика к популярным сайтам. Это усложняет
                        блокировку по протоколу и одновременно не требует «утяжеляющих» обёрток трафика, которые
                        обычно и замедляют VPN.
                    </p>
                    <p>
                        Серверы расположены в Евросоюзе на каналах 1&nbsp;Гбит/с, поэтому задержка (пинг) для пользователей
                        из России остаётся низкой, а видео, звонки и игры не «спотыкаются».
                    </p>
                </Reveal>

                <Reveal className="pricing-notes" style={{ maxWidth: 760, margin: '32px auto 0' }}>
                    <li className="pricing-note"><span className="check">✓</span> Протокол VLESS с маскировкой Reality</li>
                    <li className="pricing-note"><span className="check">✓</span> Шифрование TLS 1.3</li>
                    <li className="pricing-note"><span className="check">✓</span> Серверы на каналах 1 Гбит/с в ЕС</li>
                    <li className="pricing-note"><span className="check">✓</span> Без ограничений по скорости и трафику*</li>
                </Reveal>

                <p className="footnote" style={{ maxWidth: 760, margin: '12px auto 0', textAlign: 'left' }}>
                    * Кроме тарифа «Премиум», где действует лимит трафика — см. раздел тарифов.
                </p>
            </div>
        </section>
    );
}
