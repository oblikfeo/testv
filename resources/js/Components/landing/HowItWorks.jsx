import { motion } from 'framer-motion';
import Reveal from '@/Components/landing/Reveal';
import SectionHeading from '@/Components/ui/SectionHeading';

const STEPS = [
    { num: '01', title: 'Создайте аккаунт', text: 'Только email и пароль — больше ничего не нужно. Никаких паспортов и номеров телефона.' },
    { num: '02', title: 'Получите конфигурацию', text: 'В личном кабинете появится готовый ключ. Скопируйте ссылку или скачайте файл — всё в один клик.' },
    { num: '03', title: 'Подключите устройство', text: 'Импортируйте конфигурацию в Happ, V2RayTun или любое совместимое — и интернет работает без ограничений.' },
];

export default function HowItWorks() {
    return (
        <section className="relative bg-ink-950 py-24 sm:py-28" id="how" aria-labelledby="how-title">
            <div className="mx-auto max-w-5xl px-5 sm:px-8">
                <SectionHeading
                    eyebrow="Подключение"
                    id="how-title"
                    title="Три шага — и вы в деле"
                    subtitle="Не нужно разбираться в настройках. Всё работает «из коробки»."
                />

                <div className="relative grid gap-8 sm:grid-cols-3">
                    <div className="absolute left-0 right-0 top-7 hidden h-px bg-gradient-to-r from-transparent via-white/15 to-transparent sm:block">
                        <motion.div
                            initial={{ scaleX: 0 }}
                            whileInView={{ scaleX: 1 }}
                            viewport={{ once: true, amount: 0.5 }}
                            transition={{ duration: 1.1, ease: [0.16, 1, 0.3, 1] }}
                            className="h-full origin-left bg-gradient-to-r from-red-500 to-fuchsia-500"
                        />
                    </div>

                    {STEPS.map((s, i) => (
                        <Reveal key={s.num} delay={i * 0.12} className="relative flex flex-col items-center text-center sm:items-start sm:text-left">
                            <div className="relative z-10 mb-5 flex h-14 w-14 items-center justify-center rounded-full border border-white/15 bg-ink-900 text-lg font-extrabold text-white shadow-glow">
                                {s.num}
                            </div>
                            <h3 className="mb-2 text-lg font-bold text-white">{s.title}</h3>
                            <p className="text-sm leading-relaxed text-white/55">{s.text}</p>
                        </Reveal>
                    ))}
                </div>
            </div>
        </section>
    );
}
