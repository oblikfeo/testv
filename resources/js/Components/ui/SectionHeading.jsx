import Reveal from '@/Components/landing/Reveal';

export default function SectionHeading({ eyebrow, title, subtitle, id, align = 'center' }) {
    const alignClass = align === 'left' ? 'items-start text-left' : 'items-center text-center';

    return (
        <Reveal as="header" className={`mx-auto mb-14 flex max-w-2xl flex-col gap-4 ${alignClass}`}>
            {eyebrow && (
                <span className="inline-flex w-fit items-center gap-2 rounded-full border border-red-500/30 bg-red-500/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-red-300">
                    {eyebrow}
                </span>
            )}
            <h2 id={id} className="bg-gradient-to-b from-white to-white/70 bg-clip-text text-3xl font-extrabold leading-tight text-transparent sm:text-4xl lg:text-5xl">
                {title}
            </h2>
            {subtitle && <p className="text-base leading-relaxed text-white/55 sm:text-lg">{subtitle}</p>}
        </Reveal>
    );
}
