async function initPortlets() {
  const panel = await waitForElement('tgui-panel');
  if (!panel) {
    return;
  }

  const panelContent = panel.querySelector('.tgui-sidebar-content');
  const first = panelContent.querySelector('.tgui-menu');
  first.classList.add('first');

  const menus = panelContent.querySelectorAll('.tgui-menu:not(.first)');
  menus.forEach(function (menu, index) {
    let state;
    const id = menu.id;

    state = localStorage.getItem('TGUI' + '-nav-' + id);
    if (state === null) {
      localStorage.setItem('TGUI' + '-nav-' + id, JSON.stringify(true));
      state = true;
    } else {
      state = JSON.parse(state);
    }

    if (state === true || state === null) {
      menu.classList.add('expanded');
      menu.classList.remove('collapsed');

      const content = menu.querySelector('.tgui-menu__content');
      const height = content.getAttribute('max-height');
      if (content) {
        content.style.maxHeight = height;
      }

      const anchor = menu.querySelector('.tgui-menu__heading');
      if (anchor) {
        anchor.setAttribute('aria-pressed', 'true');
        anchor.setAttribute('aria-expanded', 'true');
      }
    } else {
      menu.classList.add('collapsed');
      menu.classList.remove('expanded');

      const anchor = menu.querySelector('.tgui-menu__heading');
      if (anchor) {
        anchor.setAttribute('aria-pressed', 'false');
        anchor.setAttribute('aria-expanded', 'false');
      }
    }
  });
  handleEvents();
}

function toggleMenu(menu) {
  const isCollapsed = menu.classList.contains('collapsed');
  const content = menu.querySelector('.tgui-menu__content');

  menu.classList.toggle('expanded');
  menu.classList.toggle('collapsed');
  localStorage.setItem('TGUI' + '-nav-' + menu.id, JSON.stringify(isCollapsed));

  const height = content.getAttribute('max-height');
  if (content) {
    if (isCollapsed) {
      content.style.maxHeight = height;
    } else {
      content.style.maxHeight = '0';
    }
  }

  const anchor = menu.querySelector('.tgui-menu__heading');
  if (anchor) {
    anchor.setAttribute('aria-pressed', isCollapsed ? 'false' : 'true');
    anchor.setAttribute('aria-expanded', isCollapsed ? 'false' : 'true');
  }
}

function handleEvents() {
  const panel = document.getElementById('tgui-panel');
  if (!panel) return;

  panel.addEventListener('click', function (event) {
    const target = event.target;
    const heading = target.closest('.tgui-menu__heading');
    if (heading) {
      const menu = heading.parentElement;
      if (!menu.classList.contains('first')) {
        toggleMenu(menu);
        event.preventDefault();
      }
    }
  });
}

async function setMaxHeightForContent() {
  const panel = await waitForElement('tgui-panel');
  if (!panel) {
    return;
  }

  const menus = await waitForElements('.tgui-menu__content', panel);
  menus.forEach(function (menu) {
    const height = menu.scrollHeight + 'px';
    menu.setAttribute('max-height', height);
  });
}

async function main() {
  await setMaxHeightForContent();
  await initPortlets();
}

main();
