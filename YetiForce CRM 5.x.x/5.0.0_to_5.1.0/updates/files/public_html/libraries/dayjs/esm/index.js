import * as C from './constant';
import U from './utils';
import en from './locale/en';
var L = 'en'; // global locale

var Ls = {}; // global loaded locale

Ls[L] = en;

var isDayjs = function isDayjs(d) {
  return d instanceof Dayjs;
}; // eslint-disable-line no-use-before-define


var parseLocale = function parseLocale(preset, object, isLocal) {
  var l;
  if (!preset) return null;

  if (typeof preset === 'string') {
    if (Ls[preset]) {
      l = preset;
    }

    if (object) {
      Ls[preset] = object;
      l = preset;
    }
  } else {
    var name = preset.name;
    Ls[name] = preset;
    l = name;
  }

  if (!isLocal) L = l;
  return l;
};

var dayjs = function dayjs(date, c, pl) {
  if (isDayjs(date)) {
    return date.clone();
  } // eslint-disable-next-line no-nested-ternary


  var cfg = c ? typeof c === 'string' ? {
    format: c,
    pl: pl
  } : c : {};
  cfg.date = date;
  return new Dayjs(cfg); // eslint-disable-line no-use-before-define
};

var wrapper = function wrapper(date, instance) {
  return dayjs(date, {
    locale: instance.$L,
    utc: instance.$u
  });
};

var Utils = U; // for plugin use

Utils.l = parseLocale;
Utils.i = isDayjs;
Utils.w = wrapper;

var parseDate = function parseDate(cfg) {
  var date = cfg.date,
      utc = cfg.utc;
  if (date === null) return new Date(NaN); // null is invalid

  if (Utils.u(date)) return new Date(); // today

  if (date instanceof Date) return new Date(date);

  if (typeof date === 'string' && !/Z$/i.test(date)) {
    var d = date.match(C.REGEX_PARSE);

    if (d) {
      if (utc) {
        return new Date(Date.UTC(d[1], d[2] - 1, d[3] || 1, d[4] || 0, d[5] || 0, d[6] || 0, d[7] || 0));
      }

      return new Date(d[1], d[2] - 1, d[3] || 1, d[4] || 0, d[5] || 0, d[6] || 0, d[7] || 0);
    }
  }

  return new Date(date); // everything else
};

var Dayjs =
/*#__PURE__*/
function () {
  function Dayjs(cfg) {
    this.$L = this.$L || parseLocale(cfg.locale, null, true) || L;
    this.parse(cfg); // for plugin
  }

  var _proto = Dayjs.prototype;

  _proto.parse = function parse(cfg) {
    this.$d = parseDate(cfg);
    this.init();
  };

  _proto.init = function init() {
    var $d = this.$d;
    this.$y = $d.getFullYear();
    this.$M = $d.getMonth();
    this.$D = $d.getDate();
    this.$W = $d.getDay();
    this.$H = $d.getHours();
    this.$m = $d.getMinutes();
    this.$s = $d.getSeconds();
    this.$ms = $d.getMilliseconds();
  }; // eslint-disable-next-line class-methods-use-this


  _proto.$utils = function $utils() {
    return Utils;
  };

  _proto.isValid = function isValid() {
    return !(this.$d.toString() === C.INVALID_DATE_STRING);
  };

  _proto.isSame = function isSame(that, units) {
    var other = dayjs(that);
    return this.startOf(units) <= other && other <= this.endOf(units);
  };

  _proto.isAfter = function isAfter(that, units) {
    return dayjs(that) < this.startOf(units);
  };

  _proto.isBefore = function isBefore(that, units) {
    return this.endOf(units) < dayjs(that);
  };

  _proto.$g = function $g(input, get, set) {
    if (Utils.u(input)) return this[get];
    return this.set(set, input);
  };

  _proto.year = function year(input) {
    return this.$g(input, '$y', C.Y);
  };

  _proto.month = function month(input) {
    return this.$g(input, '$M', C.M);
  };

  _proto.day = function day(input) {
    return this.$g(input, '$W', C.D);
  };

  _proto.date = function date(input) {
    return this.$g(input, '$D', C.DATE);
  };

  _proto.hour = function hour(input) {
    return this.$g(input, '$H', C.H);
  };

  _proto.minute = function minute(input) {
    return this.$g(input, '$m', C.MIN);
  };

  _proto.second = function second(input) {
    return this.$g(input, '$s', C.S);
  };

  _proto.millisecond = function millisecond(input) {
    return this.$g(input, '$ms', C.MS);
  };

  _proto.unix = function unix() {
    return Math.floor(this.valueOf() / 1000);
  };

  _proto.valueOf = function valueOf() {
    // timezone(hour) * 60 * 60 * 1000 => ms
    return this.$d.getTime();
  };

  _proto.startOf = function startOf(units, _startOf) {
    var _this = this;

    // startOf -> endOf
    var isStartOf = !Utils.u(_startOf) ? _startOf : true;
    var unit = Utils.p(units);

    var instanceFactory = function instanceFactory(d, m) {
      var ins = Utils.w(_this.$u ? Date.UTC(_this.$y, m, d) : new Date(_this.$y, m, d), _this);
      return isStartOf ? ins : ins.endOf(C.D);
    };

    var instanceFactorySet = function instanceFactorySet(method, slice) {
      var argumentStart = [0, 0, 0, 0];
      var argumentEnd = [23, 59, 59, 999];
      return Utils.w(_this.toDate()[method].apply( // eslint-disable-line prefer-spread
      _this.toDate(), (isStartOf ? argumentStart : argumentEnd).slice(slice)), _this);
    };

    var $W = this.$W,
        $M = this.$M,
        $D = this.$D;
    var utcPad = "set" + (this.$u ? 'UTC' : '');

    switch (unit) {
      case C.Y:
        return isStartOf ? instanceFactory(1, 0) : instanceFactory(31, 11);

      case C.M:
        return isStartOf ? instanceFactory(1, $M) : instanceFactory(0, $M + 1);

      case C.W:
        {
          var weekStart = this.$locale().weekStart || 0;
          var gap = ($W < weekStart ? $W + 7 : $W) - weekStart;
          return instanceFactory(isStartOf ? $D - gap : $D + (6 - gap), $M);
        }

      case C.D:
      case C.DATE:
        return instanceFactorySet(utcPad + "Hours", 0);

      case C.H:
        return instanceFactorySet(utcPad + "Minutes", 1);

      case C.MIN:
        return instanceFactorySet(utcPad + "Seconds", 2);

      case C.S:
        return instanceFactorySet(utcPad + "Milliseconds", 3);

      default:
        return this.clone();
    }
  };

  _proto.endOf = function endOf(arg) {
    return this.startOf(arg, false);
  };

  _proto.$set = function $set(units, int) {
    var _C$D$C$DATE$C$M$C$Y$C;

    // private set
    var unit = Utils.p(units);
    var utcPad = "set" + (this.$u ? 'UTC' : '');
    var name = (_C$D$C$DATE$C$M$C$Y$C = {}, _C$D$C$DATE$C$M$C$Y$C[C.D] = utcPad + "Date", _C$D$C$DATE$C$M$C$Y$C[C.DATE] = utcPad + "Date", _C$D$C$DATE$C$M$C$Y$C[C.M] = utcPad + "Month", _C$D$C$DATE$C$M$C$Y$C[C.Y] = utcPad + "FullYear", _C$D$C$DATE$C$M$C$Y$C[C.H] = utcPad + "Hours", _C$D$C$DATE$C$M$C$Y$C[C.MIN] = utcPad + "Minutes", _C$D$C$DATE$C$M$C$Y$C[C.S] = utcPad + "Seconds", _C$D$C$DATE$C$M$C$Y$C[C.MS] = utcPad + "Milliseconds", _C$D$C$DATE$C$M$C$Y$C)[unit];
    var arg = unit === C.D ? this.$D + (int - this.$W) : int;
    if (this.$d[name]) this.$d[name](arg);
    this.init();
    return this;
  };

  _proto.set = function set(string, int) {
    return this.clone().$set(string, int);
  };

  _proto.add = function add(number, units) {
    var _this2 = this,
        _C$MIN$C$H$C$S$unit;

    number = Number(number); // eslint-disable-line no-param-reassign

    var unit = Utils.p(units);

    var instanceFactory = function instanceFactory(u, n) {
      // clone is for badMutable plugin
      var date = _this2.clone().set(C.DATE, 1).set(u, n + number);

      return date.set(C.DATE, Math.min(_this2.$D, date.daysInMonth()));
    };

    var instanceFactorySet = function instanceFactorySet(n) {
      var date = new Date(_this2.$d);
      date.setDate(date.getDate() + n * number);
      return Utils.w(date, _this2);
    };

    if (unit === C.M) {
      return instanceFactory(C.M, this.$M);
    }

    if (unit === C.Y) {
      return instanceFactory(C.Y, this.$y);
    }

    if (unit === C.D) {
      return instanceFactorySet(1);
    }

    if (unit === C.W) {
      return instanceFactorySet(7);
    }

    var step = (_C$MIN$C$H$C$S$unit = {}, _C$MIN$C$H$C$S$unit[C.MIN] = C.MILLISECONDS_A_MINUTE, _C$MIN$C$H$C$S$unit[C.H] = C.MILLISECONDS_A_HOUR, _C$MIN$C$H$C$S$unit[C.S] = C.MILLISECONDS_A_SECOND, _C$MIN$C$H$C$S$unit)[unit] || 1; // ms

    var nextTimeStamp = this.valueOf() + number * step;
    return Utils.w(nextTimeStamp, this);
  };

  _proto.subtract = function subtract(number, string) {
    return this.add(number * -1, string);
  };

  _proto.format = function format(formatStr) {
    var _this3 = this;

    if (!this.isValid()) return C.INVALID_DATE_STRING;
    var str = formatStr || C.FORMAT_DEFAULT;
    var zoneStr = Utils.z(this);
    var locale = this.$locale();
    var weekdays = locale.weekdays,
        months = locale.months;

    var getShort = function getShort(arr, index, full, length) {
      return arr && arr[index] || full[index].substr(0, length);
    };

    var get$H = function get$H(num) {
      return Utils.s(_this3.$H % 12 || 12, num, '0');
    };

    var matches = {
      YY: String(this.$y).slice(-2),
      YYYY: String(this.$y),
      M: String(this.$M + 1),
      MM: Utils.s(this.$M + 1, 2, '0'),
      MMM: getShort(locale.monthsShort, this.$M, months, 3),
      MMMM: months[this.$M],
      D: String(this.$D),
      DD: Utils.s(this.$D, 2, '0'),
      d: String(this.$W),
      dd: getShort(locale.weekdaysMin, this.$W, weekdays, 2),
      ddd: getShort(locale.weekdaysShort, this.$W, weekdays, 3),
      dddd: weekdays[this.$W],
      H: String(this.$H),
      HH: Utils.s(this.$H, 2, '0'),
      h: get$H(1),
      hh: get$H(2),
      a: this.$H < 12 ? 'am' : 'pm',
      A: this.$H < 12 ? 'AM' : 'PM',
      m: String(this.$m),
      mm: Utils.s(this.$m, 2, '0'),
      s: String(this.$s),
      ss: Utils.s(this.$s, 2, '0'),
      SSS: Utils.s(this.$ms, 3, '0'),
      Z: zoneStr // 'ZZ' logic below

    };
    return str.replace(C.REGEX_FORMAT, function (match) {
      if (match.indexOf('[') > -1) return match.replace(/\[|\]/g, '');
      return matches[match] || zoneStr.replace(':', ''); // 'ZZ'
    });
  };

  _proto.utcOffset = function utcOffset() {
    // Because a bug at FF24, we're rounding the timezone offset around 15 minutes
    // https://github.com/moment/moment/pull/1871
    return -Math.round(this.$d.getTimezoneOffset() / 15) * 15;
  };

  _proto.diff = function diff(input, units, float) {
    var _C$Y$C$M$C$Q$C$W$C$D$;

    var unit = Utils.p(units);
    var that = dayjs(input);
    var zoneDelta = (that.utcOffset() - this.utcOffset()) * C.MILLISECONDS_A_MINUTE;
    var diff = this - that;
    var result = Utils.m(this, that);
    result = (_C$Y$C$M$C$Q$C$W$C$D$ = {}, _C$Y$C$M$C$Q$C$W$C$D$[C.Y] = result / 12, _C$Y$C$M$C$Q$C$W$C$D$[C.M] = result, _C$Y$C$M$C$Q$C$W$C$D$[C.Q] = result / 3, _C$Y$C$M$C$Q$C$W$C$D$[C.W] = (diff - zoneDelta) / C.MILLISECONDS_A_WEEK, _C$Y$C$M$C$Q$C$W$C$D$[C.D] = (diff - zoneDelta) / C.MILLISECONDS_A_DAY, _C$Y$C$M$C$Q$C$W$C$D$[C.H] = diff / C.MILLISECONDS_A_HOUR, _C$Y$C$M$C$Q$C$W$C$D$[C.MIN] = diff / C.MILLISECONDS_A_MINUTE, _C$Y$C$M$C$Q$C$W$C$D$[C.S] = diff / C.MILLISECONDS_A_SECOND, _C$Y$C$M$C$Q$C$W$C$D$)[unit] || diff; // milliseconds

    return float ? result : Utils.a(result);
  };

  _proto.daysInMonth = function daysInMonth() {
    return this.endOf(C.M).$D;
  };

  _proto.$locale = function $locale() {
    // get locale object
    return Ls[this.$L];
  };

  _proto.locale = function locale(preset, object) {
    if (!preset) return this.$L;
    var that = this.clone();
    that.$L = parseLocale(preset, object, true);
    return that;
  };

  _proto.clone = function clone() {
    return Utils.w(this.toDate(), this);
  };

  _proto.toDate = function toDate() {
    return new Date(this.$d);
  };

  _proto.toJSON = function toJSON() {
    return this.toISOString();
  };

  _proto.toISOString = function toISOString() {
    // ie 8 return
    // new Dayjs(this.valueOf() + this.$d.getTimezoneOffset() * 60000)
    // .format('YYYY-MM-DDTHH:mm:ss.SSS[Z]')
    return this.$d.toISOString();
  };

  _proto.toString = function toString() {
    return this.$d.toUTCString();
  };

  return Dayjs;
}();

dayjs.prototype = Dayjs.prototype;

dayjs.extend = function (plugin, option) {
  plugin(option, Dayjs, dayjs);
  return dayjs;
};

dayjs.locale = parseLocale;
dayjs.isDayjs = isDayjs;

dayjs.unix = function (timestamp) {
  return dayjs(timestamp * 1e3);
};

dayjs.en = Ls[L];
dayjs.Ls = Ls;
export default dayjs;