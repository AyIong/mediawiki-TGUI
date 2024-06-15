/**
 * Set up scroll affordance for an overflowed element
 * TODO: Move this out of tables when this is used by more stuff
 *
 * @param {HTMLElement} element
 * @return {void}
 */
function setupOverflowState(element) {
  const initState = () => {
    const updateState = () => {
      const containerWidth = element.parentNode.offsetWidth,
        contentWidth = element.scrollWidth;

      // Break if no horizontal overflow
      if (contentWidth <= containerWidth) {
        element.parentNode.classList.remove("tgui-overflow--left");
        element.parentNode.classList.remove("tgui-overflow--right");
        return;
      }

      const currentPosition = Math.ceil(element.parentNode.scrollLeft);

      if (currentPosition <= 0) {
        // At the start
        element.parentNode.classList.remove("tgui-overflow--left");
        element.parentNode.classList.add("tgui-overflow--right");
      } else if (currentPosition + containerWidth >= contentWidth) {
        // At the end
        element.parentNode.classList.remove("tgui-overflow--right");
        element.parentNode.classList.add("tgui-overflow--left");
      } else {
        // At the middle
        element.parentNode.classList.add("tgui-overflow--left");
        element.parentNode.classList.add("tgui-overflow--right");
      }
    };

    if (element.parentNode === null) {
      return;
    }

    updateState();

    // Update state on element scroll
    element.parentNode.addEventListener("scroll", () => {
      window.requestAnimationFrame(updateState);
    });
  };

  initState();

  // Listen for window resize
  if (window.ResizeObserver) {
    const overflowResizeObserver = new ResizeObserver(mw.util.debounce(250, initState));
    overflowResizeObserver.observe(element);
  }
}

/**
 * Wrap table in div container to make it scrollable without breaking layout
 *
 * @param {HTMLTableElement} table
 * @return {void}
 */
function wrapTable(table) {
  const wrapper = document.createElement("div");
  const blur = document.createElement("div");

  // Some classes should be inherited from the table
  // For example, float helper classes like floatleft and floatright
  const inheritTableClass = () => {
    const inheritedClasses = ["floatleft", "floatright"];

    inheritedClasses.forEach((inheritedClass) => {
      if (table.classList.contains(inheritedClass)) {
        wrapper.classList.add(inheritedClass);
        table.classList.remove(inheritedClass);
      }
    });
  };

  wrapper.classList.add("tgui-table-wrapper");
  blur.classList.add("tgui-blur");
  inheritTableClass();
  table.parentNode.insertBefore(wrapper, table);
  wrapper.appendChild(table);
  table.appendChild(blur);

  setupOverflowState(table);
}

/**
 * @param {HTMLElement} bodyContent
 * @return {void}
 */
function init(bodyContent) {
  // Don't touch nested tables since we only need to wrap the outer layer
  if (!bodyContent.querySelector("table:not( table table )")) {
    return;
  }

  const tables = bodyContent.querySelectorAll("table:not( table table )");

  tables.forEach((table) => {
    wrapTable(table);
  });
}

module.exports = {
  init: init,
};
