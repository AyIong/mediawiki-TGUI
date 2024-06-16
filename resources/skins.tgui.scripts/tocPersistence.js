const TOC_COLLAPSED_CLASS = "tgui-toc-collapsed";
const isCollapsed = localStorage.getItem("TGUI-ToC-Collapsed");

function restoreTOCState() {
  if (isCollapsed === "true") {
    document.body.classList.add(TOC_COLLAPSED_CLASS);
  }

  const checkWindowSize = () => {
    if (window.innerWidth < 999) {
      document.body.classList.add("tgui-toc-collapsed");
    }
  };
  checkWindowSize();
}

// Move ToC for mobile devices
function moveElement() {
  const toc = document.querySelector(".sidebar-toc");
  const newContainer = document.querySelector(".mw-page-container-inner");
  const mediaQuery = window.matchMedia("(max-width: 719px)"); // @max-width-mobile LESS var;

  function handleMediaChange(e) {
    if (!toc) {
      return;
    }
    if (e.matches) {
      newContainer.appendChild(toc);
    } else {
      document.querySelector(".tgui-sidebar-container").appendChild(toc);
    }
  }

  handleMediaChange(mediaQuery);
  mediaQuery.addEventListener("change", handleMediaChange);
}

requestAnimationFrame(restoreTOCState);
document.addEventListener("DOMContentLoaded", moveElement);
