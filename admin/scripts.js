// admin/script.js
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById("sidebar");
    const main = document.querySelector(".main");
    const toggleBtn = document.getElementById("toggle-btn");
    if (toggleBtn) {
      toggleBtn.addEventListener("click", () => {
        if (sidebar.style.width === "60px") {
          sidebar.style.width = "220px";
          main.style.marginLeft = "220px";
        } else {
          sidebar.style.width = "60px";
          main.style.marginLeft = "60px";
        }
      });
    }
  
    // flash message (optional): if server sets session flash into DOM, it can be dismissed here
    const flash = document.querySelector('.flash-msg');
    if (flash) {
      setTimeout(()=> flash.style.display = 'none', 3500);
    }
  });
  