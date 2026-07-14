import { motion } from 'framer-motion';

export default function GlassCard({ as: Tag = 'div', className = '', glow = false, children, ...rest }) {
    const MotionTag = motion[Tag] ?? motion.div;

    return (
        <MotionTag
            whileHover={{ y: -6 }}
            transition={{ type: 'spring', stiffness: 300, damping: 22 }}
            className={`group relative rounded-2xl border border-white/10 bg-white/[0.035] p-6 sm:p-8
                backdrop-blur-sm transition-colors duration-300 hover:border-white/20 hover:bg-white/[0.06]
                ${glow ? 'shadow-glow' : ''} ${className}`}
            {...rest}
        >
            {children}
        </MotionTag>
    );
}
