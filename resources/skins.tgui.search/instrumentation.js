/* global FetchEndEvent, SuggestionClickEvent, SearchSubmitEvent */
/**
 * The value of the `inputLocation` property of any and all SearchSatisfaction events sent by the
 * corresponding instrumentation.
 *
 * @see https://gerrit.wikimedia.org/r/plugins/gitiles/mediawiki/skins/TGUI/+/refs/heads/master/includes/Constants.php
 */
const INPUT_LOCATION_MOVED = "header-moved",
  wgScript = mw.config.get("wgScript"),
  // T251544: Collect search performance metrics to compare Vue search with
  // mediawiki.searchSuggest performance. Marks and Measures will only be
  // recorded on the TGUI skin and only if browser supported.
  shouldTestSearchPerformance = !!(
    window.performance &&
    // @ts-ignore
    window.requestAnimationFrame &&
    /* eslint-disable compat/compat */
    // @ts-ignore
    performance.mark &&
    // @ts-ignore
    performance.measure &&
    // @ts-ignore
    performance.getEntriesByName &&
    performance.clearMarks
  ),
  /* eslint-enable compat/compat */
  loadStartMark = "mwTGUIVueSearchLoadStart",
  queryMark = "mwTGUIVueSearchQuery",
  renderMark = "mwTGUIVueSearchRender",
  queryToRenderMeasure = "mwTGUIVueSearchQueryToRender",
  loadStartToFirstRenderMeasure = "mwTGUIVueSearchLoadStartToFirstRender";

function onFetchStart() {
  if (!shouldTestSearchPerformance) {
    return;
  }

  // Clear past marks that are no longer relevant. This likely means that the
  // search request failed or was cancelled. Whatever the reason, the mark
  // is no longer needed since we are only interested in collecting the time
  // from query to render.
  if (performance.getEntriesByName(queryMark).length) {
    performance.clearMarks(queryMark);
  }

  /* eslint-disable-next-line compat/compat */
  performance.mark(queryMark);
}

/**
 * @param {FetchEndEvent} event
 */
function onFetchEnd(event) {
  mw.track("mediawiki.searchSuggest", {
    action: "impression-results",
    numberOfResults: event.numberOfResults,
    // resultSetType: '',
    // searchId: '',
    query: event.query,
    inputLocation: INPUT_LOCATION_MOVED,
  });

  if (shouldTestSearchPerformance) {
    // Schedule the mark after the search results have rendered and are
    // visible to the user. Two rAF's are needed for this since rAF will
    // execute before the rendering steps happen (e.g. layout and paint). A
    // nested rAF will execute after these rendering steps have completed
    // and ensure the search results are visible to the user.
    requestAnimationFrame(function () {
      requestAnimationFrame(function () {
        if (!performance.getEntriesByName(queryMark).length) {
          return;
        }

        performance.mark(renderMark);
        performance.measure(queryToRenderMeasure, queryMark, renderMark);

        // Measure from the start of the lazy load to the first render if we
        // haven't already captured that info.
        if (
          performance.getEntriesByName(loadStartMark).length &&
          !performance.getEntriesByName(loadStartToFirstRenderMeasure).length
        ) {
          performance.measure(loadStartToFirstRenderMeasure, loadStartMark, renderMark);
        }

        // The measures are the most meaningful info so we remove the marks
        // after we have the measure.
        performance.clearMarks(queryMark);
        performance.clearMarks(renderMark);
      });
    });
  }
}

/**
 * @param {SuggestionClickEvent|SearchSubmitEvent} event
 */
function onSuggestionClick(event) {
  mw.track("mediawiki.searchSuggest", {
    action: "click-result",
    numberOfResults: event.numberOfResults,
    index: event.index,
  });
}

/**
 * Generates the value of the `wprov` parameter to be used in the URL of a search result and the
 * `wprov` hidden input.
 *
 * See https://gerrit.wikimedia.org/r/plugins/gitiles/mediawiki/extensions/WikimediaEvents/+/refs/heads/master/modules/ext.wikimediaEvents/searchSatisfaction.js
 * and also the top of that file for additional detail about the shape of the parameter.
 *
 * @param {number} index
 * @return {string}
 */
function getWprovFromResultIndex(index) {
  // If the user hasn't highlighted an autocomplete result.
  if (index === -1) {
    return "acrw1";
  }

  return "acrw1" + index;
}

/**
 * @typedef {Object} SearchResultPartial
 * @property {string} title
 */

/**
 * @typedef {Object} GenerateUrlMeta
 * @property {number} index
 */

/**
 * Used by the Vue-enhanced search component to generate URLs for the search results. Adds a
 * `wprov` paramater to the URL to satisfy the SearchSatisfaction instrumentation.
 *
 * @see getWprovFromResultIndex
 *
 * @param {SearchResultPartial|string} suggestion
 * @param {GenerateUrlMeta} meta
 * @return {string}
 */
function generateUrl(suggestion, meta) {
  const result = new mw.Uri(wgScript);

  if (typeof suggestion !== "string") {
    suggestion = suggestion.title;
  }

  result.query.title = "Special:Search";
  result.query.suggestion = suggestion;
  result.query.wprov = getWprovFromResultIndex(meta.index);

  return result.toString();
}
/**
 * @typedef {Object} Instrumentation
 * @property {Object} listeners
 * @property {Function} getWprovFromResultIndex
 * @property {Function} generateUrl
 */

/**
 * @type {Instrumentation}
 */
module.exports = {
  listeners: {
    onFetchStart,
    onFetchEnd,
    onSuggestionClick,

    // As of writing (2020/12/08), both the "click-result" and "submit-form" kind of
    // mediawiki.searchSuggestion events result in a "click" SearchSatisfaction event being
    // logged [0]. However, when processing the "submit-form" kind of mediawiki.searchSuggestion
    // event, the SearchSatisfaction instrument will modify the DOM, adding a hidden input
    // element, in order to set the appropriate provenance parameter (see [1] for additional
    // detail).
    //
    // In this implementation of the mediawiki.searchSuggestion protocol, we don't want to
    // trigger the above behavior as we're using Vue.js, which doesn't expect the DOM to be
    // modified underneath it.
    //
    // [0] https://gerrit.wikimedia.org/g/mediawiki/extensions/WikimediaEvents/+/df97aa9c9407507e8c48827666beeab492fd56a8/modules/ext.wikimediaEvents/searchSatisfaction.js#735
    // [1] https://phabricator.wikimedia.org/T257698#6416826
    onSubmit: onSuggestionClick,
  },
  getWprovFromResultIndex,
  generateUrl,
};
