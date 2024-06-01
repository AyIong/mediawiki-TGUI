var languageButton = require("./languageButton.js"),
  initSearchLoader = require("./searchLoader.js").initSearchLoader,
  dropdownMenus = require("./dropdownMenus.js").dropdownMenus,
  checkbox = require("./checkbox.js"),
  purgeButton = require("./purgeButton.js");

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
  checkbox.init(window.document);
  initSearchLoader(document);
  languageButton();
  dropdownMenus();
  purgeButton();

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
      require(/** @type {string} */ ("skins.tgui.es6")).main();
      main(window);
    },
    function () {
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
