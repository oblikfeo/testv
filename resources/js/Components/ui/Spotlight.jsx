import { useEffect, useRef } from 'react';
import { motion, useMotionTemplate, useMotionValue } from 'framer-motion';

export default function Spotlight({ className = '', size = 500 }) {
    const ref = useRef(null);
    const x = useMotionValue(0);
    const y = useMotionValue(0);
    const background = useMotionTemplate`radial-gradient(${size}px circle at ${x}px ${y}px, rgba(220,38,38,0.16), transparent 70%)`;

    useEffect(() => {
        const parent = ref.current?.parentElement;
        if (!parent) return undefined;

        function handleMove(e) {
            const rect = parent.getBoundingClientRect();
            x.set(e.clientX - rect.left);
            y.set(e.clientY - rect.top);
        }

        parent.addEventListener('pointermove', handleMove);
        return () => parent.removeEventListener('pointermove', handleMove);
    }, [x, y]);

    return (
        <motion.div
            ref={ref}
            aria-hidden="true"
            className={`pointer-events-none absolute inset-0 ${className}`}
            style={{ background }}
        />
    );
}
