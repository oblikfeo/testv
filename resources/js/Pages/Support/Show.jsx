import { useState } from 'react';
import { Link, useForm, usePage } from '@inertiajs/react';
import CabinetLayout from '@/Layouts/CabinetLayout';
import GlassCard from '@/Components/ui/GlassCard';
import Alert from '@/Components/cabinet/Alert';
import ConfirmDialog from '@/Components/cabinet/ConfirmDialog';

export default function SupportShow({ ticket }) {
    const { props } = usePage();
    const errorList = Object.values(props.errors ?? {});
    const [confirmOpen, setConfirmOpen] = useState(false);

    const replyForm = useForm({ body: '' });
    const closeForm = useForm({});

    function submitReply(e) {
        e.preventDefault();
        replyForm.post(route('cabinet.support.reply', ticket.id), { onSuccess: () => replyForm.reset() });
    }

    function closeTicket() {
        closeForm.post(route('cabinet.support.close', ticket.id), { onFinish: () => setConfirmOpen(false) });
    }

    return (
        <CabinetLayout title={`Тикет #${ticket.id}`}>
            <div className="mb-5 flex items-center justify-between">
                <Link href={route('cabinet.support.index')} className="inline-flex items-center gap-2 text-sm text-white/55 transition hover:text-white">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M19 12H5" /><path d="m12 19-7-7 7-7" /></svg>
                    К списку обращений
                </Link>
                <span className={`rounded-full px-3 py-1 text-xs font-semibold ${ticket.isOpen ? 'bg-amber-500/15 text-amber-300' : 'bg-white/10 text-white/45'}`}>
                    {ticket.statusLabel}
                </span>
            </div>

            <GlassCard className="mb-5">
                <h1 className="text-xl font-bold text-white">
                    <span className="mr-2 text-white/35">#{ticket.id}</span>
                    {ticket.subject}
                </h1>
                <p className="mt-1 text-sm text-white/45">{ticket.categoryLabel} · создан {ticket.createdAt}</p>
            </GlassCard>

            <Alert variant="success">{props.flash?.success}</Alert>
            {errorList.length > 0 && (
                <Alert variant="error">
                    {errorList.map((err) => (
                        <p key={err}>{err}</p>
                    ))}
                </Alert>
            )}

            <div role="log" aria-label="Переписка" className="mb-5 flex flex-col items-start gap-3">
                {ticket.messages.map((message) => (
                    <article
                        key={message.id}
                        className={`max-w-2xl rounded-2xl border p-4 ${
                            message.isAdmin ? 'border-red-500/20 bg-red-500/[0.06]' : 'ml-auto border-white/10 bg-white/[0.04]'
                        }`}
                    >
                        <div className="mb-2 flex items-center justify-between gap-4 text-sm">
                            <span className="flex items-center gap-2 font-semibold text-white/80">
                                <span className={`flex h-6 w-6 items-center justify-center rounded-full text-xs ${message.isAdmin ? 'bg-red-500/20 text-red-300' : 'bg-white/10 text-white/60'}`}>
                                    {message.isAdmin ? 'A' : 'Вы'}
                                </span>
                                {message.isAdmin ? 'Поддержка AVA VPN' : 'Вы'}
                            </span>
                            <time className="text-white/35">{message.createdAt}</time>
                        </div>
                        <div className="whitespace-pre-wrap text-sm leading-relaxed text-white/75">{message.body}</div>
                    </article>
                ))}
            </div>

            {ticket.isOpen ? (
                <GlassCard>
                    <h2 className="mb-1 text-lg font-bold text-white">Ваш ответ</h2>
                    <p className="mb-4 text-sm text-white/50">Дополните детали или приложите ID платежа</p>

                    <form onSubmit={submitReply} className="space-y-4">
                        <textarea
                            rows={5} maxLength={5000} required placeholder="Напишите ответ поддержке…"
                            className="w-full resize-y rounded-xl border border-white/10 bg-black/30 px-4 py-2.5 text-white placeholder-white/30 outline-none transition focus:border-red-500/50 focus:ring-2 focus:ring-red-500/20"
                            value={replyForm.data.body} onChange={(e) => replyForm.setData('body', e.target.value)}
                        />
                        {replyForm.errors.body && <p className="text-sm text-red-400">{replyForm.errors.body}</p>}

                        <div className="flex flex-wrap items-center justify-between gap-3">
                            <button
                                type="button" onClick={() => setConfirmOpen(true)}
                                className="rounded-full border border-white/15 bg-white/[0.06] px-5 py-2.5 text-sm font-semibold text-white/80 transition hover:bg-white/[0.1]"
                            >
                                Закрыть тикет
                            </button>
                            <button
                                type="submit" disabled={replyForm.processing}
                                className="rounded-full bg-gradient-to-r from-red-600 to-fuchsia-600 px-5 py-2.5 text-sm font-semibold text-white shadow-glow transition hover:brightness-110 disabled:opacity-60"
                            >
                                Отправить
                            </button>
                        </div>
                    </form>
                </GlassCard>
            ) : (
                <GlassCard>
                    <p className="text-white/55">
                        Тикет закрыт. Если нужна помощь —{' '}
                        <Link href={route('cabinet.support.index')} className="text-red-300 underline hover:text-red-200">создайте новое обращение</Link>.
                    </p>
                </GlassCard>
            )}

            <ConfirmDialog
                open={confirmOpen}
                title="Закрыть тикет?"
                description="Если вопрос вернётся — создайте новое обращение."
                confirmLabel="Закрыть"
                processing={closeForm.processing}
                onConfirm={closeTicket}
                onCancel={() => setConfirmOpen(false)}
            />
        </CabinetLayout>
    );
}
