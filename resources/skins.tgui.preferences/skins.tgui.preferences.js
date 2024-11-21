const CLASS = 'tgui-pref';

/**
 * Clientprefs names theme differently from TGUI.
 * TODO: Migrate to clientprefs fully on MW 1.43
 */
const CLIENTPREFS_THEME_MAP = {
  auto: 'os',
  light: 'day',
  dark: 'night',
};

const clientPrefs = require('./clientPrefs.polyfill.js')();

/**
 * Test if storage is avaliable
 * Taken from https://developer.mozilla.org/en-US/docs/Web/API/Web_Storage_API/Using_the_Web_Storage_API
 *
 * TODO: Move it to better place
 *
 * @param {string} type
 * @return {boolean|Error}
 */
function storageAvailable(type) {
  let storage;

  try {
    storage = window[type];
    const x = '__storage_test__';
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
        e.name === 'QuotaExceededError' ||
        // Firefox
        e.name === 'NS_ERROR_DOM_QUOTA_REACHED') &&
      // acknowledge QuotaExceededError only if there's something already stored
      storage &&
      storage.length !== 0
    );
  }
}

/**
 * Load client preferences based on the existence of 'tgui-preferences' element.
 */
function loadClientPreferences() {
  const clientPreferenceId = 'tgui-preferences__content';
  const clientPreferenceExists = document.getElementById(clientPreferenceId) !== null;
  if (!clientPreferenceExists) {
    return;
  }

  const clientPreferences = require(/** @type {string} */ ('./clientPreferences.js'));
  const clientPreferenceConfig = require('./clientPreferences.json');

  clientPreferenceConfig['skin-theme'].callback = () => {
    const LEGACY_THEME_CLASSES = ['skin-tgui-auto', 'skin-tgui-light', 'skin-tgui-dark'];
    const legacyThemeKey = Object.keys(CLIENTPREFS_THEME_MAP).find(
      (key) => CLIENTPREFS_THEME_MAP[key] === clientPrefs.get('skin-theme'),
    );
    document.documentElement.classList.remove(...LEGACY_THEME_CLASSES);
    document.documentElement.classList.add(`skin-tgui-${legacyThemeKey}`);
  };

  const details = document.getElementById('tgui-preferences-details');
  if (!localStorage.getItem('prefChecked')) {
    localStorage.setItem('prefChecked', 'true');
    details.classList.remove(CLASS + '-notice');
  }

  clientPreferences.render(`#${clientPreferenceId}`, clientPreferenceConfig);
}

/**
 * Set up the listen for preferences button
 *
 * @return {void}
 */
function listenForButtonClick() {
  const details = document.getElementById('tgui-preferences-details');
  if (!details) {
    return;
  }

  if (storageAvailable('localStorage') && !localStorage.getItem('prefChecked')) {
    details.classList.add(CLASS + '-notice');
  }

  details.addEventListener('click', loadClientPreferences, { once: true });
}

listenForButtonClick();
