export default function AnimatedBackground({ variant = 'hero' }) {
    if (variant === 'hero') {
        return (
            <div aria-hidden="true" className="pointer-events-none absolute inset-0 overflow-hidden">
                <div className="absolute inset-0 bg-grid-pattern bg-[size:56px_56px] [mask-image:radial-gradient(ellipse_60%_60%_at_50%_0%,black,transparent)]" />
                <div className="absolute -top-32 left-[10%] h-[34rem] w-[34rem] rounded-full bg-red-600/40 blur-[90px] animate-float" />
                <div className="absolute top-0 right-[8%] h-[28rem] w-[28rem] rounded-full bg-fuchsia-600/30 blur-[90px] animate-float-slow" />
                <div className="absolute top-1/3 left-1/2 h-[24rem] w-[24rem] -translate-x-1/2 rounded-full bg-orange-500/25 blur-[90px] animate-float-fast" />
            </div>
        );
    }

    return (
        <div aria-hidden="true" className="pointer-events-none absolute inset-0 overflow-hidden">
            <div className="absolute top-0 left-1/2 h-[24rem] w-[24rem] -translate-x-1/2 rounded-full bg-red-600/20 blur-[90px] animate-float-slow" />
        </div>
    );
}
