// Enable TGUI features limited to ES6 browse
const searchToggle = require('./searchToggle.js'),
  initSectionObserver = require('./sectionObserver.js').init,
  initTableOfContents = require('./tableOfContents.js'),
  deferUntilFrame = require('./deferUntilFrame.js'),
  TOC_ID = 'mw-panel-toc',
  BODY_CONTENT_ID = 'bodyContent',
  HEADLINE_SELECTOR = '.mw-headline',
  TOC_SECTION_ID_PREFIX = 'toc-';

/**
 * @callback OnIntersection
 * @param {HTMLElement} element The section that triggered the new intersection change.
 */

/**
 * @ignore
 * @param {Function} changeActiveSection
 * @return {OnIntersection}
 */
const getHeadingIntersectionHandler = (changeActiveSection) => {
  /**
   * @param {HTMLElement} section
   */
  return (section) => {
    const headline = section.classList.contains('mw-body-content') ? section : section.querySelector(HEADLINE_SELECTOR);
    if (headline) {
      changeActiveSection(`${TOC_SECTION_ID_PREFIX}${headline.id}`);
    }
  };
};

/**
 * Updates TOC's status
 * Makes it hidden or not
 *
 * @return {void}
 */
const updateTocStatus = () => {
  const tocElements = document.querySelectorAll('.sidebar-toc');
  tocElements.forEach((element) => {
    element.classList.toggle('hidden');
  });
};

/**
 * @return {void}
 */
const main = () => {
  // Initialize the search toggle for the main header only. The sticky header
  // toggle is initialized after Codex search loads.
  const searchToggleElement = document.querySelector('.mw-header .search-toggle');
  if (searchToggleElement) {
    searchToggle(searchToggleElement);
  }

  // Table of contents
  const tocElement = document.getElementById(TOC_ID);
  const bodyContent = document.getElementById(BODY_CONTENT_ID);
  const tocElementLegacy = document.getElementById('toc');

  // Remove old ToC
  if (tocElementLegacy && tocElementLegacy.parentNode) {
    tocElementLegacy.parentNode.removeChild(tocElementLegacy);
  }

  if (!(tocElement && bodyContent && window.IntersectionObserver && window.requestAnimationFrame)) {
    return;
  }

  const tableOfContents = initTableOfContents({
    container: tocElement,
    onHeadingClick: (id) => {
      sectionObserver.pause();
      tableOfContents.expandSection(id);
      tableOfContents.changeActiveSection(id);
      deferUntilFrame(() => sectionObserver.resume(), 220);
    },
    onToggleClick: (id) => {
      tableOfContents.toggleExpandSection(id);
    },
    onToggleCollapse: updateTocStatus,
  });

  const headingSelector = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span']
    .map((tag) => `.mw-parser-output > ${tag}`)
    .join(',');
  const computedStyle = window.getComputedStyle(document.documentElement);
  const scrollPaddingTop = computedStyle.getPropertyValue('scroll-padding-top');
  const topMargin = Number(scrollPaddingTop.slice(0, -2)) + 20;
  const getTopMargin = () => topMargin;
  const sectionObserver = initSectionObserver({
    elements: bodyContent.querySelectorAll(`${headingSelector}, .mw-body-content`),
    topMargin: getTopMargin(),
    onIntersection: getHeadingIntersectionHandler(tableOfContents.changeActiveSection),
  });

  updateTocStatus();
};

module.exports = {
  main,
  test: {
    getHeadingIntersectionHandler,
  },
};
