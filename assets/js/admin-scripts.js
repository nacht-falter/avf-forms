jQuery(document).ready(function ($) {
  $("#avf-membership-admin-form").on("submit", function (e) {
    e.preventDefault();

    let formData = $(this).serialize();

    $.post(avf_ajax_admin.ajaxurl, formData, function (response) {
      let data = JSON.parse(response);
      if (data.status === "success") {
        window.history.back();
      } else {
        alert("Error: " + data.message);
      }
    });
  });

  const checkboxes = $(".membership-checkbox");
  const selectAll = $("#select-all");
  const deleteButton = $("#delete-membership");
  const deleteButtonSingle = $("#delete-membership-single");
  const exportCsvButton = $("#export-csv");
  const goBackButton = $("#go-back");
  const cancelButton = $("#cancel");
  const mitgliedschaftArt = $("#mitgliedschaft_art");
  const vornameEltern = $("#vorname_eltern");
  const labelVornameEltern = $('label[for="' + vornameEltern.attr("id") + '"]');
  const nachnameEltern = $("#nachname_eltern");
  const labelNachnameEltern = $(
    'label[for="' + nachnameEltern.attr("id") + '"]',
  );
  const geschwisterkind = $("#geschwisterkind");
  const starterpaket = $("#starterpaket");
  const spende = $("#spende");
  const spendeMonatlich = $("#spende_monatlich");
  const labelSpendeMonatlich = $(
    'label[for="' + spendeMonatlich.attr("id") + '"]',
  );
  const spendeEinmalig = $("#spende_einmalig");
  const labelSpendeEinmalig = $(
    'label[for="' + spendeEinmalig.attr("id") + '"]',
  );

  function updateButtons() {
    const selectedCheckboxes = checkboxes.filter(":checked");
    deleteButton.prop("disabled", selectedCheckboxes.length === 0);
    exportCsvButton.prop("disabled", selectedCheckboxes.length === 0);
  }

  const chieldFields = [
    vornameEltern,
    labelVornameEltern,
    nachnameEltern,
    labelNachnameEltern,
    geschwisterkind.parent(),
  ];

  const adultFields = [
    starterpaket.parent(),
    spende.parent(),
    spendeMonatlich,
    labelSpendeMonatlich,
    spendeEinmalig,
    labelSpendeEinmalig,
  ];

  function showHideFields(fields, show) {
    fields.forEach((field) => {
      if (show) {
        field.show();
      } else {
        field.hide();
      }
    });
  }

  function updateFields() {
    const isChildOrYouth =
      mitgliedschaftArt.val() === "kind" ||
      mitgliedschaftArt.val() === "jugend";

    vornameEltern.prop("required", isChildOrYouth);
    nachnameEltern.prop("required", isChildOrYouth);

    showHideFields(chieldFields, isChildOrYouth);
    showHideFields(adultFields, !isChildOrYouth);
  }

  function getUrlParams() {
    const urlParams = new URLSearchParams(window.location.search);
    return Object.fromEntries(urlParams.entries());
  }

  function setUrlParams(params) {
    const currentUrlParams = getUrlParams();
    const urlParams = new URLSearchParams();

    if (currentUrlParams.page) {
      urlParams.set("page", currentUrlParams.page);
    }

    Object.entries({ ...currentUrlParams, ...params }).forEach(
      ([key, value]) => {
        if (key !== "page") {
          urlParams.set(key, value);
        }
      },
    );

    window.history.pushState({}, "", `?${urlParams.toString()}`);
  }

  function fetchMembershipData(column, order) {
    jQuery.ajax({
      url: avf_ajax_admin.ajaxurl,
      method: "POST",
      data: {
        action: "avf_fetch_memberships",
        column,
        order,
        _ajax_nonce: avf_ajax_admin.nonce,
      },
      success: function (response) {
        $("#membership-table-body").html(response.data);
        $(".table-header-link")
          .removeClass("asc desc")
          .addClass("inactive")
          .each(function () {
            if ($(this).data("column") === column) {
              $(this).addClass(order).removeClass("inactive");
            }
          });
      },
    });
  }

  document.querySelectorAll(".table-header-link").forEach((a) => {
    a.addEventListener("click", function (e) {
      e.preventDefault();
      let column = this.getAttribute("data-column");
      let order = "asc";
      const currentParams = getUrlParams();
      if (currentParams.column === column && currentParams.order === "asc") {
        order = "desc";
      }

      fetchMembershipData(column, order);
      setUrlParams({ column, order });
    });
  });

  const urlParams = getUrlParams();
  fetchMembershipData(urlParams.column, urlParams.order);

  checkboxes.on("change", function () {
    updateButtons();
  });

  selectAll.on("change", function () {
    checkboxes.prop("checked", this.checked);
    updateButtons();
  });

  updateButtons();
  updateFields();

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

      $.post(avf_ajax_admin.ajaxurl, formData, function (response) {
        let data = JSON.parse(response);
        if (data.status === "success") {
          window.history.back();
        } else {
          alert("Error: " + data.message);
        }
      });
    }
  });

  exportCsvButton.on("click", function (e) {
    e.preventDefault();
    const selectedIds = checkboxes
      .filter(":checked")
      .map(function () {
        return $(this).val();
      })
      .get();
    if (selectedIds.length === 0) {
      alert("Bitte wähle mindestens einen Eintrag zum Exportieren aus.");
      return;
    }
    let formData = {
      action_type: "export_csv",
      action: "avf_membership_action",
      ids: selectedIds,
      _ajax_nonce: avf_ajax_admin.nonce,
    };

    $.post(
      avf_ajax_admin.ajaxurl,
      formData,
      function (response) {
        if (response.success) {
          window.location.href = response.data.download_url;
        } else {
          alert("Error: " + response.data.message);
        }
      },
      "json",
    );
  });

  goBackButton.on("click", function (e) {
    e.preventDefault();
    window.history.back();
  });

  cancelButton.on("click", function (e) {
    e.preventDefault();
    window.history.back();
  });

  mitgliedschaftArt.on("change", updateFields);
});
