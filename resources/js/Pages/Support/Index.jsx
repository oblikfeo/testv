import { Link, useForm, usePage } from '@inertiajs/react';
import CabinetLayout from '@/Layouts/CabinetLayout';
import GlassCard from '@/Components/ui/GlassCard';
import Alert from '@/Components/cabinet/Alert';

const STATUS_STYLES = {
    open: 'bg-amber-500/15 text-amber-300',
    pending_user: 'bg-sky-500/15 text-sky-300',
    closed: 'bg-white/10 text-white/45',
};

export default function SupportIndex({ tickets, categories }) {
    const { props } = usePage();
    const errorList = Object.values(props.errors ?? {});

    const { data, setData, post, processing, errors } = useForm({
        subject: '',
        category: 'connection',
        body: '',
    });

    function submit(e) {
        e.preventDefault();
        post(route('cabinet.support.store'));
    }

    return (
        <CabinetLayout title="Поддержка">
            <header className="mb-6">
                <h1 className="text-2xl font-bold text-white sm:text-3xl">Поддержка</h1>
                <p className="mt-1 text-white/50">
                    Опишите проблему — поможем с подключением, оплатой или возвратом. Ответ придёт здесь; при привязанном Telegram продублируем уведомление.
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
                <GlassCard>
                    <h2 className="mb-1 text-lg font-bold text-white">Новое обращение</h2>
                    <p className="mb-5 text-sm text-white/50">Укажите тему и опишите ситуацию — чем подробнее, тем быстрее поможем</p>

                    <form onSubmit={submit} className="space-y-4">
                        <div className="grid gap-4 sm:grid-cols-3">
                            <div className="space-y-1.5 sm:col-span-2">
                                <label htmlFor="subject" className="block text-sm font-medium text-white/70">Тема</label>
                                <input
                                    id="subject" type="text" maxLength={200} required
                                    placeholder="Например: не подключается VPN на iPhone"
                                    className="w-full rounded-xl border border-white/10 bg-black/30 px-4 py-2.5 text-white placeholder-white/30 outline-none transition focus:border-red-500/50 focus:ring-2 focus:ring-red-500/20"
                                    value={data.subject} onChange={(e) => setData('subject', e.target.value)}
                                />
                                {errors.subject && <p className="text-sm text-red-400">{errors.subject}</p>}
                            </div>
                            <div className="space-y-1.5">
                                <label htmlFor="category" className="block text-sm font-medium text-white/70">Категория</label>
                                <select
                                    id="category" required
                                    className="w-full rounded-xl border border-white/10 bg-black/30 px-4 py-2.5 text-white outline-none transition focus:border-red-500/50 focus:ring-2 focus:ring-red-500/20"
                                    value={data.category} onChange={(e) => setData('category', e.target.value)}
                                >
                                    {Object.entries(categories).map(([key, label]) => (
                                        <option key={key} value={key}>{label}</option>
                                    ))}
                                </select>
                            </div>
                        </div>

                        <div className="space-y-1.5">
                            <label htmlFor="body" className="block text-sm font-medium text-white/70">Сообщение</label>
                            <textarea
                                id="body" rows={6} maxLength={5000} required
                                placeholder="Устройство, приложение (Happ / v2RayTun), текст ошибки, скриншоты — если есть"
                                className="w-full resize-y rounded-xl border border-white/10 bg-black/30 px-4 py-2.5 text-white placeholder-white/30 outline-none transition focus:border-red-500/50 focus:ring-2 focus:ring-red-500/20"
                                value={data.body} onChange={(e) => setData('body', e.target.value)}
                            />
                            {errors.body && <p className="text-sm text-red-400">{errors.body}</p>}
                        </div>

                        <button
                            type="submit" disabled={processing}
                            className="rounded-full bg-gradient-to-r from-red-600 to-fuchsia-600 px-5 py-2.5 text-sm font-semibold text-white shadow-glow transition hover:brightness-110 disabled:opacity-60"
                        >
                            Отправить обращение
                        </button>
                    </form>
                </GlassCard>

                <GlassCard>
                    <div className="mb-5 flex items-center justify-between">
                        <div>
                            <h2 className="text-lg font-bold text-white">Ваши обращения</h2>
                            <p className="text-sm text-white/45">История переписки с поддержкой</p>
                        </div>
                        {tickets.total > 0 && (
                            <span className="rounded-full bg-white/10 px-3 py-1 text-sm font-semibold text-white/60">{tickets.total}</span>
                        )}
                    </div>

                    {tickets.data.length > 0 ? (
                        <ul className="flex flex-col gap-2.5">
                            {tickets.data.map((ticket) => (
                                <li key={ticket.id}>
                                    <Link
                                        href={route('cabinet.support.show', ticket.id)}
                                        className="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-white/10 bg-black/20 p-4 transition hover:border-red-500/25"
                                    >
                                        <div className="min-w-0">
                                            <div className="flex items-center gap-2 text-white">
                                                <span className="text-white/35">#{ticket.id}</span>
                                                <span className="truncate font-medium">{ticket.subject}</span>
                                            </div>
                                            <div className="mt-1 text-sm text-white/40">{ticket.categoryLabel} · {ticket.lastMessageAt}</div>
                                        </div>
                                        <span className={`shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold ${STATUS_STYLES[ticket.status] ?? 'bg-white/10 text-white/45'}`}>
                                            {ticket.statusLabel}
                                        </span>
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    ) : (
                        <p className="text-white/45">Обращений пока нет — форма выше всегда доступна.</p>
                    )}

                    {tickets.last_page > 1 && (
                        <div className="mt-5 flex flex-wrap gap-2">
                            {Array.from({ length: tickets.last_page }, (_, i) => i + 1).map((page) => (
                                <Link
                                    key={page}
                                    href={route('cabinet.support.index', { page })}
                                    preserveScroll
                                    className={`rounded-lg px-3 py-1.5 text-sm font-medium transition ${
                                        page === tickets.current_page ? 'bg-red-600 text-white' : 'border border-white/10 text-white/55 hover:bg-white/[0.06]'
                                    }`}
                                >
                                    {page}
                                </Link>
                            ))}
                        </div>
                    )}
                </GlassCard>
            </div>
        </CabinetLayout>
    );
}
