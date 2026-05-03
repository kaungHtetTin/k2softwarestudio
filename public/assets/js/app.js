/**
 * K2 — navbar shadow + scroll reveals (respects prefers-reduced-motion)
 */
(function () {
    'use strict';

    var nav = document.querySelector('.k2-navbar');

    function onScroll() {
        if (!nav) {
            return;
        }
        nav.classList.toggle('k2-navbar--scrolled', window.scrollY > 8);
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var animated = document.querySelectorAll('.k2-animate');

    if (reduced) {
        animated.forEach(function (el) {
            el.classList.add('is-visible');
        });
        return;
    }

    if (!('IntersectionObserver' in window)) {
        animated.forEach(function (el) {
            el.classList.add('is-visible');
        });
        return;
    }

    var io = new IntersectionObserver(
        function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    io.unobserve(entry.target);
                }
            });
        },
        { rootMargin: '0px 0px -8% 0px', threshold: 0.08 }
    );

    animated.forEach(function (el) {
        io.observe(el);
    });
})();
