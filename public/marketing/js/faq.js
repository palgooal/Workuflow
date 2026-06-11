// ============================================================
// SECTION: FAQ Accordion
// Handles: one-open-per-section, slide animation, icon rotation
// ============================================================

$(document).ready(function () {
  $(".faq-page-trigger").on("click", function () {
    const $item    = $(this).closest(".faq-page-item");
    const $section = $item.closest(".faq-page-section");
    const $answer  = $item.find(".faq-page-answer");
    const $icon    = $item.find(".faq-page-icon");
    const isOpen   = !$answer.hasClass("hidden");

    // Close all other open items in this section
    $section.find(".faq-page-item").each(function () {
      const $other       = $(this);
      const $otherAnswer = $other.find(".faq-page-answer");
      const $otherIcon   = $other.find(".faq-page-icon");

      if (!$otherAnswer.hasClass("hidden")) {
        $otherAnswer.slideUp(200, function () { $(this).addClass("hidden"); });
        $other.removeClass("bg-g-mint-soft border-s-4 border-g-green-lt").addClass("bg-white");
        $otherIcon.removeClass("rotate-180");
      }
    });

    // Toggle the clicked item
    if (isOpen) return; // already closed above

    $answer.hide().removeClass("hidden").slideDown(200);
    $item.addClass("bg-g-mint-soft border-s-4 border-g-green-lt").removeClass("bg-white");
    $icon.addClass("rotate-180");
  });
});


// ============================================================
// SECTION: Sidebar Scroll Spy
// Handles: active nav item updates as user scrolls through sections
// ============================================================

$(document).ready(function () {
  const $navLinks = $(".faq-sidebar-link");

  function setActive(id) {
    $navLinks.each(function () {
      const isActive = $(this).attr("href") === "#" + id;
      $(this)
        .toggleClass("border-s-4 border-g-green-lt bg-white font-bold text-g-purple shadow-sm", isActive)
        .toggleClass("text-g-body hover:bg-white hover:text-g-purple", !isActive);
    });
  }

  const observer = new IntersectionObserver(
    function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          setActive(entry.target.id);
        }
      });
    },
    { rootMargin: "-30% 0px -60% 0px", threshold: 0 }
  );

  $(".faq-page-section").each(function () {
    observer.observe(this);
  });
});


// ============================================================
// SECTION: FAQ Search
// Handles: filtering questions from hero search input
// ============================================================

$(document).ready(function () {
  $("#faq-search").on("input", function () {
    const query = $(this).val().trim().toLowerCase();

    $(".faq-page-item").each(function () {
      const text = $(this).text().toLowerCase();
      $(this).toggleClass("hidden", query.length > 0 && !text.includes(query));
    });

    $(".faq-page-section").each(function () {
      const hasVisibleItems = $(this).find(".faq-page-item:not(.hidden)").length > 0;
      $(this).toggleClass("hidden", !hasVisibleItems);
    });
  });
});
