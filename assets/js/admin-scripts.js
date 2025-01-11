jQuery(document).ready(function ($) {
  const selectAll = $("#select-all");
  const deleteButton = $("#delete-bulk");
  const deleteButtonSingle = $("#delete-single");
  const exportCsvButton = $("#export-csv");
  const goBackButton = $("#go-back");
  const cancelButton = $("#cancel");
  const mitgliedschaftArt = $("#mitgliedschaft_art");
  const vornameEltern = $("#vorname_eltern");
  const nachnameEltern = $("#nachname_eltern");
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
  const wieErfahren = $("#wie_erfahren");
  const wieErfahrenLabel = $('label[for="wie_erfahren"]');
  const wieErfahrenSonstiges = $("#wie_erfahren_sonstiges");
  const wieErfahrenSonstigesLabel = $('label[for="wie_erfahren_sonstiges"]');
  const schnupperkursArt = $("#schnupperkurs_art");

  function updateButtons() {
    const selectedCheckboxes = $(".membership-checkbox").filter(":checked");
    deleteButton.prop("disabled", selectedCheckboxes.length === 0);
    exportCsvButton.prop("disabled", selectedCheckboxes.length === 0);
  }

  const childFields = [
    vornameEltern.parent().parent(),
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

  const wieErfahrenFields = [wieErfahren, wieErfahrenLabel];

  function showHideFields(fields, show) {
    fields.forEach((field) => field.toggle(show));
  }

  function updateMembershipFields() {
    const isChildOrYouth =
      mitgliedschaftArt.val() === "kind" ||
      mitgliedschaftArt.val() === "jugend";

    vornameEltern.prop("required", isChildOrYouth);
    nachnameEltern.prop("required", isChildOrYouth);

    showHideFields(childFields, isChildOrYouth);
    showHideFields(adultFields, !isChildOrYouth);
  }

  function updateSchnupperkursFields() {
    const isChild = schnupperkursArt.val() === "kind";

    showHideFields(wieErfahrenFields, !isChild);
    toggleWieErfahrenSonstiges();
  }

  function toggleWieErfahrenSonstiges() {
    const isSonstiges = wieErfahren.val() === "sonstiges";
    const isChild = schnupperkursArt.val() === "kind";
    wieErfahrenSonstiges.toggle(isSonstiges && !isChild);
    wieErfahrenSonstigesLabel.toggle(isSonstiges && !isChild);
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
  }

  function fetchData(
    column,
    order,
    filters = [],
    search = "",
    dataType = "memberships",
  ) {
    const tableBodyId =
      dataType === "memberships"
        ? "#membership-table-body"
        : "#schnupperkurs-table-body";

    $(tableBodyId).html(
      '<td class="loading-spinner" style="visibility: visible"></td>',
    );

    jQuery.ajax({
      url: avf_ajax_admin.ajaxurl,
      method: "POST",
      data: {
        action:
          dataType === "memberships"
            ? "avf_fetch_memberships"
            : "avf_fetch_schnupperkurse",
        column,
        order,
        filters,
        search,
        _ajax_nonce: avf_ajax_admin.nonce,
      },
      success: function (response) {
        $(tableBodyId).html(response.data.html);

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

        $("#record-count").html(response.data.count);
      },
    });
  }

  function get_total_membership_fees() {
    jQuery.ajax({
      url: avf_ajax_admin.ajaxurl,
      method: "POST",
      data: {
        action: "avf_get_total_membership_fees",
        _ajax_nonce: avf_ajax_admin.nonce,
      },
      success: function (response) {
        $("#total-beitraege").html(response.data);
      },
    });
  }

  function get_membership_stats() {
    jQuery.ajax({
      url: avf_ajax_admin.ajaxurl,
      method: "POST",
      data: {
      action: "avf_get_membership_stats",
      _ajax_nonce: avf_ajax_admin.nonce,
      },
      success: function (response) {
        $("#membership_stats_by_type").html(response.data.membership_stats_by_type)
        $("#new_members_resignations").html(response.data.yearly_membership_stats)
      },
    });
  }

  function init() {
    setCheckboxStatesFromUrl();
    setSearchFromUrl();

    const isSchnupperkursPage = $("#schnupperkurs-form").length > 0;

    let dataType = "memberships";
    if (isSchnupperkursPage) {
      dataType = "schnupperkurse";
    }

    const urlParams = getCurrentUrlParams();
    fetchData(
      urlParams.column,
      urlParams.order,
      urlParams.filters ? urlParams.filters.split(",") : getSelectedFilters(),
      urlParams.search || "",
      dataType,
    );

    updateButtons();
    updateMembershipFields();
    updateSchnupperkursFields();

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
        fetchData(column, order, filters, currentParams.search || "", dataType);
        updateUrlParams({ column, order, filters });
      });
    });

    document.querySelectorAll(".filter-checkbox").forEach((checkbox) => {
      checkbox.addEventListener("change", function () {
        const filters = getSelectedFilters();
        const urlParams = getCurrentUrlParams();
        fetchData(
          urlParams.column,
          urlParams.order,
          filters,
          urlParams.search || "",
          dataType,
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
        fetchData(
          urlParams.column,
          urlParams.order,
          filters,
          this.value,
          dataType,
        );
        updateUrlParams({ ...urlParams, search: this.value });
      }, 300),
    );

    selectAll.on("change", function () {
      $(".membership-checkbox").prop("checked", this.checked);
      updateButtons();
    });

    $("#avf-membership-admin-form").on("submit", function (e) {
      e.preventDefault();

      let formData = $(this).serialize();

      $.post(avf_ajax_admin.ajaxurl, formData, function (response) {
        let data = JSON.parse(response);
        if (data.status === "success") {
          if (data.redirect_url) {
            window.location.href = data.redirect_url;
          } else {
            window.history.back();
          }
        } else {
          alert("Error: " + data.message);
        }
      });
    });

    $("#avf-schnupperkurs-admin-form").on("submit", function (e) {
      e.preventDefault();

      let formData = $(this).serialize();

      $.post(avf_ajax_admin.ajaxurl, formData, function (response) {
        let data = JSON.parse(response);
        if (data.status === "success") {
          if (data.redirect_url) {
            window.location.href = data.redirect_url;
          } else {
            window.history.back();
          }
        } else {
          alert("Error: " + data.message);
        }
      });
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

      if (confirm("Möchtest Du die ausgewählten Einträge wirklich löschen?")) {
        let dataType = isSchnupperkursPage ? "schnupperkurs" : "membership";
        let formData = {
          action_type: "bulk_delete",
          action: `avf_${dataType}_action`,
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
      if (confirm("Möchtest Du den Eintrag wirklich löschen?")) {
        let dataType = $(this).data("type");
        let formData = {
          action_type: "delete",
          action: `avf_${dataType}_action`,
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

    mitgliedschaftArt.on("change", updateMembershipFields);

    schnupperkursArt.on("change", updateSchnupperkursFields);

    wieErfahren.on("change", toggleWieErfahrenSonstiges);

    get_total_membership_fees();
    get_membership_stats();
  }

  init();
});
