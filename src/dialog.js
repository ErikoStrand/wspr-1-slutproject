const dialog = document.getElementById("signUp");
const openButton = document.getElementById("signUpButton");
const closeButton = document.getElementById("signUpClose");

if (dialog != null) {
  openButton.addEventListener("click", () => {
    console.log("Opened Modal");
    dialog.showModal();
  });

  closeButton.addEventListener("click", () => {
    console.log("closed Modal");
    dialog.close();
  });
}

const iDialog = document.getElementById("signIn");
const iCloseButton = document.getElementById("signInClose");

function openModal() {
  console.log("Opened Modal");
  iDialog.showModal();
}

iCloseButton.addEventListener("click", () => {
  console.log("closed Modal");
  iDialog.close();
});
