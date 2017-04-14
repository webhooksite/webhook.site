var querystring = require('querystring');
var url = require('url');
var util = require('util');

var shellescape = require('shell-escape');

module.exports.args = args;
module.exports.cmd = cmd;

// create the cURL command for a url or url parsed object
function args(o, opts) {
  if (typeof o === 'string')
    o = url.parse(o);
  opts = opts || {};

  var cmd = ['curl'];

  // method
  cmd.push('-X');
  cmd.push(o.method || 'GET');

  // username
  if (o.auth) {
    cmd.push('-u');
    cmd.push(o.auth);
  }

  // headers
  Object.keys(o.headers || {}).forEach(function(key) {
    var val = o.headers[key];
    cmd.push('-H');
    cmd.push(util.format('%s%s%s',
        key,
        val ? ': ' : ';',
        val ? val  : ''
    ));
  });

  // curl options
  if (opts.verbose) cmd.push('-v');
  if (opts.headers) cmd.push('-i');

  if (typeof opts.options === 'string')
    cmd.push(opts.options);
  else if (opts.options instanceof Array)
    cmd = cmd.concat(opts.options);

  // URL
  if (o.href) {
    cmd.push(o.href);
  } else {
    var u = util.format(
        '%s://%s:%d%s',
         opts.ssl ? 'https' : 'http',
         o.hostname || o.host,
         o.port || (opts.ssl ? 443 : 80),
         o.path
    );
    if (typeof o.query === 'string')
      u += '?' + o.query;
    else if (typeof o.query === 'object')
      u += '?' + querystring.stringify(o.query);
    cmd.push(u);
  }

  return cmd;
}

// same as above, but return a shellescape'd version
function cmd(o, opts) {
  return shellescape(args(o, opts));
}
