var languageButton = require("./languageButton.js"),
  initSearchLoader = require("./searchLoader.js").initSearchLoader,
  dropdownMenus = require("./dropdownMenus.js").dropdownMenus,
  sidebarPersistence = require("./sidebarPersistence.js"),
  checkbox = require("./checkbox.js"),
  collapsiblePortlets = require("./collapsiblePortlets.js");

/**
 * Wait for first paint before calling this function.
 * (see T234570#5779890, T246419).
 *
 * @param {Document} document
 * @return {void}
 */
function enableCssAnimations(document) {
  document.documentElement.classList.add("tgui-animations-ready");
}

/**
 * In https://phabricator.wikimedia.org/T313409 #p-namespaces was renamed to #p-associatedPages
 * This code maps items added by gadgets to the new menu.
 * This code can be removed in MediaWiki 1.40.
 */
function addNamespacesGadgetSupport() {
  // Set up hidden dummy portlet.
  var dummyPortlet = document.createElement("div");
  dummyPortlet.setAttribute("id", "p-namespaces");
  dummyPortlet.setAttribute("style", "display: none;");
  dummyPortlet.appendChild(document.createElement("ul"));
  document.body.appendChild(dummyPortlet);
  mw.hook("util.addPortletLink").add(function (/** @type {Element} */ node) {
    // If it was added to p-namespaces, show warning and move.
    // eslint-disable-next-line no-jquery/no-global-selector
    if ($("#p-namespaces").find(node).length) {
      // eslint-disable-next-line no-jquery/no-global-selector
      $("#p-associated-pages ul").append(node);
      // @ts-ignore
      mw.log.warn("Please update call to mw.util.addPortletLink with ID p-namespaces. Use p-associatedPages instead.");
      // in case it was empty before:
      mw.util.showPortlet("p-associated-pages");
    }
  });
}

/**
 * @param {Window} window
 * @return {void}
 */
function main(window) {
  const config = require("./config.json");

  enableCssAnimations(window.document);
  collapsiblePortlets();
  sidebarPersistence.init();
  checkbox.init(window.document);
  initSearchLoader(document);
  languageButton();
  dropdownMenus();
  addNamespacesGadgetSupport();

  // Preference module
  if (config.wgTGUIEnablePreferences === true && typeof document.createElement("nav").prepend === "function") {
    mw.loader.load("skins.tgui.preferences");
  }
}

/**
 * @param {Window} window
 * @return {void}
 */
function init(window) {
  var now = mw.now();
  mw.loader.using("ext.eventLogging").then(function () {
    if (
      mw.eventLog &&
      mw.eventLog.eventInSample(100 /* 1 in 100 */) &&
      window.performance &&
      window.performance.timing &&
      window.performance.timing.navigationStart
    ) {
      mw.track("timing.TGUI.ready", now - window.performance.timing.navigationStart); // milliseconds
    }
  });
}

init(window);

function initAfterEs6Module() {
  mw.loader.using("skins.tgui.es6").then(
    function () {
      // Loading of the 'skins.tgui.es6' module has succeeded. Initialize the
      // `skins.tgui.es6` module first.
      require(/** @type {string} */ ("skins.tgui.es6")).main();
      // Initialize this module second.
      main(window);
    },
    function () {
      // Loading of the 'skins.tgui.es6' has failed (e.g. this will fail in
      // browsers that don't support ES6) so only initialize this module.
      main(window);
    },
  );
}

if (document.readyState === "interactive" || document.readyState === "complete") {
  initAfterEs6Module();
} else {
  // This is needed when document.readyState === 'loading'.
  document.addEventListener("DOMContentLoaded", function () {
    initAfterEs6Module();
  });
}
