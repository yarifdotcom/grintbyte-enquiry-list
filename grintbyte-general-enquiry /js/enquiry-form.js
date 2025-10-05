document.addEventListener('DOMContentLoaded', function () {
  jQuery(function ($) {
    // console.log('loaded gen enquiry form');

    // 🔹 Handle enquiry form submission
    $(document).on('submit', '#gen-enquiry-form', function (e) {
      e.preventDefault();

      const $form = $(this);
      const $button = $form.find('#gen-enquiry-submit');
      const $msgBox = $('#gen-inline-message');

      // Clear previous messages
      $msgBox.removeClass().empty();

      // Basic HTML5 validation
      if (!this.checkValidity()) {
        $form[0].reportValidity();
        return;
      }

      // Disable button and show loading text
      $button.prop('disabled', true).text('Sending...');

      // Collect form data
      const formData = $form.serializeArray();
      formData.push({ name: 'security', value: gen_ajax.nonce }); // localized nonce for security

      $.post({
        url: gen_ajax.url, // localized admin-ajax.php URL
        data: formData,
        dataType: 'json',
        success: function (response) {
          console.log(response);

          if (response.status === 'success') {
            $msgBox.addClass('gen-message gen-success').text(response.message);
            if (response.redirect) {
              setTimeout(() => (window.location.href = response.redirect), 2000);
            }
          } else {
            $msgBox.addClass('gen-message gen-error').text(response.message);
            if (response.redirect) {
              setTimeout(() => (window.location.href = response.redirect), 2000);
            }
          }
        },
        error: function () {
          $msgBox
            .addClass('gen-message gen-error')
            .text('An unexpected error occurred. Please try again.');
        },
        complete: function () {
          // Re-enable button and reset label
          $button.prop('disabled', false).text('Submit Enquiry');
        },
      });
    });
  });
});
