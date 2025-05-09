/**
 * Set up functionality of collapsable sections
 *
 * @param {HTMLElement} bodyContent
 * @return {void}
 */
function init(bodyContent) {
  if (!document.body.classList.contains('tgui-sections-enabled')) {
    return;
  }

  const onEditSectionClick = (e) => {
    e.stopPropagation();
  };

  const handleClick = (e) => {
    const target = e.target;
    const isEditSection = target.closest('.mw-editsection, .mw-editsection-like');

    if (isEditSection) {
      onEditSectionClick(e);
      return;
    }

    const heading = target.closest('.tgui-section-heading');

    if (heading?.nextElementSibling?.classList.contains('tgui-section')) {
      const section = heading.nextElementSibling;

      if (section) {
        section.hidden = section.hidden ? false : 'until-found';
      }
    }
  };

  bodyContent.addEventListener('click', handleClick, false);
}

module.exports = {
  init: init,
};
