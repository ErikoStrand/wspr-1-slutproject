function openModal(dialog) {
  dialog = document.getElementById(dialog);
  console.log("Opened Modal");
  dialog.showModal();
}
function closeModal(dialog) {
  dialog = document.getElementById(dialog);
  console.log("Closed Modal");
  removeQueryParamAndReload("followers");
  removeQueryParamAndReload("following");
  dialog.close();
}
document.addEventListener("keydown", function (event) {
  if (event.key == "Escape") {
    removeQueryParamAndReload("followers");
    removeQueryParamAndReload("following");
    console.log("pressed espece.");
  }
});
function doesURLContain(string) {
  return window.location.href.includes(string);
}

if (doesURLContain("followers")) {
  openModal("emptyDialog");
}

if (doesURLContain("following")) {
  openModal("emptyDialog");
}

function removeQueryParamAndReload(param) {
  // Create a URL object based on the current location
  const url = new URL(window.location.href);

  // Use URLSearchParams to work with the query string
  const params = new URLSearchParams(url.search);

  // Check if the parameter exists and delete it
  if (params.has(param)) {
    params.delete(param);

    // Update the search property of the URL object
    url.search = params.toString();

    // Use history.replaceState to update the URL without reloading the page
    history.replaceState(null, "", url);

    // Reload the page to reflect changes
    location.reload();
  }
}
