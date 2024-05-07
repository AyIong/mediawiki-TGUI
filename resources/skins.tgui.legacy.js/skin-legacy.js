/** @interface MediaWikiPageReadyModule */
var
	collapsibleTabs = require( './collapsibleTabs.js' ),
	/** @type {MediaWikiPageReadyModule} */
	pageReady = require( /** @type {string} */( 'mediawiki.page.ready' ) ),
	tgui = require( './tgui.js' );

function main() {
	collapsibleTabs.init();
	$( tgui.init );
	pageReady.loadSearchModule( 'mediawiki.searchSuggest' );
}

main();
