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
        } else {
          freibetragInput.required = false;
        }
      });
    });
  }

  if (membershipForm || membershipChildrenForm) {
    document.getElementById("geburtsdatum").addEventListener("change", function () {
      const geburtsdatumValue = new Date(this.value);
      const today = new Date();
      const ageError = document.getElementById("age-error");

      if (isNaN(geburtsdatumValue.getTime())) {
        return;
      }

      let age = today.getFullYear() - geburtsdatumValue.getFullYear();

      const isBirthdayPassedThisYear = today.getMonth() > geburtsdatumValue.getMonth() ||
        (today.getMonth() === geburtsdatumValue.getMonth() && today.getDate() >= geburtsdatumValue.getDate());

      if (!isBirthdayPassedThisYear) {
        age--;
      }

      if (age < 18 && membershipForm) {
        ageError.style.display = "block";
        ageError.innerHTML = "Für Kinder und Jugendliche verwenden Sie bitte das <a href='/mitgliedschaftsantrag-kinder-jugendliche'>Anmeldeformular für Kinder und Jugendliche.</a>.";
      } else if (age >= 18 && membershipChildrenForm) {
        ageError.style.display = "block";
        ageError.innerHTML = "Für Erwachsene verwenden Sie bitte das <a href='/mitgliedschaftsantrag-erwachsene'>Anmeldeformular für Erwachsene.</a>.";
      } else {
        ageError.style.display = "none";
      }
    });
  }
});
