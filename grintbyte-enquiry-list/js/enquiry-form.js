document.addEventListener('DOMContentLoaded', function () {
  jQuery(function ($) {
    // console.log('loaded enquiry-form.js');

    // Function to initialize Select2
    function initializeSelect2() {
      const $select = $('#gbe-variation-id');
      if ($select.length && !$select.hasClass('select2-hidden-accessible')) {
        $select.select2({
          placeholder: 'Select variations...',
          allowClear: true,
          width: '100%',
          templateSelection: function (data) {
            if (data.text.length > 50) {
              return data.text.substring(0, 50) + '...';
            }
            return data.text;
          },
          maximumSelectionLength: 10,
          minimumResultsForSearch: 10,
        });
      }
    }

    // Initialize on DOM ready
    initializeSelect2();

    // Re-initialize when form is loaded dynamically
    const observer = new MutationObserver(function (mutations) {
      mutations.forEach(function (mutation) {
        if (mutation.type === 'childList') {
          mutation.addedNodes.forEach(function (node) {
            if (
              node.nodeType === 1 &&
              (node.id === 'gbe-variation-id' ||
                (node.querySelector && node.querySelector('#gbe-variation-id')))
            ) {
              setTimeout(initializeSelect2, 100);
            }
          });
        }
      });
    });

    // Observe body for dynamic content
    observer.observe(document.body, {
      childList: true,
      subtree: true,
    });

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
          console.log(response);
          console.log(formData);

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
