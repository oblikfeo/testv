import { Link } from '@inertiajs/react';

export default function Pagination({ links }) {
    if (!links || links.length <= 3) return null;

    return (
        <div className="mt-5 flex flex-wrap gap-1.5">
            {links.map((link, i) => {
                const raw = link.label;
                const label = /prev/i.test(raw)
                    ? '‹'
                    : /next/i.test(raw)
                        ? '›'
                        : raw.replace('&laquo;', '‹').replace('&raquo;', '›');

                if (!link.url) {
                    return (
                        <span key={i} className="rounded-lg px-3 py-1.5 text-sm text-white/25" dangerouslySetInnerHTML={{ __html: label }} />
                    );
                }

                return (
                    <Link key={i} href={link.url} preserveScroll
                        className={`rounded-lg px-3 py-1.5 text-sm transition ${link.active ? 'bg-gradient-to-r from-red-600 to-fuchsia-600 font-semibold text-white' : 'text-white/60 hover:bg-white/[0.06] hover:text-white'}`}
                        dangerouslySetInnerHTML={{ __html: label }} />
                );
            })}
        </div>
    );
}
