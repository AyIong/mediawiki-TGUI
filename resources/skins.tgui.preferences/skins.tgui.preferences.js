const CLASS = "tgui-pref";

/**
 * Clientprefs names theme differently from TGUI.
 * TODO: Migrate to clientprefs fully on MW 1.43
 */
const CLIENTPREFS_THEME_MAP = {
  auto: "os",
  light: "day",
  dark: "night",
};

const clientPrefs = require("./clientPrefs.polyfill.js")();

/**
 * Dismiss the prefernce panel when clicked outside
 *
 * @param {Event} event
 */
function dismissOnClickOutside(event) {
  const pref = document.getElementById(CLASS);

  if (event.target instanceof Node && !pref.contains(event.target)) {
    const panel = document.getElementById(CLASS + "-panel");

    if (panel.classList.contains(CLASS + "-panel--active")) {
      togglePanel();
    }
  }
}

/**
 * Dismiss the prefernce panel when ESCAPE is pressed
 *
 * @param {Event} event
 */
function dismissOnEscape(event) {
  if (event.key !== "Escape") {
    return;
  }

  togglePanel();
}

/**
 * Add/remove toggle class and form input eventlistener
 *
 * @return {void}
 */
function togglePanel() {
  // .tgui-pref-panel--active
  const CLASS_PANEL_ACTIVE = CLASS + "-panel--active";
  const toggle = document.getElementById(CLASS + "-toggle"),
    panel = document.getElementById(CLASS + "-panel");

  if (!panel.classList.contains(CLASS_PANEL_ACTIVE)) {
    panel.classList.add(CLASS_PANEL_ACTIVE);
    toggle.setAttribute("aria-expanded", true);
    window.addEventListener("click", dismissOnClickOutside);
    window.addEventListener("keydown", dismissOnEscape);
  } else {
    panel.classList.remove(CLASS_PANEL_ACTIVE);
    toggle.setAttribute("aria-expanded", false);
    window.removeEventListener("click", dismissOnClickOutside);
    window.removeEventListener("keydown", dismissOnEscape);
  }
}

/**
 * Set up the DOM and initial input states for the panel
 * It only loads when user first clicked the toggle
 *
 * @param {Event} event
 * @return {void}
 */
function initPanel(event) {
  const panel = document.createElement("aside");
  panel.id = "tgui-pref-panel";
  panel.classList.add("tgui-pref-panel");

  const header = document.createElement("header");
  header.id = "tgui-pref-header";
  header.textContent = mw.message("preferences").text();

  const container = document.createElement("div");
  container.id = "tgui-client-prefs";
  container.classList.add("tgui-client-prefs");

  panel.append(header, container);

  // Attach panel after button
  event.currentTarget.parentNode.insertBefore(panel, event.currentTarget.nextSibling);

  event.currentTarget.addEventListener("click", togglePanel);

  const clientPreferenceSelector = "#tgui-client-prefs";
  const clientPreferenceExists = document.querySelectorAll(clientPreferenceSelector).length > 0;
  if (clientPreferenceExists) {
    const clientPreferences = require(/** @type {string} */ ("./clientPreferences.js"));
    const clientPreferenceConfig = require("./clientPreferences.json");

    // Support legacy skin-tgui-* class
    // TODO: Remove it in the future version after sufficient time
    clientPreferenceConfig["skin-theme"].callback = () => {
      const LEGACY_THEME_CLASSES = ["skin-tgui-auto", "skin-tgui-light", "skin-tgui-dark"];
      const legacyThemeKey = Object.keys(CLIENTPREFS_THEME_MAP).find((key) => {
        return CLIENTPREFS_THEME_MAP[key] === clientPrefs.get("skin-theme");
      });
      document.documentElement.classList.remove(...LEGACY_THEME_CLASSES);
      document.documentElement.classList.add(`skin-tgui-${legacyThemeKey}`);
    };

    clientPreferences.render(clientPreferenceSelector, clientPreferenceConfig);
  }
}

/**
 * Test if storage is avaliable
 * Taken from https://developer.mozilla.org/en-US/docs/Web/API/Web_Storage_API/Using_the_Web_Storage_API
 *
 * @param {string} type
 * @return {boolean|Error}
 */
function storageAvailable(type) {
  let storage;

  try {
    storage = window[type];
    const x = "__storage_test__";
    storage.setItem(x, x);
    storage.removeItem(x);
    return true;
  } catch (/** @type {Error} */ e) {
    return (
      e instanceof DOMException &&
      // everything except Firefox
      (e.code === 22 ||
        // Firefox
        e.code === 1014 ||
        // test name field too, because code might not be present
        // everything except Firefox
        e.name === "QuotaExceededError" ||
        // Firefox
        e.name === "NS_ERROR_DOM_QUOTA_REACHED") &&
      // acknowledge QuotaExceededError only if there's something already stored
      storage &&
      storage.length !== 0
    );
  }
}

/**
 * Set up the container and toggle
 *
 * @param {Window} window
 * @return {void}
 */
function initPref(window) {
  // Object.fromEntries() polyfill https://github.com/feross/fromentries
  // MIT. Copyright (c) Feross Aboukhadijeh.
  if (typeof Object.fromEntries !== "function") {
    Object.defineProperty(Object, "fromEntries", {
      value(iterable) {
        return Array.from(iterable).reduce((obj, [key, val]) => {
          obj[key] = val;
          return obj;
        }, {});
      },
    });
  }

  if (storageAvailable("localStorage")) {
    if (typeof window.mw !== "undefined") {
      const headerTools = document.querySelector(".tgui-user-links"),
        container = document.createElement("div"),
        button = document.createElement("button"),
        icon = document.createElement("span");

      // tgui-pref
      container.id = CLASS;

      container.classList.add(CLASS, "tgui-header__item");
      button.id = CLASS + "-toggle";
      button.classList.add(CLASS + "__button", "tgui-header__button", "tgui-button");
      button.setAttribute("title", mw.message("preferences").text());
      button.setAttribute("aria-label", mw.message("preferences").text());
      button.setAttribute("aria-controls", CLASS + "-panel");
      button.setAttribute("aria-expanded", false);
      icon.classList.add("tgui-icon", "tgui-icon-settings");
      button.prepend(icon);
      container.prepend(button);
      headerTools.prepend(container);

      initPanel({ currentTarget: button });
    }
  } else {
    // eslint-disable-next-line no-console
    console.log("Preference module is disabled due to localStoarge being not avaliable.");
  }
}

initPref(window);
