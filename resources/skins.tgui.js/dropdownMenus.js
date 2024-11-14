/** @interface CheckboxHack */

var checkboxHack = /** @type {CheckboxHack} */ require(/** @type {string} */ ('mediawiki.page.ready')).checkboxHack,
  CHECKBOX_HACK_CONTAINER_SELECTOR = '.tgui-menu-dropdown',
  CHECKBOX_HACK_CHECKBOX_SELECTOR = '.tgui-menu-checkbox',
  CHECKBOX_HACK_BUTTON_SELECTOR = '.tgui-menu-heading',
  CHECKBOX_HACK_TARGET_SELECTOR = '.tgui-menu-content';

/**
 * Enhance dropdownMenu functionality and accessibility using core's checkboxHack.
 */
function bind() {
  // Search for all dropdown containers using the CHECKBOX_HACK_CONTAINER_SELECTOR.
  var containers = document.querySelectorAll(CHECKBOX_HACK_CONTAINER_SELECTOR);

  Array.prototype.forEach.call(containers, function (container) {
    var checkbox = container.querySelector(CHECKBOX_HACK_CHECKBOX_SELECTOR),
      button = container.querySelector(CHECKBOX_HACK_BUTTON_SELECTOR),
      target = container.querySelector(CHECKBOX_HACK_TARGET_SELECTOR);

    if (!(checkbox && button && target)) {
      return;
    }

    checkboxHack.bind(window, checkbox, button, target);
  });
}

/**
 * Create an icon element to be appended inside the anchor tag.
 *
 * @param {HTMLElement|null} menuElement
 * @param {HTMLElement|null} parentElement
 * @param {string|null} id
 *
 * @return {HTMLElement|undefined}
 */
function createIconElement(menuElement, parentElement, id) {
  var isIconCapable =
    menuElement &&
    menuElement.classList.contains('tgui-menu-dropdown') &&
    !menuElement.classList.contains('tgui-menu-dropdown-noicon');

  if (!isIconCapable || !parentElement) {
    return;
  }

  var iconElement = document.createElement('span');
  iconElement.classList.add('mw-ui-icon');

  if (id) {
    iconElement.classList.add('mw-ui-icon-tgui-gadget-' + id);
  }

  return iconElement;
}

/**
 * Adds icon placeholder for gadgets to use.
 *
 * @param {HTMLElement} item
 * @param {PortletLinkData} data
 * @typedef {Object} PortletLinkData
 * @property {string|null} id
 */
function addPortletLinkHandler(item, data) {
  var link = item.querySelector('a');
  var $menu = $(item).parents('.tgui-menu');
  var menuElement = ($menu.length && $menu.get(0)) || null;
  var iconElement = createIconElement(menuElement, link, data.id);

  if ($menu.prop('id') === 'p-views') {
    var availableWidth =
      $('.mw-article-toolbar-container').width() -
      $('#p-namespaces').width() -
      $('#p-variants').width() -
      $('#p-views').width() -
      $('#p-cactions').width();
    var moreDropdown = document.querySelector('#p-cactions ul');
    if (moreDropdown && (availableWidth < 0 || window.innerWidth < 720)) {
      moreDropdown.appendChild(item);
      // reveal if hidden
      mw.util.showPortlet('p-cactions');
    }
  }

  if (link && iconElement) {
    link.prepend(iconElement);
  }
}

// Enhance previously added items.
Array.prototype.forEach.call(document.querySelectorAll('.mw-list-item-js'), function (item) {
  addPortletLinkHandler(item, {
    id: item.getAttribute('id'),
  });
});

mw.hook('util.addPortletLink').add(addPortletLinkHandler);

module.exports = {
  dropdownMenus: function dropdownMenus() {
    bind();
  },
  addPortletLinkHandler: addPortletLinkHandler,
};
