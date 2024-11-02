const config = require("./config.json");

function init(content) {
  if (!config.wgTGUIReplaceTitleTooltips) {
    return;
  }

  if (window.matchMedia("(max-width: 719px)").matches) {
    return;
  }

  const { computePosition, offset, flip, shift, arrow } = window.FloatingUIDOM;
  function initializeTooltips() {
    const tooltipElements = content.querySelectorAll("[title]");
    tooltipElements.forEach((tooltip) => {
      if (tooltip.hasAttribute("data-tooltip-initialized")) {
        return;
      }

      if (tooltip.parentElement.hasAttribute("data-notitle")) {
        tooltip.setAttribute("data-tooltip-initialized", "true");
        tooltip.removeAttribute("title");
        return;
      }

      const tooltipText = tooltip.getAttribute("title");
      if (!tooltipText) {
        return;
      }

      tooltip.setAttribute("data-tooltip-initialized", "true");
      tooltip.removeAttribute("title");

      let tooltipContent = null;
      let hideTimeout = null;
      let appearTimeout = null;

      tooltip.addEventListener("mouseover", showTooltip);
      tooltip.addEventListener("mouseleave", hideTooltip);

      function showTooltip() {
        clearTimeout(appearTimeout);

        appearTimeout = setTimeout(() => {
          if (!tooltipContent) {
            tooltipContent = createTooltipElement(tooltipText);
            document.body.appendChild(tooltipContent);
          }
          positionTooltip(tooltip, tooltipContent);
          tooltipContent.classList.add("visible");
        }, 750);
      }

      function hideTooltip() {
        clearTimeout(appearTimeout);

        if (!tooltipContent) return;

        tooltipContent.classList.remove("visible");
        hideTimeout = hideTimeout || setTimeout(() => removeTooltipElement(), 200);
      }

      function createTooltipElement(text) {
        const tooltipContent = document.createElement("div");
        tooltipContent.classList.add("tooltip-content");
        tooltipContent.textContent = text;

        const arrowEl = document.createElement("div");
        arrowEl.classList.add("tooltip-arrow");
        tooltipContent.appendChild(arrowEl);

        return tooltipContent;
      }

      function positionTooltip(reference, floatingElement) {
        const arrowEl = floatingElement.querySelector(".tooltip-arrow");
        computePosition(reference, floatingElement, {
          placement: "top",
          middleware: [flip(), shift({ padding: 9 }), offset(9), arrow({ element: arrowEl })],
        }).then(({ x, y, middlewareData, placement }) => {
          Object.assign(floatingElement.style, { top: `${y}px`, left: `${x}px` });
          positionArrow(arrowEl, middlewareData.arrow, placement);
        });
      }

      function positionArrow(arrowEl, arrowData, placement) {
        if (!arrowData) return;

        const arrowOffset = arrowEl.offsetHeight / 2;
        const arrowPosition = { left: `${arrowData.x}px` };
        arrowPosition[placement === "top" ? "bottom" : "top"] = `${-arrowOffset}px`;
        Object.assign(arrowEl.style, arrowPosition);
      }

      function removeTooltipElement() {
        if (tooltipContent) {
          document.body.removeChild(tooltipContent);
          tooltipContent = null;
          hideTimeout = null;
        }
      }
    });
  }

  initializeTooltips();
  const observer = new MutationObserver(() => {
    initializeTooltips();
  });

  observer.observe(content, {
    childList: true,
    subtree: true,
  });
}

module.exports = { init };
