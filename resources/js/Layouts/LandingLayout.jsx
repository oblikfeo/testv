import { Head, usePage } from '@inertiajs/react';
import Navbar from '@/Components/landing/Navbar';
import Footer from '@/Components/landing/Footer';

export default function LandingLayout({
    children,
    title,
    description,
    canonical,
    ogImage,
    visitorCount,
    footerVariant = 'default',
}) {
    const { props } = usePage();
    const siteUrl = (props.ziggy?.url ?? '').replace(/\/$/, '');
    const resolvedCanonical = canonical ?? props.ziggy?.location;
    const resolvedOgImage = ogImage ?? `${siteUrl}/assets/logo.png`;

    return (
        <>
            <Head>
                {title && <title>{title}</title>}
                {description && <meta name="description" content={description} />}
                {resolvedCanonical && <link rel="canonical" href={resolvedCanonical} />}
                <meta property="og:type" content="website" />
                <meta property="og:site_name" content="AVA VPN" />
                <meta property="og:locale" content="ru_RU" />
                {title && <meta property="og:title" content={title} />}
                {description && <meta property="og:description" content={description} />}
                {resolvedCanonical && <meta property="og:url" content={resolvedCanonical} />}
                <meta property="og:image" content={resolvedOgImage} />
                <meta name="twitter:card" content="summary_large_image" />
                <link rel="stylesheet" href="/css/common.css" />

                <script type="application/ld+json">
                    {JSON.stringify({
                        '@context': 'https://schema.org',
                        '@type': 'Organization',
                        name: 'AVA VPN',
                        url: siteUrl,
                        logo: resolvedOgImage,
                        contactPoint: {
                            '@type': 'ContactPoint',
                            contactType: 'customer support',
                            availableLanguage: ['Russian', 'English'],
                        },
                    })}
                </script>
                <script type="application/ld+json">
                    {JSON.stringify({
                        '@context': 'https://schema.org',
                        '@type': 'WebSite',
                        name: 'AVA VPN',
                        url: siteUrl,
                        inLanguage: 'ru-RU',
                    })}
                </script>
            </Head>

            <Navbar visitorCount={visitorCount} />
            <main>{children}</main>
            <Footer variant={footerVariant} />
        </>
    );
}
