const dialog = document.getElementById("signUp");
const openButton = document.getElementById("signUpButton");
const closeButton = document.getElementById("signUpClose");

openButton.addEventListener("click", () => {
  console.log("Opened Modal");
  dialog.showModal();
});

closeButton.addEventListener("click", () => {
  console.log("closed Modal");
  dialog.close();
});

const iDialog = document.getElementById("signIn");
const iOpenButton = document.getElementById("signInButton");
const iCloseButton = document.getElementById("signInClose");

iOpenButton.addEventListener("click", () => {
  console.log("Opened Modal");
  iDialog.showModal();
});

iCloseButton.addEventListener("click", () => {
  console.log("closed Modal");
  iDialog.close();
});
