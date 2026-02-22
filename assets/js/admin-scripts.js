jQuery(document).ready(function ($) {
  const selectAll = $("#select-all");
  const deleteButton = $("#delete-bulk");
  const deleteButtonSingle = $("#delete-single");
  const exportCsvButton = $("#export-csv");
  const goBackButton = $("#go-back");
  const cancelButton = $("#cancel");
  const deleteReminderButton = $("#delete-reminder");
  const sendEmailButton = $("#send-email");
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
  const thgutscheine = $("#thgutscheine");
  const kontoinhaber = $("#kontoinhaber");
  const iban = $("#iban");
  const bic = $("#bic");
  const bank = $("#bank");
  const sepa = $("#sepa");
  const labelKontoinhaber = $('label[for="kontoinhaber"]');
  const labelIban = $('label[for="iban"]');
  const labelBic = $('label[for="bic"]');
  const labelBank = $('label[for="bank"]');
  const labelSepa = $('label[for="sepa"]');
  const wieErfahren = $("#wie_erfahren");
  const wieErfahrenLabel = $('label[for="wie_erfahren"]');
  const wieErfahrenSonstiges = $("#wie_erfahren_sonstiges");
  const wieErfahrenSonstigesLabel = $('label[for="wie_erfahren_sonstiges"]');
  const schnupperkursArt = $("#schnupperkurs_art");
  const kuendigungseingang = $("#kuendigungseingang");
  const austrittsdatum = $("#austrittsdatum");
  const wiedervorlage = $("#wiedervorlage");
  const wiedervorlageGrund = $("#wiedervorlage-grund");
  const austrittsdatumWarning = $("#austrittsdatum-warning");
  let calculatedAustrittsdatum = null;

  function updateButtons() {
    const selectedCheckboxes = $(".membership-checkbox").filter(":checked");
    deleteButton.prop("disabled", selectedCheckboxes.length === 0);
    exportCsvButton.prop("disabled", selectedCheckboxes.length === 0);
    sendEmailButton.prop("disabled", selectedCheckboxes.length === 0);
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

  function handleRowClick(event) {
    const selection = window.getSelection();
    const $target = $(event.target);
    const $row = $(this);

    // Skip checkboxes and table headers
    if ($target.is('input[type="checkbox"], th')) {
      return;
    }

    const id = $row.data("id");
    const type = $row.data("type");
    if (id) {
      setTimeout(() => {
        // Ignore selections
        if (selection.isCollapsed && !selection.toString()) {
          window.location.href =
            `admin.php?page=avf-${type}-form-page&edit=` + id;
        }
      }, 250);
    }
  }

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

  function updateThgutscheineFields() {
    const isChecked = thgutscheine.is(":checked");
    const paymentFields = [
      kontoinhaber,
      iban,
      bic,
      bank,
      sepa,
      labelKontoinhaber,
      labelIban,
      labelBic,
      labelBank,
      labelSepa,
    ];

    paymentFields.forEach((field) => {
      field.prop("disabled", isChecked);
    });

    if (isChecked) {
      sepa.prop("checked", false);
    }

    validatePaymentFields();
  }

  function validatePaymentFields() {
    const isThgutscheineChecked = thgutscheine.is(":checked");
    const isSepaChecked = sepa.is(":checked");
    const paymentFieldsList = [kontoinhaber, iban, bic, bank];

    // Payment fields are required only if sepa is checked AND thgutscheine is not checked
    const shouldBeRequired = isSepaChecked && !isThgutscheineChecked;

    paymentFieldsList.forEach((field) => {
      field.prop("required", shouldBeRequired);

      // Show warning if field is required and empty
      if (shouldBeRequired && !field.val()) {
        field.closest("div").addClass("field-warning");
      } else {
        field.closest("div").removeClass("field-warning");
      }
    });
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

  function formatLocalDate(date) {
    // Format date as YYYY-MM-DD using local date values (not UTC)
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }

  function calculateResignationDate(kuendigungseingangDate) {
    // Mirror PHP logic: avf_calculate_resignation_date
    const inputDate = new Date(kuendigungseingangDate);

    // Get current quarter (1-4)
    const month = inputDate.getMonth() + 1; // getMonth is 0-indexed
    const currentQuarter = Math.ceil(month / 3);

    // Get quarter end month
    const quarterEndMonth = currentQuarter * 3;
    const year = inputDate.getFullYear();

    // Create quarter end date (last day of quarter month)
    const quarterEnd = new Date(year, quarterEndMonth, 0); // Day 0 = last day of previous month

    // Calculate threshold: quarter end - 6 weeks
    const threshold = new Date(quarterEnd);
    threshold.setDate(threshold.getDate() - 42); // 6 weeks = 42 days

    if (inputDate > threshold) {
      // Too late, use next quarter
      let nextQuarter = currentQuarter + 1;
      let nextYear = year;

      if (nextQuarter > 4) {
        nextQuarter = 1;
        nextYear++;
      }

      const nextQuarterMonth = nextQuarter * 3;
      const nextQuarterEnd = new Date(nextYear, nextQuarterMonth, 0);
      return nextQuarterEnd;
    } else {
      // Use current quarter
      return quarterEnd;
    }
  }

  function updateResignationFields() {
    const kuendigungValue = kuendigungseingang.val();

    if (!kuendigungValue) {
      return; // No date entered
    }

    try {
      // Calculate austrittsdatum
      const resignationDate = calculateResignationDate(kuendigungValue);
      const resignationDateString = formatLocalDate(resignationDate);

      // Store the calculated value
      calculatedAustrittsdatum = resignationDateString;

      austrittsdatum.val(resignationDateString);
      checkAustrittsdatumMismatch();

      // Calculate wiedervorlage date: austrittsdatum - 2 months + 15 days
      const wiedervorlageDate = new Date(resignationDate);
      wiedervorlageDate.setMonth(wiedervorlageDate.getMonth() - 2);
      wiedervorlageDate.setDate(wiedervorlageDate.getDate() + 15);
      const wiedervorlageString = formatLocalDate(wiedervorlageDate);

      wiedervorlage.val(wiedervorlageString);

      // Update wiedervorlage-grund with SEPA deletion info
      const deleteSepaDays = new Date(resignationDate);
      deleteSepaDays.setDate(deleteSepaDays.getDate() + 1);
      const deleteSepaDate = (deleteSepaDays.getMonth() + 1).toString().padStart(2, '0') + '/' +
                             deleteSepaDays.getFullYear();

      const currentGrund = wiedervorlageGrund.val();
      if (!currentGrund.includes('SEPA löschen')) {
        const sepaText = `SEPA löschen ab ${deleteSepaDate}`;
        const newGrund = currentGrund ? `${sepaText}, ${currentGrund}` : sepaText;
        wiedervorlageGrund.val(newGrund);
      }
    } catch (error) {
      console.error('Error calculating resignation date:', error);
    }
  }

  function checkAustrittsdatumMismatch() {
    const currentValue = austrittsdatum.val();
    const hasCalculatedValue = calculatedAustrittsdatum !== null;
    const mismatch = hasCalculatedValue && currentValue !== calculatedAustrittsdatum;

    if (mismatch) {
      austrittsdatumWarning.show();
    } else {
      austrittsdatumWarning.hide();
    }
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

    // Get filters from url params. "ausgetreten" is initially unchecked.
    document.querySelectorAll(".filter-checkbox").forEach((checkbox) => {
      if (checkbox.value == "beitragsbefreit") {
        checkbox.checked = filters.includes("beitragsbefreit");
      } else {
        checkbox.checked =
          filters.length === 0 || filters.includes(checkbox.value);
      }
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

  function get_follow_ups() {
    $("#follow-ups").html(
      '<div class="loading-spinner" style="visibility: visible"></div>',
    );
    jQuery.ajax({
      url: avf_ajax_admin.ajaxurl,
      method: "POST",
      data: {
        action: "avf_get_follow_ups",
        _ajax_nonce: avf_ajax_admin.nonce,
      },
      success: function (response) {
        $("#follow-ups").html(response.data.follow_ups);
      },
    });
  }

  function get_membership_stats() {
    $("#membership-stats").html(
      '<div class="loading-spinner" style="visibility: visible"></div>',
    );
    jQuery.ajax({
      url: avf_ajax_admin.ajaxurl,
      method: "POST",
      data: {
        action: "avf_get_membership_stats",
        _ajax_nonce: avf_ajax_admin.nonce,
      },
      success: function (response) {
        $("#membership-stats").html(response.data.membership_stats);
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
    updateThgutscheineFields();
    updateSchnupperkursFields();

    // Calculate and check austrittsdatum if kuendigungseingang exists
    if (kuendigungseingang.val()) {
      updateResignationFields();
    }

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

    $("#avf-membership-fees-form").on("submit", function (e) {
      e.preventDefault();

      let formData = $(this).serialize();

      if (
        !confirm(
          "Bist Du dir sicher, dass Du die Mitgliedsbeiträge aktualisieren willst?",
        )
      ) {
        return;
      }

      $.post(
        avf_ajax_admin.ajaxurl,
        formData,
        function (response) {
          let className = response.success ? "updated" : "error",
            $message = $(
              `<div class="${className}"><p>${response.data.message || response.data}</p></div>`,
            );

          $("#avf-membership-fees-form").prepend($message);
          setTimeout(() => $message.remove(), 5000);
        },
        "json",
      );
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

    sendEmailButton.on("click", function (e) {
      e.preventDefault();
      const selectedIds = $(".membership-checkbox")
        .filter(":checked")
        .map(function () {
          return $(this).val();
        })
        .get();
      if (selectedIds.length === 0) {
        alert("Bitte wähle mindestens einen Eintrag aus.");
        return;
      }
      let formData = {
        action_type: "send_email",
        action: "avf_membership_action",
        ids: selectedIds,
        _ajax_nonce: avf_ajax_admin.nonce,
      };

      $.post(
        avf_ajax_admin.ajaxurl,
        formData,
        function (response) {
          if (response.success) {
            window.location.href = response.data.mailto;
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

    deleteReminderButton.on("click", function (e) {
      e.preventDefault();
      document.getElementById("wiedervorlage-grund").value = "";
      document.getElementById("wiedervorlage").value = "";
    });

    mitgliedschaftArt.on("change", updateMembershipFields);

    thgutscheine.on("change", updateThgutscheineFields);

    // Validate payment fields when they change
    kontoinhaber.on("input", validatePaymentFields);
    iban.on("input", validatePaymentFields);
    bic.on("input", validatePaymentFields);
    bank.on("input", validatePaymentFields);
    sepa.on("change", validatePaymentFields);

    schnupperkursArt.on("change", updateSchnupperkursFields);

    wieErfahren.on("change", toggleWieErfahrenSonstiges);

    kuendigungseingang.on("change", updateResignationFields);

    austrittsdatum.on("change", checkAustrittsdatumMismatch);

    get_membership_stats();

    get_follow_ups();
  }

  $(document).on("click", ".table-row-link", handleRowClick);

  init();
});
