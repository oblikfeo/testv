import AdminLayout from '@/Layouts/AdminLayout';

export default function Placeholder({ title }) {
    return (
        <AdminLayout title={title}>
            <header className="mb-6">
                <h1 className="text-2xl font-bold text-white sm:text-3xl">{title}</h1>
            </header>
            <div className="flex flex-col items-center justify-center rounded-2xl border border-dashed border-white/15 bg-white/[0.02] py-20 text-center">
                <div className="mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-white/5 text-2xl">🚧</div>
                <p className="text-lg font-semibold text-white">Раздел в разработке</p>
                <p className="mt-1 max-w-sm text-sm text-white/45">Модуль «{title}» появится в одном из следующих этапов переписывания админки.</p>
            </div>
        </AdminLayout>
    );
}
