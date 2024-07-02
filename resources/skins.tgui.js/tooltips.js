const config = require("./config.json");

/**
 * Initialize tooltips using Popper.js
 *
 * @param {HTMLElement} bodyContent
 * @return {void}
 */
function init(bodyContent) {
  function createPopperInstance(reference, popperElement) {
    return Popper.createPopper(reference, popperElement, {
      placement: "bottom-start",
      modifiers: [
        {
          name: "preventOverflow",
          options: {
            boundariesElement: "scrollParent",
            padding: 12,
          },
        },
        {
          name: "flip",
          options: {
            boundariesElement: "scrollParent",
            flipVariations: true,
          },
        },
        {
          name: "eventListeners",
          enabled: false,
        },
      ],
    });
  }

  const tooltipElements = document.querySelectorAll(".tooltip");
  if (!tooltipElements.length) {
    return;
  }

  const tooltipText = config.wgTGUITooltips;
  if (!tooltipText || !Array.isArray(tooltipText)) {
    mw.log.error("[TGUI] Invalid or missing $wgTGUITooltips. Cannot use PopperJS for Tooltips.");
    return;
  }

  const contentClasses = tooltipText.map((className) => `.${className}`).join(", ");

  tooltipElements.forEach((tooltip) => {
    const tooltipContent = tooltip.querySelector(contentClasses);

    let popperInstance = null;
    if (tooltipContent && !popperInstance) {
      popperInstance = createPopperInstance(tooltip, tooltipContent);
    }

    function show() {
      popperInstance.setOptions((options) => ({
        ...options,
        modifiers: [...options.modifiers, { name: "eventListeners", enabled: true }],
      }));
    }

    function hide() {
      popperInstance.setOptions((options) => ({
        ...options,
        modifiers: [...options.modifiers, { name: "eventListeners", enabled: false }],
      }));

      popperInstance.update();
    }

    const showEvents = ["mouseenter", "focus"];
    const hideEvents = ["mouseleave", "blur"];

    showEvents.forEach((event) => {
      tooltip.addEventListener(event, show);
    });

    hideEvents.forEach((event) => {
      tooltip.addEventListener(event, hide);
    });
  });
}

module.exports = {
  init: init,
};
