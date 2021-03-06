function strtotime (text, now) {
  // Convert string representation of date and time to a timestamp
  //
  // version: 1109.2015
  // discuss at: http://phpjs.org/functions/strtotime
  // +   original by: Caio Ariede (http://caioariede.com)
  // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +      input by: David
  // +   improved by: Caio Ariede (http://caioariede.com)
  // +   improved by: Brett Zamir (http://brett-zamir.me)
  // +   bugfixed by: Wagner B. Soares
  // +   bugfixed by: Artur Tchernychev
  // +   improved by: A. Matías Quezada (http://amatiasq.com)
  // %        note 1: Examples all have a fixed timestamp to prevent tests to fail because of variable time(zones)
  // *     example 1: strtotime('+1 day', 1129633200);
  // *     returns 1: 1129719600
  // *     example 2: strtotime('+1 week 2 days 4 hours 2 seconds', 1129633200);
  // *     returns 2: 1130425202
  // *     example 3: strtotime('last month', 1129633200);
  // *     returns 3: 1127041200
  // *     example 4: strtotime('2009-05-04 08:30:00');
  // *     returns 4: 1241418600
  if (!text)
      return null;

  // Unecessary spaces
  text = text.trim()
      .replace(/\s{2,}/g, ' ')
      .replace(/[\t\r\n]/g, '')
      .toLowerCase();

  var parsed;

  if (text === 'now')
      return now === null || isNaN(now) ? new Date().getTime() / 1000 | 0 : now | 0;
  else if (!isNaN(parse = Date.parse(text)))
      return parse / 1000 | 0;
  if (text === 'now')
      return new Date().getTime() / 1000; // Return seconds, not milli-seconds
  else if (!isNaN(parsed = Date.parse(text)))
      return parsed / 1000;

  var match = text.match(/^(\d{2,4})-(\d{2})-(\d{2})(?:\s(\d{1,2}):(\d{2})(?::\d{2})?)?(?:\.(\d+)?)?$/);
  if (match) {
      var year = match[1] >= 0 && match[1] <= 69 ? +match[1] + 2000 : match[1];
      return new Date(year, parseInt(match[2], 10) - 1, match[3],
          match[4] || 0, match[5] || 0, match[6] || 0, match[7] || 0) / 1000;
  }

  var date = now ? new Date(now * 1000) : new Date();
  var days = {
      'sun': 0,
      'mon': 1,
      'tue': 2,
      'wed': 3,
      'thu': 4,
      'fri': 5,
      'sat': 6
  };
  var ranges = {
      'yea': 'FullYear',
      'mon': 'Month',
      'day': 'Date',
      'hou': 'Hours',
      'min': 'Minutes',
      'sec': 'Seconds'
  };

  function lastNext(type, range, modifier) {
      var day = days[range];

      if (typeof(day) !== 'undefined') {
          var diff = day - date.getDay();

          if (diff === 0)
              diff = 7 * modifier;
          else if (diff > 0 && type === 'last')
              diff -= 7;
          else if (diff < 0 && type === 'next')
              diff += 7;

          date.setDate(date.getDate() + diff);
      }
  }
  function process(val) {
      var split = val.split(' ');
      var type = split[0];
      var range = split[1].substring(0, 3);
      var typeIsNumber = /\d+/.test(type);

      var ago = split[2] === 'ago';
      var num = (type === 'last' ? -1 : 1) * (ago ? -1 : 1);

      if (typeIsNumber)
          num *= parseInt(type, 10);

      if (ranges.hasOwnProperty(range))
          return date['set' + ranges[range]](date['get' + ranges[range]]() + num);
      else if (range === 'wee')
          return date.setDate(date.getDate() + (num * 7));

      if (type === 'next' || type === 'last')
          lastNext(type, range, num);
      else if (!typeIsNumber)
          return false;

      return true;
  }

  var regex = '([+-]?\\d+\\s' +
      '(years?|months?|weeks?|days?|hours?|min|minutes?|sec|seconds?' +
      '|sun\\.?|sunday|mon\\.?|monday|tue\\.?|tuesday|wed\\.?|wednesday' +
      '|thu\\.?|thursday|fri\\.?|friday|sat\\.?|saturday)|(last|next)\\s' +
      '(years?|months?|weeks?|days?|hours?|min|minutes?|sec|seconds?' +
      '|sun\\.?|sunday|mon\\.?|monday|tue\\.?|tuesday|wed\\.?|wednesday' +
      '|thu\\.?|thursday|fri\\.?|friday|sat\\.?|saturday))(\\sago)?';

  match = text.match(new RegExp(regex, 'gi'));
  if (!match)
      return false;

  for (var i = 0, len = match.length; i < len; i++)
      if (!process(match[i]))
          return false;

  // ECMAScript 5 only
  //if (!match.every(process))
  // return false;

  return (date.getTime() / 1000);
}

function date (format, timestamp) {
    var that = this,
      jsdate,
      f,
      formatChr = /\\?([a-z])/gi,
      formatChrCb,
      _pad = function (n, c) {
        n = n.toString();
        return n.length < c ? _pad('0' + n, c, '0') : n;
      },
      txt_words = ["Sun", "Mon", "Tues", "Wednes", "Thurs", "Fri", "Satur", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
  formatChrCb = function (t, s) {
    return f[t] ? f[t]() : s;
  };
  f = {
    // Day
    d: function () { // Day of month w/leading 0; 01..31
      return _pad(f.j(), 2);
    },
    D: function () { // Shorthand day name; Mon...Sun
      return f.l().slice(0, 3);
    },
    j: function () { // Day of month; 1..31
      return jsdate.getDate();
    },
    l: function () { // Full day name; Monday...Sunday
      return txt_words[f.w()] + 'day';
    },
    N: function () { // ISO-8601 day of week; 1[Mon]..7[Sun]
      return f.w() || 7;
    },
    S: function(){ // Ordinal suffix for day of month; st, nd, rd, th
      var j = f.j()
      i = j%10;
      if (i <= 3 && parseInt((j%100)/10) == 1) i = 0;
      return ['st', 'nd', 'rd'][i - 1] || 'th';
    },
    w: function () { // Day of week; 0[Sun]..6[Sat]
      return jsdate.getDay();
    },
    z: function () { // Day of year; 0..365
      var a = new Date(f.Y(), f.n() - 1, f.j()),
        b = new Date(f.Y(), 0, 1);
      return Math.round((a - b) / 864e5);
    },

    // Week
    W: function () { // ISO-8601 week number
      var a = new Date(f.Y(), f.n() - 1, f.j() - f.N() + 3),
        b = new Date(a.getFullYear(), 0, 4);
      return _pad(1 + Math.round((a - b) / 864e5 / 7), 2);
    },

    // Month
    F: function () { // Full month name; January...December
      return txt_words[6 + f.n()];
    },
    m: function () { // Month w/leading 0; 01...12
      return _pad(f.n(), 2);
    },
    M: function () { // Shorthand month name; Jan...Dec
      return f.F().slice(0, 3);
    },
    n: function () { // Month; 1...12
      return jsdate.getMonth() + 1;
    },
    t: function () { // Days in month; 28...31
      return (new Date(f.Y(), f.n(), 0)).getDate();
    },

    // Year
    L: function () { // Is leap year?; 0 or 1
      var j = f.Y();
      return j % 4 === 0 & j % 100 !== 0 | j % 400 === 0;
    },
    o: function () { // ISO-8601 year
      var n = f.n(),
        W = f.W(),
        Y = f.Y();
      return Y + (n === 12 && W < 9 ? 1 : n === 1 && W > 9 ? -1 : 0);
    },
    Y: function () { // Full year; e.g. 1980...2010
      return jsdate.getFullYear();
    },
    y: function () { // Last two digits of year; 00...99
      return f.Y().toString().slice(-2);
    },

    // Time
    a: function () { // am or pm
      return jsdate.getHours() > 11 ? "pm" : "am";
    },
    A: function () { // AM or PM
      return f.a().toUpperCase();
    },
    B: function () { // Swatch Internet time; 000..999
      var H = jsdate.getUTCHours() * 36e2,
        // Hours
        i = jsdate.getUTCMinutes() * 60,
        // Minutes
        s = jsdate.getUTCSeconds(); // Seconds
      return _pad(Math.floor((H + i + s + 36e2) / 86.4) % 1e3, 3);
    },
    g: function () { // 12-Hours; 1..12
      return f.G() % 12 || 12;
    },
    G: function () { // 24-Hours; 0..23
      return jsdate.getHours();
    },
    h: function () { // 12-Hours w/leading 0; 01..12
      return _pad(f.g(), 2);
    },
    H: function () { // 24-Hours w/leading 0; 00..23
      return _pad(f.G(), 2);
    },
    i: function () { // Minutes w/leading 0; 00..59
      return _pad(jsdate.getMinutes(), 2);
    },
    s: function () { // Seconds w/leading 0; 00..59
      return _pad(jsdate.getSeconds(), 2);
    },
    u: function () { // Microseconds; 000000-999000
      return _pad(jsdate.getMilliseconds() * 1000, 6);
    },

    // Timezone
    e: function () { 
      throw 'Not supported (see source code of date() for timezone on how to add support)';
    },
    I: function () { // DST observed?; 0 or 1
      // Compares Jan 1 minus Jan 1 UTC to Jul 1 minus Jul 1 UTC.
      // If they are not equal, then DST is observed.
      var a = new Date(f.Y(), 0),
        // Jan 1
        c = Date.UTC(f.Y(), 0),
        // Jan 1 UTC
        b = new Date(f.Y(), 6),
        // Jul 1
        d = Date.UTC(f.Y(), 6); // Jul 1 UTC
      return ((a - c) !== (b - d)) ? 1 : 0;
    },
    O: function () { // Difference to GMT in hour format; e.g. +0200
      var tzo = jsdate.getTimezoneOffset(),
        a = Math.abs(tzo);
      return (tzo > 0 ? "-" : "+") + _pad(Math.floor(a / 60) * 100 + a % 60, 4);
    },
    P: function () { // Difference to GMT w/colon; e.g. +02:00
      var O = f.O();
      return (O.substr(0, 3) + ":" + O.substr(3, 2));
    },
    T: function () { 
      return 'UTC';
    },
    Z: function () { // Timezone offset in seconds (-43200...50400)
      return -jsdate.getTimezoneOffset() * 60;
    },

    // Full Date/Time
    c: function () { // ISO-8601 date.
      return 'Y-m-d\\TH:i:sP'.replace(formatChr, formatChrCb);
    },
    r: function () { // RFC 2822
      return 'D, d M Y H:i:s O'.replace(formatChr, formatChrCb);
    },
    U: function () { // Seconds since UNIX epoch
      return jsdate / 1000 | 0;
    }
  };
  this.date = function (format, timestamp) {
    that = this;
    jsdate = (timestamp === undefined ? new Date() : // Not provided
      (timestamp instanceof Date) ? new Date(timestamp) : // JS Date()
      new Date(timestamp * 1000) // UNIX timestamp (auto-convert to int)
    );
    return format.replace(formatChr, formatChrCb);
  };
  return this.date(format, timestamp);
}
