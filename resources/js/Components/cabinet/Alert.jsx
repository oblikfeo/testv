const VARIANTS = {
    success: 'border-emerald-500/25 bg-emerald-500/10 text-emerald-300',
    error: 'border-red-500/25 bg-red-500/10 text-red-300',
    info: 'border-sky-500/25 bg-sky-500/10 text-sky-200',
};

export default function Alert({ variant = 'info', children }) {
    if (!children) return null;

    return (
        <div className={`mb-5 rounded-xl border px-4 py-3 text-sm leading-relaxed ${VARIANTS[variant]}`}>
            {children}
        </div>
    );
}
