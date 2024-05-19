document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("membership-form");
  if (form) {
    const spende = document.getElementById("spende");
    const spendeDetails = document.getElementById("spende-details");
    spende.addEventListener("change", function () {
      if (spende.checked) {
        spendeDetails.style.display = "block";
        spendeDetails.querySelector("input").setAttribute("required", true);
      } else {
        spendeDetails.style.display = "none";
        spendeDetails.querySelector("input").removeAttribute("required");
      }
    });
  }
});
