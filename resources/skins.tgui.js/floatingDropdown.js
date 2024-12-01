// @ts-nocheck
const config = require('./config.json');

/**
 * Initialize dropdowns for MediaWiki templates using Floating UI
 *
 * @param {HTMLElement} bodyContent
 * @return {void}
 */
function init(bodyContent) {
  const { computePosition, offset, size, flip, shift, autoUpdate } = window.FloatingUIDOM;
  const dropdownElements = document.querySelectorAll('.floating-dropdown');

  if (!dropdownElements.length) {
    return;
  }

  const dropdownContent = config.wgTGUIDropdowns;
  if (!dropdownContent || !Array.isArray(dropdownContent)) {
    mw.log.error('[TGUI] Invalid or missing $wgTGUIDropdowns. Cannot use Floating UI for Dropdowns.');
    return;
  }

  const contentClasses = dropdownContent.map((className) => `.${className}`).join(', ');
  dropdownElements.forEach((dropdown) => {
    const content = dropdown.querySelector(contentClasses);

    if (content) {
      let cleanup = null;
      let isFocused = false;
      const position = dropdown.getAttribute('data-position');

      createFloatingInstance(dropdown, content, position);

      function toggle() {
        if (isFocused) {
          hide();
        } else {
          show();
        }
      }

      function show() {
        isFocused = true;

        createFloatingInstance(dropdown, content, position);
        cleanup = autoUpdate(dropdown, content, () => {
          createFloatingInstance(dropdown, content, position);
        });

        content.classList.add('visible');
      }

      function hide() {
        isFocused = false;

        if (cleanup) {
          cleanup();
          cleanup = null;
        }

        content.classList.remove('visible');
      }

      dropdown.addEventListener('mouseup', toggle);
    }

    function createFloatingInstance(reference, floatingElement, position) {
      const dropdownPosition = position ? position : 'bottom';

      return computePosition(reference, floatingElement, {
        placement: dropdownPosition,
        middleware: [
          offset(6),
          shift({ padding: 9 }),
          size({
            apply({ rects }) {
              floatingElement.style.width = `${rects.reference.width * 0.95}px`;
            },
          }),
          flip(),
        ],
      }).then(({ x, y, strategy, placement }) => {
        reference.setAttribute('data-position', placement);

        Object.assign(floatingElement.style, {
          position: strategy,
          left: `${x}px`,
          top: `${y}px`,
        });
      });
    }
  });
}

module.exports = {
  init: init,
};
