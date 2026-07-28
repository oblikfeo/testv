import { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Pagination from '@/Components/admin/Pagination';
import AccessBadge from '@/Components/admin/AccessBadge';

export default function UsersIndex({ users, filters }) {
    const [q, setQ] = useState(filters.q ?? '');

    function search(e) {
        e.preventDefault();
        router.get(route('admin.users'), { q }, { preserveState: true, replace: true });
    }

    function reset() {
        setQ('');
        router.get(route('admin.users'), {}, { preserveState: true, replace: true });
    }

    return (
        <AdminLayout title="Пользователи">
            <header className="mb-6 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-bold text-white sm:text-3xl">Пользователи</h1>
                    <p className="mt-1 text-white/50">Всего: {users.total}</p>
                </div>
                <form onSubmit={search} className="flex gap-2">
                    <input
                        type="text" value={q} onChange={(e) => setQ(e.target.value)}
                        placeholder="Email, @username, TG id, id…"
                        className="w-56 rounded-xl border border-white/10 bg-black/30 px-4 py-2 text-sm text-white placeholder-white/30 outline-none transition focus:border-red-500/50 focus:ring-2 focus:ring-red-500/20 sm:w-72"
                    />
                    <button type="submit" className="rounded-xl bg-gradient-to-r from-red-600 to-fuchsia-600 px-4 py-2 text-sm font-semibold text-white transition hover:brightness-110">Найти</button>
                    {filters.q && <button type="button" onClick={reset} className="rounded-xl border border-white/10 px-4 py-2 text-sm text-white/60 transition hover:text-white">Сброс</button>}
                </form>
            </header>

            <div className="rounded-2xl border border-white/10 bg-white/[0.035] p-4 sm:p-5">
                {/* Mobile: cards */}
                <div className="flex flex-col gap-2 sm:hidden">
                    {users.data.length === 0 && <p className="py-6 text-center text-white/40">Ничего не найдено</p>}
                    {users.data.map((u) => (
                        <Link key={u.id} href={route('admin.users.show', u.id)} className="flex items-center justify-between gap-3 rounded-xl border border-white/10 bg-black/20 px-3.5 py-3 transition active:bg-white/[0.04]">
                            <div className="min-w-0">
                                <div className="truncate font-medium text-white">{u.name}</div>
                                <div className="truncate text-xs text-white/45">
                                    {u.isTelegram && <span className="mr-1 rounded bg-sky-500/15 px-1 py-0.5 text-[9px] font-semibold text-sky-300">TG</span>}
                                    {u.contact}
                                </div>
                            </div>
                            <div className="flex shrink-0 flex-col items-end gap-1">
                                <AccessBadge status={u.access} />
                                <span className="text-[11px] text-white/35">{u.createdAt}</span>
                            </div>
                        </Link>
                    ))}
                </div>

                {/* Desktop: table */}
                <div className="hidden overflow-x-auto sm:block">
                    <table className="w-full text-left text-sm">
                        <thead className="text-xs uppercase tracking-wider text-white/35">
                            <tr>
                                <th className="pb-3 font-medium">#</th>
                                <th className="pb-3 font-medium">Имя</th>
                                <th className="pb-3 font-medium">Контакт</th>
                                <th className="pb-3 font-medium">Доступ</th>
                                <th className="pb-3 text-right font-medium">Регистрация</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-white/5">
                            {users.data.length === 0 && (
                                <tr><td colSpan={5} className="py-6 text-center text-white/40">Ничего не найдено</td></tr>
                            )}
                            {users.data.map((u) => (
                                <tr key={u.id} className="group cursor-pointer transition hover:bg-white/[0.03]" onClick={() => router.visit(route('admin.users.show', u.id))}>
                                    <td className="py-3 pr-2 text-white/40">{u.id}</td>
                                    <td className="py-3 pr-2">
                                        <Link href={route('admin.users.show', u.id)} className="font-medium text-white group-hover:text-red-300" onClick={(e) => e.stopPropagation()}>
                                            {u.name}
                                        </Link>
                                    </td>
                                    <td className="py-3 pr-2 text-white/70">
                                        {u.isTelegram && <span className="mr-1.5 rounded bg-sky-500/15 px-1.5 py-0.5 text-[10px] font-semibold text-sky-300">TG</span>}
                                        {u.contact}
                                    </td>
                                    <td className="py-3 pr-2"><AccessBadge status={u.access} /></td>
                                    <td className="py-3 text-right text-white/50">{u.createdAt}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
                <Pagination links={users.links} />
            </div>
        </AdminLayout>
    );
}
