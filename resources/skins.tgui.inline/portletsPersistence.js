/**
 * Initializes portlets by setting menu states based on saved preferences.
 * Waits for the 'tgui-panel' element before proceeding. Assigns the 'first' class
 * to the first menu and retrieves menus that aren't the first. For each menu, retrieves
 * its state from localStorage, setting it to true if not found. Sets the initial
 * max height for each menu content and applies the saved state by collapsing or expanding
 * the menu. Binds events to toggle the menu state on interaction.
 */
async function initPortlets() {
  const panel = await waitForElement('tgui-panel');
  if (!panel) {
    return;
  }

  const panelContent = panel.querySelector('.tgui-sidebar-content');
  panelContent.querySelector('.tgui-menu').classList.add('first');

  const menus = panelContent.querySelectorAll('.tgui-menu:not(.first)');
  for (const menu of menus) {
    const id = menu.id;
    let openState = localStorage.getItem(`TGUI-nav-${id}`);

    // Get current portlets state from localStorage
    // If none is found, set it to true
    if (openState === null || openState === undefined) {
      localStorage.setItem(`TGUI-nav-${id}`, 'true');
      openState = 'true';
    }

    // Set menu state
    if (openState === 'true') {
      menu.classList.remove('collapsed');
    } else {
      menu.classList.add('collapsed');
    }

    const anchor = menu.querySelector('.tgui-menu__heading');
    handleEvents(menu, anchor);
  }
}

/**
 * Attaches a click event listener to the anchor element within the menu.
 *
 * @param {HTMLElement} menu - The menu element containing the anchor.
 * @param {HTMLElement} anchor - The clickable heading element of the menu.
 */
function handleEvents(menu, anchor) {
  if (!menu || !anchor) {
    return;
  }

  anchor.addEventListener('click', (event) => {
    event.preventDefault();
    toggleMenu(menu);
  });
}

/**
 * Toggles the collapsed state class of the given menu.
 *
 * @param {HTMLElement} menu - The menu element containing the content.
 */
function toggleMenu(menu) {
  if (!menu) {
    return;
  }

  const isCollapsed = menu.classList.contains('collapsed');
  menu.classList.toggle('collapsed');
  localStorage.setItem(`TGUI-nav-${menu.id}`, isCollapsed ? 'true' : 'false');
}

function main() {
  initPortlets();
}

main();
