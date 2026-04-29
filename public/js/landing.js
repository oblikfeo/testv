(function () {
    'use strict';

    // ===== Scroll reveal via IntersectionObserver =====
    function initReveal() {
        var nodes = document.querySelectorAll('.reveal');
        if (!nodes.length) return;

        if (!('IntersectionObserver' in window)) {
            nodes.forEach(function (n) { n.classList.add('is-visible'); });
            return;
        }

        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    io.unobserve(entry.target);
                }
            });
        }, {
            root: null,
            rootMargin: '0px 0px -8% 0px',
            threshold: 0.08
        });

        nodes.forEach(function (n) { io.observe(n); });
    }

    // ===== Feature card hover glow (cursor tracking) =====
    function initCardGlow() {
        var cards = document.querySelectorAll('.feature-card');
        cards.forEach(function (card) {
            card.addEventListener('pointermove', function (e) {
                var rect = card.getBoundingClientRect();
                var x = ((e.clientX - rect.left) / rect.width) * 100;
                var y = ((e.clientY - rect.top) / rect.height) * 100;
                card.style.setProperty('--mx', x + '%');
                card.style.setProperty('--my', y + '%');
            });
        });
    }

    // ===== Single-open accordion behaviour for FAQ =====
    function initFaq() {
        var items = document.querySelectorAll('.faq-list .faq-item');
        items.forEach(function (item) {
            item.addEventListener('toggle', function () {
                if (item.open) {
                    items.forEach(function (other) {
                        if (other !== item && other.open) other.open = false;
                    });
                }
            });
        });
    }

    // ===== Navbar shadow on scroll =====
    function initNavbar() {
        var nav = document.querySelector('.navbar');
        if (!nav) return;
        var update = function () {
            if (window.scrollY > 8) nav.classList.add('is-scrolled');
            else nav.classList.remove('is-scrolled');
        };
        update();
        window.addEventListener('scroll', update, { passive: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            initReveal();
            initCardGlow();
            initFaq();
            initNavbar();
        });
    } else {
        initReveal();
        initCardGlow();
        initFaq();
        initNavbar();
    }
})();
