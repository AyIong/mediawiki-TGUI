const TOC_COLLAPSED_CLASS = "tgui-toc-collapsed";
const isCollapsed = localStorage.getItem("TGUI-ToC-Collapsed");

function restoreTOCState() {
  if (isCollapsed === "true") {
    document.body.classList.add(TOC_COLLAPSED_CLASS);
  }
}

document.addEventListener("DOMContentLoaded", restoreTOCState);
