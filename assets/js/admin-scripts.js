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
    const selectedCheckboxes = $(".membership-checkbox").filter(":checked");
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
  function getCurrentUrlParams() {
    const urlParams = new URLSearchParams(window.location.search);
    return Object.fromEntries(urlParams.entries());
  }

  function updateUrlParams(params) {
    const currentUrlParams = getCurrentUrlParams();
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

  function getSelectedFilters() {
    return Array.from(document.querySelectorAll(".filter-checkbox"))
      .filter((checkbox) => checkbox.checked)
      .map((checkbox) => checkbox.value);
  }

  function setCheckboxStatesFromUrl() {
    const urlParams = getCurrentUrlParams();
    const filters = urlParams.filters ? urlParams.filters.split(",") : [];

    document.querySelectorAll(".filter-checkbox").forEach((checkbox) => {
      checkbox.checked =
        filters.length === 0 || filters.includes(checkbox.value);
    });
  }

  function setSearchFromUrl() {
    const search = getCurrentUrlParams().search || "";
    $("#search").val(search);
  };

  function fetchMembershipData(column, order, filters = [], search = "") {
    $("#membership-table-body").html(
      '<td class="loading-spinner" style="visibility: visible"></td>',
    );

    jQuery.ajax({
      url: avf_ajax_admin.ajaxurl,
      method: "POST",
      data: {
        action: "avf_fetch_memberships",
        column,
        order,
        filters,
        search,
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
        $(".membership-checkbox").on("change", function () {
          updateButtons();
        });
      },
    });
  }

  function init() {
    setCheckboxStatesFromUrl();
    setSearchFromUrl();

    const urlParams = getCurrentUrlParams();
    fetchMembershipData(
      urlParams.column,
      urlParams.order,
      urlParams.filters ? urlParams.filters.split(",") : getSelectedFilters(),
      urlParams.search || "",
    );

    updateButtons();
    updateFields();

    // Event listeners for sorting, filtering, searching and bulk actions
    document.querySelectorAll(".table-header-link").forEach((a) => {
      a.addEventListener("click", function (e) {
        e.preventDefault();
        const column = this.getAttribute("data-column");
        const currentParams = getCurrentUrlParams();
        let order =
          currentParams.column === column && currentParams.order === "asc"
            ? "desc"
            : "asc";

        const filters = getSelectedFilters();
        fetchMembershipData(column, order, filters, currentParams.search || "");
        updateUrlParams({ column, order, filters });
      });
    });

    document.querySelectorAll(".filter-checkbox").forEach((checkbox) => {
      checkbox.addEventListener("change", function () {
        const filters = getSelectedFilters();
        const urlParams = getCurrentUrlParams();
        fetchMembershipData(
          urlParams.column,
          urlParams.order,
          filters,
          urlParams.search || "",
        );
        updateUrlParams({ ...urlParams, filters });
      });
    });

    // Debounce function for search input delay
    function debounce(func, delay) {
      let debounceTimer;
      return function (...args) {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => func.apply(this, args), delay);
      };
    }

    $("#search").on(
      "input",
      debounce(function () {
        const filters = getSelectedFilters();
        const urlParams = getCurrentUrlParams();
        fetchMembershipData(
          urlParams.column,
          urlParams.order,
          filters,
          this.value,
        );
        updateUrlParams({ ...urlParams, search: this.value });
      }, 300),
    );

    selectAll.on("change", function () {
      $(".membership-checkbox").prop("checked", this.checked);
      updateButtons();
    });

    deleteButton.on("click", function (e) {
      e.preventDefault();

      const selectedIds = $(".membership-checkbox")
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
        confirm(
          "Möchtest Du die ausgewählten Mitgliedschaften wirklich löschen?",
        )
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
      const selectedIds = $(".membership-checkbox")
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
  }

  init();
});
