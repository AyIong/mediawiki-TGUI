/**
 * JavaScript enhancement to persist the sidebar state for logged-in users.
 *
 */

var SIDEBAR_BUTTON_ID = "mw-sidebar-button";
var SIDEBAR_CHECKBOX_ID = "mw-sidebar-checkbox";
var SIDEBAR_PREFERENCE_NAME = "TGUISidebarVisible";

function restoreSidebarState() {
  var sidebarVisible = localStorage.getItem(SIDEBAR_PREFERENCE_NAME) === "1";
  var checkbox = document.getElementById(SIDEBAR_CHECKBOX_ID);
  if (checkbox) {
    checkbox.checked = sidebarVisible;
    window.dispatchEvent(new Event("resize"));
  }
}

function saveSidebarState(checkbox) {
  localStorage.setItem(SIDEBAR_PREFERENCE_NAME, checkbox.checked ? "1" : "0");
  window.dispatchEvent(new Event("resize"));
}

function bindSidebarClickEvent(checkbox, button) {
  if (checkbox instanceof HTMLInputElement && button) {
    checkbox.addEventListener("input", function () {
      saveSidebarState(checkbox);
    });
  }
}

function init() {
  var checkbox = window.document.getElementById(SIDEBAR_CHECKBOX_ID);
  var button = window.document.getElementById(SIDEBAR_BUTTON_ID);

  if (checkbox && button) {
    bindSidebarClickEvent(checkbox, button);
    restoreSidebarState();
  }
}

document.addEventListener("DOMContentLoaded", init);

module.exports = {
  init: init,
};
