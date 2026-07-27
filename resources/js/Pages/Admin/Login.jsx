import { Head, useForm } from '@inertiajs/react';

const inputClass =
    'w-full rounded-xl border border-white/10 bg-black/30 px-4 py-2.5 text-white placeholder-white/30 outline-none transition focus:border-red-500/50 focus:ring-2 focus:ring-red-500/20';

export default function Login({ configured }) {
    const { data, setData, post, processing, errors } = useForm({
        login: '',
        password: '',
    });

    function submit(e) {
        e.preventDefault();
        post(route('admin.authenticate'));
    }

    return (
        <>
            <Head title="Вход — Админка AVA VPN" />
            <div className="flex min-h-screen items-center justify-center bg-ink-950 px-5 py-10 text-white">
                <div className="w-full max-w-sm">
                    <div className="mb-8 flex flex-col items-center gap-3">
                        <span className="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-red-600 to-fuchsia-600 text-lg font-black">A</span>
                        <h1 className="text-xl font-bold">Админка AVA VPN</h1>
                    </div>

                    <div className="rounded-2xl border border-white/10 bg-white/[0.035] p-6 backdrop-blur-sm sm:p-8">
                        {!configured && (
                            <div className="mb-5 rounded-xl border border-amber-500/25 bg-amber-500/10 px-4 py-3 text-sm text-amber-200">
                                Задайте <code>ADMIN_LOGIN</code> и <code>ADMIN_PASSWORD</code> в <code>.env</code>.
                            </div>
                        )}

                        <form onSubmit={submit} className="space-y-4">
                            <div className="space-y-1.5">
                                <label htmlFor="login" className="block text-sm font-medium text-white/70">Логин</label>
                                <input id="login" type="text" autoFocus autoComplete="username"
                                    className={inputClass} value={data.login} onChange={(e) => setData('login', e.target.value)} />
                                {errors.login && <p className="text-sm text-red-400">{errors.login}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <label htmlFor="password" className="block text-sm font-medium text-white/70">Пароль</label>
                                <input id="password" type="password" autoComplete="current-password"
                                    className={inputClass} value={data.password} onChange={(e) => setData('password', e.target.value)} />
                                {errors.password && <p className="text-sm text-red-400">{errors.password}</p>}
                            </div>

                            <button type="submit" disabled={processing}
                                className="w-full rounded-full bg-gradient-to-r from-red-600 to-fuchsia-600 px-5 py-2.5 text-sm font-semibold text-white shadow-glow transition hover:brightness-110 disabled:opacity-60">
                                Войти
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </>
    );
}
