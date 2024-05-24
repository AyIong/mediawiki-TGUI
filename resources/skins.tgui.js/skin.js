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
 * Register service worker
 *
 * @return {void}
 */
function registerServiceWorker() {
  const scriptPath = mw.config.get("wgScriptPath");

  // Only allow serviceWorker when the scriptPath is at root because of its scope
  // I can't figure out how to add the Service-Worker-Allowed HTTP header
  // to change the default scope
  if (scriptPath === "") {
    if ("serviceWorker" in navigator) {
      const SW_MODULE_NAME = "skins.tgui.serviceWorker",
        version = mw.loader.moduleRegistry[SW_MODULE_NAME].version,
        // HACK: Faking a RL link
        swUrl =
          scriptPath + "/load.php?modules=" + SW_MODULE_NAME + "&only=scripts&raw=true&skin=tgui&version=" + version;
      navigator.serviceWorker.register(swUrl, { scope: "/" });
    }
  }
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

  // Preference module
  if (config.wgTGUIEnablePreferences === true && typeof document.createElement("div").prepend === "function") {
    mw.loader.load("skins.tgui.preferences");
  }
}

registerServiceWorker();

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
