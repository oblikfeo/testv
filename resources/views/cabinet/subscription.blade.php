@extends('layouts.cabinet')

@section('title', 'Подписка')

@section('content')
    <h1 class="cab-page-title">Подписка</h1>
    <p class="cab-page-desc">Статус вашего текущего тарифа и ключ подключения.</p>

    <div class="cab-card">
        <div class="cab-card-header">
            <span class="cab-card-title">Текущий тариф</span>
            <span class="cab-badge gray">Не активна</span>
        </div>
        <div class="sub-row">
            <span class="sub-row-label">Тариф</span>
            <span class="sub-row-value muted">—</span>
        </div>
        <div class="sub-row">
            <span class="sub-row-label">Устройств</span>
            <span class="sub-row-value muted">—</span>
        </div>
        <div class="sub-row">
            <span class="sub-row-label">Действует до</span>
            <span class="sub-row-value muted">—</span>
        </div>
        <div class="sub-row">
            <span class="sub-row-label">Ключ подключения</span>
            <span class="sub-row-value muted">—</span>
        </div>
        <div style="margin-top: 20px; display: flex; flex-wrap: wrap; gap: 12px;">
            <a href="{{ route('home') }}#pricing" class="btn btn-primary btn-sm">Оформить подписку</a>
            <a href="{{ route('keys.index') }}" class="btn btn-secondary btn-sm">Мои ключи</a>
        </div>
    </div>
@endsection
