@php
    $featured = $featured ?? false;
    $tierName = $tierName ?? 'Тариф';
    $devices = (int) ($devices ?? 2);
    $plans = $plans ?? collect();
@endphp

<article class="shop-tier {{ $featured ? 'shop-tier--featured' : '' }}">
    @if($featured)
        <span class="shop-tier-badge">Выгодно</span>
    @endif

    <header class="shop-tier-head">
        <div class="shop-tier-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                <rect x="2" y="3" width="20" height="14" rx="2"/>
                <path d="M8 21h8"/><path d="M12 17v4"/>
            </svg>
        </div>
        <div>
            <h3 class="shop-tier-name">{{ $tierName }}</h3>
            <p class="shop-tier-devices">
                {{ $devices }}
                @if($devices % 10 === 1 && $devices % 100 !== 11)
                    устройство
                @elseif(in_array($devices % 10, [2, 3, 4], true) && ! in_array($devices % 100, [12, 13, 14], true))
                    устройства
                @else
                    устройств
                @endif
            </p>
        </div>
    </header>

    <div class="shop-tier-options">
        @forelse($plans as $plan)
            <div class="shop-tier-row">
                <div class="shop-tier-row-info">
                    <span class="shop-tier-period">{{ $plan->period_label }}</span>
                    <div class="shop-tier-pricing">
                        <span class="shop-tier-price">{{ $plan->formatted_price }}</span>
                        @if($plan->discount > 0)
                            <span class="shop-tier-discount">−{{ $plan->discount }}%</span>
                        @endif
                    </div>
                </div>
                <form action="{{ route('payment.create') }}" method="POST" class="shop-tier-form">
                    @csrf
                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                    <button type="submit" class="shop-tier-btn {{ $featured ? 'shop-tier-btn--primary' : '' }}">
                        Купить
                    </button>
                </form>
            </div>
        @empty
            <p class="shop-tier-empty">Тарифы временно недоступны</p>
        @endforelse
    </div>
</article>
