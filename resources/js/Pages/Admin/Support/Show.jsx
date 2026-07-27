import { Link, router, useForm } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';

export default function SupportShow({ ticket, messages }) {
    const { data, setData, post, processing, errors, reset } = useForm({ body: '' });

    function reply(e) {
        e.preventDefault();
        post(route('admin.support.reply', ticket.id), { preserveScroll: true, onSuccess: () => reset('body') });
    }

    function close() {
        router.post(route('admin.support.close', ticket.id), {}, { preserveScroll: true });
    }
    function reopen() {
        router.post(route('admin.support.reopen', ticket.id), {}, { preserveScroll: true });
    }

    return (
        <AdminLayout title={`Тикет #${ticket.id}`}>
            <div className="mb-6">
                <Link href={route('admin.support.index')} className="text-sm text-white/45 transition hover:text-white">← К тикетам</Link>
                <div className="mt-2 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 className="text-xl font-bold text-white sm:text-2xl">{ticket.subject}</h1>
                        <p className="mt-1 text-sm text-white/45">
                            #{ticket.id} · {ticket.category} · {ticket.createdAt} ·{' '}
                            {ticket.user
                                ? <Link href={route('admin.users.show', ticket.user.id)} className="text-red-300 hover:underline">{ticket.user.label}</Link>
                                : '—'}
                        </p>
                    </div>
                    {ticket.isClosed
                        ? <button onClick={reopen} className="rounded-full border border-white/10 px-4 py-2 text-sm text-white/70 transition hover:text-white">Переоткрыть</button>
                        : <button onClick={close} className="rounded-full border border-white/10 px-4 py-2 text-sm text-white/70 transition hover:text-white">Закрыть тикет</button>}
                </div>
            </div>

            <div className="flex flex-col gap-3">
                {messages.map((m) => (
                    <div key={m.id} className={`max-w-[85%] rounded-2xl border px-4 py-3 ${m.isAdmin ? 'ml-auto border-red-500/25 bg-red-500/10' : 'border-white/10 bg-white/[0.04]'}`}>
                        <div className="mb-1 flex items-center gap-2 text-xs">
                            <span className={`font-semibold ${m.isAdmin ? 'text-red-300' : 'text-white/70'}`}>{m.author}</span>
                            <span className="text-white/30">{m.createdAt}</span>
                        </div>
                        <p className="whitespace-pre-wrap text-sm text-white/85">{m.body}</p>
                    </div>
                ))}
            </div>

            {!ticket.isClosed && (
                <form onSubmit={reply} className="mt-6 rounded-2xl border border-white/10 bg-white/[0.035] p-5">
                    <textarea rows={4} value={data.body} onChange={(e) => setData('body', e.target.value)} placeholder="Ответ пользователю…"
                        className="w-full rounded-xl border border-white/10 bg-black/30 px-4 py-3 text-sm text-white placeholder-white/30 outline-none focus:border-red-500/50" />
                    {errors.body && <p className="mt-1 text-sm text-red-400">{errors.body}</p>}
                    <div className="mt-3 flex justify-end">
                        <button type="submit" disabled={processing || !data.body.trim()}
                            className="rounded-full bg-gradient-to-r from-red-600 to-fuchsia-600 px-5 py-2.5 text-sm font-semibold text-white shadow-glow transition hover:brightness-110 disabled:opacity-60">
                            Отправить ответ
                        </button>
                    </div>
                </form>
            )}
        </AdminLayout>
    );
}
