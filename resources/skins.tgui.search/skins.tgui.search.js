/** @module search */

const Vue = require("vue"),
  App = require("./App.vue"),
  config = require("./config.json");

/**
 * @param {Element} searchBox
 * @return {void}
 */
function initApp(searchBox) {
  const searchForm = searchBox.querySelector(".tgui-search-box-form"),
    titleInput = /** @type {HTMLInputElement|null} */ (searchBox.querySelector("input[name=title]")),
    search = /** @type {HTMLInputElement|null} */ (searchBox.querySelector('input[name="search"]')),
    searchPageTitle = titleInput && titleInput.value;

  if (!searchForm || !search || !titleInput) {
    throw new Error("Attempted to create Vue search element from an incompatible element.");
  }

  // @ts-ignore
  Vue.createMwApp(
    App,
    $.extend(
      {
        id: searchForm.id,
        autofocusInput: search === document.activeElement,
        action: searchForm.getAttribute("action"),
        searchAccessKey: search.getAttribute("accessKey"),
        searchPageTitle: searchPageTitle,
        searchTitle: search.getAttribute("title"),
        searchPlaceholder: search.getAttribute("placeholder"),
        searchQuery: search.value,
        autoExpandWidth: searchBox ? searchBox.classList.contains("tgui-search-box-auto-expand-width") : false,
        // Pass additional config from server.
      },
      config,
    ),
  ).mount(searchForm.parentNode);
}
/**
 * @param {Document} document
 * @return {void}
 */
function main(document) {
  const searchBoxes = document.querySelectorAll(".tgui-search-box");

  searchBoxes.forEach((searchBox) => {
    initApp(searchBox);
  });
}
main(document);
