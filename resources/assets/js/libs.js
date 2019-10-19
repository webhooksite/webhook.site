window.$ = window.jQuery = require('jquery');
require('bootstrap-sass');
require('bootstrap-notify');
require('angular');
require('angular-ui-router');
window.Clipboard = require('clipboard');
window.JSONbig = require('json-bigint');
window.copyToClipboard = require('copy-to-clipboard');

window.hljs = require('highlight.js/lib/highlight');
var javascript = require('highlight.js/lib/languages/javascript');
var xml = require('highlight.js/lib/languages/javascript');
hljs.registerLanguage('javascript', javascript);
hljs.registerLanguage('xml', xml);

require('angular-highlightjs');