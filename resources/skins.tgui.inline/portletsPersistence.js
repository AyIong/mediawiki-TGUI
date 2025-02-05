/**
 * Initializes portlets by setting menu states and heights based on saved preferences.
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
  menus.forEach(function (menu, index) {
    let state;
    const id = menu.id;
    // Get current portlets states from localStorage
    // If none is found, set it to true
    state = localStorage.getItem('TGUI' + '-nav-' + id);
    if (state === null) {
      localStorage.setItem('TGUI' + '-nav-' + id, JSON.stringify(true));
      state = true;
    } else {
      state = JSON.parse(state);
    }

    // Set initial max height for every menu content
    const content = menu.querySelector('.tgui-menu__content');
    const initialHeight = content.scrollHeight + 'px';
    content.setAttribute('data-height', initialHeight);
    console.log(`Setted initial height for menu ${content.id}: ${initialHeight}`);

    // Set menu state
    if (state === true) {
      menu.classList.remove('collapsed');

      if (content) {
        content.style.maxHeight = initialHeight;
      }
    } else {
      menu.classList.add('collapsed');

      if (content) {
        content.style.maxHeight = 0;
      }
    }

    const anchor = menu.querySelector('.tgui-menu__heading');
    handleEvents(menu, anchor, content);
  });
}

/**
 * Attaches a click event listener to the anchor element within the menu.
 * Toggles the visibility of the menu content when the anchor is clicked.
 *
 * @param {HTMLElement} menu - The menu element containing the anchor and content.
 * @param {HTMLElement} anchor - The clickable heading element of the menu.
 * @param {HTMLElement} content - The content element whose visibility is toggled.
 */
function handleEvents(menu, anchor, content) {
  if (!menu || !content || !anchor) {
    return;
  }

  anchor.addEventListener('click', function (event) {
    toggleMenu(menu, content);
    event.preventDefault();
  });
}

/**
 * Toggles the visibility of the given menu content.
 *
 * If the menu is collapsed, it sets the max-height of the content to the
 * value of the data-height attribute, otherwise it sets it to 0.
 *
 * @param {HTMLElement} menu - The menu element containing the content.
 * @param {HTMLElement} content - The content element whose visibility is toggled.
 */
function toggleMenu(menu, content) {
  if (!menu || !content) {
    return;
  }

  const isCollapsed = menu.classList.contains('collapsed');
  menu.classList.toggle('collapsed');
  localStorage.setItem('TGUI' + '-nav-' + menu.id, JSON.stringify(isCollapsed));

  const height = content.getAttribute('data-height');
  if (isCollapsed) {
    content.style.maxHeight = height;
  } else {
    content.style.maxHeight = '0';
  }
}

async function main() {
  await initPortlets();
}

main();
