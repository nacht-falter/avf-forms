document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("membership-form");
  if (form) {
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
});
