import { Head, usePage } from '@inertiajs/react';
import CabinetNavbar from '@/Components/cabinet/CabinetNavbar';
import Sidebar from '@/Components/cabinet/Sidebar';
import EmailVerifyBanner from '@/Components/cabinet/EmailVerifyBanner';
import TrialFeedbackBanner from '@/Components/cabinet/TrialFeedbackBanner';

export default function CabinetLayout({ title, children }) {
    const { props } = usePage();
    const user = props.auth?.user;
    const showVerifyBanner = user && !user.email_verified_at;
    const showTrialFeedback = Boolean(props.pendingTrialFeedback);

    return (
        <>
            <Head title={title ? `${title} — AVA VPN` : 'AVA VPN'} />
            <div className="min-h-screen bg-ink-950">
                <CabinetNavbar />
                <div className="mx-auto flex max-w-7xl flex-col gap-6 px-5 py-8 sm:px-8 lg:flex-row lg:gap-8">
                    <Sidebar />
                    <main className="min-w-0 flex-1">
                        {showVerifyBanner && <EmailVerifyBanner />}
                        {showTrialFeedback && <TrialFeedbackBanner />}
                        {children}
                    </main>
                </div>
            </div>
        </>
    );
}
