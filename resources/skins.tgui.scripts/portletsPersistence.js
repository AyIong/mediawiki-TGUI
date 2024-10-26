async function init() {
  const panel = await waitForElement("mw-panel");
  if (!panel) return;
  panel.classList.add("collapsible-nav");

  const first = panel.querySelector(".portal:not(.persistent)");
  first.classList.add("first");
  first.classList.remove("collapsible-nav");

  const menus = panel.querySelectorAll(".portal:not(.persistent)");
  menus.forEach(function (menu, index) {
    const id = menu.id;
    let state;
    if (index !== 0) {
      state = localStorage.getItem("TGUI" + "-nav-" + id);
      if (state === null) {
        localStorage.setItem("TGUI" + "-nav-" + id, JSON.stringify(true));
        state = true;
      } else {
        state = JSON.parse(state);
      }
    } else {
      state = true;
    }

    if (state === true || (state === null && menu === menus[0]) || (state === null && id === "p-lang")) {
      menu.classList.add("expanded");
      menu.classList.remove("collapsed");

      const content = menu.querySelector(".tgui-menu-content");
      const height = content.getAttribute("max-height");
      if (content) {
        content.style.maxHeight = height;
      }

      const anchor = menu.querySelector(".tgui-menu-heading > a");
      if (anchor) {
        anchor.setAttribute("aria-pressed", "true");
        anchor.setAttribute("aria-expanded", "true");
      }
    } else {
      menu.classList.add("collapsed");
      menu.classList.remove("expanded");

      const anchor = menu.querySelector(".tgui-menu-heading > a");
      if (anchor) {
        anchor.setAttribute("aria-pressed", "false");
        anchor.setAttribute("aria-expanded", "false");
      }
    }
  });
  handleEvents();
}

function toggleMenu(menu) {
  const isCollapsed = menu.classList.contains("collapsed");
  const content = menu.querySelector(".tgui-menu-content");

  menu.classList.toggle("expanded");
  menu.classList.toggle("collapsed");
  localStorage.setItem("TGUI" + "-nav-" + menu.id, JSON.stringify(isCollapsed));

  const height = content.getAttribute("max-height");
  if (content) {
    if (isCollapsed) {
      content.style.maxHeight = height;
    } else {
      content.style.maxHeight = "0";
    }
  }

  const anchor = menu.querySelector(".tgui-menu-heading > a");
  if (anchor) {
    anchor.setAttribute("aria-pressed", isCollapsed ? "false" : "true");
    anchor.setAttribute("aria-expanded", isCollapsed ? "false" : "true");
  }
}

function handleEvents() {
  const panel = document.getElementById("mw-panel");
  if (!panel) return;

  panel.addEventListener("click", function (event) {
    const target = event.target;
    const heading = target.closest(".tgui-menu-heading");
    if (heading) {
      const menu = heading.parentElement;
      if (!menu.classList.contains("first")) {
        toggleMenu(menu);
        event.preventDefault();
      }
    }
  });
}

async function setMaxHeightForContent() {
  const menus = await waitForElements(".tgui-menu-content");
  menus.forEach(function (menu) {
    const height = menu.scrollHeight + "px";
    menu.setAttribute("max-height", height);
  });
}

async function main() {
  await setMaxHeightForContent();
  await init();
}

requestAnimationFrame(main);
