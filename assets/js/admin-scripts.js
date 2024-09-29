jQuery(document).ready(function ($) {
  $("#avf-membership-form").on("submit", function (e) {
    e.preventDefault();

    let formData = $(this).serialize();

    $.post(avf_ajax_admin.ajaxurl, formData, function (response) {
      let data = JSON.parse(response);
      if (data.status === "success") {
        window.location.href = "admin.php?page=avf-membership-admin"; // Redirect after successful operation
      } else {
        alert("Error: " + data.message);
      }
    });
  });

  const checkboxes = $(".membership-checkbox");
  const selectAll = $("#select-all");
  const deleteButton = $("#delete-membership");
  const deleteButtonSingle = $("#delete-membership-single");
  const goBackButton = $("#go-back");

  function updateButtons() {
    const selectedCheckboxes = checkboxes.filter(":checked");
    deleteButton.prop("disabled", selectedCheckboxes.length === 0);
  }

  checkboxes.on("change", function () {
    updateButtons();
  });

  selectAll.on("change", function () {
    checkboxes.prop("checked", this.checked);
    updateButtons();
  });

  updateButtons();

  deleteButton.on("click", function (e) {
    e.preventDefault();

    const selectedIds = checkboxes
      .filter(":checked")
      .map(function () {
        return $(this).val();
      })
      .get();

    if (selectedIds.length === 0) {
      alert("Bitte wähle mindestens einen Eintrag zum Löschen aus.");
      return;
    }

    if (
      confirm("Möchtest Du die ausgewählten Mitgliedschaften wirklich löschen?")
    ) {
      let formData = {
        action_type: "bulk_delete",
        action: "avf_membership_action",
        ids: selectedIds,
        _ajax_nonce: avf_ajax_admin.nonce,
      };

      $.post(avf_ajax_admin.ajaxurl, formData, function (response) {
        let data = JSON.parse(response);
        if (data.status === "success") {
          location.reload();
        } else {
          alert("Error: " + data.message);
        }
      });
    }
  });

  deleteButtonSingle.on("click", function (e) {
    e.preventDefault();
    if (confirm("Möchtest Du die Mitgliedschaft wirklich löschen?")) {
      let formData = {
        action_type: "delete",
        action: "avf_membership_action",
        id: $(this).data("id"),
        _ajax_nonce: avf_ajax_admin.nonce,
      };

      console.log(formData);
      $.post(avf_ajax_admin.ajaxurl, formData, function (response) {
        let data = JSON.parse(response);
        if (data.status === "success") {
          window.location.href = "admin.php?page=avf-membership-admin";
        } else {
          alert("Error: " + data.message);
        }
      });
    }
  });

  goBackButton.on("click", function (e) {
    e.preventDefault();
    window.location.href = "admin.php?page=avf-membership-admin";
  });
});
