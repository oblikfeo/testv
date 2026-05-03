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

    // ===== Traffic insights modal (счётчик в шапке) =====
    function initTrafficInsights() {
        var btn = document.getElementById('visitor-counter-trigger');
        var root = document.getElementById('traffic-insights-root');
        var cfgEl = document.getElementById('traffic-modal-config');
        var bodyEl = document.getElementById('traffic-modal-body');
        if (!btn || !root || !cfgEl || !bodyEl) return;

        var cfg = {};
        try {
            cfg = JSON.parse(cfgEl.textContent || '{}');
        } catch (e) {
            return;
        }

        var i18n = cfg.i18n || {};
        var statsUrl = cfg.statsUrl;
        var modalUrl = cfg.modalUrl;

        function csrf() {
            var m = document.querySelector('meta[name="csrf-token"]');
            return m ? m.getAttribute('content') : '';
        }

        function esc(t) {
            var d = document.createElement('div');
            d.textContent = t == null ? '' : String(t);
            return d.innerHTML;
        }

        function closeModal() {
            root.hidden = true;
            root.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('traffic-modal-open');
        }

        function renderStats(data) {
            var sources = data.sources || [];
            var html = '';
            html += '<div class="traffic-stat-grid">';
            html += '<div class="traffic-stat-card"><span>' + esc(i18n.total) + '</span><strong>' + esc(String(data.total_visits || 0)) + '</strong></div>';
            html += '<div class="traffic-stat-card traffic-stat-card--accent"><span>' + esc(i18n.opens) + '</span><strong>' + esc(String(data.modal_opens || 0)) + '</strong></div>';
            html += '</div>';

            if (!sources.length) {
                html += '<p class="traffic-modal-empty">' + esc(i18n.empty || '') + '</p>';
                bodyEl.innerHTML = html;
                return;
            }

            html += '<div class="traffic-bars">';
            sources.forEach(function (s) {
                var pct = typeof s.pct === 'number' ? s.pct : 0;
                var w = Math.min(100, Math.max(6, pct));
                html += '<div class="traffic-bar-row">';
                html += '<span class="traffic-bar-label">' + esc(s.label) + '</span>';
                html += '<div class="traffic-bar-track"><div class="traffic-bar-fill" style="width:' + w + '%"></div></div>';
                html += '<span class="traffic-bar-meta">' + esc(String(s.hits)) + ' · ' + esc(String(pct)) + '%</span>';
                html += '</div>';
            });
            html += '</div>';
            bodyEl.innerHTML = html;
        }

        function openModal() {
            root.hidden = false;
            root.setAttribute('aria-hidden', 'false');
            document.body.classList.add('traffic-modal-open');
            bodyEl.innerHTML = '<p class="traffic-modal-loading">' + esc(i18n.loading || '…') + '</p>';

            fetch(modalUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf(),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ opened: true })
            })
                .catch(function () {})
                .then(function () {
                    return fetch(statsUrl, {
                        credentials: 'same-origin',
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                })
                .then(function (r) {
                    if (!r.ok) throw new Error('bad');
                    return r.json();
                })
                .then(renderStats)
                .catch(function () {
                    bodyEl.innerHTML = '<p class="traffic-modal-error">' + esc(i18n.error || '') + '</p>';
                });
        }

        btn.addEventListener('click', openModal);

        root.querySelectorAll('[data-traffic-close]').forEach(function (el) {
            el.addEventListener('click', closeModal);
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !root.hidden) closeModal();
        });
    }

    function bootLandingJs() {
        initReveal();
        initCardGlow();
        initFaq();
        initNavbar();
        initTrafficInsights();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootLandingJs);
    } else {
        bootLandingJs();
    }
})();
