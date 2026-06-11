$(function () {

  /* ──────────────────────────────
     Mobile menu toggle
  ────────────────────────────── */
  $('#menu-toggle').on('click', function () {
    const open = $('#mobile-menu').is(':hidden');
    $('#mobile-menu').slideToggle(200);
    $('#icon-menu').toggleClass('hidden', open);
    $('#icon-close').toggleClass('hidden', !open);
  });

  // Close mobile menu on nav link click
  $('#mobile-menu a').on('click', function () {
    $('#mobile-menu').slideUp(200);
    $('#icon-menu').removeClass('hidden');
    $('#icon-close').addClass('hidden');
  });


  /* ──────────────────────────────
     Sticky navbar shadow on scroll
  ────────────────────────────── */
  $(window).on('scroll', function () {
    if ($(this).scrollTop() > 10) {
      $('#navbar').addClass('shadow-md');
    } else {
      $('#navbar').removeClass('shadow-md');
    }
  });


  /* ──────────────────────────────
     Pricing toggle: monthly / annual
  ────────────────────────────── */
  let billingMode = 'annual'; // default matches the active button

  $('#toggle-annual').on('click', function () {
    if (billingMode === 'annual') return;
    billingMode = 'annual';

    // Active styles
    $(this)
      .addClass('bg-g-purple text-white')
      .removeClass('text-g-body hover:bg-g-light');
    $('#toggle-monthly')
      .removeClass('bg-g-purple text-white')
      .addClass('text-g-body hover:bg-g-light');

    // Update prices
    updatePrices('annual');
  });

  $('#toggle-monthly').on('click', function () {
    if (billingMode === 'monthly') return;
    billingMode = 'monthly';

    $(this)
      .addClass('bg-g-purple text-white')
      .removeClass('text-g-body hover:bg-g-light');
    $('#toggle-annual')
      .removeClass('bg-g-purple text-white')
      .addClass('text-g-body hover:bg-g-light');

    updatePrices('monthly');
  });

  function updatePrices(mode) {
    $('.price-professional, .price-team').each(function () {
      const val = $(this).data(mode);
      $(this).text(val);
    });
  }


  /* ──────────────────────────────
     FAQ accordion
  ────────────────────────────── */
  $('.faq-trigger').on('click', function () {
    const $item    = $(this).closest('.faq-item');
    const $answer  = $item.find('.faq-answer');
    const $icon    = $(this).find('.faq-icon');
    const isOpen   = !$answer.hasClass('hidden');

    if (isOpen) {
      $answer.slideUp(200).addClass('hidden');
      $icon.addClass('-rotate-90').removeClass('text-g-navy').addClass('text-g-muted');
      $item.removeClass('border-g-navy').addClass('border-g-border/30');
    } else {
      $answer.slideDown(200).removeClass('hidden');
      $icon.removeClass('-rotate-90').addClass('text-g-navy').removeClass('text-g-muted');
      $item.addClass('border-g-navy').removeClass('border-g-border/30');
    }
  });


  /* ──────────────────────────────
     "Watch demo" button — scroll to hero / open modal placeholder
  ────────────────────────────── */
  $('#watch-demo').on('click', function () {
    // Placeholder: scroll back to top for now
    $('html, body').animate({ scrollTop: 0 }, 400);
  });


  /* ──────────────────────────────
     Smooth scroll for anchor links
  ────────────────────────────── */
  $('a[href^="#"]').on('click', function (e) {
    const target = $(this.hash);
    if (!target.length) return;
    e.preventDefault();
    const navH = $('#navbar').outerHeight() || 80;
    $('html, body').animate({ scrollTop: target.offset().top - navH }, 500);
  });


  /* ──────────────────────────────
     Animate numbers in Stats section
     (triggers once when section enters viewport)
  ────────────────────────────── */
  const statsAnimated = { done: false };

  function animateStats() {
    if (statsAnimated.done) return;
    statsAnimated.done = true;

    // Nothing to count here since values are baked in HTML;
    // add a simple fade-in pop effect instead
    $('.stats-value').each(function (i) {
      const $el = $(this);
      setTimeout(function () {
        $el.addClass('scale-110');
        setTimeout(function () { $el.removeClass('scale-110'); }, 200);
      }, i * 150);
    });
  }

  // Intersection Observer (with jQuery fallback)
  if ('IntersectionObserver' in window) {
    const statsEl = document.querySelector('#stats-section');
    if (statsEl) {
      const obs = new IntersectionObserver(function (entries) {
        if (entries[0].isIntersecting) {
          animateStats();
          obs.disconnect();
        }
      }, { threshold: 0.3 });
      obs.observe(statsEl);
    }
  }

});
