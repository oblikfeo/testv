import { useState } from 'react';
import { AnimatePresence, motion } from 'framer-motion';
import { usePage } from '@inertiajs/react';
import Reveal from '@/Components/landing/Reveal';
import SectionHeading from '@/Components/ui/SectionHeading';
import { FAQ_ITEMS } from '@/Components/landing/faqData';

function FaqItem({ item, isOpen, onToggle }) {
    return (
        <div className={`overflow-hidden rounded-2xl border transition-colors ${isOpen ? 'border-red-500/30 bg-white/[0.04]' : 'border-white/10 bg-white/[0.02]'}`}>
            <button
                type="button"
                onClick={onToggle}
                aria-expanded={isOpen}
                className="flex w-full items-center justify-between gap-4 px-6 py-5 text-left"
            >
                <span className="text-sm font-semibold text-white sm:text-base">{item.q}</span>
                <motion.span
                    animate={{ rotate: isOpen ? 45 : 0 }}
                    transition={{ duration: 0.25 }}
                    className={`flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-lg ${
                        isOpen ? 'bg-gradient-to-br from-red-600 to-fuchsia-600 text-white' : 'bg-white/10 text-white/60'
                    }`}
                >
                    +
                </motion.span>
            </button>
            <AnimatePresence initial={false}>
                {isOpen && (
                    <motion.div
                        initial={{ height: 0, opacity: 0 }}
                        animate={{ height: 'auto', opacity: 1 }}
                        exit={{ height: 0, opacity: 0 }}
                        transition={{ duration: 0.28, ease: [0.16, 1, 0.3, 1] }}
                        style={{ overflow: 'hidden' }}
                    >
                        <p className="px-6 pb-5 text-sm leading-relaxed text-white/55">{item.a}</p>
                    </motion.div>
                )}
            </AnimatePresence>
        </div>
    );
}

export default function FaqAccordion() {
    const [openIndex, setOpenIndex] = useState(0);
    const { props } = usePage();
    const supportUrl = props.telegram?.supportUrl;

    return (
        <section className="relative bg-ink-950 py-24 sm:py-28" id="faq" aria-labelledby="faq-title">
            <div className="mx-auto max-w-3xl px-5 sm:px-8">
                <SectionHeading
                    eyebrow="FAQ"
                    id="faq-title"
                    title="Частые вопросы о VPN-сервисе"
                    subtitle={
                        <>
                            Если не нашли ответа — напишите нам в{' '}
                            {supportUrl ? (
                                <a href={supportUrl} target="_blank" rel="noopener" className="text-red-400 underline-offset-4 hover:underline">Telegram-чат поддержки</a>
                            ) : 'Telegram-чат поддержки'}.
                        </>
                    }
                />

                <Reveal className="space-y-3">
                    {FAQ_ITEMS.map((item, i) => (
                        <FaqItem
                            key={item.q}
                            item={item}
                            isOpen={openIndex === i}
                            onToggle={() => setOpenIndex(openIndex === i ? null : i)}
                        />
                    ))}
                </Reveal>
            </div>
        </section>
    );
}
