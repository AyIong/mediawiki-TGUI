const SIDEBAR_BUTTON_ID = 'mw-sidebar-button';
const SIDEBAR_CHECKBOX_ID = 'mw-sidebar-checkbox';
const SIDEBAR_PREFERENCE_NAME = 'TGUI-SidebarVisible';

function restoreSidebarState() {
  // Check if the key exists in localStorage, if not, set it to true
  if (localStorage.getItem(SIDEBAR_PREFERENCE_NAME) === null) {
    localStorage.setItem(SIDEBAR_PREFERENCE_NAME, 'true');
  }

  const sidebarVisible = localStorage.getItem(SIDEBAR_PREFERENCE_NAME) === 'true';
  const checkbox = document.getElementById(SIDEBAR_CHECKBOX_ID);
  if (checkbox) {
    checkbox.checked = sidebarVisible;
    window.dispatchEvent(new Event('resize'));
  }
}

function saveSidebarState(checkbox) {
  localStorage.setItem(SIDEBAR_PREFERENCE_NAME, checkbox.checked ? 'true' : 'false');
  window.dispatchEvent(new Event('resize'));
}

function bindSidebarClickEvent(checkbox, button) {
  if (checkbox instanceof HTMLInputElement && button) {
    checkbox.addEventListener('input', function () {
      saveSidebarState(checkbox);
    });
  }
}

async function initSidebar() {
  const [checkbox, button] = await Promise.all([
    waitForElement(SIDEBAR_CHECKBOX_ID),
    waitForElement(SIDEBAR_BUTTON_ID),
  ]);

  if (checkbox && button) {
    bindSidebarClickEvent(checkbox, button);
    restoreSidebarState();
  }
}

requestAnimationFrame(initSidebar);
