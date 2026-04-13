@extends('layouts.cabinet')

@section('title', 'Покупки')

@section('content')
    <h1 class="cab-page-title">История покупок</h1>
    <p class="cab-page-desc">Все ваши оплаты и продления.</p>

    <div class="cab-card">
        <div class="cab-empty">
            <p>Покупок пока нет.</p>
            <a href="{{ route('home') }}#pricing" class="btn btn-primary btn-sm">Выбрать тариф</a>
        </div>
    </div>
@endsection
