const SIDEBAR_MOBILE_ID = 'tgui-panel-mbg';
const SIDEBAR_BUTTON_ID = 'tgui-sidebar-button';
const SIDEBAR_CHECKBOX_ID = 'tgui-sidebar-checkbox';
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

function bindSidebarClickEvent(checkbox, button) {
  if (checkbox instanceof HTMLInputElement && button) {
    checkbox.addEventListener('input', () => {
      saveSidebarState(checkbox);
    });
  }
}

function saveSidebarState(checkbox) {
  localStorage.setItem(SIDEBAR_PREFERENCE_NAME, checkbox.checked ? 'true' : 'false');
  window.dispatchEvent(new Event('resize'));
}

async function initSidebar() {
  const [checkbox, button, mobile] = await Promise.all([
    waitForElement(SIDEBAR_CHECKBOX_ID),
    waitForElement(SIDEBAR_BUTTON_ID),
    waitForElement(SIDEBAR_MOBILE_ID),
  ]);

  if (window.matchMedia('(min-width: 1119px)').matches) {
    if (checkbox && button) {
      bindSidebarClickEvent(checkbox, button);
      restoreSidebarState();
    }
  } else {
    document.addEventListener('scroll', () => {
      checkbox.checked = false;
    });
  }

  if (mobile) {
    const events = ['mousedown', 'touchstart'];
    for (const event of events) {
      mobile.addEventListener(event, () => {
        checkbox.checked = false;
      });
    }
  }
}

initSidebar();
