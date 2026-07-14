import { Link } from '@inertiajs/react';
import { motion } from 'framer-motion';

const SIZES = {
    lg: 'px-8 py-4 text-base sm:text-lg',
    md: 'px-6 py-3 text-sm sm:text-base',
    sm: 'px-4 py-2 text-sm',
};

const VARIANTS = {
    primary: 'text-white shadow-glow bg-gradient-to-r from-red-600 via-rose-500 to-fuchsia-600',
    secondary: 'text-white/90 bg-white/[0.06] border border-white/15 backdrop-blur hover:bg-white/[0.1] hover:border-white/25',
    ghost: 'text-white/70 hover:text-white',
};

export default function GlowButton({
    as = 'link',
    href,
    onClick,
    variant = 'primary',
    size = 'lg',
    className = '',
    children,
    type = 'button',
    ...rest
}) {
    const base = `group relative inline-flex items-center justify-center gap-2 rounded-full font-semibold
        transition-all duration-300 ease-out overflow-hidden whitespace-nowrap
        ${SIZES[size]} ${VARIANTS[variant]} ${className}`;

    const shine = variant === 'primary' && (
        <span
            aria-hidden="true"
            className="pointer-events-none absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/40 to-transparent
                group-hover:translate-x-full transition-transform duration-700 ease-out skew-x-12"
        />
    );

    const content = (
        <motion.span
            whileHover={{ scale: 1.035, y: -1 }}
            whileTap={{ scale: 0.97 }}
            transition={{ type: 'spring', stiffness: 400, damping: 20 }}
            className={base}
        >
            {shine}
            <span className="relative z-10 flex items-center gap-2">{children}</span>
        </motion.span>
    );

    if (as === 'a') {
        return (
            <a href={href} onClick={onClick} {...rest}>
                {content}
            </a>
        );
    }

    if (as === 'button') {
        return (
            <button type={type} onClick={onClick} {...rest}>
                {content}
            </button>
        );
    }

    return (
        <Link href={href} {...rest}>
            {content}
        </Link>
    );
}
