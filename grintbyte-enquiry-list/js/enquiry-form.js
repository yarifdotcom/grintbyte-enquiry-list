document.addEventListener('DOMContentLoaded', function () {
  jQuery(function ($) {
    $(document).on('submit', '#gbe-enquiry-form', function (e) {
      e.preventDefault();

      const $form = $(this);
      const $button = $form.find('#gbe-enquiry-submit');
      const $msgBox = $('#gbe-inline-message');

      // Reset messages
      $msgBox.removeClass().empty();

      // Basic client-side validation
      if (!this.checkValidity()) {
        $form[0].reportValidity();
        return;
      }

      // Disable button & show loading text
      $button.prop('disabled', true).text('Sending...');

      // Collect form data
      const formData = $form.serializeArray();
      formData.push({ name: 'security', value: gbe_ajax.nonce });

      $.post({
        url: gbe_ajax.url,
        data: formData,
        dataType: 'json',
        success: function (response) {
          // console.log(response);

          if (response.status === 'success') {
            $msgBox.addClass('gbe-message gbe-success').text(response.message);
            setTimeout(() => (window.location.href = response.redirect), 2000);
          } else {
            $msgBox.addClass('gbe-message gbe-error').text(response.message);
            if (response.redirect) {
              setTimeout(() => (window.location.href = response.redirect), 2000);
            }
          }
        },
        error: function () {
          $msgBox
            .addClass('gbe-message gbe-error')
            .text('An unexpected error occurred. Please try again.');
        },
        complete: function () {
          // Reset button
          $button.prop('disabled', false).text('Submit Enquiry');
        },
      });
    });
  });
});
