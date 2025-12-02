const sidebar = document.getElementById("sidebar");
const main = document.querySelector(".main");
const toggleBtn = document.getElementById("toggle-btn");

toggleBtn.addEventListener("click", () => {
  if (sidebar.style.width === "60px") {
    sidebar.style.width = "220px";
    main.style.marginLeft = "220px";
  } else {
    sidebar.style.width = "60px";
    main.style.marginLeft = "60px";
  }
});
