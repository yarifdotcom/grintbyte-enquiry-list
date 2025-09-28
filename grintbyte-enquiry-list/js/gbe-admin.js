jQuery(document).ready(function ($) {
  // button Preview
  console.log('gbe admin js loaded');
  $(document).on('click', '.gbe-preview-link', function (e) {
    console.log('preview clicked');
    e.preventDefault();

    let enquiryId = $(this).data('id');

    $.ajax({
      url: ajaxurl,
      type: 'POST',
      dataType: 'json',
      data: {
        action: 'gbe_preview_enquiry',
        id: enquiryId,
        _ajax_nonce: gbe_admin.nonce,
      },
      success: function (response) {
        if (response.success) {
          $('#gbe-preview-modal')
            .html(response.data.html) // isi konten modal
            .fadeIn(); // tampil overlay
        } else {
          alert(response.data || 'Error.');
        }
      },
    });
  });

  // close modal
  $(document).on('click', '#gbe-preview-modal .gbe-close', function () {
    $('#gbe-preview-modal').fadeOut();
  });

  // klik luar konten → close juga
  $(document).on('click', '#gbe-preview-modal', function (e) {
    if ($(e.target).is('#gbe-preview-modal')) {
      $(this).fadeOut();
    }
  });
});
