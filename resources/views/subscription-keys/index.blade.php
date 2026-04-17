@extends('layouts.cabinet')

@section('title', 'Ключи')

@section('content')
    <h1 class="cab-page-title">Тест-драйв и ключи</h1>
    <p class="cab-page-desc">Подписка Happ для оплаченного тарифа и пул ключей (legacy).</p>

    @if(!empty($saleKey))
        <div class="cab-card" style="margin-bottom: 24px;">
            <div class="cab-card-header">
                <span class="cab-card-title">Подписка Happ (оплаченный тариф)</span>
                @if($saleKey->is_sponsor)
                    <span class="cab-badge gray">2 соединения</span>
                @endif
            </div>
            <p class="cab-page-desc" style="margin-bottom: 12px;">Добавьте ссылку в Happ — трафик и срок синхронизируются с сервером.</p>
            <div class="key-row">
                <code id="happ-sale-url" style="word-break: break-all; font-size: 0.82rem;">{{ url('/sub/'.$saleKey->sub_id) }}</code>
                <button type="button" class="btn btn-primary btn-sm" onclick="navigator.clipboard.writeText(document.getElementById('happ-sale-url').innerText)">{{ __('Копировать') }}</button>
            </div>
            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 10px;">Альтернатива: <code>{{ url('/api/sub/'.$saleKey->sub_id) }}</code></p>
        </div>
    @endif

    @if (session('status') === 'key-issued')
        <p style="color: var(--red-light); font-size: 0.9rem; margin-bottom: 16px;">{{ __('Ключ выдан.') }}</p>
    @endif
    @if (session('status') === 'key-activated')
        <p style="color: var(--red-light); font-size: 0.9rem; margin-bottom: 16px;">{{ __('Подключение отмечено как активированное.') }}</p>
    @endif
    @error('issue')
        <p style="color: var(--red-light); font-size: 0.9rem; margin-bottom: 16px;">{{ $message }}</p>
    @enderror

    <div class="cab-card">
        <div class="cab-card-header">
            <span class="cab-card-title">Выдача ключа</span>
        </div>
        <p style="color: var(--text-secondary); font-size: 0.9rem; line-height: 1.6; margin-bottom: 16px;">
            {{ __('Выдача из пула серверов (отдельно от оплаченной подписки Happ выше).') }}
        </p>
        <form method="POST" action="{{ route('keys.issue') }}">
            @csrf
            <button type="submit" class="btn btn-primary btn-sm">{{ __('Получить ключ') }}</button>
        </form>
    </div>

    <div style="height: 20px;"></div>

    <div class="cab-card">
        <div class="cab-card-header">
            <span class="cab-card-title">Ваши ключи</span>
        </div>
        @forelse ($keys as $key)
            <div style="padding: 16px 0; border-bottom: 1px solid var(--border-color);">
                <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 8px; margin-bottom: 8px;">
                    <span style="font-weight: 600; font-size: 0.9rem;">#{{ $key->id }} — {{ $key->pair?->name ?? '—' }}</span>
                    <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted);">{{ $key->status }}</span>
                </div>
                <div class="key-block" style="margin-top: 8px;">
                    <div class="key-row">
                        <code id="key-url-{{ $key->id }}" style="word-break: break-all; font-size: 0.8rem;">{{ $key->connection_url }}</code>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="navigator.clipboard.writeText(document.getElementById('key-url-{{ $key->id }}').innerText)">{{ __('Копировать') }}</button>
                    </div>
                </div>
                @if ($key->status === \App\Enums\SubscriptionKeyStatus::Issued->value)
                    <form method="POST" action="{{ route('keys.activate', $key) }}" style="margin-top: 10px;">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm" style="font-size: 0.75rem;">{{ __('Отметить первый вход (тест)') }}</button>
                    </form>
                @endif
                @if ($key->issued_at)
                    <p style="margin-top: 8px; font-size: 0.75rem; color: var(--text-muted);">
                        {{ __('Выдан:') }} {{ $key->issued_at->timezone(config('app.timezone'))->format('Y-m-d H:i') }}
                    </p>
                @endif
            </div>
        @empty
            <p style="color: var(--text-secondary); font-size: 0.9rem;">{{ __('Пока нет ключей.') }}</p>
        @endforelse
        <div style="margin-top: 20px;">
            {{ $keys->links() }}
        </div>
    </div>
@endsection
