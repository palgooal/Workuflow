// ============================================================
// SECTION: Billing Toggle
// Handles: monthly/yearly switch and price updates
// ============================================================

$(document).ready(function () {
  const prices = {
    monthly: { pro: "17", team: "45" },
    yearly:  { pro: "13", team: "34" },
  };

  $(".pricing-billing-toggle").on("click", function () {
    const mode = $(this).data("billing");

    $(".pricing-billing-toggle")
      .removeClass("bg-white text-g-purple shadow")
      .addClass("text-white/80");

    $(this)
      .removeClass("text-white/80")
      .addClass("bg-white text-g-purple shadow");

    $(".pricing-price").each(function () {
      const plan = $(this).data("plan");
      if (plan && prices[mode][plan] !== undefined) {
        $(this).text(prices[mode][plan]);
      }
    });
  });
});


// ============================================================
// SECTION: FAQ Accordion
// Handles: expand/collapse with exclusive open (one at a time)
// ============================================================

$(document).ready(function () {
  function closeItem($item) {
    $item.find(".pricing-faq-answer").slideUp(180, function () { $(this).addClass("hidden"); });
    $item
      .removeClass("border-2 border-g-navy hover:shadow-md")
      .addClass("border border-g-border");
    $item.attr("data-faq-open", "false");
    $item.find(".pricing-faq-icon")
      .removeClass("bg-g-navy/10")
      .addClass("bg-g-light2");
    $item.find(".pricing-faq-icon-minus").addClass("hidden");
    $item.find(".pricing-faq-icon-plus").removeClass("hidden");
  }

  function openItem($item) {
    $item.find(".pricing-faq-answer").hide().removeClass("hidden").slideDown(180);
    $item
      .removeClass("border border-g-border")
      .addClass("border-2 border-g-navy");
    $item.attr("data-faq-open", "true");
    $item.find(".pricing-faq-icon")
      .removeClass("bg-g-light2")
      .addClass("bg-g-navy/10");
    $item.find(".pricing-faq-icon-minus").removeClass("hidden");
    $item.find(".pricing-faq-icon-plus").addClass("hidden");
  }

  $(".pricing-faq-trigger").on("click", function () {
    const $item  = $(this).closest(".pricing-faq-item");
    const isOpen = $item.attr("data-faq-open") === "true";

    // close all others first
    $(".pricing-faq-item[data-faq-open='true']").not($item).each(function () {
      closeItem($(this));
    });

    if (isOpen) {
      closeItem($item);
    } else {
      openItem($item);
    }
  });
});
