import { useForm, usePage } from '@inertiajs/react';
import CabinetLayout from '@/Layouts/CabinetLayout';
import GlassCard from '@/Components/ui/GlassCard';
import Alert from '@/Components/cabinet/Alert';

const inputClass =
    'w-full rounded-xl border border-white/10 bg-black/30 px-4 py-2.5 text-white placeholder-white/30 outline-none transition focus:border-red-500/50 focus:ring-2 focus:ring-red-500/20';

export default function Profile({ hasActiveAccess, createdAt }) {
    const { props } = usePage();
    const user = props.auth?.user;
    const status = props.flash?.status;

    const { data, setData, patch, processing, errors } = useForm({
        name: user?.name ?? '',
        email: user?.email ?? '',
    });

    const resendForm = useForm({});

    function submit(e) {
        e.preventDefault();
        patch(route('profile.update'));
    }

    function resend() {
        resendForm.post(route('verification.send'));
    }

    const initial = (user?.name || user?.email || '?').slice(0, 1).toUpperCase();

    return (
        <CabinetLayout title="Профиль">
            <header className="mb-6">
                <h1 className="text-2xl font-bold text-white sm:text-3xl">Профиль</h1>
                <p className="mt-1 text-white/50">Основная информация об аккаунте и настройки.</p>
            </header>

            <div className="flex flex-col gap-5">
                <GlassCard>
                    <div className="mb-6 flex items-center gap-4">
                        <div className="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-red-600 to-fuchsia-600 text-xl font-bold text-white">
                            {initial}
                        </div>
                        <div>
                            <h2 className="text-lg font-bold text-white">{user?.name || 'Пользователь'}</h2>
                            <p className="text-sm text-white/45">{user?.email}</p>
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-5 sm:grid-cols-4">
                        <div>
                            <div className="text-xs uppercase tracking-wider text-white/35">ID аккаунта</div>
                            <div className="mt-1 font-semibold text-white">#{user?.id}</div>
                        </div>
                        <div>
                            <div className="text-xs uppercase tracking-wider text-white/35">Регистрация</div>
                            <div className="mt-1 font-semibold text-white">{createdAt}</div>
                        </div>
                        <div>
                            <div className="text-xs uppercase tracking-wider text-white/35">Email</div>
                            <div className="mt-1">
                                {user?.email_verified_at ? (
                                    <span className="rounded-full bg-emerald-500/15 px-2.5 py-0.5 text-xs font-semibold text-emerald-300">Подтверждён</span>
                                ) : (
                                    <span className="rounded-full bg-amber-500/15 px-2.5 py-0.5 text-xs font-semibold text-amber-300">Не подтверждён</span>
                                )}
                            </div>
                        </div>
                        <div>
                            <div className="text-xs uppercase tracking-wider text-white/35">Доступ VPN</div>
                            <div className="mt-1">
                                {hasActiveAccess ? (
                                    <span className="rounded-full bg-emerald-500/15 px-2.5 py-0.5 text-xs font-semibold text-emerald-300">Активен</span>
                                ) : (
                                    <span className="rounded-full bg-white/10 px-2.5 py-0.5 text-xs font-semibold text-white/45">Нет подписки</span>
                                )}
                            </div>
                        </div>
                        {(user?.telegram_username || user?.telegram_id) && (
                            <div className="col-span-2 sm:col-span-4">
                                <div className="text-xs uppercase tracking-wider text-white/35">Telegram</div>
                                <div className="mt-1 font-semibold text-white">
                                    {user.telegram_username ? `@${String(user.telegram_username).replace(/^@/, '')}` : `ID ${user.telegram_id}`}
                                </div>
                            </div>
                        )}
                    </div>
                </GlassCard>

                <GlassCard>
                    <h2 className="mb-1 text-lg font-bold text-white">Редактирование</h2>
                    <p className="mb-5 text-sm text-white/50">Имя и email для входа и уведомлений</p>

                    {user && !user.email_verified_at && (
                        <div className="mb-5 rounded-xl border border-amber-500/25 bg-amber-500/10 px-4 py-3 text-sm text-amber-200">
                            <p className="mb-1.5">Email ещё не подтверждён — часть функций (включая тест-драйв) недоступна.</p>
                            <button type="button" onClick={resend} disabled={resendForm.processing} className="font-semibold underline">
                                Отправить ссылку ещё раз
                            </button>
                        </div>
                    )}
                    {status === 'verification-link-sent' && <Alert variant="success">Новая ссылка подтверждения отправлена на ваш email.</Alert>}

                    <form onSubmit={submit} className="space-y-4">
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div className="space-y-1.5">
                                <label htmlFor="name" className="block text-sm font-medium text-white/70">Имя</label>
                                <input id="name" type="text" placeholder="Как вас зовут" autoComplete="name"
                                    className={inputClass} value={data.name} onChange={(e) => setData('name', e.target.value)} />
                                {errors.name && <p className="text-sm text-red-400">{errors.name}</p>}
                            </div>
                            <div className="space-y-1.5">
                                <label htmlFor="email" className="block text-sm font-medium text-white/70">Email</label>
                                <input id="email" type="email" placeholder="name@mail.ru" autoComplete="email"
                                    className={inputClass} value={data.email} onChange={(e) => setData('email', e.target.value)} />
                                {errors.email && <p className="text-sm text-red-400">{errors.email}</p>}
                            </div>
                        </div>
                        <div className="flex items-center gap-3">
                            <button type="submit" disabled={processing}
                                className="rounded-full bg-gradient-to-r from-red-600 to-fuchsia-600 px-5 py-2.5 text-sm font-semibold text-white shadow-glow transition hover:brightness-110 disabled:opacity-60">
                                Сохранить изменения
                            </button>
                            {status === 'profile-updated' && <span className="text-sm text-emerald-300">Сохранено</span>}
                        </div>
                    </form>
                </GlassCard>
            </div>
        </CabinetLayout>
    );
}
