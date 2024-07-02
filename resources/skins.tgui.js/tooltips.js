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

  document.querySelectorAll(".tooltip").forEach(function (tooltip) {
    const tooltipText = tooltip.querySelector(".tooltiptext, .tooltiptext2");
    let popperInstance = null;

    if (tooltipText && !popperInstance) {
      popperInstance = createPopperInstance(tooltip, tooltipText);
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
