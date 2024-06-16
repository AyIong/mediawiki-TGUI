function collapsiblePortlets() {
  "use strict";

  function toggleMenu(menu) {
    var isCollapsed = menu.classList.contains("collapsed");
    var content = menu.querySelector(".tgui-menu-content");

    menu.classList.toggle("expanded");
    menu.classList.toggle("collapsed");
    localStorage.setItem("TGUI" + "-nav-" + menu.id, JSON.stringify(isCollapsed));

    var height = content.getAttribute("max-height");
    if (content) {
      if (isCollapsed) {
        content.style.maxHeight = height;
      } else {
        content.style.maxHeight = "0";
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
      var heading = target.closest(".tgui-menu-heading");
      if (heading) {
        var menu = heading.parentElement;
        if (!menu.classList.contains("first")) {
          toggleMenu(menu);
          event.preventDefault();
        }
      }
    });
  }

  function init() {
    var panel = document.getElementById("mw-panel");
    if (!panel) return;
    panel.classList.add("collapsible-nav");

    var first = panel.querySelector(".portal:not(.persistent)");
    first.classList.add("first");
    first.classList.remove("collapsible-nav");

    var menus = panel.querySelectorAll(".portal:not(.persistent)");
    menus.forEach(function (menu, index) {
      var id = menu.id;
      var state;
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

        var content = menu.querySelector(".tgui-menu-content");
        var height = content.getAttribute("max-height");
        if (content) {
          content.style.maxHeight = height;
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

function setMaxHeightForContent() {
  var menus = document.querySelectorAll(".tgui-menu-content");
  menus.forEach(function (menu) {
    var height = menu.scrollHeight + "px";
    menu.setAttribute("max-height", height);
  });
}

document.addEventListener("DOMContentLoaded", function () {
  requestAnimationFrame(setMaxHeightForContent);
  requestAnimationFrame(collapsiblePortlets);
});
