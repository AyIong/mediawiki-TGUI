/**
 * Moves the TOC element to a new parent container.
 *
 * @param {string} position The position to move the TOC into: sidebar or stickyheader
 */
function moveToc(position) {
  const toc = document.getElementById("mw-panel-toc");
  const currTocContainer = toc && toc.parentElement;
  if (!toc || !currTocContainer) {
    return;
  }

  // FIXME: Remove after Ia263c606dce5a6060b6b29fbaedc49cef3e17a5c has been in prod for 5 days
  const isCachedHtml = document.querySelector(
    ".mw-table-of-contents-container .mw-sticky-header-element"
  );

  let newTocContainer;
  const sidebarTocContainerClass = isCachedHtml
    ? "mw-table-of-contents-container"
    : "tgui-sticky-toc-container";
  const stickyHeaderTocContainerClass = "tgui-menu-content";
  // Avoid moving TOC if unnecessary
  if (
    !currTocContainer.classList.contains(sidebarTocContainerClass) &&
    position === "sidebar"
  ) {
    newTocContainer = document.querySelector(`.${sidebarTocContainerClass}`);
  } else if (
    !currTocContainer.classList.contains(stickyHeaderTocContainerClass) &&
    position === "stickyheader"
  ) {
    newTocContainer = document.querySelector(
      `.tgui-sticky-header-toc .${stickyHeaderTocContainerClass}`
    );
  }

  if (newTocContainer) {
    newTocContainer.insertAdjacentElement("beforeend", toc);
  }
}
