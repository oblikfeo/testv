export default function AnimatedBackground({ variant = 'hero' }) {
    if (variant === 'hero') {
        return (
            <div aria-hidden="true" className="pointer-events-none absolute inset-0 overflow-hidden">
                <div className="absolute inset-0 bg-grid-pattern bg-[size:56px_56px] [mask-image:radial-gradient(ellipse_60%_60%_at_50%_0%,black,transparent)]" />
                <div className="absolute -top-40 left-1/4 h-[32rem] w-[32rem] -translate-x-1/2 rounded-full bg-red-600/30 blur-[110px] animate-float" />
                <div className="absolute top-10 right-0 h-[26rem] w-[26rem] rounded-full bg-fuchsia-600/20 blur-[110px] animate-float-slow" />
                <div className="absolute top-1/2 left-1/2 h-[20rem] w-[20rem] -translate-x-1/2 rounded-full bg-orange-500/10 blur-[100px] animate-float" style={{ animationDelay: '-6s' }} />
            </div>
        );
    }

    return (
        <div aria-hidden="true" className="pointer-events-none absolute inset-0 overflow-hidden">
            <div className="absolute top-0 left-1/2 h-[24rem] w-[24rem] -translate-x-1/2 rounded-full bg-red-600/15 blur-[100px] animate-float-slow" />
        </div>
    );
}
