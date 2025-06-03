const config = require('./config.json');
const tooltipInitializedAttr = 'data-tooltip-initialized';
const tooltipVisibleTimeout = 1000;

function init(content) {
  if (!config.wgTGUIReplaceTitleTooltips) {
    return;
  }

  if (window.matchMedia('(max-width: 719px)').matches) {
    return;
  }

  function initializeTooltips() {
    const titleElements = content.querySelectorAll('[title]');
    for (const titleElement of titleElements) {
      if (titleElement.hasAttribute(tooltipInitializedAttr)) {
        continue;
      }

      if (titleElement.parentElement.hasAttribute('data-notitle')) {
        titleElement.setAttribute(tooltipInitializedAttr, '');
        titleElement.removeAttribute('title');
        continue;
      }

      const tooltipText = titleElement.getAttribute('title');
      if (!tooltipText) {
        continue;
      }

      titleElement.setAttribute(tooltipInitializedAttr, '');
      titleElement.removeAttribute('title');
      titleElement.addEventListener('mouseover', showTooltip);
      titleElement.addEventListener('mouseout', hideTooltip);

      let tooltipContent = null;
      let hideTimeout = null;
      let appearTimeout = null;
      function showTooltip() {
        clearTimeout(appearTimeout);
        appearTimeout = setTimeout(() => {
          if (!titleElement.parentNode) {
            return;
          }

          if (!tooltipContent) {
            tooltipContent = createTooltipElement(tooltipText);
            document.body.appendChild(tooltipContent);
          }

          positionTooltip(titleElement, tooltipContent);
          tooltipContent.classList.add('visible');
        }, tooltipVisibleTimeout);
      }

      function hideTooltip() {
        clearTimeout(appearTimeout);
        if (!tooltipContent) {
          return;
        }

        tooltipContent.classList.remove('visible');
        hideTimeout = hideTimeout || setTimeout(() => removeTooltipElement(), 200);
      }

      function removeTooltipElement() {
        if (tooltipContent) {
          document.body.removeChild(tooltipContent);
          tooltipContent = null;
          hideTimeout = null;
        }
      }

      function createTooltipElement(text) {
        const tooltipContent = document.createElement('div');
        tooltipContent.classList.add('tgui-tooltip');
        tooltipContent.textContent = text;

        const arrowEl = document.createElement('div');
        arrowEl.classList.add('tooltip-arrow');
        tooltipContent.appendChild(arrowEl);

        return tooltipContent;
      }

      function positionTooltip(reference, floatingElement) {
        const { computePosition, offset, flip, shift, arrow } = window.FloatingUIDOM;
        const arrowEl = floatingElement.querySelector('.tooltip-arrow');
        computePosition(reference, floatingElement, {
          placement: 'top',
          middleware: [flip(), shift({ padding: 9 }), offset(9), arrow({ element: arrowEl })],
        }).then(({ x, y, middlewareData, placement }) => {
          Object.assign(floatingElement.style, {
            top: `${y}px`,
            left: `${x}px`,
          });
          floatingElement.setAttribute('data-position', placement);
          positionArrow(arrowEl, middlewareData.arrow, placement);
        });
      }

      function positionArrow(arrowEl, arrowData, placement) {
        if (!arrowData) {
          return;
        }

        const arrowOffset = arrowEl.offsetHeight / 2;
        const arrowPosition = { left: `${arrowData.x}px` };
        arrowPosition[placement === 'top' ? 'bottom' : 'top'] = `${-arrowOffset}px`;
        Object.assign(arrowEl.style, arrowPosition);
      }
    }
  }

  const observer = new MutationObserver(() => {
    const orphanTooltips = document.body.querySelectorAll('.tgui-tooltip');
    if (orphanTooltips.length > 1) {
      for (const tooltip of orphanTooltips) {
        document.body.removeChild(tooltip);
        break; // ONLY 1
      }
    }

    initializeTooltips();
  });

  observer.observe(content, {
    childList: true,
    subtree: true,
  });
}

module.exports = { init };
