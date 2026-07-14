import { useState } from 'react';
import { useForm, usePage } from '@inertiajs/react';
import CabinetLayout from '@/Layouts/CabinetLayout';
import GlassCard from '@/Components/ui/GlassCard';
import ConfirmDialog from '@/Components/cabinet/ConfirmDialog';

const inputClass =
    'w-full rounded-xl border border-white/10 bg-black/30 px-4 py-2.5 text-white placeholder-white/30 outline-none transition focus:border-red-500/50 focus:ring-2 focus:ring-red-500/20';

export default function Security() {
    const { props } = usePage();
    const status = props.flash?.status;
    const [confirmOpen, setConfirmOpen] = useState(false);

    const passwordForm = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const deleteForm = useForm({ password: '' });

    function updatePassword(e) {
        e.preventDefault();
        passwordForm.put(route('password.update'), {
            errorBag: 'updatePassword',
            preserveScroll: true,
            onSuccess: () => passwordForm.reset(),
        });
    }

    function destroyAccount() {
        deleteForm.delete(route('profile.destroy'), {
            errorBag: 'userDeletion',
            onFinish: () => setConfirmOpen(false),
        });
    }

    return (
        <CabinetLayout title="Безопасность">
            <header className="mb-6">
                <h1 className="text-2xl font-bold text-white sm:text-3xl">Безопасность</h1>
                <p className="mt-1 text-white/50">Смена пароля и необратимые действия с аккаунтом.</p>
            </header>

            <div className="flex flex-col gap-5">
                <GlassCard>
                    <h2 className="mb-1 text-lg font-bold text-white">Смена пароля</h2>
                    <p className="mb-5 text-sm text-white/50">Используйте надёжный пароль длиной от 8 символов</p>

                    <form onSubmit={updatePassword} className="space-y-4">
                        <div className="space-y-1.5">
                            <label htmlFor="current_password" className="block text-sm font-medium text-white/70">Текущий пароль</label>
                            <input
                                id="current_password" type="password" autoComplete="current-password" placeholder="Введите текущий пароль"
                                className={inputClass} value={passwordForm.data.current_password}
                                onChange={(e) => passwordForm.setData('current_password', e.target.value)}
                            />
                            {passwordForm.errors.current_password && <p className="text-sm text-red-400">{passwordForm.errors.current_password}</p>}
                        </div>

                        <div className="grid gap-4 sm:grid-cols-2">
                            <div className="space-y-1.5">
                                <label htmlFor="password" className="block text-sm font-medium text-white/70">Новый пароль</label>
                                <input
                                    id="password" type="password" autoComplete="new-password" placeholder="Минимум 8 символов"
                                    className={inputClass} value={passwordForm.data.password}
                                    onChange={(e) => passwordForm.setData('password', e.target.value)}
                                />
                                {passwordForm.errors.password && <p className="text-sm text-red-400">{passwordForm.errors.password}</p>}
                            </div>
                            <div className="space-y-1.5">
                                <label htmlFor="password_confirmation" className="block text-sm font-medium text-white/70">Подтверждение</label>
                                <input
                                    id="password_confirmation" type="password" autoComplete="new-password" placeholder="Повторите пароль"
                                    className={inputClass} value={passwordForm.data.password_confirmation}
                                    onChange={(e) => passwordForm.setData('password_confirmation', e.target.value)}
                                />
                                {passwordForm.errors.password_confirmation && <p className="text-sm text-red-400">{passwordForm.errors.password_confirmation}</p>}
                            </div>
                        </div>

                        <div className="flex items-center gap-3">
                            <button type="submit" disabled={passwordForm.processing}
                                className="rounded-full bg-gradient-to-r from-red-600 to-fuchsia-600 px-5 py-2.5 text-sm font-semibold text-white shadow-glow transition hover:brightness-110 disabled:opacity-60">
                                Сменить пароль
                            </button>
                            {status === 'password-updated' && <span className="text-sm text-emerald-300">Пароль обновлён</span>}
                        </div>
                    </form>
                </GlassCard>

                <GlassCard className="border-red-500/20">
                    <div className="mb-5 flex items-start gap-3">
                        <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-500/10 text-red-400">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" className="h-5 w-5">
                                <path d="M3 6h18" /><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                                <line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" />
                            </svg>
                        </div>
                        <div>
                            <h2 className="text-lg font-bold text-white">Удаление аккаунта</h2>
                            <p className="text-sm text-red-300/70">Будут удалены подписка, ключи, история оплат и все данные профиля. Восстановить аккаунт нельзя.</p>
                        </div>
                    </div>

                    <form
                        onSubmit={(e) => {
                            e.preventDefault();
                            setConfirmOpen(true);
                        }}
                        className="space-y-4"
                    >
                        <div className="space-y-1.5">
                            <label htmlFor="delete_password" className="block text-sm font-medium text-white/70">Подтвердите паролем</label>
                            <input
                                id="delete_password" type="password" required autoComplete="current-password" placeholder="Текущий пароль для подтверждения"
                                className={inputClass} value={deleteForm.data.password}
                                onChange={(e) => deleteForm.setData('password', e.target.value)}
                            />
                            {deleteForm.errors.password && <p className="text-sm text-red-400">{deleteForm.errors.password}</p>}
                        </div>
                        <button type="submit" className="rounded-full bg-red-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-red-500">
                            Удалить аккаунт
                        </button>
                    </form>
                </GlassCard>
            </div>

            <ConfirmDialog
                open={confirmOpen}
                title="Удалить аккаунт безвозвратно?"
                description="Это действие нельзя отменить. Подписка, ключи и история покупок будут удалены навсегда."
                confirmLabel="Удалить"
                danger
                processing={deleteForm.processing}
                onConfirm={destroyAccount}
                onCancel={() => setConfirmOpen(false)}
            />
        </CabinetLayout>
    );
}
