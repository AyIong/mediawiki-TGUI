/*
 * TGUI
 *
 * Inline script used in includes/SkinHooks.php
 */
const LEGACY_PREFIX = 'skin-tgui-';

/**
 * Backported from MW 1.42
 * Modified to use localStorage only
 */
window.clientPrefs = () => {
  let className = document.documentElement.className;
  const storage = localStorage.getItem('mwclientpreferences');
  if (storage) {
    // TODO: Just use array for localStorage
    storage.split(',').forEach(function (pref) {
      className = className.replace(
        // eslint-disable-next-line security/detect-non-literal-regexp
        new RegExp('(^| )' + pref.replace(/-clientpref-\w+$|[^\w-]+/g, '') + '-clientpref-\\w+( |$)'),
        '$1' + pref + '$2',
      );

      if (pref.includes('-slider')) {
        const variableName = `--${pref.replace(/^tgui-feature-(.*?)-slider-clientpref-.*$/, '$1')}`;
        const value = pref.match(/-clientpref-(\w+)$/)[1];
        document.documentElement.style.setProperty(variableName, value);
      }

      // Legacy support
      if (pref.startsWith('skin-theme-clientpref-')) {
        const CLIENTPREFS_THEME_MAP = {
          os: 'auto',
          day: 'light',
          night: 'dark',
        };
        const matchedKey = CLIENTPREFS_THEME_MAP[pref.replace('skin-theme-clientpref-', '')];
        if (matchedKey) {
          // eslint-disable-next-line max-len, es-x/no-object-values
          const classesToRemove = Object.values(CLIENTPREFS_THEME_MAP).map((theme) => LEGACY_PREFIX + theme);
          className = className.replace(
            // eslint-disable-next-line security/detect-non-literal-regexp
            new RegExp(classesToRemove.join('|'), 'g'),
            '',
          );
          className += ` ${LEGACY_PREFIX}${matchedKey}`;
        }
      }
    });
    document.documentElement.className = className;
  }
};

(() => {
  window.clientPrefs();
})();
