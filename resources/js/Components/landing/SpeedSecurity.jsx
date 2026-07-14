import Reveal from '@/Components/landing/Reveal';
import SectionHeading from '@/Components/ui/SectionHeading';

const SPECS = [
    'Протокол VLESS с маскировкой Reality',
    'Шифрование TLS 1.3',
    'Серверы на каналах 1 Гбит/с в ЕС',
    'Без ограничений по скорости и трафику*',
];

export default function SpeedSecurity() {
    return (
        <section className="relative overflow-hidden bg-ink-900 py-20 sm:py-24" id="speed-security" aria-labelledby="speed-security-title">
            <div className="mx-auto max-w-5xl px-5 sm:px-8">
                <SectionHeading
                    eyebrow="Под капотом"
                    id="speed-security-title"
                    title="Какой протокол и почему это быстро"
                    subtitle="Коротко о том, что делает соединение одновременно быстрым и устойчивым к блокировкам — без лишнего технического жаргона."
                />

                <div className="grid gap-8 md:grid-cols-2 md:items-center">
                    <Reveal className="space-y-4 text-sm leading-relaxed text-white/60 sm:text-base">
                        <p>
                            AVA VPN работает на протоколе <strong className="text-white">VLESS</strong> с маскировкой трафика Reality — она
                            делает зашифрованное соединение неотличимым от обычного HTTPS-трафика к популярным сайтам. Это усложняет
                            блокировку по протоколу и не требует «утяжеляющих» обёрток трафика, которые обычно и замедляют VPN.
                        </p>
                        <p>
                            Серверы расположены в Евросоюзе на каналах 1&nbsp;Гбит/с, поэтому задержка для пользователей из России
                            остаётся низкой, а видео, звонки и игры не «спотыкаются».
                        </p>
                    </Reveal>

                    <Reveal delay={0.1} className="rounded-2xl border border-white/10 bg-white/[0.03] p-6 backdrop-blur-sm">
                        <ul className="space-y-3.5">
                            {SPECS.map((spec) => (
                                <li key={spec} className="flex items-center gap-3 text-sm font-medium text-white/80">
                                    <span className="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-red-600 to-fuchsia-600 text-xs text-white">✓</span>
                                    {spec}
                                </li>
                            ))}
                        </ul>
                        <p className="mt-5 text-xs text-white/35">
                            * Кроме тарифа «Премиум», где действует лимит трафика — см. раздел тарифов.
                        </p>
                    </Reveal>
                </div>
            </div>
        </section>
    );
}
