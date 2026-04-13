<footer class="footer">
    <div class="container">
        <p>
            {{ config('app.brand_name') }} {{ config('app.brand_suffix') }} — сервис для защиты и ускорения вашего интернет-соединения.<br>
            Используя сервис, вы принимаете на себя ответственность за его применение
            в соответствии с действующим законодательством.<br><br>
            <a href="{{ route('agreement') }}">Условия использования</a>
        </p>
    </div>
</footer>
