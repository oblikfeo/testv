import { Head, usePage } from '@inertiajs/react';
import LandingLayout from '@/Layouts/LandingLayout';
import Hero from '@/Components/landing/Hero';
import StatsStrip from '@/Components/landing/StatsStrip';
import WhatIsVpn from '@/Components/landing/WhatIsVpn';
import FeatureGrid from '@/Components/landing/FeatureGrid';
import HowItWorks from '@/Components/landing/HowItWorks';
import UnblockedServices from '@/Components/landing/UnblockedServices';
import DeviceGrid from '@/Components/landing/DeviceGrid';
import SpeedSecurity from '@/Components/landing/SpeedSecurity';
import PricingTable from '@/Components/landing/PricingTable';
import ComparisonSection from '@/Components/landing/ComparisonSection';
import FaqAccordion from '@/Components/landing/FaqAccordion';
import FinalCta from '@/Components/landing/FinalCta';
import { FAQ_ITEMS } from '@/Components/landing/faqData';

export default function Welcome({ visitorCount, planGroups = [] }) {
    const { props } = usePage();
    const siteUrl = (props.ziggy?.url ?? '').replace(/\/$/, '');
    const allPlans = planGroups.flat();

    const productJsonLd = {
        '@context': 'https://schema.org',
        '@type': 'Product',
        name: 'AVA VPN',
        description: 'VPN-сервис для России с высокой скоростью, защитой от блокировок и поддержкой всех устройств. Подключение по протоколу VLESS, шифрование трафика, тестовый доступ бесплатно.',
        brand: { '@type': 'Brand', name: 'AVA VPN' },
        image: `${siteUrl}/assets/logo.png`,
        offers: allPlans.map((plan) => ({
            '@type': 'Offer',
            name: `${plan.name} — ${plan.periodLabel}`,
            description: `VPN-подписка на ${plan.periodLabel}, до ${plan.devices} устройств`,
            price: String(plan.price),
            priceCurrency: 'RUB',
            availability: 'https://schema.org/InStock',
            url: `${siteUrl}/#pricing`,
        })),
    };

    const faqJsonLd = {
        '@context': 'https://schema.org',
        '@type': 'FAQPage',
        mainEntity: FAQ_ITEMS.map((item) => ({
            '@type': 'Question',
            name: item.q,
            acceptedAnswer: { '@type': 'Answer', text: item.a },
        })),
    };

    const breadcrumbJsonLd = {
        '@context': 'https://schema.org',
        '@type': 'BreadcrumbList',
        itemListElement: [
            { '@type': 'ListItem', position: 1, name: 'Главная', item: siteUrl },
        ],
    };

    return (
        <LandingLayout
            title="AVA VPN — быстрый и приватный VPN для России | Бесплатный тест 3 часа"
            description="AVA VPN — надёжный VPN-сервис для России: высокая скорость, защита от блокировок, простое подключение на телефон, компьютер и роутер. Тестовый доступ на 3 часа бесплатно, без банковской карты."
            visitorCount={visitorCount}
        >
            <Head>
                <script type="application/ld+json">{JSON.stringify(productJsonLd)}</script>
                <script type="application/ld+json">{JSON.stringify(faqJsonLd)}</script>
                <script type="application/ld+json">{JSON.stringify(breadcrumbJsonLd)}</script>
            </Head>

            <Hero />
            <StatsStrip />
            <WhatIsVpn />
            <FeatureGrid />
            <HowItWorks />
            <UnblockedServices />
            <DeviceGrid />
            <SpeedSecurity />
            <PricingTable planGroups={planGroups} />
            <ComparisonSection />
            <FaqAccordion />
            <FinalCta />

            <p className="mx-auto max-w-6xl px-5 py-6 text-center text-xs text-white/30 sm:px-8">
                * Instagram — проект Meta, признан в РФ экстремистским и запрещён.
            </p>
        </LandingLayout>
    );
}
