import { Link, usePage } from '@inertiajs/react';
import CabinetLayout from '@/Layouts/CabinetLayout';
import GlassCard from '@/Components/ui/GlassCard';
import Alert from '@/Components/cabinet/Alert';
import SubscriptionLinkBox from '@/Components/cabinet/SubscriptionLinkBox';
import PlatformInstructions from '@/Components/cabinet/PlatformInstructions';
import PaymentStatusBanner from '@/Components/cabinet/PaymentStatusBanner';

function Badge({ children, tone }) {
    const tones = {
        green: 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30',
        red: 'bg-red-500/15 text-red-300 border-red-500/30',
        gray: 'bg-white/10 text-white/50 border-white/15',
    };
    return <span className={`inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold ${tones[tone]}`}>{children}</span>;
}

export default function Subscription({ subscriptions, activeTrial, connectionUri }) {
    const { props } = usePage();
    const orderId = new URL(props.ziggy.location).searchParams.get('order_id');
    const errorList = Object.values(props.errors ?? {});

    return (
        <CabinetLayout title="Подписка">
            <header className="mb-6">
                <h1 className="text-2xl font-bold text-white sm:text-3xl">Подписка</h1>
                <p className="mt-1 text-white/50">Тариф, срок действия и ссылка для подключения в одном месте.</p>
            </header>

            <PaymentStatusBanner orderId={orderId} />
            <Alert variant="success">{props.flash?.success}</Alert>
            {errorList.length > 0 && (
                <Alert variant="error">
                    {errorList.map((err) => (
                        <p key={err}>{err}</p>
                    ))}
                </Alert>
            )}

            <div className="flex flex-col gap-5">
                {subscriptions.length > 0 ? (
                    subscriptions.map((sub, i) => (
                        <GlassCard key={sub.id}>
                            <div className="mb-4 flex items-center gap-3">
                                {sub.isActive ? <Badge tone="green">Активна</Badge> : sub.isExpired ? <Badge tone="red">Истекла</Badge> : <Badge tone="gray">Не активна</Badge>}
                                <h2 className="text-xl font-bold text-white">{sub.planName}</h2>
                            </div>
                            <div className="flex flex-wrap gap-8">
                                <div>
                                    <div className="text-xs uppercase tracking-wider text-white/35">Действует до</div>
                                    <div className={`mt-1 font-semibold ${sub.daysLeft <= 7 && sub.isActive ? 'text-amber-300' : 'text-white'}`}>{sub.expiresAt}</div>
                                </div>
                                {sub.isActive && (
                                    <div>
                                        <div className="text-xs uppercase tracking-wider text-white/35">Осталось</div>
                                        <div className="mt-1 font-semibold text-white">{sub.daysLeft} дн.</div>
                                    </div>
                                )}
                            </div>
                            {i === 0 && <SubscriptionLinkBox connectionUri={connectionUri} />}
                            <Link href={route('cabinet.history')} className="mt-5 inline-flex rounded-full bg-gradient-to-r from-red-600 to-fuchsia-600 px-5 py-2.5 text-sm font-semibold text-white shadow-glow transition hover:brightness-110">
                                Продлить подписку
                            </Link>
                        </GlassCard>
                    ))
                ) : activeTrial ? (
                    <GlassCard>
                        <div className="mb-4 flex items-center gap-3">
                            <Badge tone="green">Активна</Badge>
                            <h2 className="text-xl font-bold text-white">Пробный доступ</h2>
                        </div>
                        <div className="flex flex-wrap gap-8">
                            <div>
                                <div className="text-xs uppercase tracking-wider text-white/35">Действует до</div>
                                <div className="mt-1 font-semibold text-white">{activeTrial.expiresAt}</div>
                            </div>
                            <div>
                                <div className="text-xs uppercase tracking-wider text-white/35">Осталось</div>
                                <div className="mt-1 font-semibold text-white">{activeTrial.remainingTime}</div>
                            </div>
                        </div>
                        <SubscriptionLinkBox connectionUri={connectionUri} />
                        <Link href={route('cabinet.history')} className="mt-5 inline-flex rounded-full bg-gradient-to-r from-red-600 to-fuchsia-600 px-5 py-2.5 text-sm font-semibold text-white shadow-glow transition hover:brightness-110">
                            Оформить подписку
                        </Link>
                    </GlassCard>
                ) : (
                    <GlassCard>
                        <div className="mb-3 flex items-center gap-3">
                            <Badge tone="gray">Не активна</Badge>
                            <h2 className="text-xl font-bold text-white">Нет активного тарифа</h2>
                        </div>
                        <p className="mb-5 text-white/55">Оформите подписку или активируйте бесплатный пробный доступ на 3 часа.</p>
                        <div className="flex flex-wrap gap-3">
                            <Link href={route('cabinet.trial')} className="rounded-full border border-white/15 bg-white/[0.06] px-5 py-2.5 text-sm font-semibold text-white/90 transition hover:bg-white/[0.1]">
                                Пробный доступ
                            </Link>
                            <Link href={route('cabinet.history')} className="rounded-full bg-gradient-to-r from-red-600 to-fuchsia-600 px-5 py-2.5 text-sm font-semibold text-white shadow-glow transition hover:brightness-110">
                                Оформить подписку
                            </Link>
                        </div>
                    </GlassCard>
                )}

                <PlatformInstructions
                    connectionUri={connectionUri}
                    title="Как подключиться"
                    description={
                        connectionUri
                            ? 'Выберите платформу и следуйте шагам — ссылку можно скопировать выше или добавить в приложение одной кнопкой.'
                            : 'После оформления подписки здесь появится ссылка и пошаговая инструкция.'
                    }
                />
            </div>
        </CabinetLayout>
    );
}
