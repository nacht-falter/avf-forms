document.addEventListener("DOMContentLoaded", function () {
  const membershipForm = document.getElementById("membership-form");
  const membershipChildrenForm = document.getElementById("membership-children-form");

  if (membershipForm) {
    const spende = document.getElementById("spende");
    const spendeDetails = document.getElementById("spende-details");
    const freibetrag = document.getElementById("spende-freibetrag");
    const freibetragInput = document.getElementById("freibetrag-input");

    spende.addEventListener("change", function () {
      if (spende.checked) {
        const elements = spendeDetails.querySelectorAll("input, label");
        for (let element of elements) {
          element.disabled = false;
          element.classList.remove("disabled");
        }
      } else {
        const elements = spendeDetails.querySelectorAll("input, label");
        for (let element of elements) {
          element.disabled = true;
          element.checked = false;
          element.classList.add("disabled");
        }
      }
    });

    document.querySelectorAll('input[name="spende"]').forEach((radio) => {
      radio.addEventListener("change", function () {
        if (freibetrag.checked) {
          freibetragInput.required = true;
          freibetragInput.disabled = false;
          freibetragInput.focus();
        } else {
          freibetragInput.required = false;
          freibetragInput.disabled = true;
        }
      });
    });
  }
  if (membershipForm || membershipChildrenForm) {
    const geburtsdatum = document.getElementById("geburtsdatum");
    const ageError = document.getElementById("age-error");
    const submitButton = document.getElementById("submit-btn");

    const handleDateChange = function () {
      const geburtsdatumValue = new Date(this.value);
      const today = new Date();

      if (isNaN(geburtsdatumValue.getTime())) {
        return; // Invalid date, exit early
      }

      let age = today.getFullYear() - geburtsdatumValue.getFullYear();
      const isBirthdayPassedThisYear = today.getMonth() > geburtsdatumValue.getMonth() ||
        (today.getMonth() === geburtsdatumValue.getMonth() && today.getDate() >= geburtsdatumValue.getDate());

      if (!isBirthdayPassedThisYear) {
        age--;
      }

      if (membershipForm) {
        if (age < 18) {
          ageError.style.display = "block";
          ageError.innerHTML = "Für Kinder und Jugendliche verwende bitte das <a href='/mitgliedschaftsantrag-kinder-jugendliche'>Anmeldeformular für Kinder und Jugendliche.</a>.";
          submitButton.disabled = true;
        } else {
          ageError.style.display = "none";
          submitButton.disabled = false;
        }
      } else if (membershipChildrenForm) {
        if (age >= 18) {
          ageError.style.display = "block";
          ageError.innerHTML = "Für Erwachsene verwende bitte das <a href='/mitgliedschaftsantrag-erwachsene'>Anmeldeformular für Erwachsene.</a>.";
          submitButton.disabled = true;
        } else {
          ageError.style.display = "none";
          submitButton.disabled = false;
        }
      }
    };

    if (!geburtsdatum.dataset.listenerAttached) {
      geburtsdatum.addEventListener("change", handleDateChange);
      geburtsdatum.dataset.listenerAttached = "true";
    }
  }
});
