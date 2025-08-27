document.addEventListener('DOMContentLoaded', function () {
  jQuery(function ($) {
    $('#gbe-enquiry-form').on('submit', function (e) {
      e.preventDefault();

      const formData = jQuery(this).serializeArray();
      formData.push({ name: 'action', value: 'gbe_submit_enquiry' });
      formData.push({ name: 'security', value: gbe_ajax.nonce });

      $.post({
        url: gbe_ajax.url,
        data: formData,
        dataType: 'json',
        success: function (response) {
          console.log(response);
          var $msgBox = $('#gbe-inline-message');
          $msgBox.removeClass().empty();

          if (response.status === 'success') {
            $msgBox.addClass('gbe-message gbe-success').text(response.message);

            setTimeout(function () {
              window.location.href = response.redirect;
            }, 3000);
          } else {
            $msgBox.addClass('gbe-message gbe-error').text(response.message);

            if (response.redirect) {
              setTimeout(function () {
                window.location.href = response.redirect;
              }, 2000);
            }
          }
        },
      });
    });
  });
});
