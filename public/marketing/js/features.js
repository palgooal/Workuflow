// ============================================================
// SECTION: Feature Sections — Scroll Reveal
// Handles: animated entrance for each feature block on scroll
// ============================================================

$(document).ready(function () {

  // ── Intersection Observer — fade + slide up on enter ──────
  var observerOptions = {
    threshold: 0.12,
    rootMargin: '0px 0px -60px 0px'
  };

  if ('IntersectionObserver' in window) {
    var revealObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          $(entry.target).addClass('revealed');
          revealObserver.unobserve(entry.target);
        }
      });
    }, observerOptions);

    // Observe all feature sections and anchor badges
    $('section[id^="feature-"] .grid > *').each(function () {
      $(this).addClass('reveal-on-scroll');
      revealObserver.observe(this);
    });

    // Observe anchor badge cards with staggered delay
    $('.grid.grid-cols-2 a, .grid.grid-cols-6 a').each(function (index) {
      $(this).addClass('reveal-on-scroll').css('transition-delay', (index * 80) + 'ms');
      revealObserver.observe(this);
    });
  } else {
    // Fallback: show everything immediately
    $('.reveal-on-scroll').addClass('revealed');
  }

});


// ============================================================
// SECTION: Active Nav Anchor Highlight
// Handles: highlights anchor badge when its section is in view
// ============================================================

$(document).ready(function () {

  var featureSections = [
    '#feature-crm',
    '#feature-projects',
    '#feature-quotes',
    '#feature-invoices',
    '#feature-expenses',
    '#feature-analytics'
  ];

  var sectionObserver = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        var id = '#' + entry.target.id;
        var anchor = $('a[href="' + id + '"]');
        // Add active style
        $('a[href^="#feature-"]')
          .removeClass('ring-2 ring-g-green shadow-lg')
          .addClass('shadow-[0px_10px_15px_-3px_rgba(0,0,0,0.1),0px_4px_6px_-4px_rgba(0,0,0,0.1)]');
        anchor.addClass('ring-2 ring-g-green shadow-lg');
      }
    });
  }, { threshold: 0.4 });

  featureSections.forEach(function (sel) {
    var el = document.querySelector(sel);
    if (el) sectionObserver.observe(el);
  });

});
