/**
 * @return {void}
 */
function deferredTasks() {
  const setupObservers = require('./setupObservers.js');

  setupObservers.main();
  document.documentElement.classList.add('tgui-animations-ready');
  registerServiceWorker();
}

/**
 * Register service worker
 *
 * @return {void}
 */
function registerServiceWorker() {
  const scriptPath = mw.config.get('wgScriptPath');
  // Only allow serviceWorker when the scriptPath is at root because of its scope
  // I can't figure out how to add the Service-Worker-Allowed HTTP header
  // to change the default scope
  if (scriptPath !== '') {
    return;
  }

  if ('serviceWorker' in navigator) {
    const SW_MODULE_NAME = 'skins.tgui.serviceWorker',
      version = mw.loader.moduleRegistry[SW_MODULE_NAME].version,
      // HACK: Faking a RL link
      swUrl =
        scriptPath + '/load.php?modules=' + SW_MODULE_NAME + '&only=scripts&raw=true&skin=tgui&version=' + version;
    navigator.serviceWorker.register(swUrl, { scope: '/' });
  }
}

/**
 * Initialize scripts related to wiki page content
 *
 * @param {HTMLElement} bodyContent
 * @return {void}
 */
function initBodyContent(bodyContent) {
  const tables = require('./tables.js'),
    floatingPopups = require('./floatingPopups.js'),
    floatingTooltips = require('./floatingTooltips.js'),
    floatingDropdown = require('./floatingDropdown.js');

  // Table enhancements
  tables.init(bodyContent);
  // Floating UI Popups
  floatingPopups.init(bodyContent);
  // Floating UI Tooltips for MediaWiki templates
  floatingTooltips.init(bodyContent);
  // Floating UI Dropdowns for MediaWiki templates
  floatingDropdown.init(bodyContent);
}

/**
 * @param {Window} window
 * @return {void}
 */
function main(window) {
  const config = require('./config.json'),
    initSearchLoader = require('./searchLoader.js').initSearchLoader,
    dropdown = require('./dropdown.js'),
    floatingTitles = require('./floatingTitles.js'),
    purgeButton = require('./purgeButton.js');

  floatingTitles.init(window.document);
  initSearchLoader(document);
  dropdown.init();
  purgeButton();

  mw.hook('wikipage.content').add((content) => {
    // content is a jQuery object
    // note that this refers to .mw-body-content, not #bodyContent
    initBodyContent(content[0]);
  });

  // Preference module
  if (config.wgTGUIEnablePreferences === true) {
    mw.loader.load('skins.tgui.preferences');
  }

  // Defer non-essential tasks
  // eslint-disable-next-line compat/compat
  requestIdleCallback(deferredTasks, { timeout: 3000 });
}

if (document.readyState === 'interactive' || document.readyState === 'complete') {
  main(window);
} else {
  // This is needed when document.readyState === 'loading'.
  document.addEventListener('DOMContentLoaded', function () {
    main(window);
  });
}
