// @ts-nocheck
/**
 * Initialize popups using Floating UI
 *
 * @param {HTMLElement} bodyContent
 * @return {void}
 */
function init(bodyContent) {
  const { computePosition, offset, flip, shift } = window.FloatingUIDOM;
  const popupElements = bodyContent.querySelectorAll(".popup");

  if (!popupElements.length) {
    return;
  }

  popupElements.forEach((popup) => {
    let popupContent = null;

    function createPopupContent() {
      popupContent = document.createElement("div");
      popupContent.classList.add("popup-content");
      popupContent.textContent = popup.getAttribute("data-popup-text");
      document.body.appendChild(popupContent);
    }

    function removePopupContent() {
      if (popupContent) {
        document.body.removeChild(popupContent);
        popupContent = null;
      }
    }

    function show(event) {
      if (!popupContent) {
        createPopupContent();
      }
      updatePosition(event.clientX, event.clientY);
    }

    function updatePosition(clientX, clientY) {
      const freeSpace = {
        getBoundingClientRect() {
          return {
            width: 0,
            height: 0,
            x: clientX,
            y: clientY,
            left: clientX,
            right: clientX,
            top: clientY,
            bottom: clientY,
          };
        },
      };

      computePosition(freeSpace, popupContent, {
        placement: "bottom-start",
        middleware: [offset({ mainAxis: 20, crossAxis: 10 }), shift({ padding: 15 }), flip()],
      }).then(({ x, y, strategy }) => {
        Object.assign(popupContent.style, {
          left: `${x}px`,
          top: `${y}px`,
          position: strategy,
        });
      });
    }

    function hide() {
      removePopupContent();
    }

    popup.addEventListener("mouseenter", show);
    popup.addEventListener("mousemove", (event) => {
      updatePosition(event.clientX, event.clientY);
    });
    popup.addEventListener("mouseleave", hide);
  });
}

module.exports = {
  init: init,
};
