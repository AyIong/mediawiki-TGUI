/**
 * Create an observer based vertical scroll direction
 *
 * @param {Function} onScrollDown functionality for when viewport is scrolled down
 * @param {Function} onScrollUp functionality for when viewport is scrolled up
 * @param {number} threshold minimum scrolled px to trigger the function
 * @return {void}
 */
function initDirectionObserver(onScrollDown, onScrollUp, threshold) {
  const throttle = require('mediawiki.util').throttle;

  let lastScrollTop = window.scrollY;

  const onScroll = () => {
    const scrollTop = window.scrollY;

    if (Math.abs(scrollTop - lastScrollTop) < threshold) {
      return;
    }

    if (scrollTop > lastScrollTop) {
      onScrollDown();
    } else {
      onScrollUp();
    }
    lastScrollTop = scrollTop;
  };

  window.addEventListener('scroll', throttle(onScroll, 250));
}

/**
 * Create an observer for showing/hiding feature and for firing scroll event hooks.
 *
 * @param {Function} show functionality for when feature is visible
 * @param {Function} hide functionality for when feature is hidden
 * @return {IntersectionObserver}
 */
function initScrollObserver(show, hide) {
  return new IntersectionObserver((entries) => {
    if (!entries[0].isIntersecting && entries[0].boundingClientRect.top < 0) {
      // Viewport has crossed the bottom edge of the target element.
      show();
    } else {
      // Viewport is above the bottom edge of the target element.
      hide();
    }
  });
}

module.exports = {
  initDirectionObserver,
  initScrollObserver,
};
