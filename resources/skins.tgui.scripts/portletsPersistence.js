function collapsiblePortlets() {
  "use strict";

  function toggleMenu(menu) {
    var isCollapsed = menu.classList.contains("collapsed");
    var content = menu.querySelector(".tgui-menu-content");

    menu.classList.toggle("expanded");
    menu.classList.toggle("collapsed");
    localStorage.setItem("TGUI" + "-nav-" + menu.id, JSON.stringify(isCollapsed));

    if (content) {
      if (isCollapsed) {
        content.style.display = "block";
      } else {
        content.style.display = "none";
      }
    }

    var anchor = menu.querySelector(".tgui-menu-heading > a");
    if (anchor) {
      anchor.setAttribute("aria-pressed", isCollapsed ? "false" : "true");
      anchor.setAttribute("aria-expanded", isCollapsed ? "false" : "true");
    }
  }

  function handleEvents() {
    var panel = document.getElementById("mw-panel");
    if (!panel) return;

    panel.addEventListener("click", function (event) {
      var target = event.target;
      if (target && target.classList.contains("tgui-menu-heading")) {
        toggleMenu(target.parentElement);
        event.preventDefault();
      }
    });
  }

  function init() {
    var panel = document.getElementById("mw-panel");
    if (!panel) return;
    panel.classList.add("collapsible-nav");

    var menus = panel.querySelectorAll(".portal:not(.persistent)");
    menus.forEach(function (menu) {
      var id = menu.id;
      var state = localStorage.getItem("TGUI" + "-nav-" + id);
      if (state === null) {
        localStorage.setItem("TGUI" + "-nav-" + id, JSON.stringify(true));
        state = true;
      } else {
        state = JSON.parse(state);
      }

      if (state === true || (state === null && menu === menus[0]) || (state === null && id === "p-lang")) {
        menu.classList.add("expanded");
        menu.classList.remove("collapsed");

        var content = menu.querySelector(".tgui-menu-content");
        if (content) {
          content.style.display = "block";
        }

        var anchor = menu.querySelector(".tgui-menu-heading > a");
        if (anchor) {
          anchor.setAttribute("aria-pressed", "true");
          anchor.setAttribute("aria-expanded", "true");
        }
      } else {
        menu.classList.add("collapsed");
        menu.classList.remove("expanded");

        var anchor = menu.querySelector(".tgui-menu-heading > a");
        if (anchor) {
          anchor.setAttribute("aria-pressed", "false");
          anchor.setAttribute("aria-expanded", "false");
        }
      }
    });
    handleEvents();
  }
  init();
}

document.addEventListener("DOMContentLoaded", function () {
  collapsiblePortlets();
});
