/* eslint-disable */
const mockMediaWiki = require('@wikimedia/mw-node-qunit/src/mockMediaWiki.js');
global.mw = mockMediaWiki();
global.$ = require('jquery');
global.mw.util.showPortlet = () => {};
