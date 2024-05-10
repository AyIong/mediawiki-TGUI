/**
 * Collapsible navigation for TGUI
 * Used a CollapsibleVector extention
 * https://www.mediawiki.org/wiki/Extension:CollapsibleVector
 */
function collapsiblePortlets() {
  "use strict";
  // Use the same function for all navigation headings - don't repeat
  function toggle($element) {
    var isCollapsed = $element.parent().is(".collapsed");

    localStorage.setItem(
      "tgui" + "-nav-" + $element.parent().attr("id"),
      JSON.stringify(isCollapsed)
    );

    $element
      .parent()
      .toggleClass("expanded")
      .toggleClass("collapsed")
      .find(".tgui-menu-content")
      .slideToggle("fast");
    isCollapsed = !isCollapsed;

    $element.find("> a").attr({
      "aria-pressed": isCollapsed ? "false" : "true",
      "aria-expanded": isCollapsed ? "false" : "true",
    });
  }

  function executeMain($) {
    var $headings;

    /* General Portal Modification */

    // Always show the first portal
    $("#mw-panel > .portal:first").addClass("first persistent");
    // Apply a class to the entire panel to activate styles
    $("#mw-panel").addClass("collapsible-nav");
    // Use localStorage data to restore preferences of what to show and hide
    $("#mw-panel > .portal:not(.persistent)").each(function (i) {
      var id = $(this).attr("id"),
        state = JSON.parse(localStorage.getItem("tgui" + "-nav-" + id));
      $(this)
        .find("ul:first")
        .attr("id", id + "-list");
      // Add anchor tag to heading for better accessibility
      $(this)
        .find(".tgui-menu-heading")
        .wrapInner(
          $("<a>")
            .attr({
              href: "#",
              "aria-haspopup": "true",
              "aria-controls": id + "-list",
              role: "button",
            })
            .on("click", false)
        );
      // In the case that we are not showing the new version, let's show the languages by default
      if (
        state === true ||
        (state === null && i < 1) ||
        (state === null && id === "p-lang")
      ) {
        $(this)
          .addClass("expanded")
          .removeClass("collapsed")
          .find(".tgui-menu-content")
          .hide() // bug 34450
          .show();
        $(this).find(".tgui-menu-heading > a").attr({
          "aria-pressed": "true",
          "aria-expanded": "true",
        });
      } else {
        $(this).addClass("collapsed").removeClass("expanded");
        $(this).find(".tgui-menu-heading > a").attr({
          "aria-pressed": "false",
          "aria-expanded": "false",
        });
      }
      // Re-save localStorage
      if (state !== null) {
        localStorage.setItem(
          "tgui" + "-nav-" + $(this).attr("id"),
          JSON.stringify(state)
        );
      }
    });

    /* Tab Indexing */

    $headings = $("#mw-panel > .portal:not(.persistent) > .tgui-menu-heading");

    // Make it keyboard accessible
    $headings.attr("tabindex", "0");

    // Toggle the selected menu's class and expand or collapse the menu
    $("#mw-panel")
      .on(
        "keydown",
        ".portal:not(.persistent) > .tgui-menu-heading",
        function (e) {
          // Make the space and enter keys act as a click
          if (e.which === 13 /* Enter */ || e.which === 32 /* Space */) {
            toggle($(this));
          }
        }
      )
      .on(
        "mousedown",
        ".portal:not(.persistent) > .tgui-menu-heading",
        function (e) {
          if (e.which !== 3) {
            // Right mouse click
            toggle($(this));
            $(this).trigger("blur");
          }
          return false;
        }
      );
  }

  $(function ($) {
    mw.hook("wikipage.content").add(function () {
      executeMain($);
    });
  });
}

module.exports = function () {
  collapsiblePortlets();
};
