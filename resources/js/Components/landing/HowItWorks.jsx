import { motion } from 'framer-motion';
import Reveal from '@/Components/landing/Reveal';
import SectionHeading from '@/Components/ui/SectionHeading';

const STEPS = [
    { num: '01', title: 'Создайте аккаунт', text: 'Только email и пароль — больше ничего не нужно. Никаких паспортов и номеров телефона.' },
    { num: '02', title: 'Получите конфигурацию', text: 'В личном кабинете появится готовый ключ. Скопируйте ссылку или скачайте файл — всё в один клик.' },
    { num: '03', title: 'Подключите устройство', text: 'Импортируйте конфигурацию в Happ, V2RayTun или любое совместимое — и интернет работает без ограничений.' },
];

const LINE_DURATION = 2.4;

export default function HowItWorks() {
    return (
        <section className="relative bg-ink-950 py-20 sm:py-24" id="how" aria-labelledby="how-title">
            <div className="mx-auto max-w-5xl px-5 sm:px-8">
                <SectionHeading
                    eyebrow="Подключение"
                    id="how-title"
                    title="Три шага — и вы в деле"
                    subtitle="Не нужно разбираться в настройках. Всё работает «из коробки»."
                />

                <div className="relative grid gap-10 sm:grid-cols-3">
                    <div className="absolute top-7 hidden h-px sm:block sm:left-[16.6667%] sm:right-[16.6667%]">
                        <div className="h-full w-full bg-white/10" />
                        <motion.div
                            initial={{ scaleX: 0 }}
                            whileInView={{ scaleX: 1 }}
                            viewport={{ once: true, amount: 0.5 }}
                            transition={{ duration: LINE_DURATION, ease: 'easeInOut' }}
                            className="absolute inset-0 h-full origin-left rounded-full bg-gradient-to-r from-red-500 via-rose-400 to-fuchsia-500 shadow-glow"
                        />
                    </div>

                    {STEPS.map((s, i) => (
                        <Reveal key={s.num} delay={0} className="relative flex flex-col items-center text-center">
                            <div className="relative mb-5 flex h-14 w-14 items-center justify-center">
                                <motion.span
                                    initial={{ opacity: 0.25, scale: 0.8 }}
                                    whileInView={{ opacity: [0.25, 1, 0.6], scale: [0.8, 1.25, 1] }}
                                    viewport={{ once: true, amount: 0.5 }}
                                    transition={{ duration: 0.6, delay: (LINE_DURATION * i) / (STEPS.length - 1), ease: 'easeOut' }}
                                    className="absolute inset-0 rounded-full bg-red-500/40 blur-md"
                                />
                                <span className="relative z-10 flex h-14 w-14 items-center justify-center rounded-full border border-white/15 bg-ink-900 text-lg font-extrabold text-white shadow-glow">
                                    {s.num}
                                </span>
                            </div>
                            <h3 className="mb-2 text-lg font-bold text-white">{s.title}</h3>
                            <p className="max-w-[16rem] text-sm leading-relaxed text-white/55">{s.text}</p>
                        </Reveal>
                    ))}
                </div>
            </div>
        </section>
    );
}
