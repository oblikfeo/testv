import { motion } from 'framer-motion';

export default function Reveal({ as: Tag = 'div', delay = 0, className, children, ...rest }) {
    const MotionTag = motion[Tag] ?? motion.div;

    return (
        <MotionTag
            className={className}
            initial={{ opacity: 0, y: 24 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true, margin: '0px 0px -8% 0px', amount: 0.08 }}
            transition={{ duration: 0.5, delay, ease: [0.16, 1, 0.3, 1] }}
            {...rest}
        >
            {children}
        </MotionTag>
    );
}
