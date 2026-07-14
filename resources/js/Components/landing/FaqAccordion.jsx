import { useState } from 'react';
import { AnimatePresence, motion } from 'framer-motion';
import { usePage } from '@inertiajs/react';
import Reveal from '@/Components/landing/Reveal';
import { FAQ_ITEMS } from '@/Components/landing/faqData';

function FaqItem({ item, isOpen, onToggle }) {
    return (
        <div className="faq-item faq-item--react">
            <button
                type="button"
                className="faq-summary-btn"
                onClick={onToggle}
                aria-expanded={isOpen}
                style={{
                    all: 'unset', boxSizing: 'border-box', display: 'flex', width: '100%',
                    alignItems: 'center', justifyContent: 'space-between', cursor: 'pointer',
                }}
            >
                <span className="faq-q">{item.q}</span>
                <span className={`faq-toggle${isOpen ? ' is-open' : ''}`} aria-hidden="true" />
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
                        <div className="faq-a">
                            <p>{item.a}</p>
                        </div>
                    </motion.div>
                )}
            </AnimatePresence>
        </div>
    );
}

export default function FaqAccordion() {
    const [openIndex, setOpenIndex] = useState(null);
    const { props } = usePage();
    const supportUrl = props.telegram?.supportUrl;

    return (
        <section className="section faq" id="faq" aria-labelledby="faq-title">
            <div className="container">
                <Reveal as="header" className="section-head">
                    <span className="section-eyebrow">FAQ</span>
                    <h2 id="faq-title" className="section-title">Частые вопросы о&nbsp;VPN-сервисе</h2>
                    <p className="section-subtitle">
                        Если не&nbsp;нашли ответа — напишите нам в{' '}
                        {supportUrl ? (
                            <a href={supportUrl} target="_blank" rel="noopener">Telegram-чат поддержки</a>
                        ) : 'Telegram-чат поддержки'}.
                    </p>
                </Reveal>

                <Reveal className="faq-list">
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
