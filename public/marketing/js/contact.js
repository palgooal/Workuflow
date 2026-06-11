// ============================================================
// SECTION: Account Type Toggle
// Handles: selecting active account type button
// ============================================================

$(document).ready(function () {
  $('.account-btn').on('click', function () {
    $('.account-btn')
      .removeClass('bg-g-green border-g-green text-white active-account')
      .addClass('border-g-border text-g-muted');

    $(this)
      .removeClass('border-g-border text-g-muted')
      .addClass('bg-g-green border-g-green text-white active-account');
  });
});


// ============================================================
// SECTION: Textarea Character Counter
// Handles: live character count display
// ============================================================

$(document).ready(function () {
  var maxLen = 1000;
  var $counter = $('#char-count');
  var $textarea = $('#field-message');

  $textarea.on('input', function () {
    var len = $(this).val().length;
    $counter.text(maxLen + ' / ' + len);

    if (len >= maxLen * 0.9) {
      $counter.addClass('text-g-orange').removeClass('text-g-muted');
    } else {
      $counter.removeClass('text-g-orange').addClass('text-g-muted');
    }
  });
});


// ============================================================
// SECTION: Contact Form Submission
// Handles: basic validation and success state
// ============================================================

$(document).ready(function () {
  $('#contact-form').on('submit', function (e) {
    e.preventDefault();

    var name    = $('#field-name').val().trim();
    var email   = $('#field-email').val().trim();
    var subject = $('#field-subject').val();
    var message = $('#field-message').val().trim();

    // Simple validation
    if (!name || !email || !subject || !message) {
      // Highlight empty required fields
      [
        { id: '#field-name',    val: name },
        { id: '#field-email',   val: email },
        { id: '#field-subject', val: subject },
        { id: '#field-message', val: message },
      ].forEach(function (f) {
        if (!f.val) {
          $(f.id).addClass('border-red-400').one('input change', function () {
            $(this).removeClass('border-red-400');
          });
        }
      });
      return;
    }

    // Show success (replace with real API call when ready)
    $('#contact-form button[type=submit]').prop('disabled', true).addClass('opacity-60');
    $('#form-success').removeClass('hidden');
    $('html, body').animate({ scrollTop: $('#form-success').offset().top - 120 }, 400);
  });
});
