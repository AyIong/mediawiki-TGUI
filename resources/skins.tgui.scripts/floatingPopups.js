// @ts-nocheck
/**
 * Initialize popups using Floating UI
 *
 * @param {HTMLElement} bodyContent
 * @return {void}
 */
function init(bodyContent) {
  const { computePosition, offset, flip, shift } = window.FloatingUIDOM;
  const popupElements = bodyContent.querySelectorAll('.popup');

  if (!popupElements.length) {
    return;
  }

  popupElements.forEach((popup) => {
    const popupText = popup.getAttribute('data-popup-text');
    if (!popupText) {
      return;
    }

    const hyperLink = popup.querySelector('a');
    if (hyperLink) {
      hyperLink.removeAttribute('title');
    }

    let popupContentElement = null;
    let hideTimeout = null;
    function createPopupContentElement() {
      popupContentElement = document.createElement('div');
      popupContentElement.classList.add('popup-content');
      popupContentElement.textContent = popupText;
      document.body.appendChild(popupContentElement);
      setTimeout(() => {
        popupContentElement.classList.add('visible');
      }, 10);
    }

    function removePopupContentElement() {
      if (popupContentElement) {
        document.body.removeChild(popupContentElement);
        popupContentElement = null;
      }
    }

    function show(event) {
      if (!popupContentElement) {
        createPopupContentElement();
      }

      if (hideTimeout !== null) {
        clearTimeout(hideTimeout);
        hideTimeout = null;
        setTimeout(() => {
          popupContentElement.classList.add('visible');
        }, 10);
      }

      updatePosition(event.clientX, event.clientY);
    }

    function hide() {
      if (!popupContentElement) return;
      popupContentElement.classList.remove('visible');

      if (!hideTimeout) {
        hideTimeout = setTimeout(() => {
          removePopupContentElement();
        }, 200);
      }
    }

    function updatePosition(clientX, clientY) {
      if (!popupContentElement) {
        return;
      }

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

      computePosition(freeSpace, popupContentElement, {
        placement: 'bottom-start',
        middleware: [offset({ mainAxis: 20, crossAxis: 10 }), shift({ padding: 15 }), flip()],
      }).then(({ x, y, strategy }) => {
        Object.assign(popupContentElement.style, {
          left: `${x}px`,
          top: `${y}px`,
          position: strategy,
        });
      });
    }

    popup.addEventListener('mouseenter', show);
    popup.addEventListener('mousemove', (event) => {
      updatePosition(event.clientX, event.clientY);
    });
    popup.addEventListener('mouseleave', hide);
  });
}

module.exports = {
  init: init,
};
