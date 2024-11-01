jQuery(document).ready(function ($) {
  function showOrHideField(hide = true) {
    const urlsArea = $("#onplat_include_urls").closest("tr");

    if (hide) {
      $(urlsArea).hide();
    } else {
      $(urlsArea).show();
    }
  }

  function uploadMedia(fieldId) {
    mediaUploader = wp.media.frames.file_frame = wp.media({
      title: "Select an Image",
      button: {
        text: "Select Image",
      },
      multiple: false,
    });

    mediaUploader.on("select", function () {
      attachment = mediaUploader.state().get("selection").first().toJSON();
      $(fieldId).val(attachment.url);
    });

    mediaUploader.open();
  }

  if ($(".radio-group").data("selected") === "selected") {
    showOrHideField(false);
  } else {
    showOrHideField(true);
  }

  $("#open-upload-btn").on("click", function (e) {
    e.preventDefault();
    uploadMedia("#onplat_open_image_url");
  });

  $("#close-upload-btn").on("click", function (e) {
    e.preventDefault();
    uploadMedia("#onplat_close_image_url");
  });

  $('.radio-group input[type="radio"]').click(function () {
    const inputValue = $(this).attr("value");

    if (inputValue === "selected") {
      showOrHideField(false);
    } else {
      showOrHideField(true);
    }
  });
});
