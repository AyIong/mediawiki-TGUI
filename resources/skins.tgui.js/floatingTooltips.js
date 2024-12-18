// @ts-nocheck
const config = require('./config.json');

/**
 * Initialize tooltips for MediaWiki templates using Floating UI
 *
 * @param {HTMLElement} bodyContent
 * @return {void}
 */
function init(bodyContent) {
  const { computePosition, offset, flip, shift, autoUpdate } = window.FloatingUIDOM;
  const tooltipElements = document.querySelectorAll('.tooltip');

  if (!tooltipElements.length) {
    return;
  }

  const tooltipText = config.wgTGUITooltips;
  if (!tooltipText || !Array.isArray(tooltipText)) {
    mw.log.error('[TGUI] Invalid or missing $wgTGUITooltips. Cannot use Floating UI for Tooltips.');
    return;
  }

  function createFloatingInstance(reference, floatingElement) {
    return computePosition(reference, floatingElement, {
      placement: 'top-start',
      middleware: [offset(6), shift({ padding: 9 }), flip()],
    }).then(({ x, y, strategy }) => {
      Object.assign(floatingElement.style, {
        left: `${x}px`,
        top: `${y}px`,
        position: strategy,
      });
    });
  }

  const contentClasses = tooltipText.map((className) => `.${className}`).join(', ');
  tooltipElements.forEach((tooltip) => {
    const tooltipContent = tooltip.querySelector(contentClasses);

    if (tooltipContent) {
      createFloatingInstance(tooltip, tooltipContent);

      let cleanup = null;
      function show() {
        createFloatingInstance(tooltip, tooltipContent);
        cleanup = autoUpdate(tooltip, tooltipContent, () => {
          createFloatingInstance(tooltip, tooltipContent);
        });
      }

      function hide() {
        if (cleanup) {
          cleanup();
          cleanup = null;
        }
      }

      const showEvents = ['mouseenter', 'focus'];
      const hideEvents = ['mouseleave', 'blur'];

      showEvents.forEach((event) => {
        tooltip.addEventListener(event, show);
      });

      hideEvents.forEach((event) => {
        tooltip.addEventListener(event, hide);
      });
    }
  });
}

module.exports = {
  init: init,
};
