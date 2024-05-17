function createThemeToggleButton() {
  var container = document.getElementById("p-personal");
  if (container) {
    var checkbox = document.createElement("input");
    checkbox.type = "checkbox";
    checkbox.id = "theme-toggle";

    var label = document.createElement("label");
    label.htmlFor = "theme-toggle";
    label.id = "theme-button";

    container.parentNode.insertBefore(checkbox, container);
    container.parentNode.insertBefore(label, container);
  }

  var isLightTheme = localStorage.getItem("isLightTheme");
  if (isLightTheme === "true") {
    document.documentElement.classList.add("tgui-light");
  } else {
    document.documentElement.classList.remove("tgui-light");
  }

  $("#theme-toggle").change(function () {
    toggleTheme();
  });
}

function toggleTheme() {
  var isLightTheme = document.documentElement.classList.toggle("tgui-light");
  localStorage.setItem("isLightTheme", isLightTheme);
}

/**
 * Initialize the toggleTheme button.
 */
module.exports = function () {
  createThemeToggleButton();
};
