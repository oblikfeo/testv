import { Link, useForm, usePage } from '@inertiajs/react';
import CabinetLayout from '@/Layouts/CabinetLayout';
import GlassCard from '@/Components/ui/GlassCard';
import Alert from '@/Components/cabinet/Alert';
import SubscriptionLinkBox from '@/Components/cabinet/SubscriptionLinkBox';
import PlatformInstructions from '@/Components/cabinet/PlatformInstructions';

function ProgressBar({ label, percent, warn }) {
    return (
        <div>
            <div className="mb-1.5 flex justify-between text-xs text-white/45">
                <span>{label}</span>
                <span>{percent}%</span>
            </div>
            <div className="h-2 overflow-hidden rounded-full bg-white/10">
                <div
                    className={`h-full rounded-full transition-all ${warn ? 'bg-amber-400' : 'bg-gradient-to-r from-red-500 to-fuchsia-500'}`}
                    style={{ width: `${percent}%` }}
                />
            </div>
        </div>
    );
}

export default function Trial({ trialHours, canUseTrial, connectionUri, trial }) {
    const { props } = usePage();
    const user = props.auth?.user;
    const errorList = Object.values(props.errors ?? {});
    const createForm = useForm({});
    const resendForm = useForm({});

    function activate(e) {
        e.preventDefault();
        createForm.post(route('cabinet.trial.create'));
    }

    function resend(e) {
        e.preventDefault();
        resendForm.post(route('verification.send'));
    }

    return (
        <CabinetLayout title="Тест-драйв">
            <header className="mb-6">
                <h1 className="text-2xl font-bold text-white sm:text-3xl">Тест-драйв</h1>
                <p className="mt-1 text-white/50">
                    Бесплатный доступ на {trialHours} ч. — те же серверы и подписочная ссылка, что у платного тарифа. Один раз на аккаунт.
                </p>
            </header>

            <Alert variant="success">{props.flash?.success}</Alert>
            {errorList.length > 0 && (
                <Alert variant="error">
                    {errorList.map((err) => (
                        <p key={err}>{err}</p>
                    ))}
                </Alert>
            )}

            <div className="flex flex-col gap-5">
                {!user?.email_verified_at ? (
                    <GlassCard>
                        <h2 className="mb-2 text-xl font-bold text-white">Подтвердите email</h2>
                        <p className="mb-5 text-white/55">Чтобы активировать пробный доступ, перейдите по ссылке из письма после регистрации.</p>
                        <button
                            type="button" onClick={resend} disabled={resendForm.processing}
                            className="rounded-full bg-gradient-to-r from-red-600 to-fuchsia-600 px-5 py-2.5 text-sm font-semibold text-white shadow-glow transition hover:brightness-110 disabled:opacity-60"
                        >
                            Отправить письмо повторно
                        </button>
                    </GlassCard>
                ) : trial?.isActive ? (
                    <GlassCard>
                        <div className="mb-4 flex items-center gap-3">
                            <span className="rounded-full border border-emerald-500/30 bg-emerald-500/15 px-3 py-1 text-xs font-semibold text-emerald-300">Активна</span>
                            <h2 className="text-xl font-bold text-white">Пробная подписка</h2>
                        </div>

                        <div className="mb-5 flex flex-wrap gap-8">
                            <div>
                                <div className="text-xs uppercase tracking-wider text-white/35">Действует до</div>
                                <div className="mt-1 font-semibold text-white">{trial.expiresAt}</div>
                            </div>
                            <div>
                                <div className="text-xs uppercase tracking-wider text-white/35">Осталось</div>
                                <div className={`mt-1 font-semibold ${trial.timeProgress <= 20 ? 'text-amber-300' : 'text-white'}`}>{trial.remainingTime}</div>
                            </div>
                            {trial.showTraffic && (
                                <div>
                                    <div className="text-xs uppercase tracking-wider text-white/35">Трафик</div>
                                    <div className="mt-1 font-semibold text-white">{trial.remainingGb} / {trial.totalGb} ГБ</div>
                                </div>
                            )}
                        </div>

                        <div className="flex flex-col gap-4">
                            <ProgressBar label="Время доступа" percent={trial.timeProgress} warn={trial.timeProgress <= 20} />
                            {trial.showTraffic && <ProgressBar label="Остаток трафика" percent={trial.trafficProgress} warn={trial.trafficProgress <= 20} />}
                        </div>

                        <SubscriptionLinkBox connectionUri={connectionUri} />

                        <div className="mt-5 flex flex-wrap gap-3">
                            <Link href={route('cabinet.subscription')} className="rounded-full border border-white/15 bg-white/[0.06] px-5 py-2.5 text-sm font-semibold text-white/90 transition hover:bg-white/[0.1]">
                                Раздел «Подписка»
                            </Link>
                            <Link href={route('cabinet.history')} className="rounded-full bg-gradient-to-r from-red-600 to-fuchsia-600 px-5 py-2.5 text-sm font-semibold text-white shadow-glow transition hover:brightness-110">
                                Оформить подписку
                            </Link>
                        </div>
                    </GlassCard>
                ) : trial ? (
                    <GlassCard>
                        <div className="mb-3 flex items-center gap-3">
                            <span className="rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold text-white/50">Истекла</span>
                            <h2 className="text-xl font-bold text-white">Пробный период завершён</h2>
                        </div>
                        <p className="mb-5 text-white/55">
                            Доступ закончился {trial.expiresAt}. Оформите платный тариф — подключение по той же ссылке, что и в пробном режиме.
                        </p>
                        <Link href={route('cabinet.history')} className="inline-flex rounded-full bg-gradient-to-r from-red-600 to-fuchsia-600 px-5 py-2.5 text-sm font-semibold text-white shadow-glow transition hover:brightness-110">
                            Выбрать тариф
                        </Link>
                    </GlassCard>
                ) : canUseTrial ? (
                    <GlassCard>
                        <div className="mb-4 flex items-center gap-3">
                            <span className="rounded-full border border-emerald-500/30 bg-emerald-500/15 px-3 py-1 text-xs font-semibold text-emerald-300">Доступно</span>
                            <h2 className="text-xl font-bold text-white">Попробуйте бесплатно</h2>
                        </div>
                        <ul className="mb-6 flex flex-col gap-2.5 text-sm text-white/70">
                            <li className="flex items-start gap-2.5"><span className="text-red-400">✓</span><span>{trialHours} ч. полного доступа</span></li>
                            <li className="flex items-start gap-2.5"><span className="text-red-400">✓</span><span>Два сервера, как у платной подписки: Wi-Fi и «Обход блокировок»</span></li>
                            <li className="flex items-start gap-2.5"><span className="text-red-400">✓</span><span>Одна подписочная ссылка для Happ и v2RayTun</span></li>
                        </ul>
                        <form onSubmit={activate}>
                            <button
                                type="submit" disabled={createForm.processing}
                                className="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-red-600 to-fuchsia-600 px-6 py-3 text-sm font-semibold text-white shadow-glow transition hover:brightness-110 disabled:opacity-60"
                            >
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" /></svg>
                                Активировать пробную подписку
                            </button>
                        </form>
                    </GlassCard>
                ) : (
                    <GlassCard>
                        <div className="mb-3 flex items-center gap-3">
                            <span className="rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold text-white/50">Использовано</span>
                            <h2 className="text-xl font-bold text-white">Тест-драйв уже был</h2>
                        </div>
                        <p className="mb-5 text-white/55">На этом аккаунте пробный период уже активировали. Выберите платный тариф — подключение займёт пару минут.</p>
                        <Link href={route('cabinet.history')} className="inline-flex rounded-full bg-gradient-to-r from-red-600 to-fuchsia-600 px-5 py-2.5 text-sm font-semibold text-white shadow-glow transition hover:brightness-110">
                            Перейти к покупкам
                        </Link>
                    </GlassCard>
                )}

                {user?.email_verified_at && (
                    <PlatformInstructions
                        connectionUri={connectionUri}
                        title="Как подключиться"
                        description={
                            trial?.isActive
                                ? 'Выберите платформу — ссылка подставится в кнопки «Добавить в Happ / v2RayTun».'
                                : 'После активации пробного доступа здесь появится ссылка и пошаговая инструкция.'
                        }
                    />
                )}
            </div>
        </CabinetLayout>
    );
}
