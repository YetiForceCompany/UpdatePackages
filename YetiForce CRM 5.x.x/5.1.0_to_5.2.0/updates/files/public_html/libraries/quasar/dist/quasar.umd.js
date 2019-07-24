/*!
 * Quasar Framework v1.0.5
 * (c) 2016-present Razvan Stoenescu
 * Released under the MIT License.
 */

(function (global, factory) {
  typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory(require('vue')) :
  typeof define === 'function' && define.amd ? define(['vue'], factory) :
  (global = global || self, global.Quasar = factory(global.Vue));
}(this, function (Vue) { 'use strict';

  if (Vue === void 0) {
    console.error('[ Quasar ] Vue is required to run. Please add a script tag for it before loading Quasar.')
    return
  }
  Vue = Vue && Vue.hasOwnProperty('default') ? Vue['default'] : Vue;

  /* eslint-disable no-useless-escape */

  var isSSR = typeof window === 'undefined';
  var fromSSR = false;
  var onSSR = isSSR;

  function getMatch (userAgent, platformMatch) {
    var match = /(edge|edga|edgios)\/([\w.]+)/.exec(userAgent) ||
      /(opr)[\/]([\w.]+)/.exec(userAgent) ||
      /(vivaldi)[\/]([\w.]+)/.exec(userAgent) ||
      /(chrome|crios)[\/]([\w.]+)/.exec(userAgent) ||
      /(iemobile)[\/]([\w.]+)/.exec(userAgent) ||
      /(version)(applewebkit)[\/]([\w.]+).*(safari)[\/]([\w.]+)/.exec(userAgent) ||
      /(webkit)[\/]([\w.]+).*(version)[\/]([\w.]+).*(safari)[\/]([\w.]+)/.exec(userAgent) ||
      /(firefox|fxios)[\/]([\w.]+)/.exec(userAgent) ||
      /(webkit)[\/]([\w.]+)/.exec(userAgent) ||
      /(opera)(?:.*version|)[\/]([\w.]+)/.exec(userAgent) ||
      /(msie) ([\w.]+)/.exec(userAgent) ||
      userAgent.indexOf('trident') >= 0 && /(rv)(?::| )([\w.]+)/.exec(userAgent) ||
      userAgent.indexOf('compatible') < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec(userAgent) ||
      [];

    return {
      browser: match[5] || match[3] || match[1] || '',
      version: match[2] || match[4] || '0',
      versionNumber: match[4] || match[2] || '0',
      platform: platformMatch[0] || ''
    }
  }

  function getClientUserAgent () {
    return (navigator.userAgent || navigator.vendor || window.opera).toLowerCase()
  }

  function getPlatformMatch (userAgent) {
    return /(ipad)/.exec(userAgent) ||
      /(ipod)/.exec(userAgent) ||
      /(windows phone)/.exec(userAgent) ||
      /(iphone)/.exec(userAgent) ||
      /(kindle)/.exec(userAgent) ||
      /(silk)/.exec(userAgent) ||
      /(android)/.exec(userAgent) ||
      /(win)/.exec(userAgent) ||
      /(mac)/.exec(userAgent) ||
      /(linux)/.exec(userAgent) ||
      /(cros)/.exec(userAgent) ||
      /(playbook)/.exec(userAgent) ||
      /(bb)/.exec(userAgent) ||
      /(blackberry)/.exec(userAgent) ||
      []
  }

  function getPlatform (userAgent) {
    var
      platformMatch = getPlatformMatch(userAgent),
      matched = getMatch(userAgent, platformMatch),
      browser = {};

    if (matched.browser) {
      browser[matched.browser] = true;
      browser.version = matched.version;
      browser.versionNumber = parseInt(matched.versionNumber, 10);
    }

    if (matched.platform) {
      browser[matched.platform] = true;
    }

    var knownMobiles = browser.android ||
      browser.ios ||
      browser.bb ||
      browser.blackberry ||
      browser.ipad ||
      browser.iphone ||
      browser.ipod ||
      browser.kindle ||
      browser.playbook ||
      browser.silk ||
      browser['windows phone'];

    // These are all considered mobile platforms, meaning they run a mobile browser
    if (knownMobiles === true || userAgent.indexOf('mobile') > -1) {
      browser.mobile = true;

      if (browser.edga || browser.edgios) {
        browser.edge = true;
        matched.browser = 'edge';
      }
      else if (browser.crios) {
        browser.chrome = true;
        matched.browser = 'chrome';
      }
      else if (browser.fxios) {
        browser.firefox = true;
        matched.browser = 'firefox';
      }
    }
    // If it's not mobile we should consider it's desktop platform, meaning it runs a desktop browser
    // It's a workaround for anonymized user agents
    // (browser.cros || browser.mac || browser.linux || browser.win)
    else {
      browser.desktop = true;
    }

    // Set iOS if on iPod, iPad or iPhone
    if (browser.ipod || browser.ipad || browser.iphone) {
      browser.ios = true;
    }

    if (browser['windows phone']) {
      browser.winphone = true;
      delete browser['windows phone'];
    }

    // Chrome, Opera 15+, Vivaldi and Safari are webkit based browsers
    if (
      browser.chrome ||
      browser.opr ||
      browser.safari ||
      browser.vivaldi ||
      // we expect unknown, non iOS mobile browsers to be webkit based
      (
        browser.mobile === true &&
        browser.ios !== true &&
        knownMobiles !== true
      )
    ) {
      browser.webkit = true;
    }

    // IE11 has a new token so we will assign it msie to avoid breaking changes
    if (browser.rv || browser.iemobile) {
      matched.browser = 'ie';
      browser.ie = true;
    }

    // Blackberry browsers are marked as Safari on BlackBerry
    if (browser.safari && browser.blackberry || browser.bb) {
      matched.browser = 'blackberry';
      browser.blackberry = true;
    }

    // Playbook browsers are marked as Safari on Playbook
    if (browser.safari && browser.playbook) {
      matched.browser = 'playbook';
      browser.playbook = true;
    }

    // Opera 15+ are identified as opr
    if (browser.opr) {
      matched.browser = 'opera';
      browser.opera = true;
    }

    // Stock Android browsers are marked as Safari on Android.
    if (browser.safari && browser.android) {
      matched.browser = 'android';
      browser.android = true;
    }

    // Kindle browsers are marked as Safari on Kindle
    if (browser.safari && browser.kindle) {
      matched.browser = 'kindle';
      browser.kindle = true;
    }

    // Kindle Silk browsers are marked as Safari on Kindle
    if (browser.safari && browser.silk) {
      matched.browser = 'silk';
      browser.silk = true;
    }

    if (browser.vivaldi) {
      matched.browser = 'vivaldi';
      browser.vivaldi = true;
    }

    // Assign the name and platform variable
    browser.name = matched.browser;
    browser.platform = matched.platform;

    if (isSSR === false) {
      if (window.process && window.process.versions && window.process.versions.electron) {
        browser.electron = true;
      }
      else if (document.location.href.indexOf('chrome-extension://') === 0) {
        browser.chromeExt = true;
      }
      else if (window._cordovaNative || window.cordova) {
        browser.cordova = true;
      }

      fromSSR = browser.cordova === void 0 &&
        browser.electron === void 0 &&
        !!document.querySelector('[data-server-rendered]');

      fromSSR === true && (onSSR = true);
    }

    return browser
  }

  var webStorage;

  function hasWebStorage () {
    if (webStorage !== void 0) {
      return webStorage
    }

    try {
      if (window.localStorage) {
        webStorage = true;
        return true
      }
    }
    catch (e) {}

    webStorage = false;
    return false
  }

  function getClientProperties () {
    return {
      has: {
        touch: (function () { return 'ontouchstart' in window ||
          window.navigator.maxTouchPoints > 0; }
        )(),
        webStorage: hasWebStorage()
      },
      within: {
        iframe: window.self !== window.top
      }
    }
  }

  var Platform = {
    has: {
      touch: false,
      webStorage: false
    },
    within: { iframe: false },

    parseSSR: function parseSSR (/* ssrContext */ ssr) {
      if (ssr) {
        var userAgent$1 = (ssr.req.headers['user-agent'] || ssr.req.headers['User-Agent'] || '').toLowerCase();
        return {
          userAgent: userAgent$1,
          is: getPlatform(userAgent$1),
          has: this.has,
          within: this.within
        }
      }

      var userAgent = getClientUserAgent();
      return Object.assign({}, {userAgent: userAgent,
        is: getPlatform(userAgent)},
        getClientProperties())
    },

    install: function install ($q, queues) {
      var this$1 = this;

      if (isSSR === true) {
        queues.server.push(function (q, ctx) {
          q.platform = this$1.parseSSR(ctx.ssr);
        });
        return
      }

      this.userAgent = getClientUserAgent();
      this.is = getPlatform(this.userAgent);

      if (fromSSR === true) {
        queues.takeover.push(function (q) {
          onSSR = fromSSR = false;
          Object.assign(q.platform, getClientProperties());
        });
        Vue.util.defineReactive($q, 'platform', this);
      }
      else {
        Object.assign(this, getClientProperties());
        $q.platform = this;
      }
    }
  };

  /* eslint-disable no-extend-native, one-var, no-self-compare */

  function assign (target, firstSource) {
    var arguments$1 = arguments;

    if (target === undefined || target === null) {
      throw new TypeError('Cannot convert first argument to object')
    }

    var to = Object(target);
    for (var i = 1; i < arguments.length; i++) {
      var nextSource = arguments$1[i];
      if (nextSource === undefined || nextSource === null) {
        continue
      }

      var keysArray = Object.keys(Object(nextSource));
      for (var nextIndex = 0, len = keysArray.length; nextIndex < len; nextIndex++) {
        var nextKey = keysArray[nextIndex];
        var desc = Object.getOwnPropertyDescriptor(nextSource, nextKey);
        if (desc !== undefined && desc.enumerable) {
          to[nextKey] = nextSource[nextKey];
        }
      }
    }
    return to
  }

  if (!Object.assign) {
    Object.defineProperty(Object, 'assign', {
      enumerable: false,
      configurable: true,
      writable: true,
      value: assign
    });
  }

  if (!Number.isInteger) {
    Number.isInteger = function (value) {
      return typeof value === 'number' &&
        isFinite(value) &&
        Math.floor(value) === value
    };
  }

  if (!Array.prototype.includes) {
    Array.prototype.includes = function (searchEl, startFrom) {

      var O = Object(this);
      var len = parseInt(O.length, 10) || 0;
      if (len === 0) {
        return false
      }
      var n = parseInt(startFrom, 10) || 0;
      var k;
      if (n >= 0) {
        k = n;
      }
      else {
        k = len + n;
        if (k < 0) { k = 0; }
      }
      var curEl;
      while (k < len) {
        curEl = O[k];
        if (searchEl === curEl ||
           (searchEl !== searchEl && curEl !== curEl)) { // NaN !== NaN
          return true
        }
        k++;
      }
      return false
    };
  }

  if (!String.prototype.startsWith) {
    String.prototype.startsWith = function (str, position) {
      position = position || 0;
      return this.substr(position, str.length) === str
    };
  }

  if (!String.prototype.endsWith) {
    String.prototype.endsWith = function (str, position) {
      var subjectString = this.toString();

      if (typeof position !== 'number' || !isFinite(position) || Math.floor(position) !== position || position > subjectString.length) {
        position = subjectString.length;
      }
      position -= str.length;

      var lastIndex = subjectString.indexOf(str, position);

      return lastIndex !== -1 && lastIndex === position
    };
  }

  if (isSSR === false) {
    if (typeof Element.prototype.matches !== 'function') {
      Element.prototype.matches = Element.prototype.msMatchesSelector || Element.prototype.mozMatchesSelector || Element.prototype.webkitMatchesSelector || function matches (selector) {
        var
          element = this,
          elements = (element.document || element.ownerDocument).querySelectorAll(selector),
          index = 0;

        while (elements[index] && elements[index] !== element) {
          ++index;
        }

        return Boolean(elements[index])
      };
    }

    if (typeof Element.prototype.closest !== 'function') {
      Element.prototype.closest = function closest (selector) {
        var el = this;
        while (el && el.nodeType === 1) {
          if (el.matches(selector)) {
            return el
          }
          el = el.parentNode;
        }
        return null
      };
    }

    // from:https://github.com/jserz/js_piece/blob/master/DOM/ChildNode/remove()/remove().md
    (function (arr) {
      arr.forEach(function (item) {
        if (item.hasOwnProperty('remove')) { return }
        Object.defineProperty(item, 'remove', {
          configurable: true,
          enumerable: true,
          writable: true,
          value: function value () {
            if (this.parentNode !== null) {
              this.parentNode.removeChild(this);
            }
          }
        });
      });
    })([Element.prototype, CharacterData.prototype, DocumentType.prototype]);
  }

  if (!Array.prototype.find) {
    Object.defineProperty(Array.prototype, 'find', {
      value: function value (predicate) {
        if (this == null) {
          throw new TypeError('Array.prototype.find called on null or undefined')
        }
        if (typeof predicate !== 'function') {
          throw new TypeError('predicate must be a function')
        }

        var value;
        var
          list = Object(this),
          length = list.length >>> 0,
          thisArg = arguments[1];

        for (var i = 0; i < length; i++) {
          value = list[i];
          if (predicate.call(thisArg, value, i, list)) {
            return value
          }
        }
        return undefined
      }
    });
  }

  var version = "1.0.5";

  var listenOpts = {
    hasPassive: false,
    passiveCapture: true,
    notPassiveCapture: true
  };

  try {
    var opts = Object.defineProperty({}, 'passive', {
      get: function get () {
        Object.assign(listenOpts, {
          hasPassive: true,
          passive: { passive: true },
          notPassive: { passive: false },
          passiveCapture: { passive: true, capture: true },
          notPassiveCapture: { passive: false, capture: true }
        });
      }
    });
    window.addEventListener('qtest', null, opts);
    window.removeEventListener('qtest', null, opts);
  }
  catch (e) {}

  function leftClick (e) {
    return e.button === 0
  }

  function middleClick (e) {
    return e.button === 1
  }

  function rightClick (e) {
    return e.button === 2
  }

  function position (e) {
    if (e.touches && e.touches[0]) {
      e = e.touches[0];
    }
    else if (e.changedTouches && e.changedTouches[0]) {
      e = e.changedTouches[0];
    }

    return {
      top: e.clientY,
      left: e.clientX
    }
  }

  function getEventPath (e) {
    if (e.path) {
      return e.path
    }
    if (e.composedPath) {
      return e.composedPath()
    }

    var path = [];
    var el = e.target;

    while (el) {
      path.push(el);

      if (el.tagName === 'HTML') {
        path.push(document);
        path.push(window);
        return path
      }

      el = el.parentElement;
    }
  }

  // Reasonable defaults
  var
    LINE_HEIGHT = 40,
    PAGE_HEIGHT = 800;

  function getMouseWheelDistance (e) {
    var assign;

    var x = e.deltaX, y = e.deltaY;

    if ((x || y) && e.deltaMode) {
      var multiplier = e.deltaMode === 1 ? LINE_HEIGHT : PAGE_HEIGHT;
      x *= multiplier;
      y *= multiplier;
    }

    if (e.shiftKey && !x) {
      (assign = [x, y], y = assign[0], x = assign[1]);
    }

    return { x: x, y: y }
  }

  function stop (e) {
    e.stopPropagation();
  }

  function prevent (e) {
    e.cancelable !== false && e.preventDefault();
  }

  function stopAndPrevent (e) {
    e.cancelable !== false && e.preventDefault();
    e.stopPropagation();
  }

  function preventDraggable (el, status) {
    if (el === void 0 || (status === true && el.__dragPrevented === true)) {
      return
    }

    var fn = status === true
      ? function (el) {
        el.__dragPrevented = true;
        el.addEventListener('dragstart', prevent);
      }
      : function (el) {
        delete el.__dragPrevented;
        el.removeEventListener('dragstart', prevent);
      };

    el.querySelectorAll('a, img').forEach(fn);
  }

  function create (name, ref) {
    if ( ref === void 0 ) ref = {};
    var bubbles = ref.bubbles; if ( bubbles === void 0 ) bubbles = false;
    var cancelable = ref.cancelable; if ( cancelable === void 0 ) cancelable = false;

    try {
      return new Event(name, { bubbles: bubbles, cancelable: cancelable })
    }
    catch (e) {
      // IE doesn't support `new Event()`, so...`
      var evt = document.createEvent('Event');
      evt.initEvent(name, bubbles, cancelable);
      return evt
    }
  }

  var event = {
    listenOpts: listenOpts,
    leftClick: leftClick,
    middleClick: middleClick,
    rightClick: rightClick,
    position: position,
    getEventPath: getEventPath,
    getMouseWheelDistance: getMouseWheelDistance,
    stop: stop,
    prevent: prevent,
    stopAndPrevent: stopAndPrevent,
    preventDraggable: preventDraggable,
    create: create
  };

  function debounce (fn, wait, immediate) {
    if ( wait === void 0 ) wait = 250;

    var timeout;

    function debounced () {
      var this$1 = this;
      var args = [], len = arguments.length;
      while ( len-- ) args[ len ] = arguments[ len ];

      var later = function () {
        timeout = null;
        if (!immediate) {
          fn.apply(this$1, args);
        }
      };

      clearTimeout(timeout);
      if (immediate && !timeout) {
        fn.apply(this, args);
      }
      timeout = setTimeout(later, wait);
    }

    debounced.cancel = function () {
      clearTimeout(timeout);
    };

    return debounced
  }

  var SIZE_LIST = ['sm', 'md', 'lg', 'xl'];

  var Screen = {
    width: 0,
    height: 0,

    sizes: {
      sm: 600,
      md: 1024,
      lg: 1440,
      xl: 1920
    },

    lt: {
      sm: true,
      md: true,
      lg: true,
      xl: true
    },
    gt: {
      xs: false,
      sm: false,
      md: false,
      lg: false
    },
    xs: true,
    sm: false,
    md: false,
    lg: false,
    xl: false,

    setSizes: function setSizes () {},
    setDebounce: function setDebounce () {},

    install: function install ($q, queues) {
      var this$1 = this;

      if (isSSR === true) {
        $q.screen = this;
        return
      }

      var update = function (force) {
        if (window.innerHeight !== this$1.height) {
          this$1.height = window.innerHeight;
        }

        var w = window.innerWidth;

        if (w !== this$1.width) {
          this$1.width = w;
        }
        else if (force !== true) {
          return
        }

        var s = this$1.sizes;

        this$1.gt.xs = w >= s.sm;
        this$1.gt.sm = w >= s.md;
        this$1.gt.md = w >= s.lg;
        this$1.gt.lg = w >= s.xl;
        this$1.lt.sm = w < s.sm;
        this$1.lt.md = w < s.md;
        this$1.lt.lg = w < s.lg;
        this$1.lt.xl = w < s.xl;
        this$1.xs = this$1.lt.sm;
        this$1.sm = this$1.gt.xs && this$1.lt.md;
        this$1.md = this$1.gt.sm && this$1.lt.lg;
        this$1.lg = this$1.gt.md && this$1.lt.xl;
        this$1.xl = this$1.gt.lg;
      };

      var updateEvt, updateSizes = {}, updateDebounce = 16;

      this.setSizes = function (sizes) {
        SIZE_LIST.forEach(function (name) {
          if (sizes[name] !== void 0) {
            updateSizes[name] = sizes[name];
          }
        });
      };
      this.setDebounce = function (deb) {
        updateDebounce = deb;
      };

      var start = function () {
        var style = getComputedStyle(document.body);

        // if css props available
        if (style.getPropertyValue('--q-size-sm')) {
          SIZE_LIST.forEach(function (name) {
            this$1.sizes[name] = parseInt(style.getPropertyValue(("--q-size-" + name)), 10);
          });
        }

        this$1.setSizes = function (sizes) {
          SIZE_LIST.forEach(function (name) {
            if (sizes[name]) {
              this$1.sizes[name] = sizes[name];
            }
          });
          update(true);
        };

        this$1.setDebounce = function (delay) {
          var fn = function () { update(); };
          updateEvt && window.removeEventListener('resize', updateEvt, listenOpts.passive);
          updateEvt = delay > 0
            ? debounce(fn, delay)
            : fn;
          window.addEventListener('resize', updateEvt, listenOpts.passive);
        };

        this$1.setDebounce(updateDebounce);

        if (Object.keys(updateSizes).length > 0) {
          this$1.setSizes(updateSizes);
          updateSizes = void 0; // free up memory
        }
        else {
          update();
        }
      };

      if (fromSSR === true) {
        queues.takeover.push(start);
      }
      else {
        start();
      }

      Vue.util.defineReactive($q, 'screen', this);
    }
  };

  var getTrue = function () { return true; };

  var History = {
    __history: [],
    add: function () {},
    remove: function () {},

    install: function install ($q, cfg) {
      var this$1 = this;

      if (isSSR === true || $q.platform.is.cordova !== true) {
        return
      }

      this.add = function (entry) {
        if (entry.condition === void 0) {
          entry.condition = getTrue;
        }
        this$1.__history.push(entry);
      };
      this.remove = function (entry) {
        var index = this$1.__history.indexOf(entry);
        if (index >= 0) {
          this$1.__history.splice(index, 1);
        }
      };

      var exit = cfg.cordova === void 0 || cfg.cordova.backButtonExit !== false;

      document.addEventListener('deviceready', function () {
        document.addEventListener('backbutton', function () {
          if (this$1.__history.length) {
            var entry = this$1.__history[this$1.__history.length - 1];

            if (entry.condition() === true) {
              this$1.__history.pop();
              entry.handler();
            }
          }
          else if (exit && window.location.hash === '#/') {
            navigator.app.exitApp();
          }
          else {
            window.history.back();
          }
        }, false);
      });
    }
  };

  var langEn = {
    isoName: 'en-us',
    nativeName: 'English (US)',
    label: {
      clear: 'Clear',
      ok: 'OK',
      cancel: 'Cancel',
      close: 'Close',
      set: 'Set',
      select: 'Select',
      reset: 'Reset',
      remove: 'Remove',
      update: 'Update',
      create: 'Create',
      search: 'Search',
      filter: 'Filter',
      refresh: 'Refresh'
    },
    date: {
      days: 'Sunday_Monday_Tuesday_Wednesday_Thursday_Friday_Saturday'.split('_'),
      daysShort: 'Sun_Mon_Tue_Wed_Thu_Fri_Sat'.split('_'),
      months: 'January_February_March_April_May_June_July_August_September_October_November_December'.split('_'),
      monthsShort: 'Jan_Feb_Mar_Apr_May_Jun_Jul_Aug_Sep_Oct_Nov_Dec'.split('_'),
      firstDayOfWeek: 0, // 0-6, 0 - Sunday, 1 Monday, ...
      format24h: false
    },
    table: {
      noData: 'No data available',
      noResults: 'No matching records found',
      loading: 'Loading...',
      selectedRecords: function (rows) {
        return rows === 1
          ? '1 record selected.'
          : (rows === 0 ? 'No' : rows) + ' records selected.'
      },
      recordsPerPage: 'Records per page:',
      allRows: 'All',
      pagination: function (start, end, total) {
        return start + '-' + end + ' of ' + total
      },
      columns: 'Columns'
    },
    editor: {
      url: 'URL',
      bold: 'Bold',
      italic: 'Italic',
      strikethrough: 'Strikethrough',
      underline: 'Underline',
      unorderedList: 'Unordered List',
      orderedList: 'Ordered List',
      subscript: 'Subscript',
      superscript: 'Superscript',
      hyperlink: 'Hyperlink',
      toggleFullscreen: 'Toggle Fullscreen',
      quote: 'Quote',
      left: 'Left align',
      center: 'Center align',
      right: 'Right align',
      justify: 'Justify align',
      print: 'Print',
      outdent: 'Decrease indentation',
      indent: 'Increase indentation',
      removeFormat: 'Remove formatting',
      formatting: 'Formatting',
      fontSize: 'Font Size',
      align: 'Align',
      hr: 'Insert Horizontal Rule',
      undo: 'Undo',
      redo: 'Redo',
      header1: 'Header 1',
      header2: 'Header 2',
      header3: 'Header 3',
      header4: 'Header 4',
      header5: 'Header 5',
      header6: 'Header 6',
      paragraph: 'Paragraph',
      code: 'Code',
      size1: 'Very small',
      size2: 'A bit small',
      size3: 'Normal',
      size4: 'Medium-large',
      size5: 'Big',
      size6: 'Very big',
      size7: 'Maximum',
      defaultFont: 'Default Font'
    },
    tree: {
      noNodes: 'No nodes available',
      noResults: 'No matching nodes found'
    }
  };

  var lang = {
    install: function install ($q, queues, lang) {
      var this$1 = this;

      if (isSSR === true) {
        queues.server.push(function (q, ctx) {
          var
            opt = {
              lang: q.lang.isoName,
              dir: q.lang.rtl === true ? 'rtl' : 'ltr'
            },
            fn = ctx.ssr.setHtmlAttrs;

          if (typeof fn === 'function') {
            fn(opt);
          }
          else {
            ctx.ssr.Q_HTML_ATTRS = Object.keys(opt)
              .map(function (key) { return (key + "=" + (opt[key])); })
              .join(' ');
          }
        });
      }

      this.set = function (lang) {
        if ( lang === void 0 ) lang = langEn;

        lang.set = this$1.set;
        lang.getLocale = this$1.getLocale;
        lang.rtl = lang.rtl || false;

        if (isSSR === false) {
          var el = document.documentElement;
          el.setAttribute('dir', lang.rtl ? 'rtl' : 'ltr');
          el.setAttribute('lang', lang.isoName);
        }

        if (isSSR === true || $q.lang !== void 0) {
          $q.lang = lang;
        }
        else {
          Vue.util.defineReactive($q, 'lang', lang);
        }

        this$1.isoName = lang.isoName;
        this$1.nativeName = lang.nativeName;
        this$1.props = lang;
      };

      this.set(lang);
    },

    getLocale: function getLocale () {
      if (isSSR === true) { return }

      var val =
        navigator.language ||
        navigator.languages[0] ||
        navigator.browserLanguage ||
        navigator.userLanguage ||
        navigator.systemLanguage;

      if (val) {
        return val.toLowerCase()
      }
    }
  };

  function rgbToHex (ref) {
    var r = ref.r;
    var g = ref.g;
    var b = ref.b;
    var a = ref.a;

    var alpha = a !== void 0;

    r = Math.round(r);
    g = Math.round(g);
    b = Math.round(b);

    if (
      r > 255 ||
      g > 255 ||
      b > 255 ||
      (alpha && a > 100)
    ) {
      throw new TypeError('Expected 3 numbers below 256 (and optionally one below 100)')
    }

    a = alpha
      ? (Math.round(255 * a / 100) | 1 << 8).toString(16).slice(1)
      : '';

    return '#' + ((b | g << 8 | r << 16) | 1 << 24).toString(16).slice(1) + a
  }

  function rgbToString (ref) {
    var r = ref.r;
    var g = ref.g;
    var b = ref.b;
    var a = ref.a;

    return ("rgb" + (a !== void 0 ? 'a' : '') + "(" + r + "," + g + "," + b + (a !== void 0 ? ',' + (a / 100) : '') + ")")
  }

  function stringToRgb (str) {
    if (typeof str !== 'string') {
      throw new TypeError('Expected a string')
    }

    str = str.replace(/ /g, '');

    if (str.startsWith('#')) {
      return hexToRgb(str)
    }

    var model = str.substring(str.indexOf('(') + 1, str.length - 1).split(',');

    return {
      r: parseInt(model[0], 10),
      g: parseInt(model[1], 10),
      b: parseInt(model[2], 10),
      a: model[3] !== void 0 ? parseFloat(model[3]) * 100 : void 0
    }
  }

  function hexToRgb (hex) {
    if (typeof hex !== 'string') {
      throw new TypeError('Expected a string')
    }

    hex = hex.replace(/^#/, '');

    if (hex.length === 3) {
      hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
    }
    else if (hex.length === 4) {
      hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2] + hex[3] + hex[3];
    }

    var num = parseInt(hex, 16);

    return hex.length > 6
      ? { r: num >> 24 & 255, g: num >> 16 & 255, b: num >> 8 & 255, a: Math.round((num & 255) / 2.55) }
      : { r: num >> 16, g: num >> 8 & 255, b: num & 255 }
  }

  function hsvToRgb (ref) {
    var h = ref.h;
    var s = ref.s;
    var v = ref.v;
    var a = ref.a;

    var r, g, b, i, f, p, q, t;
    s = s / 100;
    v = v / 100;

    h = h / 360;
    i = Math.floor(h * 6);
    f = h * 6 - i;
    p = v * (1 - s);
    q = v * (1 - f * s);
    t = v * (1 - (1 - f) * s);

    switch (i % 6) {
      case 0:
        r = v;
        g = t;
        b = p;
        break
      case 1:
        r = q;
        g = v;
        b = p;
        break
      case 2:
        r = p;
        g = v;
        b = t;
        break
      case 3:
        r = p;
        g = q;
        b = v;
        break
      case 4:
        r = t;
        g = p;
        b = v;
        break
      case 5:
        r = v;
        g = p;
        b = q;
        break
    }

    return {
      r: Math.round(r * 255),
      g: Math.round(g * 255),
      b: Math.round(b * 255),
      a: a
    }
  }

  function rgbToHsv (ref) {
    var r = ref.r;
    var g = ref.g;
    var b = ref.b;
    var a = ref.a;

    var
      max = Math.max(r, g, b), min = Math.min(r, g, b),
      d = max - min,
      h,
      s = (max === 0 ? 0 : d / max),
      v = max / 255;

    switch (max) {
      case min:
        h = 0;
        break
      case r:
        h = (g - b) + d * (g < b ? 6 : 0);
        h /= 6 * d;
        break
      case g:
        h = (b - r) + d * 2;
        h /= 6 * d;
        break
      case b:
        h = (r - g) + d * 4;
        h /= 6 * d;
        break
    }

    return {
      h: Math.round(h * 360),
      s: Math.round(s * 100),
      v: Math.round(v * 100),
      a: a
    }
  }

  var reRGBA = /^\s*rgb(a)?\s*\((\s*(\d+)\s*,\s*?){2}(\d+)\s*,?\s*([01]?\.?\d*?)?\s*\)\s*$/;

  function textToRgb (color) {
    if (typeof color !== 'string') {
      throw new TypeError('Expected a string')
    }

    var m = reRGBA.exec(color);
    if (m) {
      var rgb = {
        r: Math.max(255, parseInt(m[2], 10)),
        g: Math.max(255, parseInt(m[3], 10)),
        b: Math.max(255, parseInt(m[4], 10))
      };
      if (m[1]) {
        rgb.a = Math.max(1, parseFloat(m[5]));
      }
      return rgb
    }
    return hexToRgb(color)
  }

  /* works as darken if percent < 0 */
  function lighten (color, percent) {
    if (typeof color !== 'string') {
      throw new TypeError('Expected a string as color')
    }
    if (typeof percent !== 'number') {
      throw new TypeError('Expected a numeric percent')
    }

    var rgb = textToRgb(color),
      t = percent < 0 ? 0 : 255,
      p = Math.abs(percent) / 100,
      R = rgb.r,
      G = rgb.g,
      B = rgb.b;

    return '#' + (
      0x1000000 + (Math.round((t - R) * p) + R) * 0x10000 +
      (Math.round((t - G) * p) + G) * 0x100 +
      (Math.round((t - B) * p) + B)
    ).toString(16).slice(1)
  }

  function luminosity (color) {
    if (typeof color !== 'string' && (!color || color.r === void 0)) {
      throw new TypeError('Expected a string or a {r, g, b} object as color')
    }

    var
      rgb = typeof color === 'string' ? textToRgb(color) : color,
      r = rgb.r / 255,
      g = rgb.g / 255,
      b = rgb.b / 255,
      R = r <= 0.03928 ? r / 12.92 : Math.pow((r + 0.055) / 1.055, 2.4),
      G = g <= 0.03928 ? g / 12.92 : Math.pow((g + 0.055) / 1.055, 2.4),
      B = b <= 0.03928 ? b / 12.92 : Math.pow((b + 0.055) / 1.055, 2.4);
    return 0.2126 * R + 0.7152 * G + 0.0722 * B
  }

  function setBrand (color, value, element) {
    if ( element === void 0 ) element = document.body;

    if (typeof color !== 'string') {
      throw new TypeError('Expected a string as color')
    }
    if (typeof value !== 'string') {
      throw new TypeError('Expected a string as value')
    }
    if (!(element instanceof Element)) {
      throw new TypeError('Expected a DOM element')
    }

    element.style.setProperty(("--q-color-" + color), value);
    switch (color) {
      case 'negative':
      case 'warning':
        element.style.setProperty(("--q-color-" + color + "-l"), lighten(value, 46));
        break
      case 'light':
        element.style.setProperty(("--q-color-" + color + "-d"), lighten(value, -10));
    }
  }

  function getBrand (color, element) {
    if ( element === void 0 ) element = document.body;

    if (typeof color !== 'string') {
      throw new TypeError('Expected a string as color')
    }
    if (!(element instanceof Element)) {
      throw new TypeError('Expected a DOM element')
    }

    return getComputedStyle(element).getPropertyValue(("--q-color-" + color)).trim() || null
  }

  var colors = {
    rgbToHex: rgbToHex,
    hexToRgb: hexToRgb,
    hsvToRgb: hsvToRgb,
    rgbToHsv: rgbToHsv,
    textToRgb: textToRgb,
    lighten: lighten,
    luminosity: luminosity,
    setBrand: setBrand,
    getBrand: getBrand
  };

  function getMobilePlatform (is) {
    if (is.ios === true) { return 'ios' }
    if (is.android === true) { return 'android' }
    if (is.winphone === true) { return 'winphone' }
  }

  function getBodyClasses (ref, cfg) {
    var is = ref.is;
    var has = ref.has;
    var within = ref.within;

    var cls = [
      is.desktop ? 'desktop' : 'mobile',
      has.touch ? 'touch' : 'no-touch'
    ];

    if (is.mobile === true) {
      var mobile = getMobilePlatform(is);
      if (mobile !== void 0) {
        cls.push('platform-' + mobile);
      }
    }

    if (is.cordova === true) {
      cls.push('cordova');

      if (
        is.ios === true &&
        (cfg.cordova === void 0 || cfg.cordova.iosStatusBarPadding !== false)
      ) {
        cls.push('q-ios-padding');
      }
    }
    else if (is.electron === true) {
      cls.push('electron');
    }

    within.iframe === true && cls.push('within-iframe');

    return cls
  }

  function bodyInit (Platform, cfg) {
    var cls = getBodyClasses(Platform, cfg);

    if (Platform.is.ie === true && Platform.is.versionNumber === 11) {
      cls.forEach(function (c) { return document.body.classList.add(c); });
    }
    else {
      document.body.classList.add.apply(document.body.classList, cls);
    }

    if (Platform.is.ios === true) {
      // needed for iOS button active state
      document.body.addEventListener('touchstart', function () {});
    }
  }

  function setColors (brand) {
    for (var color in brand) {
      setBrand(color, brand[color]);
    }
  }

  var Body = {
    install: function install ($q, queues, cfg) {
      if (isSSR === true) {
        queues.server.push(function (q, ctx) {
          var
            cls = getBodyClasses(q.platform, cfg),
            fn = ctx.ssr.setBodyClasses;

          if (typeof fn === 'function') {
            fn(cls);
          }
          else {
            ctx.ssr.Q_BODY_CLASSES = cls.join(' ');
          }
        });
        return
      }

      cfg.brand && setColors(cfg.brand);
      bodyInit($q.platform, cfg);
    }
  };

  var materialIcons = {
    name: 'material-icons',
    type: {
      positive: 'check_circle',
      negative: 'warning',
      info: 'info',
      warning: 'priority_high'
    },
    arrow: {
      up: 'arrow_upward',
      right: 'arrow_forward',
      down: 'arrow_downward',
      left: 'arrow_back',
      dropdown: 'arrow_drop_down'
    },
    chevron: {
      left: 'chevron_left',
      right: 'chevron_right'
    },
    colorPicker: {
      spectrum: 'gradient',
      tune: 'tune',
      palette: 'style'
    },
    pullToRefresh: {
      icon: 'refresh'
    },
    carousel: {
      left: 'chevron_left',
      right: 'chevron_right',
      navigationIcon: 'lens',
      thumbnails: 'view_carousel'
    },
    chip: {
      remove: 'cancel',
      selected: 'check'
    },
    datetime: {
      arrowLeft: 'chevron_left',
      arrowRight: 'chevron_right',
      now: 'access_time',
      today: 'today'
    },
    editor: {
      bold: 'format_bold',
      italic: 'format_italic',
      strikethrough: 'strikethrough_s',
      underline: 'format_underlined',
      unorderedList: 'format_list_bulleted',
      orderedList: 'format_list_numbered',
      subscript: 'vertical_align_bottom',
      superscript: 'vertical_align_top',
      hyperlink: 'link',
      toggleFullscreen: 'fullscreen',
      quote: 'format_quote',
      left: 'format_align_left',
      center: 'format_align_center',
      right: 'format_align_right',
      justify: 'format_align_justify',
      print: 'print',
      outdent: 'format_indent_decrease',
      indent: 'format_indent_increase',
      removeFormat: 'format_clear',
      formatting: 'text_format',
      fontSize: 'format_size',
      align: 'format_align_left',
      hr: 'remove',
      undo: 'undo',
      redo: 'redo',
      header: 'format_size',
      code: 'code',
      size: 'format_size',
      font: 'font_download'
    },
    expansionItem: {
      icon: 'keyboard_arrow_down',
      denseIcon: 'arrow_drop_down'
    },
    fab: {
      icon: 'add',
      activeIcon: 'close'
    },
    field: {
      clear: 'cancel',
      error: 'error'
    },
    pagination: {
      first: 'first_page',
      prev: 'keyboard_arrow_left',
      next: 'keyboard_arrow_right',
      last: 'last_page'
    },
    rating: {
      icon: 'grade'
    },
    stepper: {
      done: 'check',
      active: 'edit',
      error: 'warning'
    },
    tabs: {
      left: 'chevron_left',
      right: 'chevron_right',
      up: 'keyboard_arrow_up',
      down: 'keyboard_arrow_down'
    },
    table: {
      arrowUp: 'arrow_upward',
      warning: 'warning',
      prevPage: 'chevron_left',
      nextPage: 'chevron_right'
    },
    tree: {
      icon: 'play_arrow'
    },
    uploader: {
      done: 'done',
      clear: 'clear',
      add: 'add_box',
      upload: 'cloud_upload',
      removeQueue: 'clear_all',
      removeUploaded: 'done_all'
    }
  };

  var iconSet = {
    __installed: false,
    install: function install ($q, iconSet) {
      var this$1 = this;

      this.set = function (iconDef) {
        if ( iconDef === void 0 ) iconDef = materialIcons;

        iconDef.set = this$1.set;

        if (isSSR === true || $q.iconSet !== void 0) {
          $q.iconSet = iconDef;
        }
        else {
          Vue.util.defineReactive($q, 'iconSet', iconDef);
        }

        this$1.name = iconDef.name;
        this$1.def = iconDef;
      };

      this.set(iconSet);
    }
  };

  var queues = {
    server: [], // on SSR update
    takeover: [] // on client takeover
  };

  var $q = {
    version: version
  };

  function install (Vue, opts) {
    if ( opts === void 0 ) opts = {};

    if (this.__installed) { return }
    this.__installed = true;

    var cfg = opts.config || {};

    // required plugins
    Platform.install($q, queues);
    Body.install($q, queues, cfg);
    Screen.install($q, queues);
    History.install($q, cfg);
    lang.install($q, queues, opts.lang);
    iconSet.install($q, opts.iconSet);

    if (isSSR === true) {
      Vue.mixin({
        beforeCreate: function beforeCreate () {
          this.$q = this.$root.$options.$q;
        }
      });
    }
    else {
      Vue.prototype.$q = $q;
    }

    opts.components && Object.keys(opts.components).forEach(function (key) {
      var c = opts.components[key];
      if (typeof c === 'function') {
        Vue.component(c.options.name, c);
      }
    });

    opts.directives && Object.keys(opts.directives).forEach(function (key) {
      var d = opts.directives[key];
      if (d.name !== undefined && d.unbind !== void 0) {
        Vue.directive(d.name, d);
      }
    });

    if (opts.plugins) {
      var param = { $q: $q, queues: queues, cfg: cfg };
      Object.keys(opts.plugins).forEach(function (key) {
        var p = opts.plugins[key];
        if (typeof p.install === 'function' && p !== Platform && p !== Screen) {
          p.install(param);
        }
      });
    }
  }

  var units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

  function humanStorageSize (bytes) {
    var u = 0;

    while (parseInt(bytes, 10) >= 1024 && u < units.length - 1) {
      bytes /= 1024;
      ++u;
    }

    return ((bytes.toFixed(1)) + " " + (units[u]))
  }

  function capitalize (str) {
    return str.charAt(0).toUpperCase() + str.slice(1)
  }

  function between (v, min, max) {
    return max <= min
      ? min
      : Math.min(max, Math.max(min, v))
  }

  function normalizeToInterval (v, min, max) {
    if (max <= min) {
      return min
    }

    var size = (max - min + 1);

    var index = min + (v - min) % size;
    if (index < min) {
      index = size + index;
    }

    return index === 0 ? 0 : index // fix for (-a % a) => -0
  }

  function pad (v, length, char) {
    if ( length === void 0 ) length = 2;
    if ( char === void 0 ) char = '0';

    if (v === void 0 || v === null) {
      return v
    }

    var val = '' + v;
    return val.length >= length
      ? val
      : new Array(length - val.length + 1).join(char) + val
  }

  var format = {
    humanStorageSize: humanStorageSize,
    capitalize: capitalize,
    between: between,
    normalizeToInterval: normalizeToInterval,
    pad: pad
  };

  var
    xhr = isSSR ? null : XMLHttpRequest,
    send = isSSR ? null : xhr.prototype.send,
    stack = { start: [], stop: [] };

  var highjackCount = 0;

  function translate (ref) {
    var p = ref.p;
    var pos = ref.pos;
    var active = ref.active;
    var horiz = ref.horiz;
    var reverse = ref.reverse;
    var dir = ref.dir;

    var x = 1, y = 1;

    if (horiz) {
      if (reverse) { x = -1; }
      if (pos === 'bottom') { y = -1; }
      return { transform: ("translate3d(" + (x * (p - 100)) + "%," + (active ? 0 : y * -200) + "%,0)") }
    }

    if (reverse) { y = -1; }
    if (pos === 'right') { x = -1; }
    return { transform: ("translate3d(" + (active ? 0 : dir * x * -200) + "%," + (y * (p - 100)) + "%,0)") }
  }

  function inc (p, amount) {
    if (typeof amount !== 'number') {
      if (p < 25) {
        amount = Math.random() * 3 + 3;
      }
      else if (p < 65) {
        amount = Math.random() * 3;
      }
      else if (p < 85) {
        amount = Math.random() * 2;
      }
      else if (p < 99) {
        amount = 0.6;
      }
      else {
        amount = 0;
      }
    }
    return between(p + amount, 0, 100)
  }

  function highjackAjax (start, stop) {
    stack.start.push(start);
    stack.stop.push(stop);

    highjackCount++;

    if (highjackCount > 1) { return }

    function endHandler () {
      stack.stop.map(function (fn) { fn(); });
    }

    xhr.prototype.send = function () {
      var this$1 = this;
      var args = [], len = arguments.length;
      while ( len-- ) args[ len ] = arguments[ len ];

      stack.start.map(function (fn) { fn(); });

      this.addEventListener('abort', endHandler, false);
      this.addEventListener('readystatechange', function () {
        if (this$1.readyState === 4) { endHandler(); }
      }, false);

      send.apply(this, args);
    };
  }

  function restoreAjax (start, stop) {
    stack.start = stack.start.filter(function (fn) { return fn !== start; });
    stack.stop = stack.stop.filter(function (fn) { return fn !== stop; });

    highjackCount = Math.max(0, highjackCount - 1);
    if (!highjackCount) {
      xhr.prototype.send = send;
    }
  }

  var QAjaxBar = Vue.extend({
    name: 'QAjaxBar',

    props: {
      position: {
        type: String,
        default: 'top',
        validator: function validator (val) {
          return ['top', 'right', 'bottom', 'left'].includes(val)
        }
      },
      size: {
        type: String,
        default: '2px'
      },
      color: {
        type: String,
        default: 'red'
      },
      skipHijack: Boolean,
      reverse: Boolean
    },

    data: function data () {
      return {
        calls: 0,
        progress: 0,
        onScreen: false,
        animate: true
      }
    },

    computed: {
      classes: function classes () {
        return [
          ("q-loading-bar--" + (this.position)),
          ("bg-" + (this.color)),
          this.animate ? '' : 'no-transition'
        ]
      },

      style: function style () {
        var active = this.onScreen;

        var o = translate({
          p: this.progress,
          pos: this.position,
          active: active,
          horiz: this.horizontal,
          reverse: this.$q.lang.rtl && ['top', 'bottom'].includes(this.position)
            ? !this.reverse
            : this.reverse,
          dir: this.$q.lang.rtl ? -1 : 1
        });

        o[this.sizeProp] = this.size;
        o.opacity = active ? 1 : 0;

        return o
      },

      horizontal: function horizontal () {
        return this.position === 'top' || this.position === 'bottom'
      },

      sizeProp: function sizeProp () {
        return this.horizontal ? 'height' : 'width'
      }
    },

    methods: {
      start: function start (speed) {
        var this$1 = this;
        if ( speed === void 0 ) speed = 300;

        this.calls++;
        if (this.calls > 1) { return }

        clearTimeout(this.timer);
        this.$emit('start');

        if (this.onScreen) { return }

        this.progress = 0;
        this.onScreen = true;
        this.animate = false;
        this.timer = setTimeout(function () {
          this$1.animate = true;
          this$1.__work(speed);
        }, 100);
      },

      increment: function increment (amount) {
        this.calls > 0 && (this.progress = inc(this.progress, amount));
      },

      stop: function stop () {
        var this$1 = this;

        this.calls = Math.max(0, this.calls - 1);
        if (this.calls > 0) { return }

        clearTimeout(this.timer);
        this.$emit('stop');

        var end = function () {
          this$1.animate = true;
          this$1.progress = 100;
          this$1.timer = setTimeout(function () {
            this$1.onScreen = false;
          }, 1000);
        };

        if (this.progress === 0) {
          this.timer = setTimeout(end, 1);
        }
        else {
          end();
        }
      },

      __work: function __work (speed) {
        var this$1 = this;

        if (this.progress < 100) {
          this.timer = setTimeout(function () {
            this$1.increment();
            this$1.__work(speed);
          }, speed);
        }
      }
    },

    mounted: function mounted () {
      if (!this.skipHijack) {
        this.hijacked = true;
        highjackAjax(this.start, this.stop);
      }
    },

    beforeDestroy: function beforeDestroy () {
      clearTimeout(this.timer);
      this.hijacked && restoreAjax(this.start, this.stop);
    },

    render: function render (h) {
      return h('div', {
        staticClass: 'q-loading-bar',
        class: this.classes,
        style: this.style
      })
    }
  });

  function slot (vm, slotName) {
    return vm.$scopedSlots[slotName] !== void 0
      ? vm.$scopedSlots[slotName]()
      : void 0
  }

  var QIcon = Vue.extend({
    name: 'QIcon',

    props: {
      name: String,
      color: String,
      size: String,
      left: Boolean,
      right: Boolean
    },

    computed: {
      type: function type () {
        var cls;
        var icon = this.name;

        if (!icon) {
          return {
            cls: void 0,
            content: void 0
          }
        }

        var commonCls = 'q-icon' +
          (this.left === true ? ' on-left' : '') +
          (this.right === true ? ' on-right' : '');

        if (icon.startsWith('img:') === true) {
          return {
            img: true,
            cls: commonCls,
            src: icon.substring(4)
          }
        }

        var content = ' ';

        if (/^fa[s|r|l|b]{0,1} /.test(icon) || icon.startsWith('icon-') === true) {
          cls = icon;
        }
        else if (icon.startsWith('bt-') === true) {
          cls = "bt " + icon;
        }
        else if (icon.startsWith('eva-') === true) {
          cls = "eva " + icon;
        }
        else if (/^ion-(md|ios|logo)/.test(icon) === true) {
          cls = "ionicons " + icon;
        }
        else if (icon.startsWith('ion-') === true) {
          cls = "ionicons ion-" + (this.$q.platform.is.ios === true ? 'ios' : 'md') + (icon.substr(3));
        }
        else if (icon.startsWith('mdi-') === true) {
          cls = "mdi " + icon;
        }
        else if (icon.startsWith('iconfont ') === true) {
          cls = "" + icon;
        }
        else if (icon.startsWith('ti-') === true) {
          cls = "themify-icon " + icon;
        }
        else {
          cls = 'material-icons';

          if (icon.startsWith('o_') === true) {
            icon = icon.substring(2);
            cls += '-outlined';
          }
          else if (icon.startsWith('r_') === true) {
            icon = icon.substring(2);
            cls += '-round';
          }
          else if (icon.startsWith('s_') === true) {
            icon = icon.substring(2);
            cls += '-sharp';
          }

          content = icon;
        }

        return {
          cls: cls + ' ' + commonCls +
            (this.color !== void 0 ? (" text-" + (this.color)) : ''),
          content: content
        }
      },

      style: function style () {
        if (this.size !== void 0) {
          return { fontSize: this.size }
        }
      }
    },

    render: function render (h) {
      return this.type.img === true
        ? h('img', {
          staticClass: this.type.cls,
          style: this.style,
          on: this.$listeners,
          attrs: { src: this.type.src }
        })
        : h('i', {
          staticClass: this.type.cls,
          style: this.style,
          on: this.$listeners,
          attrs: { 'aria-hidden': true }
        }, [
          this.type.content,
          slot(this, 'default')
        ])
    }
  });

  var QAvatar = Vue.extend({
    name: 'QAvatar',

    props: {
      size: String,
      fontSize: String,

      color: String,
      textColor: String,

      icon: String,
      square: Boolean,
      rounded: Boolean
    },

    computed: {
      contentClass: function contentClass () {
        var obj;

        return ( obj = {}, obj[("bg-" + (this.color))] = this.color, obj[("text-" + (this.textColor) + " q-chip--colored")] = this.textColor, obj['q-avatar__content--square'] = this.square, obj['rounded-borders'] = this.rounded, obj )
      },

      style: function style () {
        if (this.size) {
          return { fontSize: this.size }
        }
      },

      contentStyle: function contentStyle () {
        if (this.fontSize) {
          return { fontSize: this.fontSize }
        }
      }
    },

    methods: {
      __getContent: function __getContent (h) {
        return this.icon !== void 0
          ? [ h(QIcon, { props: { name: this.icon } }) ].concat(slot(this, 'default'))
          : slot(this, 'default')
      }
    },

    render: function render (h) {
      return h('div', {
        staticClass: 'q-avatar',
        style: this.style,
        on: this.$listeners
      }, [
        h('div', {
          staticClass: 'q-avatar__content row flex-center overflow-hidden',
          class: this.contentClass,
          style: this.contentStyle
        }, [
          this.__getContent(h)
        ])
      ])
    }
  });

  var QBadge = Vue.extend({
    name: 'QBadge',

    props: {
      color: String,
      textColor: String,

      floating: Boolean,
      transparent: Boolean,
      multiLine: Boolean,

      label: [Number, String],

      align: {
        type: String,
        validator: function (v) { return ['top', 'middle', 'bottom'].includes(v); }
      }
    },

    computed: {
      style: function style () {
        if (this.align !== void 0) {
          return { verticalAlign: this.align }
        }
      },

      classes: function classes () {
        return 'q-badge flex inline items-center no-wrap' +
          " q-badge--" + (this.multiLine === true ? 'multi' : 'single') + "-line" +
          (this.color !== void 0 ? (" bg-" + (this.color)) : '') +
          (this.textColor !== void 0 ? (" text-" + (this.textColor)) : '') +
          (this.floating === true ? ' q-badge--floating' : '') +
          (this.transparent === true ? ' q-badge--transparent' : '')
      }
    },

    render: function render (h) {
      return h('div', {
        style: this.style,
        class: this.classes,
        on: this.$listeners
      }, this.label !== void 0 ? [ this.label ] : slot(this, 'default'))
    }
  });

  var QBanner = Vue.extend({
    name: 'QBanner',

    props: {
      inlineActions: Boolean,
      dense: Boolean,
      rounded: Boolean
    },

    render: function render (h) {
      var actions = slot(this, 'action');

      return h('div', {
        staticClass: 'q-banner row items-center',
        class: {
          'q-banner--top-padding': actions !== void 0 && !this.inlineActions,
          'q-banner--dense': this.dense,
          'rounded-borders': this.rounded
        },
        on: this.$listeners
      }, [

        h('div', {
          staticClass: 'q-banner__avatar col-auto row items-center'
        }, slot(this, 'avatar')),

        h('div', {
          staticClass: 'q-banner__content col text-body2'
        }, slot(this, 'default')),

        actions !== void 0
          ? h('div', {
            staticClass: 'q-banner__actions row items-center justify-end',
            class: this.inlineActions ? 'col-auto' : 'col-all'
          }, actions)
          : null

      ])
    }
  });

  var QBar = Vue.extend({
    name: 'QBar',

    props: {
      dense: Boolean,
      dark: Boolean
    },

    computed: {
      classes: function classes () {
        return ("q-bar--" + (this.dense ? 'dense' : 'standard') + " q-bar--" + (this.dark ? 'dark' : 'light'))
      }
    },

    render: function render (h) {
      return h('div', {
        staticClass: 'q-bar row no-wrap items-center',
        class: this.classes,
        on: this.$listeners
      }, slot(this, 'default'))
    }
  });

  var
    alignMap = {
      left: 'start',
      center: 'center',
      right: 'end',
      between: 'between',
      around: 'around',
      stretch: 'stretch'
    },
    alignValues = Object.keys(alignMap);

  var AlignMixin = {
    props: {
      align: {
        type: String,
        validator: function (v) { return alignValues.includes(v); }
      }
    },

    computed: {
      alignClass: function alignClass () {
        var align = this.align === void 0
          ? (this.vertical === true ? 'stretch' : 'left')
          : this.align;

        return ((this.vertical === true ? 'items' : 'justify') + "-" + (alignMap[align]))
      }
    }
  };

  var QBreadcrumbs = Vue.extend({
    name: 'QBreadcrumbs',

    mixins: [ AlignMixin ],

    props: {
      separator: {
        type: String,
        default: '/'
      },
      separatorColor: String,

      activeColor: {
        type: String,
        default: 'primary'
      },

      gutter: {
        type: String,
        validator: function (v) { return ['none', 'xs', 'sm', 'md', 'lg', 'xl'].includes(v); },
        default: 'sm'
      }
    },

    computed: {
      classes: function classes () {
        return ("" + (this.alignClass) + (this.gutter === 'none' ? '' : (" q-gutter-" + (this.gutter))))
      },

      sepClass: function sepClass () {
        if (this.separatorColor) {
          return ("text-" + (this.separatorColor))
        }
      },

      activeClass: function activeClass () {
        return ("text-" + (this.activeColor))
      }
    },

    render: function render (h) {
      var this$1 = this;

      var nodes = slot(this, 'default');
      if (nodes === void 0) { return }

      var els = 1;

      var
        child = [],
        len = nodes.filter(function (c) { return c.tag !== void 0 && c.tag.endsWith('-QBreadcrumbsEl'); }).length,
        separator = this.$scopedSlots.separator || (function () { return this$1.separator; });

      nodes.forEach(function (comp) {
        if (comp.tag !== void 0 && comp.tag.endsWith('-QBreadcrumbsEl')) {
          var middle = els < len;
          els++;

          child.push(h('div', {
            staticClass: 'flex items-center',
            class: middle ? this$1.activeClass : 'q-breadcrumbs--last'
          }, [ comp ]));

          if (middle) {
            child.push(h('div', {
              staticClass: 'q-breadcrumbs__separator', class: this$1.sepClass
            }, separator()));
          }
        }
        else {
          child.push(comp);
        }
      });

      return h('div', {
        staticClass: 'q-breadcrumbs',
        on: this.$listeners
      }, [
        h('div', {
          staticClass: 'flex items-center',
          class: this.classes
        }, child)
      ])
    }
  });

  var routerLinkProps = {
    to: [String, Object],
    exact: Boolean,
    append: Boolean,
    replace: Boolean,
    activeClass: String,
    exactActiveClass: String,
    disable: Boolean
  };

  var RouterLinkMixin = {
    props: routerLinkProps,

    computed: {
      hasRouterLink: function hasRouterLink () {
        return this.disable !== true && this.to !== void 0 && this.to !== null && this.to !== ''
      },

      routerLinkProps: function routerLinkProps () {
        return {
          to: this.to,
          exact: this.exact,
          append: this.append,
          replace: this.replace,
          activeClass: this.activeClass || 'q-router-link--active',
          exactActiveClass: this.exactActiveClass || 'q-router-link--exact-active',
          event: this.disable === true ? '' : void 0
        }
      }
    }
  };

  var QBreadcrumbsEl = Vue.extend({
    name: 'QBreadcrumbsEl',

    mixins: [ RouterLinkMixin ],

    props: {
      label: String,
      icon: String
    },

    render: function render (h) {
      var obj;

      return h(this.hasRouterLink === true ? 'router-link' : 'span', ( obj = {
        staticClass: 'q-breadcrumbs__el q-link flex inline items-center relative-position',
        props: this.hasRouterLink === true ? this.routerLinkProps : null
      }, obj[this.hasRouterLink === true ? 'nativeOn' : 'on'] = this.$listeners, obj ), [

        this.icon !== void 0
          ? h(QIcon, {
            staticClass: 'q-breadcrumbs__el-icon',
            class: this.label !== void 0 ? 'q-breadcrumbs__el-icon--with-label' : null,
            props: { name: this.icon }
          })
          : null,

        this.label

      ].concat(slot(this, 'default')))
    }
  });

  var mixin = {
    props: {
      color: String,
      size: {
        type: [Number, String],
        default: '1em'
      }
    },

    computed: {
      classes: function classes () {
        if (this.color) {
          return ("text-" + (this.color))
        }
      }
    }
  };

  var QSpinner = Vue.extend({
    name: 'QSpinner',

    mixins: [ mixin ],

    props: {
      thickness: {
        type: Number,
        default: 5
      }
    },

    render: function render (h) {
      return h('svg', {
        staticClass: 'q-spinner q-spinner-mat',
        class: this.classes,
        on: this.$listeners,
        attrs: {
          'width': this.size,
          'height': this.size,
          'viewBox': '25 25 50 50'
        }
      }, [
        h('circle', {
          staticClass: 'path',
          attrs: {
            'cx': '50',
            'cy': '50',
            'r': '20',
            'fill': 'none',
            'stroke': 'currentColor',
            'stroke-width': this.thickness,
            'stroke-miterlimit': '10'
          }
        })
      ])
    }
  });

  function offset (el) {
    if (el === window) {
      return { top: 0, left: 0 }
    }
    var ref = el.getBoundingClientRect();
    var top = ref.top;
    var left = ref.left;
    return { top: top, left: left }
  }

  function style (el, property) {
    return window.getComputedStyle(el).getPropertyValue(property)
  }

  function height (el) {
    return el === window
      ? window.innerHeight
      : el.getBoundingClientRect().height
  }

  function width (el) {
    return el === window
      ? window.innerWidth
      : el.getBoundingClientRect().width
  }

  function css (element, css) {
    var style = element.style;

    Object.keys(css).forEach(function (prop) {
      style[prop] = css[prop];
    });
  }

  function cssBatch (elements, style) {
    elements.forEach(function (el) { return css(el, style); });
  }

  function ready (fn) {
    if (typeof fn !== 'function') {
      return
    }

    if (document.readyState !== 'loading') {
      return fn()
    }

    document.addEventListener('DOMContentLoaded', fn, false);
  }

  var dom = {
    offset: offset,
    style: style,
    height: height,
    width: width,
    css: css,
    cssBatch: cssBatch,
    ready: ready
  };

  function showRipple (evt, el, ctx, forceCenter) {
    ctx.modifiers.stop === true && stop(evt);

    var ref = ctx.modifiers;
    var center = ref.center;
    var color = ref.color;
    center = center === true || forceCenter === true;

    var
      node = document.createElement('span'),
      innerNode = document.createElement('span'),
      pos = position(evt);
    var ref$1 = el.getBoundingClientRect();
    var left = ref$1.left;
    var top = ref$1.top;
    var width = ref$1.width;
    var height = ref$1.height;
    var diameter = Math.sqrt(width * width + height * height),
      radius = diameter / 2,
      centerX = ((width - diameter) / 2) + "px",
      x = center ? centerX : ((pos.left - left - radius) + "px"),
      centerY = ((height - diameter) / 2) + "px",
      y = center ? centerY : ((pos.top - top - radius) + "px");

    innerNode.className = 'q-ripple__inner';
    css(innerNode, {
      height: (diameter + "px"),
      width: (diameter + "px"),
      transform: ("translate3d(" + x + ", " + y + ", 0) scale3d(0.2, 0.2, 1)"),
      opacity: 0
    });

    node.className = "q-ripple" + (color ? ' text-' + color : '');
    node.setAttribute('dir', 'ltr');
    node.appendChild(innerNode);
    el.appendChild(node);

    ctx.abort = function () {
      node && node.remove();
      clearTimeout(timer);
    };

    var timer = setTimeout(function () {
      innerNode.classList.add('q-ripple__inner--enter');
      innerNode.style.transform = "translate3d(" + centerX + ", " + centerY + ", 0) scale3d(1, 1, 1)";
      innerNode.style.opacity = 0.2;

      timer = setTimeout(function () {
        innerNode.classList.remove('q-ripple__inner--enter');
        innerNode.classList.add('q-ripple__inner--leave');
        innerNode.style.opacity = 0;

        timer = setTimeout(function () {
          node && node.remove();
          ctx.abort = void 0;
        }, 275);
      }, 250);
    }, 50);
  }

  function updateCtx (ctx, ref) {
    var value = ref.value;
    var modifiers = ref.modifiers;
    var arg = ref.arg;

    ctx.enabled = value !== false;

    if (ctx.enabled === true) {
      ctx.modifiers = Object(value) === value
        ? {
          stop: value.stop === true || modifiers.stop === true,
          center: value.center === true || modifiers.center === true,
          color: value.color || arg
        }
        : {
          stop: modifiers.stop,
          center: modifiers.center,
          color: arg
        };
    }
  }

  var Ripple = {
    name: 'ripple',

    inserted: function inserted (el, binding) {
      var ctx = {
        modifiers: {},

        click: function click (evt) {
          // on ENTER in form IE emits a PointerEvent with negative client cordinates
          if (ctx.enabled === true && (Platform.is.ie !== true || evt.clientX >= 0)) {
            showRipple(evt, el, ctx, evt.qKeyEvent === true);
          }
        },

        keyup: function keyup (evt) {
          if (ctx.enabled === true && evt.keyCode === 13 && evt.qKeyEvent !== true) {
            showRipple(evt, el, ctx, true);
          }
        }
      };

      updateCtx(ctx, binding);

      if (el.__qripple) {
        el.__qripple_old = el.__qripple;
      }

      el.__qripple = ctx;
      el.addEventListener('click', ctx.click);
      el.addEventListener('keyup', ctx.keyup);
    },

    update: function update (el, binding) {
      el.__qripple !== void 0 && updateCtx(el.__qripple, binding);
    },

    unbind: function unbind (el) {
      var ctx = el.__qripple_old || el.__qripple;
      if (ctx !== void 0) {
        ctx.abort !== void 0 && ctx.abort();
        el.removeEventListener('click', ctx.click);
        el.removeEventListener('keyup', ctx.keyup);
        delete el[el.__qripple_old ? '__qripple_old' : '__qripple'];
      }
    }
  };

  var RippleMixin = {
    directives: {
      Ripple: Ripple
    },

    props: {
      ripple: {
        type: [Boolean, Object],
        default: true
      }
    }
  };

  var sizes = {
    xs: 8,
    sm: 10,
    md: 14,
    lg: 20,
    xl: 24
  };

  var BtnMixin = {
    mixins: [ RippleMixin, AlignMixin ],

    props: {
      type: String,
      to: [Object, String],
      replace: Boolean,

      label: [Number, String],
      icon: String,
      iconRight: String,

      round: Boolean,
      outline: Boolean,
      flat: Boolean,
      unelevated: Boolean,
      rounded: Boolean,
      push: Boolean,
      glossy: Boolean,

      size: String,
      fab: Boolean,
      fabMini: Boolean,

      color: String,
      textColor: String,
      noCaps: Boolean,
      noWrap: Boolean,
      dense: Boolean,

      tabindex: [Number, String],

      align: { default: 'center' },
      stack: Boolean,
      stretch: Boolean,
      loading: {
        type: Boolean,
        default: null
      },
      disable: Boolean
    },

    computed: {
      style: function style () {
        if (this.size && !this.fab && !this.fabMini) {
          return {
            fontSize: this.size in sizes ? ((sizes[this.size]) + "px") : this.size
          }
        }
      },

      isRound: function isRound () {
        return this.round === true || this.fab === true || this.fabMini === true
      },

      isDisabled: function isDisabled () {
        return this.disable === true || this.loading === true
      },

      computedTabIndex: function computedTabIndex () {
        return this.isDisabled === true ? -1 : this.tabindex || 0
      },

      hasRouterLink: function hasRouterLink () {
        return this.disable !== true && this.to !== void 0 && this.to !== null && this.to !== ''
      },

      isLink: function isLink () {
        return this.type === 'a' || this.hasRouterLink === true
      },

      design: function design () {
        if (this.flat === true) { return 'flat' }
        if (this.outline === true) { return 'outline' }
        if (this.push === true) { return 'push' }
        if (this.unelevated === true) { return 'unelevated' }
        return 'standard'
      },

      attrs: function attrs () {
        var att = { tabindex: this.computedTabIndex };
        if (this.type !== 'a') {
          att.type = this.type || 'button';
        }
        if (this.hasRouterLink === true) {
          att.href = this.$router.resolve(this.to).href;
        }
        if (this.isDisabled === true) {
          att.disabled = true;
        }
        return att
      },

      classes: function classes () {
        var colors;

        if (this.color !== void 0) {
          if (this.flat === true || this.outline === true) {
            colors = "text-" + (this.textColor || this.color);
          }
          else {
            colors = "bg-" + (this.color) + " text-" + (this.textColor || 'white');
          }
        }
        else if (this.textColor) {
          colors = "text-" + (this.textColor);
        }

        return "q-btn--" + (this.design) + " q-btn--" + (this.isRound === true ? 'round' : 'rectangle') +
          (colors !== void 0 ? ' ' + colors : '') +
          (this.isDisabled !== true ? ' q-focusable q-hoverable' : ' disabled') +
          (this.fab === true ? ' q-btn--fab' : (this.fabMini === true ? ' q-btn--fab-mini' : '')) +
          (this.noCaps === true ? ' q-btn--no-uppercase' : '') +
          (this.rounded === true ? ' q-btn--rounded' : '') +
          (this.dense === true ? ' q-btn--dense' : '') +
          (this.stretch === true ? ' no-border-radius self-stretch' : '') +
          (this.glossy === true ? ' glossy' : '')
      },

      innerClasses: function innerClasses () {
        return this.alignClass + (this.stack === true ? ' column' : ' row') +
          (this.noWrap === true ? ' no-wrap text-no-wrap' : '') +
          (this.loading === true ? ' q-btn__content--hidden' : '')
      }
    }
  };

  var QBtn = Vue.extend({
    name: 'QBtn',

    mixins: [ BtnMixin ],

    props: {
      percentage: {
        type: Number,
        validator: function (v) { return v >= 0 && v <= 100; }
      },
      darkPercentage: Boolean
    },

    computed: {
      hasLabel: function hasLabel () {
        return this.label !== void 0 && this.label !== null && this.label !== ''
      }
    },

    methods: {
      click: function click (e) {
        var this$1 = this;

        if (this.pressed === true) { return }

        if (e !== void 0) {
          // focus button if it came from ENTER on form
          // prevent the new submit (already done)
          if (this.type === 'submit') {
            var el = document.activeElement;

            if (
              (
                el !== document.body &&
                this.$el.contains(el) === false &&
                // required for iOS and desktop Safari
                el.contains(this.$el) === false
              ) ||
              (this.$q.platform.is.ie === true && (e.clientX < 0 || e.clientY < 0))
            ) {
              stopAndPrevent(e);
              this.$el.focus();
              return
            }
          }

          if (e.qKeyEvent !== true && this.$refs.blurTarget !== void 0) {
            this.$refs.blurTarget.focus();
          }

          if (e.defaultPrevented === true) {
            return
          }

          this.hasRouterLink === true && stopAndPrevent(e);
        }

        var go = function () {
          this$1.$router[this$1.replace === true ? 'replace' : 'push'](this$1.to);
        };

        this.$emit('click', e, go);
        this.hasRouterLink === true && e.navigate !== false && go();
      },

      __onKeydown: function __onKeydown (e) {
        if ([13, 32].includes(e.keyCode) === true) {
          // focus external button if the focus helper was focused before
          this.$el.focus();

          stopAndPrevent(e);

          if (this.pressed !== true) {
            this.pressed = true;
            this.$el.classList.add('q-btn--active');
            document.addEventListener('keyup', this.__onKeyupAbort);
          }
        }

        this.$emit('keydown', e);
      },

      __onKeyup: function __onKeyup (e) {
        if ([13, 32].includes(e.keyCode) === true) {
          this.__onKeyupAbort();

          // for click trigger
          var evt = new MouseEvent('click', e);
          evt.qKeyEvent = true;
          e.defaultPrevented === true && evt.preventDefault();
          this.$el.dispatchEvent(evt);

          stopAndPrevent(e);

          // for ripple
          e.qKeyEvent = true;
        }

        this.$emit('keyup', e);
      },

      __onKeyupAbort: function __onKeyupAbort () {
        this.pressed = false;
        document.removeEventListener('keyup', this.__onKeyupAbort);
        this.$el && this.$el.classList.remove('q-btn--active');
      }
    },

    beforeDestroy: function beforeDestroy () {
      document.removeEventListener('keyup', this.__onKeyupAbort);
    },

    render: function render (h) {
      var
        inner = [].concat(slot(this, 'default')),
        data = {
          staticClass: 'q-btn inline q-btn-item non-selectable',
          class: this.classes,
          style: this.style,
          attrs: this.attrs
        };

      if (this.isDisabled === false) {
        data.on = Object.assign({}, this.$listeners,
          {click: this.click,
          keydown: this.__onKeydown,
          keyup: this.__onKeyup});

        if (this.ripple !== false) {
          data.directives = [{
            name: 'ripple',
            value: this.ripple,
            modifiers: { center: this.isRound }
          }];
        }
      }

      if (this.hasLabel === true) {
        inner.unshift(
          h('div', [ this.label ])
        );
      }

      if (this.icon !== void 0) {
        inner.unshift(
          h(QIcon, {
            props: { name: this.icon, left: this.stack === false && this.hasLabel === true }
          })
        );
      }

      if (this.iconRight !== void 0 && this.isRound === false) {
        inner.push(
          h(QIcon, {
            props: { name: this.iconRight, right: this.stack === false && this.hasLabel === true }
          })
        );
      }

      return h(this.isLink ? 'a' : 'button', data, [
        h('div', {
          staticClass: 'q-focus-helper',
          ref: 'blurTarget',
          attrs: { tabindex: -1 }
        }),

        this.loading === true && this.percentage !== void 0
          ? h('div', {
            staticClass: 'q-btn__progress absolute-full',
            class: this.darkPercentage ? 'q-btn__progress--dark' : null,
            style: { transform: ("scale3d(" + (this.percentage / 100) + ",1,1)") }
          })
          : null,

        h('div', {
          staticClass: 'q-btn__content text-center col items-center q-anchor--skip',
          class: this.innerClasses
        }, inner),

        this.loading !== null
          ? h('transition', {
            props: { name: 'q-transition--fade' }
          }, this.loading === true ? [
            h('div', {
              key: 'loading',
              staticClass: 'absolute-full flex flex-center'
            }, this.$scopedSlots.loading !== void 0 ? this.$scopedSlots.loading() : [ h(QSpinner) ])
          ] : void 0)
          : null
      ])
    }
  });

  var QBtnGroup = Vue.extend({
    name: 'QBtnGroup',

    props: {
      unelevated: Boolean,
      outline: Boolean,
      flat: Boolean,
      rounded: Boolean,
      push: Boolean,
      stretch: Boolean,
      glossy: Boolean,
      spread: Boolean
    },

    computed: {
      classes: function classes () {
        var this$1 = this;

        return ['unelevated', 'outline', 'flat', 'rounded', 'push', 'stretch', 'glossy']
          .filter(function (t) { return this$1[t] === true; })
          .map(function (t) { return ("q-btn-group--" + t); }).join(' ')
      }
    },

    render: function render (h) {
      return h('div', {
        staticClass: 'q-btn-group row no-wrap ' +
          (this.spread === true ? 'q-btn-group--spread' : 'inline'),
        class: this.classes,
        on: this.$listeners
      }, slot(this, 'default'))
    }
  });

  function clearSelection () {
    if (window.getSelection !== void 0) {
      var selection = window.getSelection();
      if (selection.empty !== void 0) {
        selection.empty();
      }
      else if (selection.removeAllRanges !== void 0) {
        selection.removeAllRanges();
        Platform.is.mobile !== true && selection.addRange(document.createRange());
      }
    }
    else if (document.selection !== void 0) {
      document.selection.empty();
    }
  }

  var AnchorMixin = {
    props: {
      target: {
        type: [Boolean, String],
        default: true
      },
      contextMenu: Boolean
    },

    watch: {
      contextMenu: function contextMenu (val) {
        if (this.anchorEl !== void 0) {
          this.__unconfigureAnchorEl(!val);
          this.__configureAnchorEl(val);
        }
      },

      target: function target () {
        if (this.anchorEl !== void 0) {
          this.__unconfigureAnchorEl();
        }

        this.__pickAnchorEl();
      }
    },

    methods: {
      __showCondition: function __showCondition (evt) {
        // abort with no parent configured or on multi-touch
        if (this.anchorEl === void 0) {
          return false
        }
        if (evt === void 0) {
          return true
        }
        return evt.touches === void 0 || evt.touches.length <= 1
      },

      __contextClick: function __contextClick (evt) {
        this.hide(evt);
        this.show(evt);
        prevent(evt);
      },

      __toggleKey: function __toggleKey (evt) {
        if (evt !== void 0 && evt.keyCode === 13 && evt.qKeyEvent !== true) {
          this.toggle(evt);
        }
      },

      __mobileTouch: function __mobileTouch (evt) {
        var this$1 = this;

        this.__mobileCleanup(evt);

        if (this.__showCondition(evt) !== true) {
          return
        }

        this.hide(evt);
        this.anchorEl.classList.add('non-selectable');

        this.touchTimer = setTimeout(function () {
          this$1.show(evt);
        }, 300);
      },

      __mobileCleanup: function __mobileCleanup (evt) {
        this.anchorEl.classList.remove('non-selectable');
        clearTimeout(this.touchTimer);

        if (this.showing === true && evt !== void 0) {
          clearSelection();
          prevent(evt);
        }
      },

      __unconfigureAnchorEl: function __unconfigureAnchorEl (context) {
        var this$1 = this;
        if ( context === void 0 ) context = this.contextMenu;

        if (context === true) {
          if (this.$q.platform.is.mobile) {
            this.anchorEl.removeEventListener('touchstart', this.__mobileTouch)
            ;['touchcancel', 'touchmove', 'touchend'].forEach(function (evt) {
              this$1.anchorEl.removeEventListener(evt, this$1.__mobileCleanup);
            });
          }
          else {
            this.anchorEl.removeEventListener('click', this.hide);
            this.anchorEl.removeEventListener('contextmenu', this.__contextClick);
          }
        }
        else {
          this.anchorEl.removeEventListener('click', this.toggle);
          this.anchorEl.removeEventListener('keyup', this.__toggleKey);
        }
      },

      __configureAnchorEl: function __configureAnchorEl (context) {
        var this$1 = this;
        if ( context === void 0 ) context = this.contextMenu;

        if (this.noParentEvent === true) { return }

        if (context === true) {
          if (this.$q.platform.is.mobile) {
            this.anchorEl.addEventListener('touchstart', this.__mobileTouch)
            ;['touchcancel', 'touchmove', 'touchend'].forEach(function (evt) {
              this$1.anchorEl.addEventListener(evt, this$1.__mobileCleanup);
            });
          }
          else {
            this.anchorEl.addEventListener('click', this.hide);
            this.anchorEl.addEventListener('contextmenu', this.__contextClick);
          }
        }
        else {
          this.anchorEl.addEventListener('click', this.toggle);
          this.anchorEl.addEventListener('keyup', this.__toggleKey);
        }
      },

      __setAnchorEl: function __setAnchorEl (el) {
        this.anchorEl = el;
        while (this.anchorEl.classList.contains('q-anchor--skip')) {
          this.anchorEl = this.anchorEl.parentNode;
        }
        this.__configureAnchorEl();
      },

      __pickAnchorEl: function __pickAnchorEl () {
        if (this.target && typeof this.target === 'string') {
          var el = document.querySelector(this.target);
          if (el !== null) {
            this.anchorEl = el;
            this.__configureAnchorEl();
          }
          else {
            this.anchorEl = void 0;
            console.error(("Anchor: target \"" + (this.target) + "\" not found"), this);
          }
        }
        else if (this.target !== false) {
          this.__setAnchorEl(this.parentEl);
        }
        else {
          this.anchorEl = void 0;
        }
      }
    },

    mounted: function mounted () {
      var this$1 = this;

      this.parentEl = this.$el.parentNode;

      this.$nextTick(function () {
        this$1.__pickAnchorEl();

        if (this$1.value === true) {
          if (this$1.anchorEl === void 0) {
            this$1.$emit('input', false);
          }
          else {
            this$1.show();
          }
        }
      });
    },

    beforeDestroy: function beforeDestroy () {
      clearTimeout(this.touchTimer);
      this.__anchorCleanup !== void 0 && this.__anchorCleanup();

      if (this.anchorEl !== void 0) {
        this.__unconfigureAnchorEl();
      }
    }
  };

  var ModelToggleMixin = {
    props: {
      value: Boolean
    },

    data: function data () {
      return {
        showing: false
      }
    },

    watch: {
      value: function value (val) {
        if (this.disable === true && val === true) {
          this.$emit('input', false);
          return
        }

        if (val !== this.showing) {
          this[val ? 'show' : 'hide']();
        }
      }
    },

    methods: {
      toggle: function toggle (evt) {
        this[this.showing === true ? 'hide' : 'show'](evt);
      },

      show: function show (evt) {
        var this$1 = this;

        if (this.disable === true || this.showing === true) {
          return
        }
        if (this.__showCondition !== void 0 && this.__showCondition(evt) !== true) {
          return
        }

        this.$emit('before-show', evt);

        if (this.$q.platform.is.ie === true) {
          // IE sometimes performs a focus on body after click;
          // the delay prevents the click-outside to trigger on this focus
          setTimeout(function () {
            this$1.showing = true;
          }, 0);
        }
        else {
          this.showing = true;
        }

        this.$emit('input', true);

        if (this.$options.modelToggle !== void 0 && this.$options.modelToggle.history === true) {
          this.__historyEntry = {
            condition: function () { return this$1.persistent !== true },
            handler: this.hide
          };
          History.add(this.__historyEntry);
        }

        if (this.__show !== void 0) {
          this.__show(evt);
        }
        else {
          this.$emit('show', evt);
        }
      },

      hide: function hide (evt) {
        if (this.disable === true || this.showing === false) {
          return
        }

        this.$emit('before-hide', evt);
        this.showing = false;
        this.value !== false && this.$emit('input', false);

        this.__removeHistory();

        if (this.__hide !== void 0) {
          this.__hide(evt);
        }
        else {
          this.$emit('hide', evt);
        }
      },

      __removeHistory: function __removeHistory () {
        if (this.__historyEntry !== void 0) {
          History.remove(this.__historyEntry);
          this.__historyEntry = void 0;
        }
      }
    },

    beforeDestroy: function beforeDestroy () {
      this.showing === true && this.__removeHistory();
    }
  };

  var inject;

  function fillInject (root) {
    var
      options = (new Vue()).$root.$options,
      skip = [ 'el', 'methods', 'render', 'mixins' ]
        .concat(Vue.config._lifecycleHooks)
        .concat(Object.keys(options).filter(function (key) { return options[key] !== null; }));

    inject = {};

    Object.keys(root)
      .filter(function (name) { return skip.includes(name) === false; })
      .forEach(function (p) {
        inject[p] = root[p];
      });
  }

  function getVm (root, vm) {
    inject === void 0 && root !== void 0 && fillInject(root.$root.$options);
    return new Vue(inject !== void 0 ? Object.assign({}, inject, vm) : vm)
  }

  function getAllChildren (vm) {
    var children = vm.$children;
    vm.$children.forEach(function (child) {
      if (child.$children.length > 0) {
        children = children.concat(getAllChildren(child));
      }
    });
    return children
  }

  var PortalMixin = {
    inheritAttrs: false,

    props: {
      contentClass: [Array, String, Object],
      contentStyle: [Array, String, Object]
    },

    methods: {
      __showPortal: function __showPortal () {
        if (this.__portal !== void 0 && this.__portal.showing !== true) {
          document.body.appendChild(this.__portal.$el);
          this.__portal.showing = true;
        }
      },

      __hidePortal: function __hidePortal () {
        if (this.__portal !== void 0 && this.__portal.showing === true) {
          this.__portal.$el.remove();
          this.__portal.showing = false;
        }
      }
    },

    render: function render () {
      this.__portal !== void 0 && this.__portal.$forceUpdate();
    },

    beforeMount: function beforeMount () {
      var this$1 = this;

      var obj = {
        inheritAttrs: false,

        render: function (h) { return this$1.__render(h); },

        components: this.$options.components,
        directives: this.$options.directives
      };

      if (this.__onPortalClose !== void 0) {
        obj.methods = {
          __qClosePopup: this.__onPortalClose
        };
      }

      var onCreated = this.__onPortalCreated;

      if (onCreated !== void 0) {
        obj.created = function () {
          onCreated(this);
        };
      }

      this.__portal = getVm(this, obj).$mount();
    },

    beforeDestroy: function beforeDestroy () {
      this.__portal.$destroy();
      this.__portal.$el.remove();
      this.__portal = void 0;
    }
  };

  var TransitionMixin = {
    props: {
      transitionShow: {
        type: String,
        default: 'fade'
      },

      transitionHide: {
        type: String,
        default: 'fade'
      }
    },

    data: function data () {
      return {
        transitionState: this.showing
      }
    },

    watch: {
      showing: function showing (val) {
        var this$1 = this;

        this.transitionShow !== this.transitionHide && this.$nextTick(function () {
          this$1.transitionState = val;
        });
      }
    },

    computed: {
      transition: function transition () {
        return 'q-transition--' + (this.transitionState === true ? this.transitionHide : this.transitionShow)
      }
    }
  };

  var evtOpts = listenOpts.notPassiveCapture;

  var ClickOutside = {
    name: 'click-outside',

    bind: function bind (el, ref) {
      var value = ref.value;
      var arg = ref.arg;

      var ctx = {
        trigger: value,
        handler: function handler (evt) {
          var target = evt && evt.target;

          if (
            !target ||
            // IE wrongfully triggers focusin event with target set to body
            // when clicking, so we need this workaround:
            (Platform.is.ie && evt.type === 'focusin' && target === document.body)
          ) {
            return
          }

          if (target !== document.body) {
            var related = arg !== void 0
              ? arg.concat( [el] )
              : [ el ];

            for (var i = related.length - 1; i >= 0; i--) {
              if (related[i].contains(target)) {
                return
              }
            }

            var parent = target;
            while (parent !== document.body) {
              if (parent.classList.contains('q-menu') || parent.classList.contains('q-dialog')) {
                var sibling = parent;
                while ((sibling = sibling.previousElementSibling) !== null) {
                  if (sibling.contains(el)) {
                    return
                  }
                }
              }
              parent = parent.parentNode;
            }
          }

          // prevent accidental click/tap on something else
          // that has a trigger --> improves UX
          Platform.is.mobile === true && stopAndPrevent(evt);

          ctx.trigger(evt);
        }
      };

      if (el.__qclickoutside) {
        el.__qclickoutside_old = el.__qclickoutside;
      }

      el.__qclickoutside = ctx;
      document.body.addEventListener('mousedown', ctx.handler, evtOpts);
      document.body.addEventListener('touchstart', ctx.handler, evtOpts);
      Platform.is.desktop === true && document.body.addEventListener('focusin', ctx.handler, evtOpts);
    },

    update: function update (el, ref) {
      var value = ref.value;
      var oldValue = ref.oldValue;

      if (value !== oldValue) {
        el.__qclickoutside.trigger = value;
      }
    },

    unbind: function unbind (el) {
      var ctx = el.__qclickoutside_old || el.__qclickoutside;
      if (ctx !== void 0) {
        document.body.removeEventListener('mousedown', ctx.handler, evtOpts);
        document.body.removeEventListener('touchstart', ctx.handler, evtOpts);
        Platform.is.desktop === true && document.body.removeEventListener('focusin', ctx.handler, evtOpts);
        delete el[el.__qclickoutside_old ? '__qclickoutside_old' : '__qclickoutside'];
      }
    }
  };

  function s4 () {
    return Math.floor((1 + Math.random()) * 0x10000)
      .toString(16)
      .substring(1)
  }

  function uid () {
    return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
      s4() + '-' + s4() + s4() + s4()
  }

  function getScrollTarget (el) {
    return el.closest('.scroll,.scroll-y,.overflow-auto') || window
  }

  function getScrollHeight (el) {
    return (el === window ? document.body : el).scrollHeight
  }

  function getScrollWidth (el) {
    return (el === window ? document.body : el).scrollWidth
  }

  function getScrollPosition (scrollTarget) {
    if (scrollTarget === window) {
      return window.pageYOffset || window.scrollY || document.body.scrollTop || 0
    }
    return scrollTarget.scrollTop
  }

  function getHorizontalScrollPosition (scrollTarget) {
    if (scrollTarget === window) {
      return window.pageXOffset || window.scrollX || document.body.scrollLeft || 0
    }
    return scrollTarget.scrollLeft
  }

  function animScrollTo (el, to, duration) {
    var pos = getScrollPosition(el);

    if (duration <= 0) {
      if (pos !== to) {
        setScroll(el, to);
      }
      return
    }

    requestAnimationFrame(function () {
      var newPos = pos + (to - pos) / Math.max(16, duration) * 16;
      setScroll(el, newPos);
      if (newPos !== to) {
        animScrollTo(el, to, duration - 16);
      }
    });
  }

  function animHorizontalScrollTo (el, to, duration) {
    var pos = getHorizontalScrollPosition(el);

    if (duration <= 0) {
      if (pos !== to) {
        setHorizontalScroll(el, to);
      }
      return
    }

    requestAnimationFrame(function () {
      var newPos = pos + (to - pos) / Math.max(16, duration) * 16;
      setHorizontalScroll(el, newPos);
      if (newPos !== to) {
        animHorizontalScrollTo(el, to, duration - 16);
      }
    });
  }

  function setScroll (scrollTarget, offset) {
    if (scrollTarget === window) {
      window.scrollTo(0, offset);
      return
    }
    scrollTarget.scrollTop = offset;
  }

  function setHorizontalScroll (scrollTarget, offset) {
    if (scrollTarget === window) {
      window.scrollTo(offset, 0);
      return
    }
    scrollTarget.scrollLeft = offset;
  }

  function setScrollPosition (scrollTarget, offset, duration) {
    if (duration) {
      animScrollTo(scrollTarget, offset, duration);
      return
    }
    setScroll(scrollTarget, offset);
  }

  function setHorizontalScrollPosition (scrollTarget, offset, duration) {
    if (duration) {
      animHorizontalScrollTo(scrollTarget, offset, duration);
      return
    }
    setHorizontalScroll(scrollTarget, offset);
  }

  var size;
  function getScrollbarWidth () {
    if (size !== undefined) {
      return size
    }

    var
      inner = document.createElement('p'),
      outer = document.createElement('div');

    css(inner, {
      width: '100%',
      height: '200px'
    });
    css(outer, {
      position: 'absolute',
      top: '0px',
      left: '0px',
      visibility: 'hidden',
      width: '200px',
      height: '150px',
      overflow: 'hidden'
    });

    outer.appendChild(inner);

    document.body.appendChild(outer);

    var w1 = inner.offsetWidth;
    outer.style.overflow = 'scroll';
    var w2 = inner.offsetWidth;

    if (w1 === w2) {
      w2 = outer.clientWidth;
    }

    outer.remove();
    size = w1 - w2;

    return size
  }

  function hasScrollbar (el, onY) {
    if ( onY === void 0 ) onY = true;

    if (!el || el.nodeType !== Node.ELEMENT_NODE) {
      return false
    }

    return onY
      ? (
        el.scrollHeight > el.clientHeight && (
          el.classList.contains('scroll') ||
          el.classList.contains('overflow-auto') ||
          ['auto', 'scroll'].includes(window.getComputedStyle(el)['overflow-y'])
        )
      )
      : (
        el.scrollWidth > el.clientWidth && (
          el.classList.contains('scroll') ||
          el.classList.contains('overflow-auto') ||
          ['auto', 'scroll'].includes(window.getComputedStyle(el)['overflow-x'])
        )
      )
  }

  var scroll = {
    getScrollTarget: getScrollTarget,

    getScrollHeight: getScrollHeight,
    getScrollWidth: getScrollWidth,

    getScrollPosition: getScrollPosition,
    getHorizontalScrollPosition: getHorizontalScrollPosition,

    animScrollTo: animScrollTo,
    animHorizontalScrollTo: animHorizontalScrollTo,

    setScrollPosition: setScrollPosition,
    setHorizontalScrollPosition: setHorizontalScrollPosition,

    getScrollbarWidth: getScrollbarWidth,
    hasScrollbar: hasScrollbar
  };

  var handlers = [];

  var EscapeKey = {
    __install: function __install () {
      this.__installed = true;
      window.addEventListener('keyup', function (evt) {
        if (
          handlers.length !== 0 &&
          (evt.which === 27 || evt.keyCode === 27)
        ) {
          handlers[handlers.length - 1].fn(evt);
        }
      });
    },

    register: function register (comp, fn) {
      if (Platform.is.desktop === true) {
        this.__installed !== true && this.__install();
        handlers.push({ comp: comp, fn: fn });
      }
    },

    pop: function pop (comp) {
      if (Platform.is.desktop === true) {
        var index = handlers.findIndex(function (h) { return h.comp === comp; });
        if (index > -1) {
          handlers.splice(index, 1);
        }
      }
    }
  };

  var
    bus = new Vue(),
    tree = {},
    rootHide = {};

  /*
   * Tree has (key: value) entries where
   *
   *    key: menuId
   *
   *    value --> (true / menuId)
   *       true --- means has no sub-menu opened
   *       menuId --- menuId of the sub-menu that is currently opened
   *
   */

  function closeRootMenu (id) {
    while (tree[id] !== void 0) {
      var res = Object.keys(tree).find(function (key) { return tree[key] === id; });
      if (res !== void 0) {
        id = res;
      }
      else {
        rootHide[id] !== void 0 && rootHide[id]();
        return true
      }
    }
  }

  var MenuTreeMixin = {
    methods: {
      __registerTree: function __registerTree () {
        tree[this.menuId] = true;

        if (this.$root.menuParentId === void 0) {
          rootHide[this.menuId] = this.hide;
          return
        }

        if (tree[this.$root.menuParentId] !== true) {
          bus.$emit('hide', tree[this.$root.menuParentId]);
        }

        bus.$on('hide', this.__processEvent);
        tree[this.$root.menuParentId] = this.menuId;
      },

      __unregisterTree: function __unregisterTree () {
        // if it hasn't been registered or already unregistered (beforeDestroy)
        if (tree[this.menuId] === void 0) {
          return
        }

        delete rootHide[this.menuId];

        if (this.$root.menuParentId !== void 0) {
          bus.$off('hide', this.__processEvent);
        }

        var child = tree[this.menuId];

        delete tree[this.menuId];

        if (child !== true) {
          bus.$emit('hide', child);
        }
      },

      __processEvent: function __processEvent (id) {
        this.menuId === id && this.hide();
      }
    }
  };

  function validatePosition (pos) {
    var parts = pos.split(' ');
    if (parts.length !== 2) {
      return false
    }
    if (!['top', 'center', 'bottom'].includes(parts[0])) {
      console.error('Anchor/Self position must start with one of top/center/bottom');
      return false
    }
    if (!['left', 'middle', 'right'].includes(parts[1])) {
      console.error('Anchor/Self position must end with one of left/middle/right');
      return false
    }
    return true
  }

  function validateOffset (val) {
    if (!val) { return true }
    if (val.length !== 2) { return false }
    if (typeof val[0] !== 'number' || typeof val[1] !== 'number') {
      return false
    }
    return true
  }

  function parsePosition (pos) {
    var parts = pos.split(' ');
    return { vertical: parts[0], horizontal: parts[1] }
  }

  function getAnchorProps (el, offset) {
    var ref = el.getBoundingClientRect();
    var top = ref.top;
    var left = ref.left;
    var right = ref.right;
    var bottom = ref.bottom;
    var width = ref.width;
    var height = ref.height;

    if (offset !== void 0) {
      top -= offset[1];
      left -= offset[0];
      bottom += offset[1];
      right += offset[0];

      width += offset[0];
      height += offset[1];
    }

    return {
      top: top,
      left: left,
      right: right,
      bottom: bottom,
      width: width,
      height: height,
      middle: left + (right - left) / 2,
      center: top + (bottom - top) / 2
    }
  }

  function getTargetProps (el) {
    return {
      top: 0,
      center: el.offsetHeight / 2,
      bottom: el.offsetHeight,
      left: 0,
      middle: el.offsetWidth / 2,
      right: el.offsetWidth
    }
  }

  // cfg: { el, anchorEl, anchorOrigin, selfOrigin, offset, absoluteOffset, cover, fit, maxHeight, maxWidth }
  function setPosition (cfg) {
    var anchorProps;

    // scroll position might change
    // if max-height changes, so we
    // need to restore it after we calculate
    // the new positioning
    var scrollTop = cfg.el.scrollTop;

    cfg.el.style.maxHeight = cfg.maxHeight;
    cfg.el.style.maxWidth = cfg.maxWidth;

    if (cfg.absoluteOffset === void 0) {
      anchorProps = getAnchorProps(cfg.anchorEl, cfg.cover === true ? [0, 0] : cfg.offset);
    }
    else {
      var ref = cfg.anchorEl.getBoundingClientRect();
      var anchorTop = ref.top;
      var anchorLeft = ref.left;
      var top = anchorTop + cfg.absoluteOffset.top,
        left = anchorLeft + cfg.absoluteOffset.left;

      anchorProps = { top: top, left: left, width: 1, height: 1, right: left + 1, center: top, middle: left, bottom: top + 1 };
    }

    if (cfg.fit === true || cfg.cover === true) {
      cfg.el.style.minWidth = anchorProps.width + 'px';
      if (cfg.cover === true) {
        cfg.el.style.minHeight = anchorProps.height + 'px';
      }
    }

    var
      targetProps = getTargetProps(cfg.el),
      props = {
        top: anchorProps[cfg.anchorOrigin.vertical] - targetProps[cfg.selfOrigin.vertical],
        left: anchorProps[cfg.anchorOrigin.horizontal] - targetProps[cfg.selfOrigin.horizontal]
      };

    applyBoundaries(props, anchorProps, targetProps, cfg.anchorOrigin, cfg.selfOrigin);

    cfg.el.style.top = Math.max(0, Math.floor(props.top)) + 'px';
    cfg.el.style.left = Math.max(0, Math.floor(props.left)) + 'px';

    if (props.maxHeight !== void 0) {
      cfg.el.style.maxHeight = Math.floor(props.maxHeight) + 'px';
    }
    if (props.maxWidth !== void 0) {
      cfg.el.style.maxWidth = Math.floor(props.maxWidth) + 'px';
    }

    // restore scroll position
    if (cfg.el.scrollTop !== scrollTop) {
      cfg.el.scrollTop = scrollTop;
    }
  }

  function applyBoundaries (props, anchorProps, targetProps, anchorOrigin, selfOrigin) {
    var margin = getScrollbarWidth();
    var innerHeight = window.innerHeight;
    var innerWidth = window.innerWidth;

    // don't go bellow scrollbars
    innerHeight -= margin;
    innerWidth -= margin;

    if (props.top < 0 || props.top + targetProps.bottom > innerHeight) {
      if (selfOrigin.vertical === 'center') {
        props.top = anchorProps[selfOrigin.vertical] > innerHeight / 2
          ? innerHeight - targetProps.bottom
          : 0;
        props.maxHeight = Math.min(targetProps.bottom, innerHeight);
      }
      else if (anchorProps[selfOrigin.vertical] > innerHeight / 2) {
        var anchorY = Math.min(
          innerHeight,
          anchorOrigin.vertical === 'center'
            ? anchorProps.center
            : (anchorOrigin.vertical === selfOrigin.vertical ? anchorProps.bottom : anchorProps.top)
        );
        props.maxHeight = Math.min(targetProps.bottom, anchorY);
        props.top = Math.max(0, anchorY - props.maxHeight);
      }
      else {
        props.top = anchorOrigin.vertical === 'center'
          ? anchorProps.center
          : (anchorOrigin.vertical === selfOrigin.vertical ? anchorProps.top : anchorProps.bottom);
        props.maxHeight = Math.min(targetProps.bottom, innerHeight - props.top);
      }
    }

    if (props.left < 0 || props.left + targetProps.right > innerWidth) {
      props.maxWidth = Math.min(targetProps.right, innerWidth);
      if (selfOrigin.horizontal === 'middle') {
        props.left = anchorProps[selfOrigin.horizontal] > innerWidth / 2 ? innerWidth - targetProps.right : 0;
      }
      else if (anchorProps[selfOrigin.horizontal] > innerWidth / 2) {
        var anchorX = Math.min(
          innerWidth,
          anchorOrigin.horizontal === 'middle'
            ? anchorProps.center
            : (anchorOrigin.horizontal === selfOrigin.horizontal ? anchorProps.right : anchorProps.left)
        );
        props.maxWidth = Math.min(targetProps.right, anchorX);
        props.left = Math.max(0, anchorX - props.maxWidth);
      }
      else {
        props.left = anchorOrigin.horizontal === 'middle'
          ? anchorProps.center
          : (anchorOrigin.horizontal === selfOrigin.horizontal ? anchorProps.left : anchorProps.right);
        props.maxWidth = Math.min(targetProps.right, innerWidth - props.left);
      }
    }
  }

  var QMenu = Vue.extend({
    name: 'QMenu',

    mixins: [ AnchorMixin, ModelToggleMixin, PortalMixin, MenuTreeMixin, TransitionMixin ],

    directives: {
      ClickOutside: ClickOutside
    },

    props: {
      persistent: Boolean,
      autoClose: Boolean,

      noParentEvent: Boolean,
      noRefocus: Boolean,
      noFocus: Boolean,

      fit: Boolean,
      cover: Boolean,

      square: Boolean,

      anchor: {
        type: String,
        validator: validatePosition
      },
      self: {
        type: String,
        validator: validatePosition
      },
      offset: {
        type: Array,
        validator: validateOffset
      },

      touchPosition: Boolean,

      maxHeight: {
        type: String,
        default: null
      },
      maxWidth: {
        type: String,
        default: null
      }
    },

    data: function data () {
      return {
        menuId: uid()
      }
    },

    computed: {
      horizSide: function horizSide () {
        return this.$q.lang.rtl ? 'right' : 'left'
      },

      anchorOrigin: function anchorOrigin () {
        return parsePosition(
          this.anchor || (
            this.cover === true ? "center middle" : ("bottom " + (this.horizSide))
          )
        )
      },

      selfOrigin: function selfOrigin () {
        return this.cover === true
          ? this.anchorOrigin
          : parsePosition(this.self || ("top " + (this.horizSide)))
      },

      menuClass: function menuClass () {
        return this.square === true ? ' q-menu--square' : ''
      }
    },

    watch: {
      noParentEvent: function noParentEvent (val) {
        if (this.anchorEl !== void 0) {
          if (val === true) {
            this.__unconfigureAnchorEl();
          }
          else {
            this.__configureAnchorEl();
          }
        }
      }
    },

    methods: {
      focus: function focus () {
        var node = this.__portal.$refs !== void 0 ? this.__portal.$refs.inner : void 0;

        if (node !== void 0 && node.contains(document.activeElement) !== true) {
          node = node.querySelector('[autofocus]') || node;
          node.focus();
        }
      },

      __show: function __show (evt) {
        var this$1 = this;

        clearTimeout(this.timer);

        this.__refocusTarget = this.noRefocus === false
          ? document.activeElement
          : void 0;

        this.scrollTarget = getScrollTarget(this.anchorEl);
        this.scrollTarget.addEventListener('scroll', this.updatePosition, listenOpts.passive);
        if (this.scrollTarget !== window) {
          window.addEventListener('scroll', this.updatePosition, listenOpts.passive);
        }

        EscapeKey.register(this, function () {
          if (this$1.persistent !== true) {
            this$1.$emit('escape-key');
            this$1.hide();
          }
        });

        this.__showPortal();
        this.__registerTree();

        this.timer = setTimeout(function () {
          var ref = this$1.anchorEl.getBoundingClientRect();
          var top = ref.top;
          var left = ref.left;

          if (this$1.touchPosition || this$1.contextMenu) {
            var pos = position(evt);
            this$1.absoluteOffset = { left: pos.left - left, top: pos.top - top };
          }
          else {
            this$1.absoluteOffset = void 0;
          }

          this$1.updatePosition();

          if (this$1.unwatch === void 0) {
            this$1.unwatch = this$1.$watch('$q.screen.width', this$1.updatePosition);
          }

          this$1.$el.dispatchEvent(create('popup-show', { bubbles: true }));

          if (this$1.noFocus !== true) {
            document.activeElement.blur();

            this$1.$nextTick(function () {
              this$1.focus();
            });
          }

          this$1.timer = setTimeout(function () {
            this$1.$emit('show', evt);
          }, 300);
        }, 0);
      },

      __hide: function __hide (evt) {
        var this$1 = this;

        this.__anchorCleanup(true);

        if (this.__refocusTarget !== void 0) {
          this.__refocusTarget.focus();
        }

        this.$el.dispatchEvent(create('popup-hide', { bubbles: true }));

        this.timer = setTimeout(function () {
          this$1.__hidePortal();
          this$1.$emit('hide', evt);
        }, 300);
      },

      __anchorCleanup: function __anchorCleanup (hiding) {
        clearTimeout(this.timer);
        this.absoluteOffset = void 0;

        if (this.unwatch !== void 0) {
          this.unwatch();
          this.unwatch = void 0;
        }

        if (hiding === true || this.showing === true) {
          EscapeKey.pop(this);
          this.__unregisterTree();

          this.scrollTarget.removeEventListener('scroll', this.updatePosition, listenOpts.passive);
          if (this.scrollTarget !== window) {
            window.removeEventListener('scroll', this.updatePosition, listenOpts.passive);
          }
        }
      },

      __onAutoClose: function __onAutoClose (e) {
        closeRootMenu(this.menuId);
        this.$emit('click', e);
      },

      updatePosition: function updatePosition () {
        var this$1 = this;

        var el = this.__portal.$el;

        if (el.nodeType === 8) { // IE replaces the comment with delay
          setTimeout(function () {
            this$1.__portal !== void 0 && this$1.__portal.showing === true && this$1.updatePosition();
          }, 25);
          return
        }

        setPosition({
          el: el,
          offset: this.offset,
          anchorEl: this.anchorEl,
          anchorOrigin: this.anchorOrigin,
          selfOrigin: this.selfOrigin,
          absoluteOffset: this.absoluteOffset,
          fit: this.fit,
          cover: this.cover,
          maxHeight: this.maxHeight,
          maxWidth: this.maxWidth
        });
      },

      __render: function __render (h) {
        var on = Object.assign({}, this.$listeners,
          {input: stop});

        if (this.autoClose === true) {
          on.click = this.__onAutoClose;
        }

        return h('transition', {
          props: { name: this.transition }
        }, [
          this.showing === true ? h('div', {
            ref: 'inner',
            staticClass: 'q-menu scroll' + this.menuClass,
            class: this.contentClass,
            style: this.contentStyle,
            attrs: Object.assign({}, {tabindex: -1},
              this.$attrs),
            on: on,
            directives: this.persistent !== true ? [{
              name: 'click-outside',
              value: this.hide,
              arg: [ this.anchorEl ]
            }] : null
          }, slot(this, 'default')) : null
        ])
      },

      __onPortalCreated: function __onPortalCreated (vm) {
        vm.menuParentId = this.menuId;
      },

      __onPortalClose: function __onPortalClose () {
        closeRootMenu(this.menuId);
      }
    },

    beforeDestroy: function beforeDestroy () {
      // When the menu is destroyed while open we can only emit the event on anchorEl
      if (this.showing === true && this.anchorEl !== void 0) {
        this.anchorEl.dispatchEvent(
          create('popup-hide', { bubbles: true })
        );
      }
    }
  });

  var QBtnDropdown = Vue.extend({
    name: 'QBtnDropdown',

    mixins: [ BtnMixin ],

    props: {
      value: Boolean,
      split: Boolean,

      contentClass: [Array, String, Object],
      contentStyle: [Array, String, Object],

      cover: Boolean,
      persistent: Boolean,
      autoClose: Boolean,
      menuAnchor: {
        type: String,
        default: 'bottom right'
      },
      menuSelf: {
        type: String,
        default: 'top right'
      },

      disableMainBtn: Boolean,
      disableDropdown: Boolean
    },

    data: function data () {
      return {
        showing: this.value
      }
    },

    watch: {
      value: function value (val) {
        this.$refs.menu !== void 0 && this.$refs.menu[val ? 'show' : 'hide']();
      }
    },

    render: function render (h) {
      var this$1 = this;

      var label = this.$scopedSlots.label !== void 0
        ? this.$scopedSlots.label()
        : [];

      var Arrow = [
        h(QIcon, {
          props: {
            name: this.$q.iconSet.arrow.dropdown
          },
          staticClass: 'q-btn-dropdown__arrow',
          class: {
            'rotate-180': this.showing,
            'q-btn-dropdown__arrow-container': this.split === false
          }
        })
      ];

      this.disableDropdown !== true && Arrow.push(
        h(QMenu, {
          ref: 'menu',
          props: {
            cover: this.cover,
            fit: true,
            persistent: this.persistent,
            autoClose: this.autoClose,
            anchor: this.menuAnchor,
            self: this.menuSelf,
            contentClass: this.contentClass,
            contentStyle: this.contentStyle
          },
          on: {
            'before-show': function (e) {
              this$1.showing = true;
              this$1.$emit('before-show', e);
            },
            show: function (e) {
              this$1.$emit('show', e);
              this$1.$emit('input', true);
            },
            'before-hide': function (e) {
              this$1.showing = false;
              this$1.$emit('before-hide', e);
            },
            hide: function (e) {
              this$1.$emit('hide', e);
              this$1.$emit('input', false);
            }
          }
        }, slot(this, 'default'))
      );

      if (this.split === false) {
        return h(QBtn, {
          class: 'q-btn-dropdown q-btn-dropdown--simple',
          props: Object.assign({}, this.$props,
            {disable: this.disable === true || this.disableMainBtn === true,
            noWrap: true,
            round: false}),
          on: {
            click: function (e) {
              this$1.$emit('click', e);
            }
          }
        }, label.concat(Arrow))
      }

      var Btn = h(QBtn, {
        class: 'q-btn-dropdown--current',
        props: Object.assign({}, this.$props,
          {disable: this.disable === true || this.disableMainBtn === true,
          noWrap: true,
          iconRight: this.iconRight,
          round: false}),
        on: {
          click: function (e) {
            this$1.hide();
            this$1.$emit('click', e);
          }
        }
      }, label);

      return h(QBtnGroup, {
        props: {
          outline: this.outline,
          flat: this.flat,
          rounded: this.rounded,
          push: this.push,
          unelevated: this.unelevated,
          glossy: this.glossy
        },
        staticClass: 'q-btn-dropdown q-btn-dropdown--split no-wrap q-btn-item',
        class: this.stretch === true ? 'self-stretch no-border-radius' : null
      }, [
        Btn,

        h(QBtn, {
          staticClass: 'q-btn-dropdown__arrow-container',
          props: {
            disable: this.disable === true || this.disableDropdown === true,
            outline: this.outline,
            flat: this.flat,
            rounded: this.rounded,
            push: this.push,
            size: this.size,
            color: this.color,
            textColor: this.textColor,
            dense: this.dense,
            ripple: this.ripple
          }
        }, Arrow)
      ])
    },

    methods: {
      toggle: function toggle (evt) {
        this.$refs.menu && this.$refs.menu.toggle(evt);
      },
      show: function show (evt) {
        this.$refs.menu && this.$refs.menu.show(evt);
      },
      hide: function hide (evt) {
        this.$refs.menu && this.$refs.menu.hide(evt);
      }
    },

    mounted: function mounted () {
      this.value === true && this.show();
    }
  });

  var QBtnToggle = Vue.extend({
    name: 'QBtnToggle',

    props: {
      value: {
        required: true
      },

      options: {
        type: Array,
        required: true,
        validator: function (v) { return v.every(
          function (opt) { return ('label' in opt || 'icon' in opt || 'slot' in opt) && 'value' in opt; }
        ); }
      },

      // To avoid seeing the active raise shadow through the transparent button, give it a color (even white).
      color: String,
      textColor: String,
      toggleColor: {
        type: String,
        default: 'primary'
      },
      toggleTextColor: String,

      outline: Boolean,
      flat: Boolean,
      unelevated: Boolean,
      rounded: Boolean,
      push: Boolean,
      glossy: Boolean,

      size: String,

      noCaps: Boolean,
      noWrap: Boolean,
      dense: Boolean,
      readonly: Boolean,
      disable: Boolean,

      stack: Boolean,
      stretch: Boolean,

      spread: Boolean,

      ripple: {
        type: [Boolean, Object],
        default: true
      }
    },

    computed: {
      val: function val () {
        var this$1 = this;

        return this.options.map(function (opt) { return opt.value === this$1.value; })
      }
    },

    methods: {
      set: function set (value, opt) {
        if (this.readonly === false && value !== this.value) {
          this.$emit('input', value, opt);
        }
      }
    },

    render: function render (h) {
      var this$1 = this;

      return h(QBtnGroup, {
        staticClass: 'q-btn-toggle',
        props: {
          outline: this.outline,
          flat: this.flat,
          rounded: this.rounded,
          push: this.push,
          stretch: this.stretch,
          unelevated: this.unelevated,
          glossy: this.glossy,
          spread: this.spread
        },
        on: this.$listeners
      },
      this.options.map(
        function (opt, i) { return h(QBtn, {
          key: i,
          on: { click: function () { return this$1.set(opt.value, opt); } },
          props: {
            disable: this$1.disable || opt.disable,
            label: opt.label,
            // Colors come from the button specific options first, then from general props
            color: this$1.val[i] === true ? opt.toggleColor || this$1.toggleColor : opt.color || this$1.color,
            textColor: this$1.val[i] === true ? opt.toggleTextColor || this$1.toggleTextColor : opt.textColor || this$1.textColor,
            icon: opt.icon,
            iconRight: opt.iconRight,
            noCaps: this$1.noCaps === true || opt.noCaps === true,
            noWrap: this$1.noWrap === true || opt.noWrap === true,
            outline: this$1.outline,
            flat: this$1.flat,
            rounded: this$1.rounded,
            push: this$1.push,
            unelevated: this$1.unelevated,
            size: this$1.size,
            dense: this$1.dense,
            ripple: this$1.ripple || opt.ripple,
            stack: this$1.stack === true || opt.stack === true,
            tabindex: opt.tabindex,
            stretch: this$1.stretch
          }
        }, opt.slot !== void 0 ? slot(this$1, opt.slot) : void 0); }
      ))
    }
  });

  var QCard = Vue.extend({
    name: 'QCard',

    props: {
      dark: Boolean,

      square: Boolean,
      flat: Boolean,
      bordered: Boolean
    },

    render: function render (h) {
      return h('div', {
        staticClass: 'q-card',
        class: {
          'q-card--dark': this.dark,
          'q-card--bordered': this.bordered,
          'q-card--square no-border-radius': this.square,
          'q-card--flat no-shadow': this.flat
        },
        on: this.$listeners
      }, slot(this, 'default'))
    }
  });

  var QCardSection = Vue.extend({
    name: 'QCardSection',

    render: function render (h) {
      return h('div', {
        staticClass: 'q-card__section',
        on: this.$listeners
      }, slot(this, 'default'))
    }
  });

  var QCardActions = Vue.extend({
    name: 'QCardActions',

    mixins: [ AlignMixin ],

    props: {
      vertical: Boolean
    },

    computed: {
      classes: function classes () {
        return ("q-card__actions--" + (this.vertical === true ? 'vert column' : 'horiz row') + " " + (this.alignClass))
      }
    },

    render: function render (h) {
      return h('div', {
        staticClass: 'q-card__actions',
        class: this.classes,
        on: this.$listeners
      }, slot(this, 'default'))
    }
  });

  var directions = ['left', 'right', 'up', 'down', 'horizontal', 'vertical'];

  var modifiersAll = {
    left: true,
    right: true,
    up: true,
    down: true,
    horizontal: true,
    vertical: true,
    all: true
  };

  function getModifierDirections (mod) {
    var dir = {};

    directions.forEach(function (direction) {
      if (mod[direction]) {
        dir[direction] = true;
      }
    });

    if (Object.keys(dir).length === 0) {
      return modifiersAll
    }

    if (dir.horizontal === true) {
      dir.left = dir.right = true;
    }
    if (dir.vertical === true) {
      dir.up = dir.down = true;
    }
    if (dir.left === true && dir.right === true) {
      dir.horizontal = true;
    }
    if (dir.up === true && dir.down === true) {
      dir.vertical = true;
    }
    if (dir.horizontal === true && dir.vertical === true) {
      dir.all = true;
    }

    return dir
  }

  function updateModifiers (ctx, ref) {
    var oldValue = ref.oldValue;
    var value = ref.value;
    var modifiers = ref.modifiers;

    if (oldValue !== value) {
      ctx.handler = value;
    }

    if (directions.some(function (direction) { return modifiers[direction] !== ctx.modifiers[direction]; })) {
      ctx.modifiers = modifiers;
      ctx.direction = getModifierDirections(modifiers);
    }
  }

  function setObserver (el, evt, ctx) {
    var target = evt.target;
    ctx.touchTargetObserver = new MutationObserver(function () {
      el.contains(target) === false && ctx.end(evt);
    });
    ctx.touchTargetObserver.observe(el, { childList: true, subtree: true });
  }

  function removeObserver (ctx) {
    if (ctx.touchTargetObserver !== void 0) {
      ctx.touchTargetObserver.disconnect();
      ctx.touchTargetObserver = void 0;
    }
  }

  function parseArg (arg) {
    // delta (min velocity -- dist / time)
    // mobile min distance on first move
    // desktop min distance until deciding if it's a swipe or not
    var data = [0.06, 6, 50];

    if (typeof arg === 'string' && arg.length) {
      arg.split(':').forEach(function (val, index) {
        var v = parseInt(val, 10);
        v && (data[index] = v);
      });
    }

    return data
  }

  var TouchSwipe = {
    name: 'touch-swipe',

    bind: function bind (el, ref) {
      var value = ref.value;
      var arg = ref.arg;
      var modifiers = ref.modifiers;

      if (el.__qtouchswipe) {
        el.__qtouchswipe_old = el.__qtouchswipe;
      }

      // early return, we don't need to do anything
      if (modifiers.mouse !== true && Platform.has.touch !== true) {
        return
      }

      var ctx = {
        handler: value,
        sensitivity: parseArg(arg),

        modifiers: modifiers,
        direction: getModifierDirections(modifiers),

        mouseStart: function mouseStart (evt) {
          if (leftClick(evt)) {
            document.addEventListener('mousemove', ctx.move, true);
            document.addEventListener('mouseup', ctx.mouseEnd, true);
            ctx.start(evt, true);
          }
        },

        mouseEnd: function mouseEnd (evt) {
          document.removeEventListener('mousemove', ctx.move, true);
          document.removeEventListener('mouseup', ctx.mouseEnd, true);
          ctx.end(evt);
        },

        start: function start (evt, mouseEvent) {
          Platform.is.firefox === true && preventDraggable(el, true);
          removeObserver(ctx);
          mouseEvent !== true && setObserver(el, evt, ctx);

          var pos = position(evt);

          ctx.mouse = mouseEvent;
          ctx.event = {
            x: pos.left,
            y: pos.top,
            time: new Date().getTime(),
            dir: false,
            abort: false
          };
        },

        move: function move (evt) {
          if (ctx.event === void 0 || ctx.event.abort === true) {
            return
          }

          if (ctx.event.dir !== false) {
            stopAndPrevent(evt);
            return
          }

          var time = new Date().getTime() - ctx.event.time;

          if (time === 0) {
            return
          }

          var
            pos = position(evt),
            distX = pos.left - ctx.event.x,
            absX = Math.abs(distX),
            distY = pos.top - ctx.event.y,
            absY = Math.abs(distY);

          if (Platform.is.mobile === true) {
            if (absX < ctx.sensitivity[1] && absY < ctx.sensitivity[1]) {
              ctx.event.abort = true;
              return
            }
          }
          else if (absX < ctx.sensitivity[2] && absY < ctx.sensitivity[2]) {
            return
          }

          var
            velX = absX / time,
            velY = absY / time;

          if (
            ctx.direction.vertical === true &&
            absX < absY &&
            absX < 100 &&
            velY > ctx.sensitivity[0]
          ) {
            ctx.event.dir = distY < 0 ? 'up' : 'down';
          }

          if (
            ctx.direction.horizontal === true &&
            absX > absY &&
            absY < 100 &&
            velX > ctx.sensitivity[0]
          ) {
            ctx.event.dir = distX < 0 ? 'left' : 'right';
          }

          if (
            ctx.direction.up === true &&
            absX < absY &&
            distY < 0 &&
            absX < 100 &&
            velY > ctx.sensitivity[0]
          ) {
            ctx.event.dir = 'up';
          }

          if (
            ctx.direction.down === true &&
            absX < absY &&
            distY > 0 &&
            absX < 100 &&
            velY > ctx.sensitivity[0]
          ) {
            ctx.event.dir = 'down';
          }

          if (
            ctx.direction.left === true &&
            absX > absY &&
            distX < 0 &&
            absY < 100 &&
            velX > ctx.sensitivity[0]
          ) {
            ctx.event.dir = 'left';
          }

          if (
            ctx.direction.right === true &&
            absX > absY &&
            distX > 0 &&
            absY < 100 &&
            velX > ctx.sensitivity[0]
          ) {
            ctx.event.dir = 'right';
          }

          if (ctx.event.dir !== false) {
            document.body.classList.add('no-pointer-events');
            stopAndPrevent(evt);
            clearSelection();

            ctx.handler({
              evt: evt,
              touch: ctx.mouse !== true,
              mouse: ctx.mouse === true,
              direction: ctx.event.dir,
              duration: time,
              distance: {
                x: absX,
                y: absY
              }
            });
          }
          else {
            ctx.event.abort = true;
          }
        },

        end: function end (evt) {
          if (ctx.event === void 0) {
            return
          }

          Platform.is.firefox === true && preventDraggable(el, false);
          removeObserver(ctx);

          if (ctx.event.abort === false && ctx.event.dir !== false) {
            document.body.classList.remove('no-pointer-events');
            stopAndPrevent(evt);
          }

          ctx.event = void 0;
        }
      };

      el.__qtouchswipe = ctx;

      if (modifiers.mouse === true) {
        el.addEventListener('mousedown', ctx.mouseStart, modifiers.mouseCapture);
      }

      if (Platform.has.touch === true) {
        var opts = listenOpts['notPassive' + (modifiers.capture === true ? 'Capture' : '')];

        el.addEventListener('touchstart', ctx.start, opts);
        el.addEventListener('touchmove', ctx.move, opts);
        el.addEventListener('touchcancel', ctx.end, opts);
        el.addEventListener('touchend', ctx.end, opts);
      }
    },

    update: function update (el, binding) {
      var ctx = el.__qtouchswipe;

      if (ctx !== void 0) {
        updateModifiers(ctx, binding);
      }
    },

    unbind: function unbind (el, ref) {
      var modifiers = ref.modifiers;

      var ctx = el.__qtouchswipe_old || el.__qtouchswipe;

      if (ctx !== void 0) {
        Platform.is.firefox === true && preventDraggable(el, false);
        removeObserver(ctx);
        document.body.classList.remove('no-pointer-events');

        if (modifiers.mouse === true) {
          el.removeEventListener('mousedown', ctx.mouseStart, modifiers.mouseCapture);
          document.removeEventListener('mousemove', ctx.move, true);
          document.removeEventListener('mouseup', ctx.mouseEnd, true);
        }

        if (Platform.has.touch === true) {
          var opts = listenOpts['notPassive' + (modifiers.capture === true ? 'Capture' : '')];

          el.removeEventListener('touchstart', ctx.start, opts);
          el.removeEventListener('touchmove', ctx.move, opts);
          el.removeEventListener('touchcancel', ctx.end, opts);
          el.removeEventListener('touchend', ctx.end, opts);
        }

        delete el[el.__qtouchswipe_old ? '__qtouchswipe_old' : '__qtouchswipe'];
      }
    }
  };

  var PanelWrapper = Vue.extend({
    name: 'QTabPanelWrapper',

    render: function render (h) {
      return h('div', {
        staticClass: 'q-panel scroll',
        attrs: { role: 'tabpanel' },
        // stop propagation of content emitted @input
        // which would tamper with Panel's model
        on: {
          input: stop
        }
      }, slot(this, 'default'))
    }
  });

  var PanelParentMixin = {
    directives: {
      TouchSwipe: TouchSwipe
    },

    props: {
      value: {
        required: true
      },

      animated: Boolean,
      infinite: Boolean,
      swipeable: Boolean,

      transitionPrev: {
        type: String,
        default: 'slide-right'
      },
      transitionNext: {
        type: String,
        default: 'slide-left'
      },

      keepAlive: Boolean
    },

    data: function data () {
      return {
        panelIndex: null,
        panelTransition: null
      }
    },

    computed: {
      panelDirectives: function panelDirectives () {
        if (this.swipeable) {
          return [{
            name: 'touch-swipe',
            value: this.__swipe,
            modifiers: {
              horizontal: true,
              mouse: true
            }
          }]
        }
      },

      contentKey: function contentKey () {
        return typeof this.value === 'string' || typeof this.value === 'number'
          ? this.value
          : String(this.value)
      }
    },

    watch: {
      value: function value (newVal, oldVal) {
        var this$1 = this;

        var index = this.__isValidPanelName(newVal) === true
          ? this.__getPanelIndex(newVal)
          : -1;

        if (this.__forcedPanelTransition !== true) {
          this.__updatePanelTransition(
            index === -1 ? 0 : (index < this.__getPanelIndex(oldVal) ? -1 : 1)
          );
        }

        if (this.panelIndex !== index) {
          this.panelIndex = index;
          this.$emit('before-transition', newVal, oldVal);
          this.$nextTick(function () {
            this$1.$emit('transition', newVal, oldVal);
          });
        }
      }
    },

    methods: {
      next: function next () {
        this.__go(1);
      },

      previous: function previous () {
        this.__go(-1);
      },

      goTo: function goTo (name) {
        this.$emit('input', name);
      },

      __isValidPanelName: function __isValidPanelName (name) {
        return name !== void 0 && name !== null && name !== ''
      },

      __getPanelIndex: function __getPanelIndex (name) {
        return this.panels.findIndex(function (panel) {
          var opt = panel.componentOptions;
          return opt &&
            opt.propsData.name === name &&
            opt.propsData.disable !== '' &&
            opt.propsData.disable !== true
        })
      },

      __getAllPanels: function __getAllPanels () {
        var this$1 = this;

        return this.panels.filter(
          function (panel) { return panel.componentOptions !== void 0 &&
            this$1.__isValidPanelName(panel.componentOptions.propsData.name); }
        )
      },

      __getAvailablePanels: function __getAvailablePanels () {
        return this.panels.filter(function (panel) {
          var opt = panel.componentOptions;
          return opt &&
            opt.propsData.name !== void 0 &&
            opt.propsData.disable !== '' &&
            opt.propsData.disable !== true
        })
      },

      __updatePanelTransition: function __updatePanelTransition (direction) {
        var val = direction !== 0 && this.animated === true && this.panelIndex !== -1
          ? 'q-transition--' + (direction === -1 ? this.transitionPrev : this.transitionNext)
          : null;

        if (this.panelTransition !== val) {
          this.panelTransition = val;
        }
      },

      __go: function __go (direction, startIndex) {
        var this$1 = this;
        if ( startIndex === void 0 ) startIndex = this.panelIndex;

        var index = startIndex + direction;
        var slots = this.panels;

        while (index > -1 && index < slots.length) {
          var opt = slots[index].componentOptions;

          if (
            opt !== void 0 &&
            opt.propsData.disable !== '' &&
            opt.propsData.disable !== true
          ) {
            this.__updatePanelTransition(direction);
            this.__forcedPanelTransition = true;
            this.$emit('input', slots[index].componentOptions.propsData.name);
            setTimeout(function () {
              this$1.__forcedPanelTransition = false;
            });
            return
          }

          index += direction;
        }

        if (this.infinite === true && slots.length > 0 && startIndex !== -1 && startIndex !== slots.length) {
          this.__go(direction, direction === -1 ? slots.length : -1);
        }
      },

      __swipe: function __swipe (evt) {
        this.__go((this.$q.lang.rtl === true ? -1 : 1) * (evt.direction === 'left' ? 1 : -1));
      },

      __updatePanelIndex: function __updatePanelIndex () {
        var index = this.__getPanelIndex(this.value);

        if (this.panelIndex !== index) {
          this.panelIndex = index;
        }

        return true
      },

      __getPanelContent: function __getPanelContent (h) {
        if (this.panels.length === 0) {
          return
        }

        var panel = this.__isValidPanelName(this.value) &&
          this.__updatePanelIndex() &&
          this.panels[this.panelIndex];

        var content = this.keepAlive === true
          ? [
            h('keep-alive', [
              h(PanelWrapper, {
                key: this.contentKey
              }, [ panel ])
            ])
          ]
          : [
            h('div', {
              staticClass: 'q-panel scroll',
              key: this.contentKey,
              attrs: { role: 'tabpanel' },
              // stop propagation of content emitted @input
              // which would tamper with Panel's model
              on: { input: stop }
            }, [ panel ])
          ];

        return this.animated === true
          ? [
            h('transition', {
              props: {
                name: this.panelTransition
              }
            }, content)
          ]
          : content
      }
    },

    render: function render (h) {
      this.panels = this.$scopedSlots.default !== void 0
        ? this.$scopedSlots.default()
        : [];

      return this.__render(h)
    }
  };

  var PanelChildMixin = {
    props: {
      name: {
        required: true
      },
      disable: Boolean
    }
  };

  var FullscreenMixin = {
    props: {
      fullscreen: Boolean
    },

    data: function data () {
      return {
        inFullscreen: false
      }
    },

    watch: {
      $route: function $route () {
        this.exitFullscreen();
      },

      fullscreen: function fullscreen (v) {
        if (this.inFullscreen !== v) {
          this.toggleFullscreen();
        }
      },

      inFullscreen: function inFullscreen (v) {
        this.$emit('update:fullscreen', v);
        this.$emit('fullscreen', v);
      }
    },

    methods: {
      toggleFullscreen: function toggleFullscreen () {
        if (this.inFullscreen === true) {
          this.exitFullscreen();
        }
        else {
          this.setFullscreen();
        }
      },

      setFullscreen: function setFullscreen () {
        if (this.inFullscreen === true) {
          return
        }

        this.inFullscreen = true;
        this.container = this.$el.parentNode;
        this.container.replaceChild(this.fullscreenFillerNode, this.$el);
        document.body.appendChild(this.$el);
        document.body.classList.add('q-body--fullscreen-mixin');

        this.__historyFullscreen = {
          handler: this.exitFullscreen
        };
        History.add(this.__historyFullscreen);
      },

      exitFullscreen: function exitFullscreen () {
        var this$1 = this;

        if (this.inFullscreen !== true) {
          return
        }

        if (this.__historyFullscreen !== void 0) {
          History.remove(this.__historyFullscreen);
          this.__historyFullscreen = void 0;
        }
        this.container.replaceChild(this.$el, this.fullscreenFillerNode);
        document.body.classList.remove('q-body--fullscreen-mixin');
        this.inFullscreen = false;

        if (this.$el.scrollIntoView !== void 0) {
          setTimeout(function () { this$1.$el.scrollIntoView(); });
        }
      }
    },

    beforeMount: function beforeMount () {
      this.fullscreenFillerNode = document.createElement('span');
    },

    mounted: function mounted () {
      this.fullscreen === true && this.setFullscreen();
    },

    beforeDestroy: function beforeDestroy () {
      this.exitFullscreen();
    }
  };

  function isDeepEqual (a, b) {
    if (a === b) {
      return true
    }

    if (a instanceof Date && b instanceof Date) {
      return a.getTime() === b.getTime()
    }

    if (a !== Object(a) || b !== Object(b)) {
      return false
    }

    var props = Object.keys(a);

    if (props.length !== Object.keys(b).length) {
      return false
    }

    return props.every(function (prop) { return isDeepEqual(a[prop], b[prop]); })
  }

  function isDate (v) {
    return Object.prototype.toString.call(v) === '[object Date]'
  }

  function isNumber (v) {
    return typeof v === 'number' && isFinite(v)
  }

  var QCarousel = Vue.extend({
    name: 'QCarousel',

    mixins: [ PanelParentMixin, FullscreenMixin ],

    props: {
      height: String,
      padding: Boolean,

      transitionPrev: {
        default: 'fade'
      },
      transitionNext: {
        default: 'fade'
      },

      controlColor: String,
      autoplay: [Number, Boolean],

      arrows: Boolean,
      prevIcon: String,
      nextIcon: String,

      navigation: Boolean,
      navigationIcon: String,

      thumbnails: Boolean
    },

    computed: {
      style: function style () {
        if (this.inFullscreen !== true && this.height !== void 0) {
          return {
            height: this.height
          }
        }
      },

      classes: function classes () {
        return {
          fullscreen: this.inFullscreen,
          'q-carousel--arrows': this.padding === true && this.arrows === true,
          'q-carousel--navigation': this.padding === true && this.navigation === true
        }
      },

      arrowIcons: function arrowIcons () {
        var ico = [
          this.prevIcon || this.$q.iconSet.carousel.left,
          this.nextIcon || this.$q.iconSet.carousel.right
        ];

        return this.$q.lang.rtl
          ? ico.reverse()
          : ico
      },

      navIcon: function navIcon () {
        return this.navigationIcon || this.$q.iconSet.carousel.navigationIcon
      }
    },

    watch: {
      value: function value () {
        if (this.autoplay) {
          clearInterval(this.timer);
          this.__startTimer();
        }
      },

      autoplay: function autoplay (val) {
        if (val) {
          this.__startTimer();
        }
        else {
          clearInterval(this.timer);
        }
      }
    },

    methods: {
      __startTimer: function __startTimer () {
        this.timer = setTimeout(
          this.next,
          isNumber(this.autoplay) ? this.autoplay : 5000
        );
      },

      __getNavigationContainer: function __getNavigationContainer (h, type, mapping) {
        return h('div', {
          staticClass: 'q-carousel__control q-carousel__navigation no-wrap absolute flex scroll-x q-carousel__navigation--' + type,
          class: this.controlColor ? ("text-" + (this.controlColor)) : null
        }, [
          h('div', {
            staticClass: 'q-carousel__navigation-inner flex no-wrap justify-center'
          }, this.__getAvailablePanels().map(mapping))
        ])
      },

      __getContent: function __getContent (h) {
        var this$1 = this;

        var node = [];

        if (this.arrows === true) {
          node.push(
            h(QBtn, {
              staticClass: 'q-carousel__control q-carousel__prev-arrow absolute',
              props: { size: 'lg', color: this.controlColor, icon: this.arrowIcons[0], round: true, flat: true, dense: true },
              on: { click: this.previous }
            }),
            h(QBtn, {
              staticClass: 'q-carousel__control q-carousel__next-arrow absolute',
              props: { size: 'lg', color: this.controlColor, icon: this.arrowIcons[1], round: true, flat: true, dense: true },
              on: { click: this.next }
            })
          );
        }

        if (this.navigation === true) {
          node.push(this.__getNavigationContainer(h, 'buttons', function (panel) {
            var name = panel.componentOptions.propsData.name;

            return h(QBtn, {
              key: name,
              staticClass: 'q-carousel__navigation-icon',
              class: { 'q-carousel__navigation-icon--active': name === this$1.value },
              props: {
                icon: this$1.navIcon,
                round: true,
                flat: true,
                size: 'sm'
              },
              on: {
                click: function () { this$1.goTo(name); }
              }
            })
          }));
        }
        else if (this.thumbnails) {
          node.push(this.__getNavigationContainer(h, 'thumbnails', function (panel) {
            var slide = panel.componentOptions.propsData;

            return h('img', {
              class: { 'q-carousel__thumbnail--active': slide.name === this$1.value },
              attrs: {
                src: slide.imgSrc
              },
              on: {
                click: function () { this$1.goTo(slide.name); }
              }
            })
          }));
        }

        return node.concat(slot(this, 'control'))
      },

      __render: function __render (h) {
        return h('div', {
          staticClass: 'q-carousel q-panel-parent',
          style: this.style,
          class: this.classes
        }, [
          h('div', {
            staticClass: 'q-carousel__slides-container',
            directives: this.panelDirectives
          }, [
            this.__getPanelContent(h)
          ])
        ].concat(this.__getContent(h)))
      }
    },

    mounted: function mounted () {
      this.autoplay && this.__startTimer();
    },

    beforeDestroy: function beforeDestroy () {
      clearInterval(this.timer);
    }
  });

  var QCarouselSlide = Vue.extend({
    name: 'QCarouselSlide',

    mixins: [ PanelChildMixin ],

    props: {
      imgSrc: String
    },

    computed: {
      style: function style () {
        if (this.imgSrc) {
          return {
            backgroundImage: ("url(" + (this.imgSrc) + ")"),
            backgroundSize: 'cover',
            backgroundPosition: '50%'
          }
        }
      }
    },

    render: function render (h) {
      return h('div', {
        staticClass: 'q-carousel__slide',
        style: this.style,
        on: this.$listeners
      }, slot(this, 'default'))
    }
  });

  var QCarouselControl = Vue.extend({
    name: 'QCarouselControl',

    props: {
      position: {
        type: String,
        default: 'bottom-right'
      },
      offset: {
        type: Array,
        default: function () { return [18, 18]; }
      }
    },

    computed: {
      classes: function classes () {
        return ("absolute-" + (this.position))
      },

      style: function style () {
        return {
          margin: ((this.offset[1]) + "px " + (this.offset[0]) + "px")
        }
      }
    },

    render: function render (h) {
      return h('div', {
        staticClass: 'q-carousel__control absolute',
        style: this.style,
        class: this.classes,
        on: this.$listeners
      }, slot(this, 'default'))
    }
  });

  var QChatMessage = Vue.extend({
    name: 'QChatMessage',

    props: {
      sent: Boolean,
      label: String,
      bgColor: String,
      textColor: String,
      name: String,
      avatar: String,
      text: Array,
      stamp: String,
      size: String,
      labelSanitize: Boolean,
      nameSanitize: Boolean,
      textSanitize: Boolean,
      stampSanitize: Boolean
    },

    computed: {
      textClass: function textClass () {
        if (this.textColor) {
          return ("text-" + (this.textColor))
        }
      },

      messageClass: function messageClass () {
        if (this.bgColor) {
          return ("text-" + (this.bgColor))
        }
      },

      sizeClass: function sizeClass () {
        if (this.size) {
          return ("col-" + (this.size))
        }
      },

      classes: function classes () {
        return {
          'q-message-sent': this.sent,
          'q-message-received': !this.sent
        }
      }
    },

    methods: {
      __getText: function __getText (h) {
        var this$1 = this;

        var
          domPropText = this.textSanitize === true ? 'textContent' : 'innerHTML',
          domPropStamp = this.stampSanitize === true ? 'textContent' : 'innerHTML';

        return this.text.map(function (msg, index) {
          var obj, obj$1;

          return h('div', {
          key: index,
          staticClass: 'q-message-text',
          class: this$1.messageClass
        }, [
          h('span', {
            staticClass: 'q-message-text-content',
            class: this$1.textClass
          }, [
            h('div', { domProps: ( obj = {}, obj[domPropText] = msg, obj ) }),
            this$1.stamp
              ? h('div', {
                staticClass: 'q-message-stamp',
                domProps: ( obj$1 = {}, obj$1[domPropStamp] = this$1.stamp, obj$1 )
              })
              : null
          ])
        ]);
        })
      },

      __getMessage: function __getMessage (h) {
        var obj;

        return h('div', {
          staticClass: 'q-message-text',
          class: this.messageClass
        }, [
          h('span', {
            staticClass: 'q-message-text-content',
            class: this.textClass
          }, this.$scopedSlots.default().concat([
            this.stamp !== void 0
              ? h('div', {
                staticClass: 'q-message-stamp',
                domProps: ( obj = {}, obj[this.stampSanitize === true ? 'textContent' : 'innerHTML'] = this.stamp, obj )
              })
              : null
          ]))
        ])
      }
    },

    render: function render (h) {
      var obj, obj$1;

      return h('div', {
        staticClass: 'q-message',
        class: this.classes
      }, [
        this.label
          ? h('div', {
            staticClass: 'q-message-label text-center',
            domProps: ( obj = {}, obj[this.labelSanitize === true ? 'textContent' : 'innerHTML'] = this.label, obj )
          })
          : null,

        h('div', {
          staticClass: 'q-message-container row items-end no-wrap'
        }, [
          this.$scopedSlots.avatar !== void 0
            ? this.$scopedSlots.avatar()
            : (
              this.avatar !== void 0
                ? h('img', {
                  staticClass: 'q-message-avatar col-auto',
                  attrs: { src: this.avatar }
                })
                : null
            ),

          h('div', { class: this.sizeClass }, [
            this.name !== void 0
              ? h('div', {
                staticClass: 'q-message-name',
                domProps: ( obj$1 = {}, obj$1[this.nameSanitize === true ? 'textContent' : 'innerHTML'] = this.name, obj$1 )
              })
              : null,

            this.text !== void 0 ? this.__getText(h) : null,
            this.$scopedSlots.default !== void 0 ? this.__getMessage(h) : null
          ])
        ])
      ])
    }
  });

  var CheckboxMixin = {
    props: {
      value: {
        required: true
      },
      val: {},

      trueValue: { default: true },
      falseValue: { default: false },

      label: String,
      leftLabel: Boolean,

      color: String,
      keepColor: Boolean,
      dark: Boolean,
      dense: Boolean,

      disable: Boolean,
      tabindex: [String, Number]
    },

    computed: {
      isTrue: function isTrue () {
        return this.modelIsArray
          ? this.index > -1
          : this.value === this.trueValue
      },

      isFalse: function isFalse () {
        return this.modelIsArray
          ? this.index === -1
          : this.value === this.falseValue
      },

      index: function index () {
        if (this.modelIsArray === true) {
          return this.value.indexOf(this.val)
        }
      },

      modelIsArray: function modelIsArray () {
        return Array.isArray(this.value)
      },

      computedTabindex: function computedTabindex () {
        return this.disable === true ? -1 : this.tabindex || 0
      }
    },

    methods: {
      toggle: function toggle (e) {
        e !== void 0 && stopAndPrevent(e);

        if (this.disable === true) {
          return
        }

        var val;

        if (this.modelIsArray === true) {
          if (this.isTrue === true) {
            val = this.value.slice();
            val.splice(this.index, 1);
          }
          else {
            val = this.value.concat(this.val);
          }
        }
        else if (this.isTrue === true) {
          val = this.toggleIndeterminate ? this.indeterminateValue : this.falseValue;
        }
        else if (this.isFalse === true) {
          val = this.trueValue;
        }
        else {
          val = this.falseValue;
        }

        this.$emit('input', val);
      },

      __keyDown: function __keyDown (e) {
        if (e.keyCode === 13 || e.keyCode === 32) {
          this.toggle(e);
        }
      }
    }
  };

  var QCheckbox = Vue.extend({
    name: 'QCheckbox',

    mixins: [ CheckboxMixin ],

    props: {
      toggleIndeterminate: Boolean,
      indeterminateValue: { default: null }
    },

    computed: {
      isIndeterminate: function isIndeterminate () {
        return this.value === void 0 || this.value === this.indeterminateValue
      },

      classes: function classes () {
        return {
          'disabled': this.disable,
          'q-checkbox--dark': this.dark,
          'q-checkbox--dense': this.dense,
          'reverse': this.leftLabel
        }
      },

      innerClass: function innerClass () {
        if (this.isTrue === true) {
          return 'q-checkbox__inner--active' +
            (this.color !== void 0 ? ' text-' + this.color : '')
        }
        else if (this.isIndeterminate === true) {
          return 'q-checkbox__inner--indeterminate' +
            (this.color !== void 0 ? ' text-' + this.color : '')
        }
        else if (this.keepColor === true && this.color !== void 0) {
          return 'text-' + this.color
        }
      }
    },

    render: function render (h) {
      return h('div', {
        staticClass: 'q-checkbox cursor-pointer no-outline row inline no-wrap items-center',
        class: this.classes,
        attrs: { tabindex: this.computedTabindex },
        on: {
          click: this.toggle,
          keydown: this.__keyDown
        }
      }, [
        h('div', {
          staticClass: 'q-checkbox__inner relative-position',
          class: this.innerClass
        }, [
          this.disable !== true
            ? h('input', {
              staticClass: 'q-checkbox__native q-ma-none q-pa-none invisible',
              attrs: { type: 'checkbox' },
              on: { change: this.toggle }
            })
            : null,

          h('div', {
            staticClass: 'q-checkbox__bg absolute'
          }, [
            h('svg', {
              staticClass: 'q-checkbox__check fit absolute-full',
              attrs: { viewBox: '0 0 24 24' }
            }, [
              h('path', {
                attrs: {
                  fill: 'none',
                  d: 'M1.73,12.91 8.1,19.28 22.79,4.59'
                }
              })
            ]),

            h('div', { staticClass: 'q-checkbox__check-indet absolute' })
          ])
        ]),

        this.label !== void 0 || this.$scopedSlots.default !== void 0
          ? h('div', {
            staticClass: 'q-checkbox__label q-anchor--skip'
          }, (this.label !== void 0 ? [ this.label ] : []).concat(slot(this, 'default')))
          : null
      ])
    }
  });

  var QChip = Vue.extend({
    name: 'QChip',

    mixins: [ RippleMixin ],

    model: {
      event: 'remove'
    },

    props: {
      dense: Boolean,

      icon: String,
      iconRight: String,
      label: [String, Number],

      color: String,
      textColor: String,

      value: {
        type: Boolean,
        default: true
      },
      selected: {
        type: Boolean,
        default: null
      },

      square: Boolean,
      outline: Boolean,
      clickable: Boolean,
      removable: Boolean,

      tabindex: [String, Number],
      disable: Boolean
    },

    computed: {
      classes: function classes () {
        var obj;

        var text = this.outline
          ? this.color || this.textColor
          : this.textColor;

        return ( obj = {}, obj[("bg-" + (this.color))] = this.outline === false && this.color !== void 0, obj[("text-" + text + " q-chip--colored")] = text, obj.disabled = this.disable, obj['q-chip--dense'] = this.dense, obj['q-chip--outline'] = this.outline, obj['q-chip--selected'] = this.selected, obj['q-chip--clickable cursor-pointer non-selectable q-hoverable'] = this.isClickable, obj['q-chip--square'] = this.square, obj )
      },

      hasLeftIcon: function hasLeftIcon () {
        return this.selected === true || this.icon !== void 0
      },

      isClickable: function isClickable () {
        return this.disable === false && (this.clickable === true || this.selected !== null)
      },

      computedTabindex: function computedTabindex () {
        return this.disable === true ? -1 : this.tabindex || 0
      }
    },

    methods: {
      __onKeyup: function __onKeyup (e) {
        e.keyCode === 13 /* ENTER */ && this.__onClick(e);
      },

      __onClick: function __onClick (e) {
        if (!this.disable) {
          this.$emit('update:selected', !this.selected);
          this.$emit('click', e);
        }
      },

      __onRemove: function __onRemove (e) {
        if (e.keyCode === void 0 || e.keyCode === 13) {
          stopAndPrevent(e);
          !this.disable && this.$emit('remove', false);
        }
      },

      __getContent: function __getContent (h) {
        var child = [];

        this.isClickable && child.push(
          h('div', { staticClass: 'q-focus-helper' })
        );

        this.hasLeftIcon && child.push(
          h(QIcon, {
            staticClass: 'q-chip__icon q-chip__icon--left',
            props: { name: this.selected === true ? this.$q.iconSet.chip.selected : this.icon }
          })
        );

        child.push(
          h('div', {
            staticClass: 'q-chip__content row no-wrap items-center q-anchor--skip'
          }, this.label !== void 0 ? [ this.label ] : slot(this, 'default'))
        );

        this.iconRight && child.push(
          h(QIcon, {
            staticClass: 'q-chip__icon q-chip__icon--right',
            props: { name: this.iconRight }
          })
        );

        this.removable && child.push(
          h(QIcon, {
            staticClass: 'q-chip__icon q-chip__icon--remove cursor-pointer',
            props: { name: this.$q.iconSet.chip.remove },
            attrs: { tabindex: this.computedTabindex },
            nativeOn: {
              click: this.__onRemove,
              keyup: this.__onRemove
            }
          })
        );

        return child
      }
    },

    render: function render (h) {
      if (!this.value) { return }

      var data = this.isClickable ? {
        attrs: { tabindex: this.computedTabindex },
        on: {
          click: this.__onClick,
          keyup: this.__onKeyup
        },
        directives: [{ name: 'ripple', value: this.ripple }]
      } : {};

      data.staticClass = 'q-chip row inline no-wrap items-center';
      data.class = this.classes;

      return h('div', data, this.__getContent(h))
    }
  });

  var
    radius = 50,
    diameter = 2 * radius,
    circumference = diameter * Math.PI,
    strokeDashArray = Math.round(circumference * 1000) / 1000;

  var QCircularProgress = Vue.extend({
    name: 'QCircularProgress',

    props: {
      value: {
        type: Number,
        default: 0
      },

      min: {
        type: Number,
        default: 0
      },
      max: {
        type: Number,
        default: 100
      },

      color: String,
      centerColor: String,
      trackColor: String,

      size: String,
      fontSize: String,

      // ratio
      thickness: {
        type: Number,
        default: 0.2,
        validator: function (v) { return v >= 0 && v <= 1; }
      },

      angle: {
        type: Number,
        default: 0
      },

      indeterminate: Boolean,
      showValue: Boolean,
      reverse: Boolean,

      instantFeedback: Boolean // used by QKnob, private
    },

    computed: {
      style: function style () {
        if (this.size !== void 0) {
          return {
            fontSize: this.size
          }
        }
      },

      svgStyle: function svgStyle () {
        return { transform: ("rotate3d(0, 0, 1, " + (this.angle - 90) + "deg)") }
      },

      circleStyle: function circleStyle () {
        if (this.instantFeedback !== true && this.indeterminate !== true) {
          return { transition: 'stroke-dashoffset 0.6s ease 0s, stroke 0.6s ease' }
        }
      },

      dir: function dir () {
        return (this.$q.lang.rtl ? -1 : 1) * (this.reverse ? -1 : 1)
      },

      viewBox: function viewBox () {
        return diameter / (1 - this.thickness / 2)
      },

      viewBoxAttr: function viewBoxAttr () {
        return ((this.viewBox / 2) + " " + (this.viewBox / 2) + " " + (this.viewBox) + " " + (this.viewBox))
      },

      strokeDashOffset: function strokeDashOffset () {
        var progress = 1 - (this.value - this.min) / (this.max - this.min);
        return (this.dir * progress) * circumference
      },

      strokeWidth: function strokeWidth () {
        return this.thickness / 2 * this.viewBox
      }
    },

    methods: {
      __getCircle: function __getCircle (h, ref) {
        var thickness = ref.thickness;
        var offset = ref.offset;
        var color = ref.color;
        var cls = ref.cls;

        return h('circle', {
          staticClass: 'q-circular-progress__' + cls,
          class: color !== void 0 ? ("text-" + color) : null,
          style: this.circleStyle,
          attrs: {
            fill: 'transparent',
            stroke: 'currentColor',
            'stroke-width': thickness,
            'stroke-dasharray': strokeDashArray,
            'stroke-dashoffset': offset,
            cx: this.viewBox,
            cy: this.viewBox,
            r: radius
          }
        })
      }
    },

    render: function render (h) {
      return h('div', {
        staticClass: 'q-circular-progress',
        'class': ("q-circular-progress--" + (this.indeterminate === true ? 'in' : '') + "determinate"),
        style: this.style,
        on: this.$listeners,
        attrs: {
          'role': 'progressbar',
          'aria-valuemin': this.min,
          'aria-valuemax': this.max,
          'aria-valuenow': this.indeterminate !== true ? this.value : null
        }
      }, [
        h('svg', {
          staticClass: 'q-circular-progress__svg',
          style: this.svgStyle,
          attrs: {
            viewBox: this.viewBoxAttr
          }
        }, [
          this.centerColor !== void 0 && this.centerColor !== 'transparent' ? h('circle', {
            staticClass: 'q-circular-progress__center',
            class: ("text-" + (this.centerColor)),
            attrs: {
              fill: 'currentColor',
              r: radius - this.strokeWidth / 2,
              cx: this.viewBox,
              cy: this.viewBox
            }
          }) : null,

          this.trackColor !== void 0 && this.trackColor !== 'transparent' ? this.__getCircle(h, {
            cls: 'track',
            thickness: this.strokeWidth,
            offset: 0,
            color: this.trackColor
          }) : null,

          this.__getCircle(h, {
            cls: 'circle',
            thickness: this.strokeWidth,
            offset: this.strokeDashOffset,
            color: this.color
          })
        ]),

        this.showValue === true
          ? h('div', {
            staticClass: 'q-circular-progress__text absolute-full row flex-center content-center',
            style: { fontSize: this.fontSize }
          }, this.$scopedSlots.default !== void 0 ? this.$scopedSlots.default() : [ h('div', [ this.value ]) ])
          : null
      ])
    }
  });

  // file referenced from docs

  var
    hex = /^#[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/,
    hexa = /^#[0-9a-fA-F]{4}([0-9a-fA-F]{4})?$/,
    hexOrHexa = /^#([0-9a-fA-F]{3}|[0-9a-fA-F]{4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/,
    rgb = /^rgb\(((0|[1-9][\d]?|1[\d]{0,2}|2[\d]?|2[0-4][\d]|25[0-5]),){2}(0|[1-9][\d]?|1[\d]{0,2}|2[\d]?|2[0-4][\d]|25[0-5])\)$/,
    rgba = /^rgba\(((0|[1-9][\d]?|1[\d]{0,2}|2[\d]?|2[0-4][\d]|25[0-5]),){2}(0|[1-9][\d]?|1[\d]{0,2}|2[\d]?|2[0-4][\d]|25[0-5]),(0|0\.[0-9]+[1-9]|0\.[1-9]+|1)\)$/;

  var testPattern = {
    date: function (v) { return /^-?[\d]+\/[0-1]\d\/[0-3]\d$/.test(v); },
    time: function (v) { return /^([0-1]?\d|2[0-3]):[0-5]\d$/.test(v); },
    fulltime: function (v) { return /^([0-1]?\d|2[0-3]):[0-5]\d:[0-5]\d$/.test(v); },
    timeOrFulltime: function (v) { return /^([0-1]?\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/.test(v); },

    hexColor: function (v) { return hex.test(v); },
    hexaColor: function (v) { return hexa.test(v); },
    hexOrHexaColor: function (v) { return hexOrHexa.test(v); },

    rgbColor: function (v) { return rgb.test(v); },
    rgbaColor: function (v) { return rgba.test(v); },
    rgbOrRgbaColor: function (v) { return rgb.test(v) || rgba.test(v); },

    hexOrRgbColor: function (v) { return hex.test(v) || rgb.test(v); },
    hexaOrRgbaColor: function (v) { return hexa.test(v) || rgba.test(v); },
    anyColor: function (v) { return hexOrHexa.test(v) || rgb.test(v) || rgba.test(v); }
  };

  var patterns = {
    testPattern: testPattern
  };

  function throttle (fn, limit) {
    if ( limit === void 0 ) limit = 250;

    var wait = false;
    var result;

    return function () {
      var args = [], len = arguments.length;
      while ( len-- ) args[ len ] = arguments[ len ];

      if (wait) {
        return result
      }

      wait = true;
      result = fn.apply(this, args);
      setTimeout(function () {
        wait = false;
      }, limit);
      return result
    }
  }

  function getChanges (evt, ctx, isFinal) {
    var
      pos = position(evt),
      dir,
      distX = pos.left - ctx.event.x,
      distY = pos.top - ctx.event.y,
      absX = Math.abs(distX),
      absY = Math.abs(distY);

    var direction = ctx.direction;

    if (direction.horizontal === true && direction.vertical !== true) {
      dir = distX < 0 ? 'left' : 'right';
    }
    else if (direction.horizontal !== true && direction.vertical === true) {
      dir = distY < 0 ? 'up' : 'down';
    }
    else if (direction.up === true && distY < 0) {
      dir = 'up';
      if (absX > absY) {
        if (direction.left === true && distX < 0) {
          dir = 'left';
        }
        else if (direction.right === true && distX > 0) {
          dir = 'right';
        }
      }
    }
    else if (direction.down === true && distY > 0) {
      dir = 'down';
      if (absX > absY) {
        if (direction.left === true && distX < 0) {
          dir = 'left';
        }
        else if (direction.right === true && distX > 0) {
          dir = 'right';
        }
      }
    }
    else if (direction.left === true && distX < 0) {
      dir = 'left';
      if (absX < absY) {
        if (direction.up === true && distY < 0) {
          dir = 'up';
        }
        else if (direction.down === true && distY > 0) {
          dir = 'down';
        }
      }
    }
    else if (direction.right === true && distX > 0) {
      dir = 'right';
      if (absX < absY) {
        if (direction.up === true && distY < 0) {
          dir = 'up';
        }
        else if (direction.down === true && distY > 0) {
          dir = 'down';
        }
      }
    }

    if (dir === void 0 && isFinal !== true) {
      return
    }

    return {
      evt: evt,
      touch: ctx.event.mouse !== true,
      mouse: ctx.event.mouse,
      position: pos,
      direction: dir,
      isFirst: ctx.event.isFirst,
      isFinal: isFinal === true,
      duration: new Date().getTime() - ctx.event.time,
      distance: {
        x: absX,
        y: absY
      },
      offset: {
        x: distX,
        y: distY
      },
      delta: {
        x: pos.left - ctx.event.lastX,
        y: pos.top - ctx.event.lastY
      }
    }
  }

  var mouseEvtOpts = listenOpts.notPassiveCapture;

  var TouchPan = {
    name: 'touch-pan',

    bind: function bind (el, ref) {
      var value = ref.value;
      var modifiers = ref.modifiers;

      if (el.__qtouchpan) {
        el.__qtouchpan_old = el.__qtouchpan;
      }

      // early return, we don't need to do anything
      if (modifiers.mouse !== true && Platform.has.touch !== true) {
        return
      }

      var
        touchPassiveStr = modifiers.mightPrevent !== true && modifiers.prevent !== true
          ? 'passive'
          : 'notPassive',
        touchEvtOpts = listenOpts[touchPassiveStr + (modifiers.capture === true ? 'Capture' : '')];

      function handleEvent (evt, mouseEvent) {
        if (modifiers.mouse === true && mouseEvent === true) {
          stopAndPrevent(evt);
        }
        else {
          modifiers.stop && stop(evt);
          modifiers.prevent && prevent(evt);
        }
      }

      var ctx = {
        handler: value,
        modifiers: modifiers,
        direction: getModifierDirections(modifiers),

        mouseStart: function mouseStart (evt) {
          if (leftClick(evt)) {
            // stop propagation so possible upper v-touch-pan don't catch this as well
            modifiers.mouseAllDir === true && stop(evt);

            document.addEventListener('mousemove', ctx.move, mouseEvtOpts);
            document.addEventListener('mouseup', ctx.mouseEnd, mouseEvtOpts);
            ctx.start(evt, true);
          }
        },

        mouseEnd: function mouseEnd (evt) {
          document.removeEventListener('mousemove', ctx.move, mouseEvtOpts);
          document.removeEventListener('mouseup', ctx.mouseEnd, mouseEvtOpts);
          ctx.end(evt);
        },

        start: function start (evt, mouseEvent) {
          Platform.is.firefox === true && preventDraggable(el, true);
          removeObserver(ctx);
          mouseEvent !== true && setObserver(el, evt, ctx);

          var pos = position(evt);

          ctx.event = {
            x: pos.left,
            y: pos.top,
            time: new Date().getTime(),
            mouse: mouseEvent === true,
            detected: false,
            abort: false,
            isFirst: true,
            isFinal: false,
            lastX: pos.left,
            lastY: pos.top
          };
        },

        move: function move (evt) {
          if (ctx.event === void 0 || ctx.event.abort === true) {
            return
          }

          if (ctx.event.detected === true) {
            ctx.event.isFirst !== true && handleEvent(evt, ctx.event.mouse);

            var changes = getChanges(evt, ctx, false);

            if (changes !== void 0) {
              if (ctx.handler(changes) === false) {
                ctx.mouseEnd(evt);
              }
              else {
                if (ctx.event.isFirst === true) {
                  handleEvent(evt, ctx.event.mouse);
                  document.documentElement.style.cursor = 'grabbing';
                  document.body.classList.add('no-pointer-events');
                  document.body.classList.add('non-selectable');
                  clearSelection();
                }
                ctx.event.lastX = changes.position.left;
                ctx.event.lastY = changes.position.top;
                ctx.event.isFirst = false;
              }
            }

            return
          }

          if (
            ctx.direction.all === true ||
            (ctx.event.mouse === true && modifiers.mouseAllDir === true)
          ) {
            ctx.event.detected = true;
            ctx.move(evt);
            return
          }

          var
            pos = position(evt),
            distX = pos.left - ctx.event.x,
            distY = pos.top - ctx.event.y,
            absX = Math.abs(distX),
            absY = Math.abs(distY);

          if (absX === absY) {
            return
          }

          if (
            (ctx.direction.horizontal === true && absX > absY) ||
            (ctx.direction.vertical === true && absX < absY) ||
            (ctx.direction.up === true && absX < absY && distY < 0) ||
            (ctx.direction.down === true && absX < absY && distY > 0) ||
            (ctx.direction.left === true && absX > absY && distX < 0) ||
            (ctx.direction.right === true && absX > absY && distX > 0)
          ) {
            ctx.event.detected = true;
            ctx.move(evt);
          }
          else {
            ctx.event.abort = true;
          }
        },

        end: function end (evt) {
          if (ctx.event === void 0) {
            return
          }

          Platform.is.firefox === true && preventDraggable(el, false);
          ctx.event.mouse !== true && removeObserver(ctx);

          document.documentElement.style.cursor = '';
          document.body.classList.remove('no-pointer-events');
          document.body.classList.remove('non-selectable');

          if (
            ctx.event.abort !== true &&
            ctx.event.detected === true &&
            ctx.event.isFirst !== true
          ) {
            handleEvent(evt, ctx.event.mouse);
            ctx.handler(getChanges(evt, ctx, true));
          }

          ctx.event = void 0;
        }
      };

      el.__qtouchpan = ctx;

      if (modifiers.mouse === true) {
        el.addEventListener(
          'mousedown',
          ctx.mouseStart,
          listenOpts[("notPassive" + (modifiers.mouseCapture === true ? 'Capture' : ''))]
        );
      }

      if (Platform.has.touch === true) {
        el.addEventListener('touchstart', ctx.start, touchEvtOpts);
        el.addEventListener('touchmove', ctx.move, touchEvtOpts);
        el.addEventListener('touchcancel', ctx.end, modifiers.capture);
        el.addEventListener('touchend', ctx.end, modifiers.capture);
      }
    },

    update: function update (el, binding) {
      var ctx = el.__qtouchpan;
      if (ctx !== void 0) {
        updateModifiers(ctx, binding);
      }
    },

    unbind: function unbind (el, ref) {
      var modifiers = ref.modifiers;

      var ctx = el.__qtouchpan_old || el.__qtouchpan;

      if (ctx !== void 0) {
        Platform.is.firefox === true && preventDraggable(el, false);
        removeObserver(ctx);

        document.documentElement.style.cursor = '';
        document.body.classList.remove('no-pointer-events');
        document.body.classList.remove('non-selectable');

        var
          touchPassiveStr = modifiers.mightPrevent !== true && modifiers.prevent !== true
            ? 'passive'
            : 'notPassive',
          touchEvtOpts = listenOpts[touchPassiveStr + (modifiers.capture === true ? 'Capture' : '')];

        if (modifiers.mouse === true) {
          el.removeEventListener(
            'mousedown',
            ctx.mouseStart,
            listenOpts[("notPassive" + (modifiers.mouseCapture === true ? 'Capture' : ''))]
          );
          document.removeEventListener('mousemove', ctx.move, mouseEvtOpts);
          document.removeEventListener('mouseup', ctx.mouseEnd, mouseEvtOpts);
        }

        if (Platform.has.touch === true) {
          el.removeEventListener('touchstart', ctx.start, touchEvtOpts);
          el.removeEventListener('touchmove', ctx.move, touchEvtOpts);
          el.removeEventListener('touchcancel', ctx.end, modifiers.capture);
          el.removeEventListener('touchend', ctx.end, modifiers.capture);
        }

        delete el[el.__qtouchpan_old ? '__qtouchpan_old' : '__qtouchpan'];
      }
    }
  };

  // PGDOWN, LEFT, DOWN, PGUP, RIGHT, UP
  var keyCodes = [34, 37, 40, 33, 39, 38];

  function getRatio (evt, dragging, rtl) {
    var
      pos = position(evt),
      val = between((pos.left - dragging.left) / dragging.width, 0, 1);

    return rtl ? 1.0 - val : val
  }

  function getModel (ratio, min, max, step, decimals) {
    var model = min + ratio * (max - min);

    if (step > 0) {
      var modulo = (model - min) % step;
      model += (Math.abs(modulo) >= step / 2 ? (modulo < 0 ? -1 : 1) * step : 0) - modulo;
    }

    if (decimals > 0) {
      model = parseFloat(model.toFixed(decimals));
    }

    return between(model, min, max)
  }

  var SliderMixin = {
    directives: {
      TouchPan: TouchPan
    },

    props: {
      min: {
        type: Number,
        default: 0
      },
      max: {
        type: Number,
        default: 100
      },
      step: {
        type: Number,
        default: 1,
        validator: function (v) { return v >= 0; }
      },

      color: String,
      labelColor: String,
      dark: Boolean,
      dense: Boolean,

      label: Boolean,
      labelAlways: Boolean,
      markers: Boolean,
      snap: Boolean,

      disable: Boolean,
      readonly: Boolean,
      tabindex: [String, Number]
    },

    data: function data () {
      return {
        active: false,
        preventFocus: false,
        focus: false
      }
    },

    computed: {
      classes: function classes () {
        var obj;

        return ( obj = {}, obj[("text-" + (this.color))] = this.color, obj[("q-slider--" + (this.active ? '' : 'in') + "active")] = true, obj['disabled'] = this.disable, obj['q-slider--editable'] = this.editable, obj['q-slider--focus'] = this.focus === 'both', obj['q-slider--label'] = this.label || this.labelAlways, obj['q-slider--label-always'] = this.labelAlways, obj['q-slider--dark'] = this.dark, obj['q-slider--dense'] = this.dense, obj )
      },

      editable: function editable () {
        return !this.disable && !this.readonly
      },

      decimals: function decimals () {
        return (String(this.step).trim('0').split('.')[1] || '').length
      },

      computedStep: function computedStep () {
        return this.step === 0 ? 1 : this.step
      },

      markerStyle: function markerStyle () {
        return {
          backgroundSize: 100 * this.computedStep / (this.max - this.min) + '% 2px'
        }
      },

      computedTabindex: function computedTabindex () {
        return this.editable === true ? this.tabindex || 0 : -1
      },

      horizProp: function horizProp () {
        return this.$q.lang.rtl === true ? 'right' : 'left'
      }
    },

    methods: {
      __pan: function __pan (event) {
        if (event.isFinal) {
          if (this.dragging) {
            this.__updatePosition(event.evt);
            // only if touch, because we also have mousedown/up:
            event.touch === true && this.__updateValue(true);
            this.dragging = false;
          }
          this.active = false;
        }
        else if (event.isFirst) {
          this.dragging = this.__getDragging(event.evt);
          this.__updatePosition(event.evt);
          this.active = true;
        }
        else {
          this.__updatePosition(event.evt);
          this.__updateValue();
        }
      },

      __blur: function __blur () {
        this.focus = false;
      },

      __activate: function __activate (evt) {
        this.__updatePosition(evt, this.__getDragging(evt));

        this.preventFocus = true;
        this.active = true;

        document.addEventListener('mouseup', this.__deactivate, true);
      },

      __deactivate: function __deactivate () {
        this.preventFocus = false;
        this.active = false;

        this.__updateValue(true);
        this.__blur();

        document.removeEventListener('mouseup', this.__deactivate, true);
      },

      __mobileClick: function __mobileClick (evt) {
        this.__updatePosition(evt, this.__getDragging(evt));
        this.__updateValue(true);
      },

      __keyup: function __keyup (evt) {
        if (keyCodes.includes(evt.keyCode)) {
          this.__updateValue(true);
        }
      }
    },

    beforeDestroy: function beforeDestroy () {
      document.removeEventListener('mouseup', this.__deactivate, true);
    }
  };

  var QSlider = Vue.extend({
    name: 'QSlider',

    mixins: [ SliderMixin ],

    props: {
      value: {
        type: Number,
        required: true
      },

      labelValue: [String, Number]
    },

    data: function data () {
      return {
        model: this.value,
        curRatio: 0
      }
    },

    watch: {
      value: function value (v) {
        this.model = between(v, this.min, this.max);
      },

      min: function min (v) {
        this.model = between(this.model, v, this.max);
      },

      max: function max (v) {
        this.model = between(this.model, this.min, v);
      }
    },

    computed: {
      ratio: function ratio () {
        return this.active === true ? this.curRatio : this.modelRatio
      },

      modelRatio: function modelRatio () {
        return (this.model - this.min) / (this.max - this.min)
      },

      trackStyle: function trackStyle () {
        return { width: (100 * this.ratio) + '%' }
      },

      thumbStyle: function thumbStyle () {
        var obj;

        return ( obj = {}, obj[this.horizProp] = (100 * this.ratio) + '%', obj )
      },

      thumbClass: function thumbClass () {
        return this.preventFocus === false && this.focus === true ? 'q-slider--focus' : null
      },

      pinClass: function pinClass () {
        return this.labelColor !== void 0 ? ("text-" + (this.labelColor)) : null
      },

      events: function events () {
        if (this.editable === true) {
          return this.$q.platform.is.mobile === true
            ? { click: this.__mobileClick }
            : {
              mousedown: this.__activate,
              focus: this.__focus,
              blur: this.__blur,
              keydown: this.__keydown,
              keyup: this.__keyup
            }
        }
      },

      computedLabel: function computedLabel () {
        return this.labelValue !== void 0
          ? this.labelValue
          : this.model
      }
    },

    methods: {
      __updateValue: function __updateValue (change) {
        if (this.model !== this.value) {
          this.$emit('input', this.model);
        }
        change === true && this.$emit('change', this.model);
      },

      __getDragging: function __getDragging () {
        return this.$el.getBoundingClientRect()
      },

      __updatePosition: function __updatePosition (event, dragging) {
        if ( dragging === void 0 ) dragging = this.dragging;

        var ratio = getRatio(
          event,
          dragging,
          this.$q.lang.rtl
        );

        this.model = getModel(ratio, this.min, this.max, this.step, this.decimals);
        this.curRatio = this.snap !== true || this.step === 0
          ? ratio
          : (this.model - this.min) / (this.max - this.min);
      },

      __focus: function __focus () {
        this.focus = true;
      },

      __keydown: function __keydown (evt) {
        if (!keyCodes.includes(evt.keyCode)) {
          return
        }

        stopAndPrevent(evt);

        var
          step = ([34, 33].includes(evt.keyCode) ? 10 : 1) * this.computedStep,
          offset = [34, 37, 40].includes(evt.keyCode) ? -step : step;

        this.model = between(
          parseFloat((this.model + offset).toFixed(this.decimals)),
          this.min,
          this.max
        );

        this.__updateValue();
      }
    },

    render: function render (h) {
      return h('div', {
        staticClass: 'q-slider',
        attrs: {
          role: 'slider',
          'aria-valuemin': this.min,
          'aria-valuemax': this.max,
          'aria-valuenow': this.value,
          'data-step': this.step,
          'aria-disabled': this.disable,
          tabindex: this.computedTabindex
        },
        class: this.classes,
        on: this.events,
        directives: this.editable ? [{
          name: 'touch-pan',
          value: this.__pan,
          modifiers: {
            horizontal: true,
            prevent: true,
            stop: true,
            mouse: true,
            mouseAllDir: true
          }
        }] : null
      }, [
        h('div', { staticClass: 'q-slider__track-container absolute overflow-hidden' }, [
          h('div', {
            staticClass: 'q-slider__track absolute-full',
            style: this.trackStyle
          }),

          this.markers === true
            ? h('div', {
              staticClass: 'q-slider__track-markers absolute-full fit',
              style: this.markerStyle
            })
            : null
        ]),

        h('div', {
          staticClass: 'q-slider__thumb-container absolute non-selectable',
          class: this.thumbClass,
          style: this.thumbStyle
        }, [
          h('svg', {
            staticClass: 'q-slider__thumb absolute',
            attrs: { width: '21', height: '21' }
          }, [
            h('circle', {
              attrs: {
                cx: '10.5',
                cy: '10.5',
                r: '7.875'
              }
            })
          ]),

          this.label === true || this.labelAlways === true ? h('div', {
            staticClass: 'q-slider__pin absolute flex flex-center',
            class: this.pinClass
          }, [
            h('div', { staticClass: 'q-slider__pin-value-marker' }, [
              h('div', { staticClass: 'q-slider__pin-value-marker-bg' }),
              h('div', { staticClass: 'q-slider__pin-value-marker-text' }, [
                this.computedLabel
              ])
            ])
          ]) : null,

          h('div', { staticClass: 'q-slider__focus-ring' })
        ])
      ])
    }
  });

  // using it to manage SSR rendering with best performance

  var CanRenderMixin = {
    data: function data () {
      return {
        canRender: !onSSR
      }
    },

    mounted: function mounted () {
      this.canRender === false && (this.canRender = true);
    }
  };

  var QResizeObserver = Vue.extend({
    name: 'QResizeObserver',

    mixins: [ CanRenderMixin ],

    props: {
      debounce: {
        type: [String, Number],
        default: 100
      }
    },

    data: function data () {
      return this.hasObserver
        ? {}
        : { url: this.$q.platform.is.ie ? null : 'about:blank' }
    },

    methods: {
      trigger: function trigger (immediately) {
        if (immediately === true || this.debounce === 0 || this.debounce === '0') {
          this.__onResize();
        }
        else if (!this.timer) {
          this.timer = setTimeout(this.__onResize, this.debounce);
        }
      },

      __onResize: function __onResize () {
        this.timer = null;

        if (!this.$el || !this.$el.parentNode) {
          return
        }

        var
          parent = this.$el.parentNode,
          size = {
            width: parent.offsetWidth,
            height: parent.offsetHeight
          };

        if (size.width === this.size.width && size.height === this.size.height) {
          return
        }

        this.size = size;
        this.$emit('resize', this.size);
      },

      __cleanup: function __cleanup () {
        if (this.curDocView !== void 0) {
          this.curDocView.removeEventListener('resize', this.trigger, listenOpts.passive);
          this.curDocView = void 0;
        }
      },

      __onObjLoad: function __onObjLoad () {
        this.__cleanup();

        if (this.$el.contentDocument) {
          this.curDocView = this.$el.contentDocument.defaultView;
          this.curDocView.addEventListener('resize', this.trigger, listenOpts.passive);
        }

        this.trigger(true);
      }
    },

    render: function render (h) {
      if (this.canRender === false || this.hasObserver === true) {
        return
      }

      return h('object', {
        style: this.style,
        attrs: {
          tabindex: -1, // fix for Firefox
          type: 'text/html',
          data: this.url,
          'aria-hidden': true
        },
        on: {
          load: this.__onObjLoad
        }
      })
    },

    beforeCreate: function beforeCreate () {
      this.size = { width: -1, height: -1 };
      if (isSSR === true) { return }

      this.hasObserver = typeof ResizeObserver !== 'undefined';

      if (this.hasObserver !== true) {
        this.style = (this.$q.platform.is.ie ? 'visibility:hidden;' : '') + "display:block;position:absolute;top:0;left:0;right:0;bottom:0;height:100%;width:100%;overflow:hidden;pointer-events:none;z-index:-1;";
      }
    },

    mounted: function mounted () {
      if (this.hasObserver === true) {
        this.observer = new ResizeObserver(this.trigger);
        this.observer.observe(this.$el.parentNode);
        return
      }

      if (this.$q.platform.is.ie) {
        this.url = 'about:blank';
        this.trigger(true);
      }
      else {
        this.__onObjLoad();
      }
    },

    beforeDestroy: function beforeDestroy () {
      clearTimeout(this.timer);

      if (this.hasObserver === true) {
        this.$el.parentNode && this.observer.unobserve(this.$el.parentNode);
        return
      }

      this.__cleanup();
    }
  });

  function getIndicatorClass (color, top, vertical) {
    var pos = vertical === true
      ? ['left', 'right']
      : ['top', 'bottom'];

    return ("absolute-" + (top === true ? pos[0] : pos[1]) + (color ? (" text-" + color) : ''))
  }

  function bufferPrioritySort (t1, t2) {
    if (t1.priorityMatched === t2.priorityMatched) {
      return t2.priorityHref - t1.priorityHref
    }
    return t2.priorityMatched - t1.priorityMatched
  }

  function bufferCleanSelected (t) {
    t.selected = false;
    return t
  }

  var
    bufferFilters = [
      function (t) { return t.selected === true && t.exact === true && t.redirected !== true },
      function (t) { return t.selected === true && t.exact === true },
      function (t) { return t.selected === true && t.redirected !== true },
      function (t) { return t.selected === true },
      function (t) { return t.exact === true && t.redirected !== true },
      function (t) { return t.redirected !== true },
      function (t) { return t.exact === true },
      function (t) { return true }
    ],
    bufferFiltersLen = bufferFilters.length;

  var QTabs = Vue.extend({
    name: 'QTabs',

    provide: function provide () {
      return {
        tabs: this.tabs,
        __activateTab: this.__activateTab,
        __activateRoute: this.__activateRoute
      }
    },

    props: {
      value: [Number, String],

      align: {
        type: String,
        default: 'center',
        validator: function (v) { return ['left', 'center', 'right', 'justify'].includes(v); }
      },
      breakpoint: {
        type: [String, Number],
        default: 600
      },

      vertical: Boolean,
      shrink: Boolean,
      stretch: Boolean,

      activeColor: String,
      activeBgColor: String,
      indicatorColor: String,
      leftIcon: String,
      rightIcon: String,

      switchIndicator: Boolean,

      narrowIndicator: Boolean,
      inlineLabel: Boolean,
      noCaps: Boolean,

      dense: Boolean
    },

    data: function data () {
      return {
        tabs: {
          current: this.value,
          activeColor: this.activeColor,
          activeBgColor: this.activeBgColor,
          indicatorClass: getIndicatorClass(
            this.indicatorColor,
            this.switchIndicator,
            this.vertical
          ),
          narrowIndicator: this.narrowIndicator,
          inlineLabel: this.inlineLabel,
          noCaps: this.noCaps
        },
        scrollable: false,
        leftArrow: true,
        rightArrow: false,
        justify: false
      }
    },

    watch: {
      value: function value (name) {
        this.__activateTab(name, true, true);
      },

      activeColor: function activeColor (v) {
        this.tabs.activeColor = v;
      },

      activeBgColor: function activeBgColor (v) {
        this.tabs.activeBgColor = v;
      },

      vertical: function vertical (v) {
        this.tabs.indicatorClass = getIndicatorClass(this.indicatorColor, this.switchIndicator, v);
      },

      indicatorColor: function indicatorColor (v) {
        this.tabs.indicatorClass = getIndicatorClass(v, this.switchIndicator, this.vertical);
      },

      switchIndicator: function switchIndicator (v) {
        this.tabs.indicatorClass = getIndicatorClass(this.indicatorColor, v, this.vertical);
      },

      narrowIndicator: function narrowIndicator (v) {
        this.tabs.narrowIndicator = v;
      },

      inlineLabel: function inlineLabel (v) {
        this.tabs.inlineLabel = v;
      },

      noCaps: function noCaps (v) {
        this.tabs.noCaps = v;
      }
    },

    computed: {
      alignClass: function alignClass () {
        var align = this.scrollable === true
          ? 'left'
          : (this.justify === true ? 'justify' : this.align);

        return ("q-tabs__content--align-" + align)
      },

      classes: function classes () {
        return "q-tabs--" + (this.scrollable === true ? '' : 'not-') + "scrollable" +
          (this.dense === true ? ' q-tabs--dense' : '') +
          (this.shrink === true ? ' col-shrink' : '') +
          (this.stretch === true ? ' self-stretch' : '') +
          (this.vertical === true ? ' q-tabs--vertical' : '')
      }
    },

    methods: {
      __activateTab: function __activateTab (name, setCurrent, skipEmit) {
        if (this.tabs.current !== name) {
          skipEmit !== true && this.$emit('input', name);
          if (setCurrent === true || this.$listeners.input === void 0) {
            this.__animate(this.tabs.current, name);
            this.tabs.current = name;
          }
        }
      },

      __activateRoute: function __activateRoute (params) {
        var this$1 = this;

        if (this.bufferRoute !== this.$route && this.buffer.length > 0) {
          clearTimeout(this.bufferTimer);
          this.bufferTimer = void 0;
          this.buffer.length = 0;
        }
        this.bufferRoute = this.$route;

        if (params !== void 0) {
          if (params.remove === true) {
            this.buffer = this.buffer.filter(function (t) { return t.name !== params.name; });
          }
          else {
            this.buffer.push(params);
          }
        }

        if (this.bufferTimer === void 0) {
          this.bufferTimer = setTimeout(function () {
            var tabs = [];

            for (var i = 0; i < bufferFiltersLen && tabs.length === 0; i++) {
              tabs = this$1.buffer.filter(bufferFilters[i]);
            }

            tabs.sort(bufferPrioritySort);
            this$1.__activateTab(tabs.length === 0 ? null : tabs[0].name, true);
            this$1.buffer = this$1.buffer.map(bufferCleanSelected);
            this$1.bufferTimer = void 0;
          }, 1);
        }
      },

      __updateContainer: function __updateContainer (ref) {
        var this$1 = this;
        var width = ref.width;
        var height = ref.height;

        var scroll = this.vertical === true
          ? this.$refs.content.scrollHeight > height + 1
          : this.$refs.content.scrollWidth > width + 1;

        if (this.scrollable !== scroll) {
          this.scrollable = scroll;
        }

        scroll === true && this.$nextTick(function () { return this$1.__updateArrows(); });

        var justify = (this.vertical === true ? height : width) < parseInt(this.breakpoint, 10);
        if (this.justify !== justify) {
          this.justify = justify;
        }
      },

      __animate: function __animate (oldName, newName) {
        var this$1 = this;

        var
          oldTab = oldName
            ? this.$children.find(function (tab) { return tab.name === oldName; })
            : null,
          newTab = newName
            ? this.$children.find(function (tab) { return tab.name === newName; })
            : null;

        if (oldTab && newTab) {
          var
            oldEl = oldTab.$el.getElementsByClassName('q-tab__indicator')[0],
            newEl = newTab.$el.getElementsByClassName('q-tab__indicator')[0];

          clearTimeout(this.animateTimer);

          oldEl.style.transition = 'none';
          oldEl.style.transform = 'none';
          newEl.style.transition = 'none';
          newEl.style.transform = 'none';

          var
            oldPos = oldEl.getBoundingClientRect(),
            newPos = newEl.getBoundingClientRect();

          newEl.style.transform = this.vertical === true
            ? ("translate3d(0, " + (oldPos.top - newPos.top) + "px, 0) scale3d(1, " + (newPos.height ? oldPos.height / newPos.height : 1) + ", 1)")
            : ("translate3d(" + (oldPos.left - newPos.left) + "px, 0, 0) scale3d(" + (newPos.width ? oldPos.width / newPos.width : 1) + ", 1, 1)");

          // allow scope updates to kick in
          this.$nextTick(function () {
            this$1.animateTimer = setTimeout(function () {
              newEl.style.transition = 'transform .25s cubic-bezier(.4, 0, .2, 1)';
              newEl.style.transform = 'none';
            }, 30);
          });
        }

        if (newTab && this.scrollable) {
          var ref = this.$refs.content.getBoundingClientRect();
          var left = ref.left;
          var width = ref.width;
          var top = ref.top;
          var height = ref.height;
          var newPos$1 = newTab.$el.getBoundingClientRect();

          var offset = this.vertical === true ? newPos$1.top - top : newPos$1.left - left;

          if (offset < 0) {
            this.$refs.content[this.vertical === true ? 'scrollTop' : 'scrollLeft'] += offset;
            this.__updateArrows();
            return
          }

          offset += this.vertical === true ? newPos$1.height - height : newPos$1.width - width;
          if (offset > 0) {
            this.$refs.content[this.vertical === true ? 'scrollTop' : 'scrollLeft'] += offset;
            this.__updateArrows();
          }
        }
      },

      __updateArrows: function __updateArrows () {
        var
          content = this.$refs.content,
          rect = content.getBoundingClientRect(),
          left = this.vertical === true ? content.scrollTop : content.scrollLeft;

        this.leftArrow = left > 0;
        this.rightArrow = this.vertical === true
          ? left + rect.height + 5 < content.scrollHeight
          : left + rect.width + 5 < content.scrollWidth;
      },

      __animScrollTo: function __animScrollTo (value) {
        var this$1 = this;

        this.__stopAnimScroll();
        this.__scrollTowards(value);

        this.scrollTimer = setInterval(function () {
          if (this$1.__scrollTowards(value)) {
            this$1.__stopAnimScroll();
          }
        }, 5);
      },

      __scrollToStart: function __scrollToStart () {
        this.__animScrollTo(0);
      },

      __scrollToEnd: function __scrollToEnd () {
        this.__animScrollTo(9999);
      },

      __stopAnimScroll: function __stopAnimScroll () {
        clearInterval(this.scrollTimer);
      },

      __scrollTowards: function __scrollTowards (value) {
        var
          content = this.$refs.content,
          left = this.vertical === true ? content.scrollTop : content.scrollLeft,
          direction = value < left ? -1 : 1,
          done = false;

        left += direction * 5;
        if (left < 0) {
          done = true;
          left = 0;
        }
        else if (
          (direction === -1 && left <= value) ||
          (direction === 1 && left >= value)
        ) {
          done = true;
          left = value;
        }

        content[this.vertical === true ? 'scrollTop' : 'scrollLeft'] = left;
        this.__updateArrows();
        return done
      }
    },

    created: function created () {
      this.buffer = [];
    },

    beforeDestroy: function beforeDestroy () {
      clearTimeout(this.bufferTimer);
      clearTimeout(this.animateTimer);
    },

    render: function render (h) {
      return h('div', {
        staticClass: 'q-tabs row no-wrap items-center',
        class: this.classes,
        on: Object.assign({}, {input: stop},
          this.$listeners),
        attrs: { role: 'tablist' }
      }, [
        h(QResizeObserver, {
          on: { resize: this.__updateContainer }
        }),

        h(QIcon, {
          staticClass: 'q-tabs__arrow q-tabs__arrow--left q-tab__icon',
          class: this.leftArrow === true ? '' : 'q-tabs__arrow--faded',
          props: { name: this.leftIcon || (this.vertical === true ? this.$q.iconSet.tabs.up : this.$q.iconSet.tabs.left) },
          nativeOn: {
            mousedown: this.__scrollToStart,
            touchstart: this.__scrollToStart,
            mouseup: this.__stopAnimScroll,
            mouseleave: this.__stopAnimScroll,
            touchend: this.__stopAnimScroll
          }
        }),

        h('div', {
          ref: 'content',
          staticClass: 'q-tabs__content row no-wrap items-center self-stretch',
          class: this.alignClass
        }, slot(this, 'default')),

        h(QIcon, {
          staticClass: 'q-tabs__arrow q-tabs__arrow--right q-tab__icon',
          class: this.rightArrow === true ? '' : 'q-tabs__arrow--faded',
          props: { name: this.rightIcon || (this.vertical === true ? this.$q.iconSet.tabs.down : this.$q.iconSet.tabs.right) },
          nativeOn: {
            mousedown: this.__scrollToEnd,
            touchstart: this.__scrollToEnd,
            mouseup: this.__stopAnimScroll,
            mouseleave: this.__stopAnimScroll,
            touchend: this.__stopAnimScroll
          }
        })
      ])
    }
  });

  var QTab = Vue.extend({
    name: 'QTab',

    mixins: [ RippleMixin ],

    inject: {
      tabs: {
        default: function default$1 () {
          console.error('QTab/QRouteTab components need to be child of QTabsBar');
        }
      },
      __activateTab: {}
    },

    props: {
      icon: String,
      label: [Number, String],

      alert: [Boolean, String],

      name: {
        type: [Number, String],
        default: function () { return uid(); }
      },

      noCaps: Boolean,

      tabindex: [String, Number],
      disable: Boolean
    },

    computed: {
      isActive: function isActive () {
        return this.tabs.current === this.name
      },

      classes: function classes () {
        var obj;

        return ( obj = {}, obj[("q-tab--" + (this.isActive ? '' : 'in') + "active")] = true, obj[("text-" + (this.tabs.activeColor))] = this.isActive && this.tabs.activeColor, obj[("bg-" + (this.tabs.activeBgColor))] = this.isActive && this.tabs.activeBgColor, obj['q-tab--full'] = this.icon && this.label && !this.tabs.inlineLabel, obj['q-tab--no-caps'] = this.noCaps === true || this.tabs.noCaps === true, obj['q-focusable q-hoverable cursor-pointer'] = !this.disable, obj.disabled = this.disable, obj )
      },

      computedTabIndex: function computedTabIndex () {
        return this.disable === true || this.isActive === true ? -1 : this.tabindex || 0
      }
    },

    methods: {
      activate: function activate (e, keyboard) {
        keyboard !== true && this.$refs.blurTarget !== void 0 && this.$refs.blurTarget.focus();

        if (this.disable !== true) {
          this.$listeners.click !== void 0 && this.$emit('click', e);
          this.__activateTab(this.name);
        }
      },

      __onKeyup: function __onKeyup (e) {
        e.keyCode === 13 && this.activate(e, true);
      },

      __getContent: function __getContent (h) {
        var
          narrow = this.tabs.narrowIndicator,
          content = [],
          indicator = h('div', {
            staticClass: 'q-tab__indicator',
            class: this.tabs.indicatorClass
          });

        this.icon !== void 0 && content.push(h(QIcon, {
          staticClass: 'q-tab__icon',
          props: { name: this.icon }
        }));

        this.label !== void 0 && content.push(h('div', {
          staticClass: 'q-tab__label'
        }, [ this.label ]));

        this.alert !== false && content.push(h('div', {
          staticClass: 'q-tab__alert',
          class: this.alert !== true ? ("text-" + (this.alert)) : null
        }));

        narrow && content.push(indicator);

        var node = [
          h('div', { staticClass: 'q-focus-helper', attrs: { tabindex: -1 }, ref: 'blurTarget' }),

          h('div', {
            staticClass: 'q-tab__content self-stretch flex-center relative-position no-pointer-events q-anchor--skip non-selectable',
            class: this.tabs.inlineLabel === true ? 'row no-wrap q-tab__content--inline' : 'column'
          }, content.concat(slot(this, 'default')))
        ];

        !narrow && node.push(indicator);

        return node
      },

      __render: function __render (h, tag, props) {
        var data = {
          staticClass: 'q-tab relative-position self-stretch flex flex-center text-center',
          class: this.classes,
          attrs: {
            tabindex: this.computedTabIndex,
            role: 'tab',
            'aria-selected': this.isActive
          },
          directives: this.ripple !== false && this.disable === true ? null : [
            { name: 'ripple', value: this.ripple }
          ]
        };
        data[tag === 'div' ? 'on' : 'nativeOn'] = Object.assign({}, {input: stop},
            this.$listeners,
            {click: this.activate,
            keyup: this.__onKeyup});

        if (props !== void 0) {
          data.props = props;
        }

        return h(tag, data, this.__getContent(h))
      }
    },

    render: function render (h) {
      return this.__render(h, 'div')
    }
  });

  var QTabPanels = Vue.extend({
    name: 'QTabPanels',

    mixins: [ PanelParentMixin ],

    methods: {
      __render: function __render (h) {
        return h('div', {
          staticClass: 'q-tab-panels q-panel-parent',
          directives: this.panelDirectives,
          on: this.$listeners
        }, this.__getPanelContent(h))
      }
    }
  });

  var QTabPanel = Vue.extend({
    name: 'QTabPanel',

    mixins: [ PanelChildMixin ],

    render: function render (h) {
      return h('div', {
        staticClass: 'q-tab-panel',
        on: this.$listeners
      }, slot(this, 'default'))
    }
  });

  var palette = [
    'rgb(255,204,204)', 'rgb(255,230,204)', 'rgb(255,255,204)', 'rgb(204,255,204)', 'rgb(204,255,230)', 'rgb(204,255,255)', 'rgb(204,230,255)', 'rgb(204,204,255)', 'rgb(230,204,255)', 'rgb(255,204,255)',
    'rgb(255,153,153)', 'rgb(255,204,153)', 'rgb(255,255,153)', 'rgb(153,255,153)', 'rgb(153,255,204)', 'rgb(153,255,255)', 'rgb(153,204,255)', 'rgb(153,153,255)', 'rgb(204,153,255)', 'rgb(255,153,255)',
    'rgb(255,102,102)', 'rgb(255,179,102)', 'rgb(255,255,102)', 'rgb(102,255,102)', 'rgb(102,255,179)', 'rgb(102,255,255)', 'rgb(102,179,255)', 'rgb(102,102,255)', 'rgb(179,102,255)', 'rgb(255,102,255)',
    'rgb(255,51,51)', 'rgb(255,153,51)', 'rgb(255,255,51)', 'rgb(51,255,51)', 'rgb(51,255,153)', 'rgb(51,255,255)', 'rgb(51,153,255)', 'rgb(51,51,255)', 'rgb(153,51,255)', 'rgb(255,51,255)',
    'rgb(255,0,0)', 'rgb(255,128,0)', 'rgb(255,255,0)', 'rgb(0,255,0)', 'rgb(0,255,128)', 'rgb(0,255,255)', 'rgb(0,128,255)', 'rgb(0,0,255)', 'rgb(128,0,255)', 'rgb(255,0,255)',
    'rgb(245,0,0)', 'rgb(245,123,0)', 'rgb(245,245,0)', 'rgb(0,245,0)', 'rgb(0,245,123)', 'rgb(0,245,245)', 'rgb(0,123,245)', 'rgb(0,0,245)', 'rgb(123,0,245)', 'rgb(245,0,245)',
    'rgb(214,0,0)', 'rgb(214,108,0)', 'rgb(214,214,0)', 'rgb(0,214,0)', 'rgb(0,214,108)', 'rgb(0,214,214)', 'rgb(0,108,214)', 'rgb(0,0,214)', 'rgb(108,0,214)', 'rgb(214,0,214)',
    'rgb(163,0,0)', 'rgb(163,82,0)', 'rgb(163,163,0)', 'rgb(0,163,0)', 'rgb(0,163,82)', 'rgb(0,163,163)', 'rgb(0,82,163)', 'rgb(0,0,163)', 'rgb(82,0,163)', 'rgb(163,0,163)',
    'rgb(92,0,0)', 'rgb(92,46,0)', 'rgb(92,92,0)', 'rgb(0,92,0)', 'rgb(0,92,46)', 'rgb(0,92,92)', 'rgb(0,46,92)', 'rgb(0,0,92)', 'rgb(46,0,92)', 'rgb(92,0,92)',
    'rgb(255,255,255)', 'rgb(205,205,205)', 'rgb(178,178,178)', 'rgb(153,153,153)', 'rgb(127,127,127)', 'rgb(102,102,102)', 'rgb(76,76,76)', 'rgb(51,51,51)', 'rgb(25,25,25)', 'rgb(0,0,0)'
  ];

  var QColor = Vue.extend({
    name: 'QColor',

    directives: {
      TouchPan: TouchPan
    },

    props: {
      value: String,

      defaultValue: String,
      defaultView: {
        type: String,
        default: 'spectrum',
        validator: function (v) { return ['spectrum', 'tune', 'palette'].includes(v); }
      },

      formatModel: {
        type: String,
        default: 'auto',
        validator: function (v) { return ['auto', 'hex', 'rgb', 'hexa', 'rgba'].includes(v); }
      },

      palette: Array,

      noHeader: Boolean,
      noFooter: Boolean,

      disable: Boolean,
      readonly: Boolean,
      dark: Boolean
    },

    data: function data () {
      return {
        topView: this.formatModel === 'auto'
          ? (
            (this.value === void 0 || this.value === null || this.value === '' || this.value.startsWith('#'))
              ? 'hex'
              : 'rgb'
          )
          : (this.formatModel.startsWith('hex') ? 'hex' : 'rgb'),
        view: this.defaultView,
        model: this.__parseModel(this.value || this.defaultValue)
      }
    },

    watch: {
      value: function value (v) {
        var model = this.__parseModel(v || this.defaultValue);
        if (model.hex !== this.model.hex) {
          this.model = model;
        }
      },

      defaultValue: function defaultValue (v) {
        if (!this.value && v) {
          var model = this.__parseModel(v);
          if (model.hex !== this.model.hex) {
            this.model = model;
          }
        }
      }
    },

    computed: {
      editable: function editable () {
        return this.disable !== true && this.readonly !== true
      },

      forceHex: function forceHex () {
        return this.formatModel === 'auto'
          ? null
          : this.formatModel.indexOf('hex') > -1
      },

      forceAlpha: function forceAlpha () {
        return this.formatModel === 'auto'
          ? null
          : this.formatModel.indexOf('a') > -1
      },

      isHex: function isHex () {
        return this.value === void 0 || this.value === null || this.value === '' || this.value.startsWith('#')
      },

      isOutputHex: function isOutputHex () {
        return this.forceHex !== null
          ? this.forceHex
          : this.isHex
      },

      hasAlpha: function hasAlpha () {
        if (this.forceAlpha !== null) {
          return this.forceAlpha
        }
        return this.model.a !== void 0
      },

      currentBgColor: function currentBgColor () {
        return {
          backgroundColor: this.model.rgb || '#000'
        }
      },

      headerClass: function headerClass () {
        var light = this.model.a !== void 0 && this.model.a < 65
          ? true
          : luminosity(this.model) > 0.4;

        return ("q-color-picker__header-content--" + (light ? 'light' : 'dark'))
      },

      spectrumStyle: function spectrumStyle () {
        return {
          background: ("hsl(" + (this.model.h) + ",100%,50%)")
        }
      },

      spectrumPointerStyle: function spectrumPointerStyle () {
        var obj;

        return ( obj = {
          top: ((100 - this.model.v) + "%")
        }, obj[this.$q.lang.rtl ? 'right' : 'left'] = ((this.model.s) + "%"), obj )
      },

      inputsArray: function inputsArray () {
        var inp = ['r', 'g', 'b'];
        if (this.hasAlpha === true) {
          inp.push('a');
        }
        return inp
      },

      computedPalette: function computedPalette () {
        return this.palette !== void 0 && this.palette.length > 0
          ? this.palette
          : palette
      }
    },

    created: function created () {
      this.__spectrumChange = throttle(this.__spectrumChange, 20);
    },

    render: function render (h) {
      var child = [ this.__getContent(h) ];

      this.noHeader !== true && child.unshift(
        this.__getHeader(h)
      );

      this.noFooter !== true && child.push(
        this.__getFooter(h)
      );

      return h('div', {
        staticClass: 'q-color-picker',
        class: {
          disabled: this.disable,
          'q-color-picker--dark': this.dark
        }
      }, child)
    },

    methods: {
      __getHeader: function __getHeader (h) {
        var this$1 = this;

        return h('div', {
          staticClass: 'q-color-picker__header relative-position overflow-hidden'
        }, [
          h('div', { staticClass: 'q-color-picker__header-bg absolute-full' }),

          h('div', {
            staticClass: 'q-color-picker__header-content absolute-full',
            class: this.headerClass,
            style: this.currentBgColor
          }, [
            h(QTabs, {
              props: {
                value: this.topView,
                dense: true,
                align: 'justify'
              },
              on: {
                input: function (val) { this$1.topView = val; }
              }
            }, [
              h(QTab, {
                props: {
                  label: 'HEX' + (this.hasAlpha === true ? 'A' : ''),
                  name: 'hex',
                  ripple: false
                }
              }),

              h(QTab, {
                props: {
                  label: 'RGB' + (this.hasAlpha === true ? 'A' : ''),
                  name: 'rgb',
                  ripple: false
                }
              })
            ]),

            h('div', {
              staticClass: 'q-color-picker__header-banner row flex-center no-wrap'
            }, [
              h('input', {
                staticClass: 'fit',
                domProps: { value: this.model[this.topView] },
                attrs: !this.editable ? {
                  readonly: true
                } : null,
                on: {
                  input: function (evt) {
                    this$1.__updateErrorIcon(this$1.__onEditorChange(evt) === true);
                  },
                  blur: function (evt) {
                    this$1.__onEditorChange(evt, true) === true && this$1.$forceUpdate();
                    this$1.__updateErrorIcon(false);
                  }
                }
              }),

              h(QIcon, {
                ref: 'errorIcon',
                staticClass: 'q-color-picker__error-icon absolute no-pointer-events',
                props: { name: this.$q.iconSet.type.negative }
              })
            ])
          ])
        ])
      },

      __getContent: function __getContent (h) {
        return h(QTabPanels, {
          props: {
            value: this.view,
            animated: true
          }
        }, [
          h(QTabPanel, {
            staticClass: 'q-color-picker__spectrum-tab',
            props: { name: 'spectrum' }
          }, this.__getSpectrumTab(h)),

          h(QTabPanel, {
            staticClass: 'q-pa-md q-color-picker__tune-tab',
            props: { name: 'tune' }
          }, this.__getTuneTab(h)),

          h(QTabPanel, {
            staticClass: 'q-pa-sm q-color-picker__palette-tab',
            props: { name: 'palette' }
          }, this.__getPaletteTab(h))
        ])
      },

      __getFooter: function __getFooter (h) {
        var this$1 = this;

        return h(QTabs, {
          staticClass: 'q-color-picker__footer',
          props: {
            value: this.view,
            dense: true,
            align: 'justify'
          },
          on: {
            input: function (val) { this$1.view = val; }
          }
        }, [
          h(QTab, {
            props: {
              icon: this.$q.iconSet.colorPicker.spectrum,
              name: 'spectrum',
              ripple: false
            }
          }),

          h(QTab, {
            props: {
              icon: this.$q.iconSet.colorPicker.tune,
              name: 'tune',
              ripple: false
            }
          }),

          h(QTab, {
            props: {
              icon: this.$q.iconSet.colorPicker.palette,
              name: 'palette',
              ripple: false
            }
          })
        ])
      },

      __getSpectrumTab: function __getSpectrumTab (h) {
        var this$1 = this;

        return [
          h('div', {
            ref: 'spectrum',
            staticClass: 'q-color-picker__spectrum non-selectable relative-position cursor-pointer',
            style: this.spectrumStyle,
            class: { readonly: !this.editable },
            on: this.editable
              ? { click: this.__spectrumClick }
              : null,
            directives: this.editable
              ? [{
                name: 'touch-pan',
                modifiers: {
                  prevent: true,
                  stop: true,
                  mouse: true
                },
                value: this.__spectrumPan
              }]
              : null
          }, [
            h('div', { style: { paddingBottom: '100%' } }),
            h('div', { staticClass: 'q-color-picker__spectrum-white absolute-full' }),
            h('div', { staticClass: 'q-color-picker__spectrum-black absolute-full' }),
            h('div', {
              staticClass: 'absolute',
              style: this.spectrumPointerStyle
            }, [
              this.model.hex !== void 0 ? h('div', { staticClass: 'q-color-picker__spectrum-circle' }) : null
            ])
          ]),

          h('div', {
            staticClass: 'q-color-picker__sliders'
          }, [
            h('div', { staticClass: 'q-color-picker__hue q-mx-sm non-selectable' }, [
              h(QSlider, {
                props: {
                  value: this.model.h,
                  min: 0,
                  max: 360,
                  fillHandleAlways: true,
                  readonly: !this.editable
                },
                on: {
                  input: this.__onHueChange,
                  change: function (val) { return this$1.__onHueChange(val, true); }
                }
              })
            ]),
            this.hasAlpha === true
              ? h('div', { staticClass: 'q-mx-sm q-color-picker__alpha non-selectable' }, [
                h(QSlider, {
                  props: {
                    value: this.model.a,
                    min: 0,
                    max: 100,
                    fillHandleAlways: true,
                    readonly: !this.editable
                  },
                  on: {
                    input: function (value) { return this$1.__onNumericChange({ target: { value: value } }, 'a', 100); },
                    change: function (value) { return this$1.__onNumericChange({ target: { value: value } }, 'a', 100, true); }
                  }
                })
              ])
              : null
          ])
        ]
      },

      __getTuneTab: function __getTuneTab (h) {
        var this$1 = this;

        return [
          h('div', { staticClass: 'row items-center no-wrap' }, [
            h('div', ['R']),
            h(QSlider, {
              props: {
                value: this.model.r,
                min: 0,
                max: 255,
                color: 'red',
                dark: this.dark,
                readonly: !this.editable
              },
              on: {
                input: function (value) { return this$1.__onNumericChange({ target: { value: value } }, 'r', 255); },
                change: function (value) { return this$1.__onNumericChange({ target: { value: value } }, 'r', 255, true); }
              }
            }),
            h('input', {
              domProps: {
                value: this.model.r
              },
              attrs: {
                maxlength: 3,
                readonly: !this.editable
              },
              on: {
                input: function (evt) { return this$1.__onNumericChange(evt, 'r', 255); },
                blur: function (evt) { return this$1.__onNumericChange(evt, 'r', 255, true); }
              }
            })
          ]),

          h('div', { staticClass: 'row items-center no-wrap' }, [
            h('div', ['G']),
            h(QSlider, {
              props: {
                value: this.model.g,
                min: 0,
                max: 255,
                color: 'green',
                dark: this.dark,
                readonly: !this.editable
              },
              on: {
                input: function (value) { return this$1.__onNumericChange({ target: { value: value } }, 'g', 255); },
                change: function (value) { return this$1.__onNumericChange({ target: { value: value } }, 'g', 255, true); }
              }
            }),
            h('input', {
              domProps: {
                value: this.model.g
              },
              attrs: {
                maxlength: 3,
                readonly: !this.editable
              },
              on: {
                input: function (evt) { return this$1.__onNumericChange(evt, 'g', 255); },
                blur: function (evt) { return this$1.__onNumericChange(evt, 'g', 255, true); }
              }
            })
          ]),

          h('div', { staticClass: 'row items-center no-wrap' }, [
            h('div', ['B']),
            h(QSlider, {
              props: {
                value: this.model.b,
                min: 0,
                max: 255,
                color: 'blue',
                readonly: !this.editable,
                dark: this.dark
              },
              on: {
                input: function (value) { return this$1.__onNumericChange({ target: { value: value } }, 'b', 255); },
                change: function (value) { return this$1.__onNumericChange({ target: { value: value } }, 'b', 255, true); }
              }
            }),
            h('input', {
              domProps: {
                value: this.model.b
              },
              attrs: {
                maxlength: 3,
                readonly: !this.editable
              },
              on: {
                input: function (evt) { return this$1.__onNumericChange(evt, 'b', 255); },
                blur: function (evt) { return this$1.__onNumericChange(evt, 'b', 255, true); }
              }
            })
          ]),

          this.hasAlpha === true ? h('div', { staticClass: 'row items-center no-wrap' }, [
            h('div', ['A']),
            h(QSlider, {
              props: {
                value: this.model.a,
                color: 'grey',
                readonly: !this.editable,
                dark: this.dark
              },
              on: {
                input: function (value) { return this$1.__onNumericChange({ target: { value: value } }, 'a', 100); },
                change: function (value) { return this$1.__onNumericChange({ target: { value: value } }, 'a', 100, true); }
              }
            }),
            h('input', {
              domProps: {
                value: this.model.a
              },
              attrs: {
                maxlength: 3,
                readonly: !this.editable
              },
              on: {
                input: function (evt) { return this$1.__onNumericChange(evt, 'a', 100); },
                blur: function (evt) { return this$1.__onNumericChange(evt, 'a', 100, true); }
              }
            })
          ]) : null
        ]
      },

      __getPaletteTab: function __getPaletteTab (h) {
        var this$1 = this;

        return [
          h('div', {
            staticClass: 'row items-center q-color-picker__palette-rows',
            class: this.editable === true
              ? 'q-color-picker__palette-rows--editable'
              : null
          }, this.computedPalette.map(function (color) { return h('div', {
            staticClass: 'q-color-picker__cube col-auto',
            style: { backgroundColor: color },
            on: this$1.editable ? {
              click: function () {
                this$1.__onPalettePick(color);
              }
            } : null
          }); }))
        ]
      },

      __onSpectrumChange: function __onSpectrumChange (left, top, change) {
        var panel = this.$refs.spectrum;
        if (panel === void 0) { return }

        var
          width = panel.clientWidth,
          height = panel.clientHeight,
          rect = panel.getBoundingClientRect();

        var x = Math.min(width, Math.max(0, left - rect.left));

        if (this.$q.lang.rtl) {
          x = width - x;
        }

        var
          y = Math.min(height, Math.max(0, top - rect.top)),
          s = Math.round(100 * x / width),
          v = Math.round(100 * Math.max(0, Math.min(1, -(y / height) + 1))),
          rgb = hsvToRgb({
            h: this.model.h,
            s: s,
            v: v,
            a: this.hasAlpha === true ? this.model.a : void 0
          });

        this.model.s = s;
        this.model.v = v;
        this.__update(rgb, change);
      },

      __onHueChange: function __onHueChange (h, change) {
        h = Math.round(h);
        var rgb = hsvToRgb({
          h: h,
          s: this.model.s,
          v: this.model.v,
          a: this.hasAlpha === true ? this.model.a : void 0
        });

        this.model.h = h;
        this.__update(rgb, change);
      },

      __onNumericChange: function __onNumericChange (evt, formatModel, max, change) {
        if (!/^[0-9]+$/.test(evt.target.value)) {
          change && this.$forceUpdate();
          return
        }

        var val = Math.floor(Number(evt.target.value));

        if (val < 0 || val > max) {
          change && this.$forceUpdate();
          return
        }

        var rgb = {
          r: formatModel === 'r' ? val : this.model.r,
          g: formatModel === 'g' ? val : this.model.g,
          b: formatModel === 'b' ? val : this.model.b,
          a: this.hasAlpha === true
            ? (formatModel === 'a' ? val : this.model.a)
            : void 0
        };

        if (formatModel !== 'a') {
          var hsv = rgbToHsv(rgb);
          this.model.h = hsv.h;
          this.model.s = hsv.s;
          this.model.v = hsv.v;
        }

        this.__update(rgb, change);

        if (change !== true && evt.target.selectionEnd !== void 0) {
          var index = evt.target.selectionEnd;
          this.$nextTick(function () {
            evt.target.setSelectionRange(index, index);
          });
        }
      },

      __onEditorChange: function __onEditorChange (evt, change) {
        var rgb;
        var inp = evt.target.value;

        if (this.topView === 'hex') {
          if (
            inp.length !== (this.hasAlpha === true ? 9 : 7) ||
            !/^#[0-9A-Fa-f]+$/.test(inp)
          ) {
            return true
          }

          rgb = hexToRgb(inp);
        }
        else {
          var model;

          if (!inp.endsWith(')')) {
            return true
          }
          else if (this.hasAlpha !== true && inp.startsWith('rgb(')) {
            model = inp.substring(4, inp.length - 1).split(',').map(function (n) { return parseInt(n, 10); });

            if (
              model.length !== 3 ||
              !/^rgb\([0-9]{1,3},[0-9]{1,3},[0-9]{1,3}\)$/.test(inp)
            ) {
              return true
            }
          }
          else if (this.hasAlpha === true && inp.startsWith('rgba(')) {
            model = inp.substring(5, inp.length - 1).split(',');

            if (
              model.length !== 4 ||
              !/^rgba\([0-9]{1,3},[0-9]{1,3},[0-9]{1,3},(0|0\.[0-9]+[1-9]|0\.[1-9]+|1)\)$/.test(inp)
            ) {
              return true
            }

            for (var i = 0; i < 3; i++) {
              var v = parseInt(model[i], 10);
              if (v < 0 || v > 255) {
                return true
              }
              model[i] = v;
            }

            var v$1 = parseFloat(model[3]);
            if (v$1 < 0 || v$1 > 1) {
              return true
            }
            model[3] = v$1;
          }
          else {
            return true
          }

          if (
            model[0] < 0 || model[0] > 255 ||
            model[1] < 0 || model[1] > 255 ||
            model[2] < 0 || model[2] > 255 ||
            (this.hasAlpha === true && (model[3] < 0 || model[3] > 1))
          ) {
            return true
          }

          rgb = {
            r: model[0],
            g: model[1],
            b: model[2],
            a: this.hasAlpha === true
              ? model[3] * 100
              : void 0
          };
        }

        var hsv = rgbToHsv(rgb);
        this.model.h = hsv.h;
        this.model.s = hsv.s;
        this.model.v = hsv.v;

        this.__update(rgb, change);

        if (change !== true) {
          var index = evt.target.selectionEnd;
          this.$nextTick(function () {
            evt.target.setSelectionRange(index, index);
          });
        }
      },

      __onPalettePick: function __onPalettePick (color) {
        var def = this.__parseModel(color);
        var rgb = { r: def.r, g: def.g, b: def.b, a: def.a };

        if (rgb.a === void 0) {
          rgb.a = this.model.a;
        }

        this.model.h = def.h;
        this.model.s = def.s;
        this.model.v = def.v;

        this.__update(rgb, true);
      },

      __update: function __update (rgb, change) {
        // update internally
        this.model.hex = rgbToHex(rgb);
        this.model.rgb = rgbToString(rgb);
        this.model.r = rgb.r;
        this.model.g = rgb.g;
        this.model.b = rgb.b;
        this.model.a = rgb.a;

        var value = this.model[this.isOutputHex === true ? 'hex' : 'rgb'];

        // emit new value
        this.$emit('input', value);
        change === true && this.$emit('change', value);
      },

      __updateErrorIcon: function __updateErrorIcon (val) {
        // we MUST avoid vue triggering a render,
        // so manually changing this
        this.$refs.errorIcon.$el.style.opacity = val ? 1 : 0;
      },

      __parseModel: function __parseModel (v) {
        var forceAlpha = this.forceAlpha !== void 0
          ? this.forceAlpha
          : (
            this.formatModel === 'auto'
              ? null
              : this.formatModel.indexOf('a') > -1
          );

        if (v === null || v === void 0 || v === '' || testPattern.anyColor(v) !== true) {
          return {
            h: 0,
            s: 0,
            v: 0,
            r: 0,
            g: 0,
            b: 0,
            a: forceAlpha === true ? 100 : void 0,
            hex: void 0,
            rgb: void 0
          }
        }

        var model = stringToRgb(v);

        if (forceAlpha === true && model.a === void 0) {
          model.a = 100;
        }

        model.hex = rgbToHex(model);
        model.rgb = rgbToString(model);

        return Object.assign(model, rgbToHsv(model))
      },

      __spectrumPan: function __spectrumPan (evt) {
        if (evt.isFinal) {
          this.__onSpectrumChange(
            evt.position.left,
            evt.position.top,
            true
          );
        }
        else {
          this.__spectrumChange(evt);
        }
      },

      // throttled in created()
      __spectrumChange: function __spectrumChange (evt) {
        this.__onSpectrumChange(
          evt.position.left,
          evt.position.top
        );
      },

      __spectrumClick: function __spectrumClick (evt) {
        this.__onSpectrumChange(
          evt.pageX - window.pageXOffset,
          evt.pageY - window.pageYOffset,
          true
        );
      }
    }
  });

  // taken from https://github.com/jalaali/jalaali-js

  /*
    Jalaali years starting the 33-year rule.
  */
  var breaks = [ -61, 9, 38, 199, 426, 686, 756, 818, 1111, 1181, 1210,
    1635, 2060, 2097, 2192, 2262, 2324, 2394, 2456, 3178
  ];

  /*
    Converts a Gregorian date to Jalaali.
  */
  function toJalaali (gy, gm, gd) {
    if (Object.prototype.toString.call(gy) === '[object Date]') {
      gd = gy.getDate();
      gm = gy.getMonth() + 1;
      gy = gy.getFullYear();
    }
    return d2j(g2d(gy, gm, gd))
  }

  /*
    Converts a Jalaali date to Gregorian.
  */
  function toGregorian (jy, jm, jd) {
    return d2g(j2d(jy, jm, jd))
  }

  /*
    Is this a leap year or not?
  */
  function isLeapJalaaliYear (jy) {
    return jalCalLeap(jy) === 0
  }

  /*
    Number of days in a given month in a Jalaali year.
  */
  function jalaaliMonthLength (jy, jm) {
    if (jm <= 6) { return 31 }
    if (jm <= 11) { return 30 }
    if (isLeapJalaaliYear(jy)) { return 30 }
    return 29
  }

  /*
      This function determines if the Jalaali (Persian) year is
      leap (366-day long) or is the common year (365 days)

      @param jy Jalaali calendar year (-61 to 3177)
      @returns number of years since the last leap year (0 to 4)
   */
  function jalCalLeap (jy) {
    var bl = breaks.length,
      jp = breaks[0],
      jm,
      jump,
      leap,
      n,
      i;

    if (jy < jp || jy >= breaks[bl - 1]) { throw new Error('Invalid Jalaali year ' + jy) }

    for (i = 1; i < bl; i += 1) {
      jm = breaks[i];
      jump = jm - jp;
      if (jy < jm) { break }
      jp = jm;
    }
    n = jy - jp;

    if (jump - n < 6) { n = n - jump + div(jump + 4, 33) * 33; }
    leap = mod(mod(n + 1, 33) - 1, 4);
    if (leap === -1) {
      leap = 4;
    }

    return leap
  }

  /*
    This function determines if the Jalaali (Persian) year is
    leap (366-day long) or is the common year (365 days), and
    finds the day in March (Gregorian calendar) of the first
    day of the Jalaali year (jy).

    @param jy Jalaali calendar year (-61 to 3177)
    @param withoutLeap when don't need leap (true or false) default is false
    @return
      leap: number of years since the last leap year (0 to 4)
      gy: Gregorian year of the beginning of Jalaali year
      march: the March day of Farvardin the 1st (1st day of jy)
    @see: http://www.astro.uni.torun.pl/~kb/Papers/EMP/PersianC-EMP.htm
    @see: http://www.fourmilab.ch/documents/calendar/
  */
  function jalCal (jy, withoutLeap) {
    var bl = breaks.length,
      gy = jy + 621,
      leapJ = -14,
      jp = breaks[0],
      jm,
      jump,
      leap,
      leapG,
      march,
      n,
      i;

    if (jy < jp || jy >= breaks[bl - 1]) { throw new Error('Invalid Jalaali year ' + jy) }

    // Find the limiting years for the Jalaali year jy.
    for (i = 1; i < bl; i += 1) {
      jm = breaks[i];
      jump = jm - jp;
      if (jy < jm) { break }
      leapJ = leapJ + div(jump, 33) * 8 + div(mod(jump, 33), 4);
      jp = jm;
    }
    n = jy - jp;

    // Find the number of leap years from AD 621 to the beginning
    // of the current Jalaali year in the Persian calendar.
    leapJ = leapJ + div(n, 33) * 8 + div(mod(n, 33) + 3, 4);
    if (mod(jump, 33) === 4 && jump - n === 4) { leapJ += 1; }

    // And the same in the Gregorian calendar (until the year gy).
    leapG = div(gy, 4) - div((div(gy, 100) + 1) * 3, 4) - 150;

    // Determine the Gregorian date of Farvardin the 1st.
    march = 20 + leapJ - leapG;

    // Find how many years have passed since the last leap year.
    if (!withoutLeap) {
      if (jump - n < 6) { n = n - jump + div(jump + 4, 33) * 33; }
      leap = mod(mod(n + 1, 33) - 1, 4);
      if (leap === -1) {
        leap = 4;
      }
    }

    return {
      leap: leap,
      gy: gy,
      march: march
    }
  }

  /*
    Converts a date of the Jalaali calendar to the Julian Day number.

    @param jy Jalaali year (1 to 3100)
    @param jm Jalaali month (1 to 12)
    @param jd Jalaali day (1 to 29/31)
    @return Julian Day number
  */
  function j2d (jy, jm, jd) {
    var r = jalCal(jy, true);
    return g2d(r.gy, 3, r.march) + (jm - 1) * 31 - div(jm, 7) * (jm - 7) + jd - 1
  }

  /*
    Converts the Julian Day number to a date in the Jalaali calendar.

    @param jdn Julian Day number
    @return
      jy: Jalaali year (1 to 3100)
      jm: Jalaali month (1 to 12)
      jd: Jalaali day (1 to 29/31)
  */
  function d2j (jdn) {
    var gy = d2g(jdn).gy, // Calculate Gregorian year (gy).
      jy = gy - 621,
      r = jalCal(jy, false),
      jdn1f = g2d(gy, 3, r.march),
      jd,
      jm,
      k;

    // Find number of days that passed since 1 Farvardin.
    k = jdn - jdn1f;
    if (k >= 0) {
      if (k <= 185) {
        // The first 6 months.
        jm = 1 + div(k, 31);
        jd = mod(k, 31) + 1;
        return { jy: jy,
          jm: jm,
          jd: jd
        }
      }
      else {
        // The remaining months.
        k -= 186;
      }
    }
    else {
      // Previous Jalaali year.
      jy -= 1;
      k += 179;
      if (r.leap === 1) { k += 1; }
    }
    jm = 7 + div(k, 30);
    jd = mod(k, 30) + 1;
    return { jy: jy,
      jm: jm,
      jd: jd
    }
  }

  /*
    Calculates the Julian Day number from Gregorian or Julian
    calendar dates. This integer number corresponds to the noon of
    the date (i.e. 12 hours of Universal Time).
    The procedure was tested to be good since 1 March, -100100 (of both
    calendars) up to a few million years into the future.

    @param gy Calendar year (years BC numbered 0, -1, -2, ...)
    @param gm Calendar month (1 to 12)
    @param gd Calendar day of the month (1 to 28/29/30/31)
    @return Julian Day number
  */
  function g2d (gy, gm, gd) {
    var d = div((gy + div(gm - 8, 6) + 100100) * 1461, 4) +
        div(153 * mod(gm + 9, 12) + 2, 5) +
        gd - 34840408;
    d = d - div(div(gy + 100100 + div(gm - 8, 6), 100) * 3, 4) + 752;
    return d
  }

  /*
    Calculates Gregorian and Julian calendar dates from the Julian Day number
    (jdn) for the period since jdn=-34839655 (i.e. the year -100100 of both
    calendars) to some millions years ahead of the present.

    @param jdn Julian Day number
    @return
      gy: Calendar year (years BC numbered 0, -1, -2, ...)
      gm: Calendar month (1 to 12)
      gd: Calendar day of the month M (1 to 28/29/30/31)
  */
  function d2g (jdn) {
    var j,
      i,
      gd,
      gm,
      gy;
    j = 4 * jdn + 139361631;
    j = j + div(div(4 * jdn + 183187720, 146097) * 3, 4) * 4 - 3908;
    i = div(mod(j, 1461), 4) * 5 + 308;
    gd = div(mod(i, 153), 5) + 1;
    gm = mod(div(i, 153), 12) + 1;
    gy = div(j, 1461) - 100100 + div(8 - gm, 6);
    return {
      gy: gy,
      gm: gm,
      gd: gd
    }
  }

  /*
    Utility helper functions.
  */

  function div (a, b) {
    return ~~(a / b)
  }

  function mod (a, b) {
    return a - ~~(a / b) * b
  }

  var DateTimeMixin = {
    props: {
      value: {
        required: true
      },

      mask: {
        type: String
      },
      locale: Object,

      calendar: {
        type: String,
        validator: function (v) { return ['gregorian', 'persian'].includes(v); },
        default: 'gregorian'
      },

      landscape: Boolean,

      color: String,
      textColor: String,
      dark: Boolean,

      readonly: Boolean,
      disable: Boolean
    },

    watch: {
      mask: function mask () {
        var this$1 = this;

        this.$nextTick(function () {
          this$1.__updateValue({}, /* reason for QDate only */ 'mask');
        });
      },

      computedLocale: function computedLocale () {
        var this$1 = this;

        this.$nextTick(function () {
          this$1.__updateValue({}, /* reason for QDate only */ 'locale');
        });
      }
    },

    computed: {
      editable: function editable () {
        return this.disable !== true && this.readonly !== true
      },

      computedColor: function computedColor () {
        return this.color || 'primary'
      },

      computedTextColor: function computedTextColor () {
        return this.textColor || 'white'
      },

      computedTabindex: function computedTabindex () {
        return this.editable === true ? 0 : -1
      },

      headerClass: function headerClass () {
        var cls = [];
        this.color !== void 0 && cls.push(("bg-" + (this.color)));
        this.textColor !== void 0 && cls.push(("text-" + (this.textColor)));
        return cls.join(' ')
      },

      computedLocale: function computedLocale () {
        return this.__getComputedLocale()
      }
    },

    methods: {
      __getComputedLocale: function __getComputedLocale () {
        return this.locale || this.$q.lang.date
      },

      __getCurrentDate: function __getCurrentDate () {
        var d = new Date();

        if (this.calendar === 'persian') {
          var jDate = toJalaali(d);
          return {
            year: jDate.jy,
            month: jDate.jm,
            day: jDate.jd
          }
        }

        return {
          year: d.getFullYear(),
          month: d.getMonth() + 1,
          day: d.getDate()
        }
      },

      __getCurrentTime: function __getCurrentTime () {
        var d = new Date();

        return {
          hour: d.getHours(),
          minute: d.getMinutes(),
          second: d.getSeconds(),
          millisecond: d.getMilliseconds()
        }
      }
    }
  };

  /* eslint no-fallthrough: 0 */

  var
    MILLISECONDS_IN_DAY = 86400000,
    MILLISECONDS_IN_HOUR = 3600000,
    MILLISECONDS_IN_MINUTE = 60000,
    defaultMask = 'YYYY-MM-DDTHH:mm:ss.SSSZ',
    token = /\[((?:[^\]\\]|\\]|\\)*)\]|d{1,4}|M{1,4}|m{1,2}|w{1,2}|Qo|Do|D{1,4}|YY(?:YY)?|H{1,2}|h{1,2}|s{1,2}|S{1,3}|Z{1,2}|a{1,2}|[AQExX]/g,
    reverseToken = /(\[[^\]]*\])|d{1,4}|M{1,4}|m{1,2}|w{1,2}|Qo|Do|D{1,4}|YY(?:YY)?|H{1,2}|h{1,2}|s{1,2}|S{1,3}|Z{1,2}|a{1,2}|[AQExX]|([.*+:?^,\s${}()|\\]+)/g,
    regexStore = {};

  function getRegexData (mask, dateLocale) {
    var
      days = '(' + dateLocale.days.join('|') + ')',
      key = mask + days;

    if (regexStore[key] !== void 0) {
      return regexStore[key]
    }

    var
      daysShort = '(' + dateLocale.daysShort.join('|') + ')',
      months = '(' + dateLocale.months.join('|') + ')',
      monthsShort = '(' + dateLocale.monthsShort.join('|') + ')';

    var map = {};
    var index = 0;

    var regexText = mask.replace(reverseToken, function (match) {
      index++;
      switch (match) {
        case 'YY':
          map.YY = index;
          return '(-?\\d{1,2})'
        case 'YYYY':
          map.YYYY = index;
          return '(-?\\d{1,4})'
        case 'M':
          map.M = index;
          return '(\\d{1,2})'
        case 'MM':
          map.M = index; // bumping to M
          return '(\\d{2})'
        case 'MMM':
          map.MMM = index;
          return monthsShort
        case 'MMMM':
          map.MMMM = index;
          return months
        case 'D':
          map.D = index;
          return '(\\d{1,2})'
        case 'Do':
          map.D = index++; // bumping to D
          return '(\\d{1,2}(st|nd|rd|th))'
        case 'DD':
          map.D = index; // bumping to D
          return '(\\d{2})'
        case 'H':
          map.H = index;
          return '(\\d{1,2})'
        case 'HH':
          map.H = index; // bumping to H
          return '(\\d{2})'
        case 'h':
          map.h = index;
          return '(\\d{1,2})'
        case 'hh':
          map.h = index; // bumping to h
          return '(\\d{2})'
        case 'm':
          map.m = index;
          return '(\\d{1,2})'
        case 'mm':
          map.m = index; // bumping to m
          return '(\\d{2})'
        case 's':
          map.s = index;
          return '(\\d{1,2})'
        case 'ss':
          map.s = index; // bumping to s
          return '(\\d{2})'
        case 'S':
          map.S = index;
          return '(\\d{1})'
        case 'SS':
          map.S = index; // bump to S
          return '(\\d{2})'
        case 'SSS':
          map.S = index; // bump to S
          return '(\\d{3})'
        case 'A':
          map.A = index;
          return '(AM|PM)'
        case 'a':
          map.a = index;
          return '(am|pm)'
        case 'aa':
          map.aa = index;
          return '(a\\.m\\.|p\\.m\\.)'

        case 'ddd':
          return daysShort
        case 'dddd':
          return days
        case 'Q':
        case 'd':
        case 'E':
          return '(\\d{1})'
        case 'Qo':
          return '(1st|2nd|3rd|4th)'
        case 'DDD':
        case 'DDDD':
          return '(\\d{1,3})'
        case 'w':
          return '(\\d{1,2})'
        case 'ww':
          return '(\\d{2})'

        case 'Z': // to split: (?:(Z)()()|([+-])?(\\d{2}):?(\\d{2}))
          return '(Z|[+-]\\d{2}:\\d{2})'
        case 'ZZ':
          return '(Z|[+-]\\d{2}\\d{2})'

        case 'X':
          map.X = index;
          return '(-?\\d+)'
        case 'x':
          map.x = index;
          return '(-?\\d{4,})'

        default:
          index--;
          if (match[0] === '[') {
            match = match.substring(1, match.length - 1);
          }
          return match.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
      }
    });

    var res = { map: map, regex: new RegExp('^' + regexText + '$') };
    regexStore[key] = res;

    return res
  }

  function extractDate (str, mask, dateLocale) {
    var d = __splitDate(str, mask, dateLocale);

    return new Date(
      d.year,
      d.month === null ? null : d.month - 1,
      d.day,
      d.hour,
      d.minute,
      d.second,
      d.millisecond
    )
  }

  function __splitDate (str, mask, dateLocale, calendar) {
    var date = {
      year: null,
      month: null,
      day: null,
      hour: null,
      minute: null,
      second: null,
      millisecond: null,
      dateHash: null,
      timeHash: null
    };

    if (
      str === void 0 ||
      str === null ||
      str === '' ||
      typeof str !== 'string'
    ) {
      return date
    }

    if (mask === void 0) {
      mask = defaultMask;
    }

    var
      langOpts = dateLocale !== void 0 ? dateLocale : lang.props.date,
      months = langOpts.months,
      monthsShort = langOpts.monthsShort;

    var ref = getRegexData(mask, langOpts);
    var regex = ref.regex;
    var map = ref.map;

    var match = str.match(regex);

    if (match === null) {
      return date
    }

    if (map.X !== void 0 || map.x !== void 0) {
      var stamp = parseInt(match[map.X !== void 0 ? map.X : map.x], 10);

      if (isNaN(stamp) === true || stamp < 0) {
        return date
      }

      var d = new Date(stamp * (map.X !== void 0 ? 1000 : 1));

      date.year = d.getFullYear();
      date.month = d.getMonth() + 1;
      date.day = d.getDate();
      date.hour = d.getHours();
      date.minute = d.getMinutes();
      date.second = d.getSeconds();
      date.millisecond = d.getMilliseconds();
    }
    else {
      if (map.YYYY !== void 0) {
        date.year = parseInt(match[map.YYYY], 10);
      }
      else if (map.YY !== void 0) {
        var y = parseInt(match[map.YY], 10);
        date.year = y < 0 ? y : 2000 + y;
      }

      if (map.M !== void 0) {
        date.month = parseInt(match[map.M], 10);
        if (date.month < 1 || date.month > 12) {
          return date
        }
      }
      else if (map.MMM !== void 0) {
        date.month = monthsShort.indexOf(match[map.MMM]) + 1;
      }
      else if (map.MMMM !== void 0) {
        date.month = months.indexOf(match[map.MMMM]) + 1;
      }

      if (map.D !== void 0) {
        date.day = parseInt(match[map.D], 10);

        if (date.year === null || date.month === null || date.day < 1) {
          return date
        }

        var maxDay = calendar !== 'persian'
          ? (new Date(date.year, date.month, 0)).getDate()
          : jalaaliMonthLength(date.year, date.month);

        if (date.day > maxDay) {
          return date
        }
      }

      if (map.H !== void 0) {
        date.hour = parseInt(match[map.H], 10) % 24;
      }
      else if (map.h !== void 0) {
        date.hour = parseInt(match[map.h], 10) % 12;
        if (
          (map.A && match[map.A] === 'PM') ||
          (map.a && match[map.a] === 'pm') ||
          (map.aa && match[map.aa] === 'p.m.')
        ) {
          date.hour += 12;
        }
        date.hour = date.hour % 24;
      }

      if (map.m !== void 0) {
        date.minute = parseInt(match[map.m], 10) % 60;
      }

      if (map.s !== void 0) {
        date.second = parseInt(match[map.s], 10) % 60;
      }

      if (map.S !== void 0) {
        date.millisecond = parseInt(match[map.S], 10) * Math.pow( 10, (3 - match[map.S].length) );
      }
    }

    date.dateHash = date.year + '/' + pad(date.month) + '/' + pad(date.day);
    date.timeHash = pad(date.hour) + ':' + pad(date.minute) + ':' + pad(date.second);

    return date
  }

  function formatTimezone (offset, delimeter) {
    if ( delimeter === void 0 ) delimeter = '';

    var
      sign = offset > 0 ? '-' : '+',
      absOffset = Math.abs(offset),
      hours = Math.floor(absOffset / 60),
      minutes = absOffset % 60;

    return sign + pad(hours) + delimeter + pad(minutes)
  }

  function setMonth (date, newMonth /* 1-based */) {
    var
      test = new Date(date.getFullYear(), newMonth, 0, 0, 0, 0, 0),
      days = test.getDate();

    date.setMonth(newMonth - 1, Math.min(days, date.getDate()));
  }

  function getChange (date, mod, add) {
    var
      t = new Date(date),
      sign = (add ? 1 : -1);

    Object.keys(mod).forEach(function (key) {
      if (key === 'month') {
        setMonth(t, t.getMonth() + 1 + sign * mod.month);
        return
      }

      var op = key === 'year'
        ? 'FullYear'
        : capitalize(key === 'days' ? 'date' : key);
      t[("set" + op)](t[("get" + op)]() + sign * mod[key]);
    });
    return t
  }

  function isValid (date) {
    return typeof date === 'number'
      ? true
      : isNaN(Date.parse(date)) === false
  }

  function buildDate (mod, utc) {
    return adjustDate(new Date(), mod, utc)
  }

  function getDayOfWeek (date) {
    var dow = new Date(date).getDay();
    return dow === 0 ? 7 : dow
  }

  function getWeekOfYear (date) {
    // Remove time components of date
    var thursday = new Date(date.getFullYear(), date.getMonth(), date.getDate());

    // Change date to Thursday same week
    thursday.setDate(thursday.getDate() - ((thursday.getDay() + 6) % 7) + 3);

    // Take January 4th as it is always in week 1 (see ISO 8601)
    var firstThursday = new Date(thursday.getFullYear(), 0, 4);

    // Change date to Thursday same week
    firstThursday.setDate(firstThursday.getDate() - ((firstThursday.getDay() + 6) % 7) + 3);

    // Check if daylight-saving-time-switch occurred and correct for it
    var ds = thursday.getTimezoneOffset() - firstThursday.getTimezoneOffset();
    thursday.setHours(thursday.getHours() - ds);

    // Number of weeks between target Thursday and first Thursday
    var weekDiff = (thursday - firstThursday) / (MILLISECONDS_IN_DAY * 7);
    return 1 + Math.floor(weekDiff)
  }

  function isBetweenDates (date, from, to, opts) {
    if ( opts === void 0 ) opts = {};

    var
      d1 = new Date(from).getTime(),
      d2 = new Date(to).getTime(),
      cur = new Date(date).getTime();

    opts.inclusiveFrom && d1--;
    opts.inclusiveTo && d2++;

    return cur > d1 && cur < d2
  }

  function addToDate (date, mod) {
    return getChange(date, mod, true)
  }
  function subtractFromDate (date, mod) {
    return getChange(date, mod, false)
  }

  function adjustDate (date, mod, utc) {
    var
      t = new Date(date),
      prefix = "set" + (utc ? 'UTC' : '');

    Object.keys(mod).forEach(function (key) {
      if (key === 'month') {
        setMonth(t, mod.month);
        return
      }

      var op = key === 'year'
        ? 'FullYear'
        : key.charAt(0).toUpperCase() + key.slice(1);
      t[("" + prefix + op)](mod[key]);
    });

    return t
  }

  function startOfDate (date, unit) {
    var t = new Date(date);
    switch (unit) {
      case 'year':
        t.setMonth(0);
      case 'month':
        t.setDate(1);
      case 'day':
        t.setHours(0);
      case 'hour':
        t.setMinutes(0);
      case 'minute':
        t.setSeconds(0);
      case 'second':
        t.setMilliseconds(0);
    }
    return t
  }

  function endOfDate (date, unit) {
    var t = new Date(date);
    switch (unit) {
      case 'year':
        t.setMonth(11);
      case 'month':
        t.setDate(daysInMonth(date));
      case 'day':
        t.setHours(23);
      case 'hour':
        t.setMinutes(59);
      case 'minute':
        t.setSeconds(59);
      case 'second':
        t.setMilliseconds(59);
    }
    return t
  }

  function getMaxDate (date) {
    var args = [], len = arguments.length - 1;
    while ( len-- > 0 ) args[ len ] = arguments[ len + 1 ];

    var t = new Date(date);
    args.forEach(function (d) {
      t = Math.max(t, new Date(d));
    });
    return t
  }
  function getMinDate (date) {
    var args = [], len = arguments.length - 1;
    while ( len-- > 0 ) args[ len ] = arguments[ len + 1 ];

    var t = new Date(date);
    args.forEach(function (d) {
      t = Math.min(t, new Date(d));
    });
    return t
  }

  function getDiff (t, sub, interval) {
    return (
      (t.getTime() - t.getTimezoneOffset() * MILLISECONDS_IN_MINUTE) -
      (sub.getTime() - sub.getTimezoneOffset() * MILLISECONDS_IN_MINUTE)
    ) / interval
  }

  function getDateDiff (date, subtract, unit) {
    if ( unit === void 0 ) unit = 'days';

    var
      t = new Date(date),
      sub = new Date(subtract);

    switch (unit) {
      case 'years':
        return (t.getFullYear() - sub.getFullYear())

      case 'months':
        return (t.getFullYear() - sub.getFullYear()) * 12 + t.getMonth() - sub.getMonth()

      case 'days':
        return getDiff(startOfDate(t, 'day'), startOfDate(sub, 'day'), MILLISECONDS_IN_DAY)

      case 'hours':
        return getDiff(startOfDate(t, 'hour'), startOfDate(sub, 'hour'), MILLISECONDS_IN_HOUR)

      case 'minutes':
        return getDiff(startOfDate(t, 'minute'), startOfDate(sub, 'minute'), MILLISECONDS_IN_MINUTE)

      case 'seconds':
        return getDiff(startOfDate(t, 'second'), startOfDate(sub, 'second'), 1000)
    }
  }

  function getDayOfYear (date) {
    return getDateDiff(date, startOfDate(date, 'year'), 'days') + 1
  }

  function inferDateFormat (date) {
    return isDate(date) === true
      ? 'date'
      : (typeof date === 'number' ? 'number' : 'string')
  }

  function getDateBetween (date, min, max) {
    var t = new Date(date);

    if (min) {
      var low = new Date(min);
      if (t < low) {
        return low
      }
    }

    if (max) {
      var high = new Date(max);
      if (t > high) {
        return high
      }
    }

    return t
  }

  function isSameDate (date, date2, unit) {
    var
      t = new Date(date),
      d = new Date(date2);

    if (unit === void 0) {
      return t.getTime() === d.getTime()
    }

    switch (unit) {
      case 'second':
        if (t.getSeconds() !== d.getSeconds()) {
          return false
        }
      case 'minute': // intentional fall-through
        if (t.getMinutes() !== d.getMinutes()) {
          return false
        }
      case 'hour': // intentional fall-through
        if (t.getHours() !== d.getHours()) {
          return false
        }
      case 'day': // intentional fall-through
        if (t.getDate() !== d.getDate()) {
          return false
        }
      case 'month': // intentional fall-through
        if (t.getMonth() !== d.getMonth()) {
          return false
        }
      case 'year': // intentional fall-through
        if (t.getFullYear() !== d.getFullYear()) {
          return false
        }
        break
      default:
        throw new Error(("date isSameDate unknown unit " + unit))
    }

    return true
  }

  function daysInMonth (date) {
    return (new Date(date.getFullYear(), date.getMonth() + 1, 0)).getDate()
  }

  function getOrdinal (n) {
    if (n >= 11 && n <= 13) {
      return (n + "th")
    }
    switch (n % 10) {
      case 1: return (n + "st")
      case 2: return (n + "nd")
      case 3: return (n + "rd")
    }
    return (n + "th")
  }

  var formatter = {
    // Year: 00, 01, ..., 99
    YY: function YY (date, _, forcedYear) {
      // workaround for < 1900 with new Date()
      var y = this.YYYY(date, _, forcedYear) % 100;
      return y > 0
        ? pad(y)
        : '-' + pad(Math.abs(y))
    },

    // Year: 1900, 1901, ..., 2099
    YYYY: function YYYY (date, _, forcedYear) {
      // workaround for < 1900 with new Date()
      return forcedYear !== void 0 && forcedYear !== null
        ? forcedYear
        : date.getFullYear()
    },

    // Month: 1, 2, ..., 12
    M: function M (date) {
      return date.getMonth() + 1
    },

    // Month: 01, 02, ..., 12
    MM: function MM (date) {
      return pad(date.getMonth() + 1)
    },

    // Month Short Name: Jan, Feb, ...
    MMM: function MMM (date, dateLocale) {
      return dateLocale.monthsShort[date.getMonth()]
    },

    // Month Name: January, February, ...
    MMMM: function MMMM (date, dateLocale) {
      return dateLocale.months[date.getMonth()]
    },

    // Quarter: 1, 2, 3, 4
    Q: function Q (date) {
      return Math.ceil((date.getMonth() + 1) / 3)
    },

    // Quarter: 1st, 2nd, 3rd, 4th
    Qo: function Qo (date) {
      return getOrdinal(this.Q(date))
    },

    // Day of month: 1, 2, ..., 31
    D: function D (date) {
      return date.getDate()
    },

    // Day of month: 1st, 2nd, ..., 31st
    Do: function Do (date) {
      return getOrdinal(date.getDate())
    },

    // Day of month: 01, 02, ..., 31
    DD: function DD (date) {
      return pad(date.getDate())
    },

    // Day of year: 1, 2, ..., 366
    DDD: function DDD (date) {
      return getDayOfYear(date)
    },

    // Day of year: 001, 002, ..., 366
    DDDD: function DDDD (date) {
      return pad(getDayOfYear(date), 3)
    },

    // Day of week: 0, 1, ..., 6
    d: function d (date) {
      return date.getDay()
    },

    // Day of week: Su, Mo, ...
    dd: function dd (date, dateLocale) {
      return this.dddd(date, dateLocale).slice(0, 2)
    },

    // Day of week: Sun, Mon, ...
    ddd: function ddd (date, dateLocale) {
      return dateLocale.daysShort[date.getDay()]
    },

    // Day of week: Sunday, Monday, ...
    dddd: function dddd (date, dateLocale) {
      return dateLocale.days[date.getDay()]
    },

    // Day of ISO week: 1, 2, ..., 7
    E: function E (date) {
      return date.getDay() || 7
    },

    // Week of Year: 1 2 ... 52 53
    w: function w (date) {
      return getWeekOfYear(date)
    },

    // Week of Year: 01 02 ... 52 53
    ww: function ww (date) {
      return pad(getWeekOfYear(date))
    },

    // Hour: 0, 1, ... 23
    H: function H (date) {
      return date.getHours()
    },

    // Hour: 00, 01, ..., 23
    HH: function HH (date) {
      return pad(date.getHours())
    },

    // Hour: 1, 2, ..., 12
    h: function h (date) {
      var hours = date.getHours();
      if (hours === 0) {
        return 12
      }
      if (hours > 12) {
        return hours % 12
      }
      return hours
    },

    // Hour: 01, 02, ..., 12
    hh: function hh (date) {
      return pad(this.h(date))
    },

    // Minute: 0, 1, ..., 59
    m: function m (date) {
      return date.getMinutes()
    },

    // Minute: 00, 01, ..., 59
    mm: function mm (date) {
      return pad(date.getMinutes())
    },

    // Second: 0, 1, ..., 59
    s: function s (date) {
      return date.getSeconds()
    },

    // Second: 00, 01, ..., 59
    ss: function ss (date) {
      return pad(date.getSeconds())
    },

    // 1/10 of second: 0, 1, ..., 9
    S: function S (date) {
      return Math.floor(date.getMilliseconds() / 100)
    },

    // 1/100 of second: 00, 01, ..., 99
    SS: function SS (date) {
      return pad(Math.floor(date.getMilliseconds() / 10))
    },

    // Millisecond: 000, 001, ..., 999
    SSS: function SSS (date) {
      return pad(date.getMilliseconds(), 3)
    },

    // Meridiem: AM, PM
    A: function A (date) {
      return this.H(date) < 12 ? 'AM' : 'PM'
    },

    // Meridiem: am, pm
    a: function a (date) {
      return this.H(date) < 12 ? 'am' : 'pm'
    },

    // Meridiem: a.m., p.m.
    aa: function aa (date) {
      return this.H(date) < 12 ? 'a.m.' : 'p.m.'
    },

    // Timezone: -01:00, +00:00, ... +12:00
    Z: function Z (date) {
      return formatTimezone(date.getTimezoneOffset(), ':')
    },

    // Timezone: -0100, +0000, ... +1200
    ZZ: function ZZ (date) {
      return formatTimezone(date.getTimezoneOffset())
    },

    // Seconds timestamp: 512969520
    X: function X (date) {
      return Math.floor(date.getTime() / 1000)
    },

    // Milliseconds timestamp: 512969520900
    x: function x (date) {
      return date.getTime()
    }
  };

  function formatDate (val, mask, dateLocale, __forcedYear) {
    if (
      (val !== 0 && !val) ||
      val === Infinity ||
      val === -Infinity
    ) {
      return
    }

    var date = new Date(val);

    if (isNaN(date)) {
      return
    }

    if (mask === void 0) {
      mask = defaultMask;
    }

    var locale = dateLocale !== void 0
      ? dateLocale
      : lang.props.date;

    return mask.replace(
      token,
      function (match, text) { return match in formatter
        ? formatter[match](date, locale, __forcedYear)
        : (text === void 0 ? match : text.split('\\]').join(']')); }
    )
  }

  function clone (date) {
    return isDate(date) === true
      ? new Date(date.getTime())
      : date
  }

  var date = {
    isValid: isValid,
    extractDate: extractDate,
    buildDate: buildDate,
    getDayOfWeek: getDayOfWeek,
    getWeekOfYear: getWeekOfYear,
    isBetweenDates: isBetweenDates,
    addToDate: addToDate,
    subtractFromDate: subtractFromDate,
    adjustDate: adjustDate,
    startOfDate: startOfDate,
    endOfDate: endOfDate,
    getMaxDate: getMaxDate,
    getMinDate: getMinDate,
    getDateDiff: getDateDiff,
    getDayOfYear: getDayOfYear,
    inferDateFormat: inferDateFormat,
    getDateBetween: getDateBetween,
    isSameDate: isSameDate,
    daysInMonth: daysInMonth,
    formatDate: formatDate,
    clone: clone
  };

  var yearsInterval = 20;

  var QDate = Vue.extend({
    name: 'QDate',

    mixins: [ DateTimeMixin ],

    props: {
      title: String,
      subtitle: String,

      emitImmediately: Boolean,

      mask: {
        // this mask is forced
        // when using persian calendar
        default: 'YYYY/MM/DD'
      },

      defaultYearMonth: {
        type: String,
        validator: function (v) { return /^-?[\d]+\/[0-1]\d$/.test(v); }
      },

      events: [Array, Function],
      eventColor: [String, Function],

      options: [Array, Function],

      firstDayOfWeek: [String, Number],
      todayBtn: Boolean,
      minimal: Boolean,
      defaultView: {
        type: String,
        default: 'Calendar',
        validator: function (v) { return ['Calendar', 'Years', 'Months'].includes(v); }
      }
    },

    data: function data () {
      var ref = this.__getModels(this.value, this.mask, this.__getComputedLocale());
      var inner = ref.inner;
      var external = ref.external;
      return {
        view: this.defaultView,
        monthDirection: 'left',
        yearDirection: 'left',
        startYear: inner.year - inner.year % yearsInterval,
        innerModel: inner,
        extModel: external
      }
    },

    watch: {
      value: function value (v) {
        var this$1 = this;

        var ref = this.__getModels(v, this.mask, this.__getComputedLocale());
        var inner = ref.inner;
        var external = ref.external;

        if (
          this.extModel.dateHash !== external.dateHash ||
          this.extModel.timeHash !== external.timeHash
        ) {
          this.extModel = external;
        }

        if (inner.dateHash !== this.innerModel.dateHash) {
          this.monthDirection = this.innerModel.dateHash < inner.dateHash ? 'left' : 'right';
          if (inner.year !== this.innerModel.year) {
            this.yearDirection = this.monthDirection;
          }

          this.$nextTick(function () {
            this$1.startYear = inner.year - inner.year % yearsInterval;
            this$1.innerModel = inner;
          });
        }
      },

      view: function view () {
        this.$refs.blurTarget !== void 0 && this.$refs.blurTarget.focus();
      }
    },

    computed: {
      classes: function classes () {
        var type = this.landscape === true ? 'landscape' : 'portrait';
        return "q-date--" + type + " q-date--" + type + "-" + (this.minimal === true ? 'minimal' : 'standard') +
          (this.dark === true ? ' q-date--dark' : '') +
          (this.readonly === true ? ' q-date--readonly' : '') +
          (this.disable === true ? ' disabled' : '')
      },

      headerTitle: function headerTitle () {
        if (this.title !== void 0 && this.title !== null && this.title.length > 0) {
          return this.title
        }

        var model = this.extModel;
        if (model.dateHash === null) { return ' --- ' }

        var date;

        if (this.calendar !== 'persian') {
          date = new Date(model.year, model.month - 1, model.day);
        }
        else {
          var gDate = toGregorian(model.year, model.month, model.day);
          date = new Date(gDate.gy, gDate.gm - 1, gDate.gd);
        }

        if (isNaN(date.valueOf()) === true) { return ' --- ' }

        if (this.computedLocale.headerTitle !== void 0) {
          return this.computedLocale.headerTitle(date, model)
        }

        return this.computedLocale.daysShort[ date.getDay() ] + ', ' +
          this.computedLocale.monthsShort[ model.month - 1 ] + ' ' +
          model.day
      },

      headerSubtitle: function headerSubtitle () {
        return this.subtitle !== void 0 && this.subtitle !== null && this.subtitle.length > 0
          ? this.subtitle
          : (
            this.extModel.year !== null
              ? this.extModel.year
              : ' --- '
          )
      },

      dateArrow: function dateArrow () {
        var val = [ this.$q.iconSet.datetime.arrowLeft, this.$q.iconSet.datetime.arrowRight ];
        return this.$q.lang.rtl ? val.reverse() : val
      },

      computedFirstDayOfWeek: function computedFirstDayOfWeek () {
        return this.firstDayOfWeek !== void 0
          ? Number(this.firstDayOfWeek)
          : this.computedLocale.firstDayOfWeek
      },

      daysOfWeek: function daysOfWeek () {
        var
          days = this.computedLocale.daysShort,
          first = this.computedFirstDayOfWeek;

        return first > 0
          ? days.slice(first, 7).concat(days.slice(0, first))
          : days
      },

      daysInMonth: function daysInMonth () {
        return this.__getDaysInMonth(this.innerModel)
      },

      today: function today () {
        return this.__getCurrentDate()
      },

      evtFn: function evtFn () {
        var this$1 = this;

        return typeof this.events === 'function'
          ? this.events
          : function (date) { return this$1.events.includes(date); }
      },

      evtColor: function evtColor () {
        var this$1 = this;

        return typeof this.eventColor === 'function'
          ? this.eventColor
          : function (date) { return this$1.eventColor; }
      },

      isInSelection: function isInSelection () {
        var this$1 = this;

        return typeof this.options === 'function'
          ? this.options
          : function (date) { return this$1.options.includes(date); }
      },

      days: function days () {
        var date, endDay;

        var res = [];

        if (this.calendar !== 'persian') {
          date = new Date(this.innerModel.year, this.innerModel.month - 1, 1);
          endDay = (new Date(this.innerModel.year, this.innerModel.month - 1, 0)).getDate();
        }
        else {
          var gDate = toGregorian(this.innerModel.year, this.innerModel.month, 1);
          date = new Date(gDate.gy, gDate.gm - 1, gDate.gd);
          var prevJM = this.innerModel.month - 1;
          var prevJY = this.innerModel.year;
          if (prevJM === 0) {
            prevJM = 12;
            prevJY--;
          }
          endDay = jalaaliMonthLength(prevJY, prevJM);
        }

        var days = (date.getDay() - this.computedFirstDayOfWeek - 1);

        var len = days < 0 ? days + 7 : days;
        if (len < 6) {
          for (var i = endDay - len; i <= endDay; i++) {
            res.push({ i: i });
          }
        }

        var
          index = res.length,
          prefix = this.innerModel.year + '/' + pad(this.innerModel.month) + '/';

        for (var i$1 = 1; i$1 <= this.daysInMonth; i$1++) {
          var day = prefix + pad(i$1);

          if (this.options !== void 0 && this.isInSelection(day) !== true) {
            res.push({ i: i$1 });
          }
          else {
            var event = this.events !== void 0 && this.evtFn(day) === true
              ? this.evtColor(day)
              : false;

            res.push({ i: i$1, in: true, flat: true, event: event });
          }
        }

        if (this.innerModel.year === this.extModel.year && this.innerModel.month === this.extModel.month) {
          var i$2 = index + this.innerModel.day - 1;
          res[i$2] !== void 0 && Object.assign(res[i$2], {
            unelevated: true,
            flat: false,
            color: this.computedColor,
            textColor: this.computedTextColor
          });
        }

        if (this.innerModel.year === this.today.year && this.innerModel.month === this.today.month) {
          res[index + this.today.day - 1].today = true;
        }

        var left = res.length % 7;
        if (left > 0) {
          var afterDays = 7 - left;
          for (var i$3 = 1; i$3 <= afterDays; i$3++) {
            res.push({ i: i$3 });
          }
        }

        return res
      }
    },

    methods: {
      __getModels: function __getModels (val, mask, locale) {
        var external = __splitDate(
          val,
          this.calendar === 'persian' ? 'YYYY/MM/DD' : mask,
          locale,
          this.calendar
        );

        return {
          external: external,
          inner: external.dateHash === null
            ? this.__getDefaultModel()
            : Object.assign({}, external)
        }
      },

      __getDefaultModel: function __getDefaultModel () {
        var year, month;

        if (this.defaultYearMonth !== void 0) {
          var d = this.defaultYearMonth.split('/');
          year = parseInt(d[0], 10);
          month = parseInt(d[1], 10);
        }
        else {
          // may come from data() where computed
          // props are not yet available
          var d$1 = this.today !== void 0
            ? this.today
            : this.__getCurrentDate();

          year = d$1.year;
          month = d$1.month;
        }

        return {
          year: year,
          month: month,
          day: 1,
          hour: 0,
          minute: 0,
          second: 0,
          millisecond: 0,
          dateHash: year + '/' + pad(month) + '/01'
        }
      },

      __getHeader: function __getHeader (h) {
        var this$1 = this;

        if (this.minimal === true) { return }

        return h('div', {
          staticClass: 'q-date__header',
          class: this.headerClass
        }, [
          h('div', {
            staticClass: 'relative-position'
          }, [
            h('transition', {
              props: {
                name: 'q-transition--fade'
              }
            }, [
              h('div', {
                key: 'h-yr-' + this.headerSubtitle,
                staticClass: 'q-date__header-subtitle q-date__header-link',
                class: this.view === 'Years' ? 'q-date__header-link--active' : 'cursor-pointer',
                attrs: { tabindex: this.computedTabindex },
                on: {
                  click: function () { this$1.view = 'Years'; },
                  keyup: function (e) { e.keyCode === 13 && (this$1.view = 'Years'); }
                }
              }, [ this.headerSubtitle ])
            ])
          ]),

          h('div', {
            staticClass: 'q-date__header-title relative-position flex no-wrap'
          }, [
            h('div', {
              staticClass: 'relative-position col'
            }, [
              h('transition', {
                props: {
                  name: 'q-transition--fade'
                }
              }, [
                h('div', {
                  key: 'h-sub' + this.headerTitle,
                  staticClass: 'q-date__header-title-label q-date__header-link',
                  class: this.view === 'Calendar' ? 'q-date__header-link--active' : 'cursor-pointer',
                  attrs: { tabindex: this.computedTabindex },
                  on: {
                    click: function () { this$1.view = 'Calendar'; },
                    keyup: function (e) { e.keyCode === 13 && (this$1.view = 'Calendar'); }
                  }
                }, [ this.headerTitle ])
              ])
            ]),

            this.todayBtn === true ? h(QBtn, {
              staticClass: 'q-date__header-today',
              props: {
                icon: this.$q.iconSet.datetime.today,
                flat: true,
                size: 'sm',
                round: true,
                tabindex: this.computedTabindex
              },
              on: {
                click: this.__setToday
              }
            }) : null
          ])
        ])
      },

      __getNavigation: function __getNavigation (h, ref) {
        var this$1 = this;
        var label = ref.label;
        var view = ref.view;
        var key = ref.key;
        var dir = ref.dir;
        var goTo = ref.goTo;
        var cls = ref.cls;

        return [
          h('div', {
            staticClass: 'row items-center q-date__arrow'
          }, [
            h(QBtn, {
              props: {
                round: true,
                dense: true,
                size: 'sm',
                flat: true,
                icon: this.dateArrow[0],
                tabindex: this.computedTabindex
              },
              on: {
                click: function click () { goTo(-1); }
              }
            })
          ]),

          h('div', {
            staticClass: 'relative-position overflow-hidden flex flex-center' + cls
          }, [
            h('transition', {
              props: {
                name: 'q-transition--jump-' + dir
              }
            }, [
              h('div', { key: key }, [
                h(QBtn, {
                  props: {
                    flat: true,
                    dense: true,
                    noCaps: true,
                    label: label,
                    tabindex: this.computedTabindex
                  },
                  on: {
                    click: function () { this$1.view = view; }
                  }
                })
              ])
            ])
          ]),

          h('div', {
            staticClass: 'row items-center q-date__arrow'
          }, [
            h(QBtn, {
              props: {
                round: true,
                dense: true,
                size: 'sm',
                flat: true,
                icon: this.dateArrow[1],
                tabindex: this.computedTabindex
              },
              on: {
                click: function click () { goTo(1); }
              }
            })
          ])
        ]
      },

      __getCalendarView: function __getCalendarView (h) {
        var this$1 = this;

        return [
          h('div', {
            key: 'calendar-view',
            staticClass: 'q-date__view q-date__calendar'
          }, [
            h('div', {
              staticClass: 'q-date__navigation row items-center no-wrap'
            }, this.__getNavigation(h, {
              label: this.computedLocale.months[ this.innerModel.month - 1 ],
              view: 'Months',
              key: this.innerModel.month,
              dir: this.monthDirection,
              goTo: this.__goToMonth,
              cls: ' col'
            }).concat(this.__getNavigation(h, {
              label: this.innerModel.year,
              view: 'Years',
              key: this.innerModel.year,
              dir: this.yearDirection,
              goTo: this.__goToYear,
              cls: ''
            }))),

            h('div', {
              staticClass: 'q-date__calendar-weekdays row items-center no-wrap'
            }, this.daysOfWeek.map(function (day) { return h('div', { staticClass: 'q-date__calendar-item' }, [ h('div', [ day ]) ]); })),

            h('div', {
              staticClass: 'q-date__calendar-days-container relative-position overflow-hidden'
            }, [
              h('transition', {
                props: {
                  name: 'q-transition--slide-' + this.monthDirection
                }
              }, [
                h('div', {
                  key: this.innerModel.year + '/' + this.innerModel.month,
                  staticClass: 'q-date__calendar-days fit'
                }, this.days.map(function (day) { return h('div', {
                  staticClass: ("q-date__calendar-item q-date__calendar-item--" + (day.in === true ? 'in' : 'out'))
                }, [
                  day.in === true
                    ? h(QBtn, {
                      staticClass: day.today === true ? 'q-date__today' : null,
                      props: {
                        dense: true,
                        flat: day.flat,
                        unelevated: day.unelevated,
                        color: day.color,
                        textColor: day.textColor,
                        label: day.i,
                        tabindex: this$1.computedTabindex
                      },
                      on: {
                        click: function () { this$1.__setDay(day.i); }
                      }
                    }, day.event !== false ? [
                      h('div', { staticClass: 'q-date__event bg-' + day.event })
                    ] : null)
                    : h('div', [ day.i ])
                ]); }))
              ])
            ])
          ])
        ]
      },

      __getMonthsView: function __getMonthsView (h) {
        var this$1 = this;

        var currentYear = this.innerModel.year === this.today.year;

        var content = this.computedLocale.monthsShort.map(function (month, i) {
          var active = this$1.innerModel.month === i + 1;

          return h('div', {
            staticClass: 'q-date__months-item flex flex-center'
          }, [
            h(QBtn, {
              staticClass: currentYear === true && this$1.today.month === i + 1 ? 'q-date__today' : null,
              props: {
                flat: !active,
                label: month,
                unelevated: active,
                color: active ? this$1.computedColor : null,
                textColor: active ? this$1.computedTextColor : null,
                tabindex: this$1.computedTabindex
              },
              on: {
                click: function () { this$1.__setMonth(i + 1); }
              }
            })
          ])
        });

        return h('div', {
          key: 'months-view',
          staticClass: 'q-date__view q-date__months column flex-center'
        }, [
          h('div', {
            staticClass: 'q-date__months-content row'
          }, content)
        ])
      },

      __getYearsView: function __getYearsView (h) {
        var this$1 = this;

        var
          start = this.startYear,
          stop = start + yearsInterval,
          years = [];

        var loop = function ( i ) {
          var active = this$1.innerModel.year === i;

          years.push(
            h('div', {
              staticClass: 'q-date__years-item flex flex-center'
            }, [
              h(QBtn, {
                staticClass: this$1.today.year === i ? 'q-date__today' : null,
                props: {
                  flat: !active,
                  label: i,
                  dense: true,
                  unelevated: active,
                  color: active ? this$1.computedColor : null,
                  textColor: active ? this$1.computedTextColor : null,
                  tabindex: this$1.computedTabindex
                },
                on: {
                  click: function () { this$1.__setYear(i); }
                }
              })
            ])
          );
        };

        for (var i = start; i <= stop; i++) loop( i );

        return h('div', {
          staticClass: 'q-date__view q-date__years flex flex-center full-height'
        }, [
          h('div', {
            staticClass: 'col-auto'
          }, [
            h(QBtn, {
              props: {
                round: true,
                dense: true,
                flat: true,
                icon: this.dateArrow[0],
                tabindex: this.computedTabindex
              },
              on: {
                click: function () { this$1.startYear -= yearsInterval; }
              }
            })
          ]),

          h('div', {
            staticClass: 'q-date__years-content col full-height row items-center'
          }, years),

          h('div', {
            staticClass: 'col-auto'
          }, [
            h(QBtn, {
              props: {
                round: true,
                dense: true,
                flat: true,
                icon: this.dateArrow[1],
                tabindex: this.computedTabindex
              },
              on: {
                click: function () { this$1.startYear += yearsInterval; }
              }
            })
          ])
        ])
      },

      __getDaysInMonth: function __getDaysInMonth (obj) {
        return this.calendar !== 'persian'
          ? (new Date(obj.year, obj.month, 0)).getDate()
          : jalaaliMonthLength(obj.year, obj.month)
      },

      __goToMonth: function __goToMonth (offset) {
        var
          month = Number(this.innerModel.month) + offset,
          yearDir = this.yearDirection;

        if (month === 13) {
          month = 1;
          this.innerModel.year++;
          yearDir = 'left';
        }
        else if (month === 0) {
          month = 12;
          this.innerModel.year--;
          yearDir = 'right';
        }

        this.monthDirection = offset > 0 ? 'left' : 'right';
        this.yearDirection = yearDir;
        this.innerModel.month = month;
        this.emitImmediately === true && this.__updateValue({}, 'month');
      },

      __goToYear: function __goToYear (offset) {
        this.monthDirection = this.yearDirection = offset > 0 ? 'left' : 'right';
        this.innerModel.year = Number(this.innerModel.year) + offset;
        this.emitImmediately === true && this.__updateValue({}, 'year');
      },

      __setYear: function __setYear (year) {
        this.innerModel.year = year;
        this.emitImmediately === true && this.__updateValue({ year: year }, 'year');
        this.view = 'Calendar';
      },

      __setMonth: function __setMonth (month) {
        this.innerModel.month = month;
        this.emitImmediately === true && this.__updateValue({ month: month }, 'month');
        this.view = 'Calendar';
      },

      __setDay: function __setDay (day) {
        this.__updateValue({ day: day }, 'day');
      },

      __setToday: function __setToday () {
        this.__updateValue(Object.assign({}, this.today), 'today');
        this.view = 'Calendar';
      },

      __updateValue: function __updateValue (date, reason) {
        var this$1 = this;

        if (date.year === void 0) {
          date.year = this.innerModel.year;
        }
        if (date.month === void 0) {
          date.month = this.innerModel.month;
        }
        if (
          date.day === void 0 ||
          (this.emitImmediately === true && (reason === 'year' || reason === 'month'))
        ) {
          date.day = this.innerModel.day;
          var maxDay = this.emitImmediately === true
            ? this.__getDaysInMonth(date)
            : this.daysInMonth;

          date.day = Math.min(date.day, maxDay);
        }

        var val = this.calendar === 'persian'
          ? date.year + '/' + pad(date.month) + '/' + pad(date.day)
          : formatDate(
            new Date(
              date.year,
              date.month - 1,
              date.day,
              this.extModel.hour,
              this.extModel.minute,
              this.extModel.second,
              this.extModel.millisecond
            ),
            this.mask,
            this.computedLocale,
            date.year
          );

        if (val !== this.value) {
          this.$emit('input', val, reason, date);
        }
        else if (reason === 'today') {
          var newHash = date.year + '/' + pad(date.month) + '/' + pad(date.day);
          var curHash = this.innerModel.year + '/' + pad(this.innerModel.month) + '/' + pad(this.innerModel.day);

          if (newHash !== curHash) {
            this.monthDirection = curHash < newHash ? 'left' : 'right';
            if (date.year !== this.innerModel.year) {
              this.yearDirection = this.monthDirection;
            }

            this.$nextTick(function () {
              this$1.startYear = date.year - date.year % yearsInterval;
              Object.assign(this$1.innerModel, {
                year: date.year,
                month: date.month,
                day: date.day,
                dateHash: newHash
              });
            });
          }
        }
      }
    },

    render: function render (h) {
      return h('div', {
        staticClass: 'q-date',
        class: this.classes,
        on: this.$listeners
      }, [
        this.__getHeader(h),

        h('div', {
          staticClass: 'q-date__content relative-position overflow-auto',
          attrs: { tabindex: -1 },
          ref: 'blurTarget'
        }, [
          h('transition', {
            props: {
              name: 'q-transition--fade'
            }
          }, [
            this[("__get" + (this.view) + "View")](h)
          ])
        ])
      ])
    }
  });

  var QTime = Vue.extend({
    name: 'QTime',

    mixins: [ DateTimeMixin ],

    directives: {
      TouchPan: TouchPan
    },

    props: {
      mask: {
        default: null
      },

      format24h: {
        type: Boolean,
        default: null
      },

      options: Function,
      hourOptions: Array,
      minuteOptions: Array,
      secondOptions: Array,

      withSeconds: Boolean,
      nowBtn: Boolean
    },

    data: function data () {
      var model = __splitDate(
        this.value,
        this.__getComputedMask(),
        this.__getComputedLocale(),
        this.calendar
      );

      var view = 'Hour';

      if (model.hour !== null) {
        if (model.minute === null) {
          view = 'Minute';
        }
        else if (this.withSeconds === true && model.second === null) {
          view = 'Second';
        }
      }

      return {
        view: view,
        isAM: model.hour === null || model.hour < 12,
        innerModel: model
      }
    },

    watch: {
      value: function value (v) {
        var model = __splitDate(v, this.computedMask, this.computedLocale, this.calendar);

        if (
          model.dateHash !== this.innerModel.dateHash ||
          model.timeHash !== this.innerModel.timeHash
        ) {
          this.innerModel = model;

          if (model.hour === null) {
            this.view = 'Hour';
          }
          else {
            this.isAM = model.hour < 12;
          }
        }
      }
    },

    computed: {
      classes: function classes () {
        var obj;

        return ( obj = {
          'q-time--dark': this.dark,
          'q-time--readonly': this.readonly,
          'disabled': this.disable
        }, obj[("q-time--" + (this.landscape === true ? 'landscape' : 'portrait'))] = true, obj )
      },

      computedMask: function computedMask () {
        return this.__getComputedMask()
      },

      stringModel: function stringModel () {
        var time = this.innerModel;

        return {
          hour: time.hour === null
            ? '--'
            : (
              this.computedFormat24h === true
                ? pad(time.hour)
                : String(
                  this.isAM === true
                    ? (time.hour === 0 ? 12 : time.hour)
                    : (time.hour > 12 ? time.hour - 12 : time.hour)
                )
            ),
          minute: time.minute === null
            ? '--'
            : pad(time.minute),
          second: time.second === null
            ? '--'
            : pad(time.second)
        }
      },

      computedFormat24h: function computedFormat24h () {
        return this.format24h !== null
          ? this.format24h
          : this.$q.lang.date.format24h
      },

      pointerStyle: function pointerStyle () {
        var
          forHour = this.view === 'Hour',
          divider = forHour === true ? 12 : 60,
          amount = this.innerModel[this.view.toLowerCase()],
          degrees = Math.round(amount * (360 / divider)) - 180;

        var transform = "rotate3d(0,0,1," + degrees + "deg) translate3d(-50%,0,0)";

        if (
          forHour === true &&
          this.computedFormat24h === true &&
          !(this.innerModel.hour > 0 && this.innerModel.hour < 13)
        ) {
          transform += ' scale3d(.7,.7,.7)';
        }

        return { transform: transform }
      },

      minLink: function minLink () {
        return this.innerModel.hour !== null
      },

      secLink: function secLink () {
        return this.minLink === true && this.innerModel.minute !== null
      },

      hourInSelection: function hourInSelection () {
        var this$1 = this;

        return this.hourOptions !== void 0
          ? function (val) { return this$1.hourOptions.includes(val); }
          : (
            this.options !== void 0
              ? function (val) { return this$1.options(val, null, null); }
              : void 0
          )
      },

      minuteInSelection: function minuteInSelection () {
        var this$1 = this;

        return this.minuteOptions !== void 0
          ? function (val) { return this$1.minuteOptions.includes(val); }
          : (
            this.options !== void 0
              ? function (val) { return this$1.options(this$1.innerModel.hour, val, null); }
              : void 0
          )
      },

      secondInSelection: function secondInSelection () {
        var this$1 = this;

        return this.secondOptions !== void 0
          ? function (val) { return this$1.secondOptions.includes(val); }
          : (
            this.options !== void 0
              ? function (val) { return this$1.options(this$1.innerModel.hour, this$1.innerModel.minute, val); }
              : void 0
          )
      },

      positions: function positions () {
        var start, end, offset = 0, step = 1, inSel;

        if (this.view === 'Hour') {
          inSel = this.hourInSelection;

          if (this.computedFormat24h === true) {
            start = 0;
            end = 23;
          }
          else {
            start = 0;
            end = 11;

            if (this.isAM === false) {
              offset = 12;
            }
          }
        }
        else {
          start = 0;
          end = 55;
          step = 5;

          if (this.view === 'Minute') {
            inSel = this.minuteInSelection;
          }
          else {
            inSel = this.secondInSelection;
          }
        }

        var pos = [];

        for (var val = start, index = start; val <= end; val += step, index++) {
          var
            actualVal = val + offset,
            disable = inSel !== void 0 && inSel(actualVal) === false,
            label = this.view === 'Hour' && val === 0
              ? (this.format24h === true ? '00' : '12')
              : val;

          pos.push({ val: actualVal, index: index, disable: disable, label: label });
        }

        return pos
      }
    },

    methods: {
      __click: function __click (evt) {
        this.__drag({ isFirst: true, evt: evt });
        this.__drag({ isFinal: true, evt: evt });
      },

      __drag: function __drag (event) {
        // cases when on a popup getting closed
        // on previously emitted value
        if (this._isBeingDestroyed === true || this._isDestroyed === true) {
          return
        }

        if (event.isFirst) {
          var
            clock = this.$refs.clock;
          var ref = clock.getBoundingClientRect();
          var top = ref.top;
          var left = ref.left;
          var width = ref.width;
          var dist = width / 2;

          this.dragging = {
            top: top + dist,
            left: left + dist,
            dist: dist * 0.7
          };
          this.dragCache = null;
          this.__updateClock(event.evt);
          return
        }

        this.__updateClock(event.evt);

        if (event.isFinal) {
          this.dragging = false;

          if (this.view === 'Hour') {
            this.view = 'Minute';
          }
          else if (this.withSeconds && this.view === 'Minute') {
            this.view = 'Second';
          }
        }
      },

      __updateClock: function __updateClock (evt) {
        var
          val,
          pos = position(evt),
          height = Math.abs(pos.top - this.dragging.top),
          distance = Math.sqrt(
            Math.pow(Math.abs(pos.top - this.dragging.top), 2) +
            Math.pow(Math.abs(pos.left - this.dragging.left), 2)
          ),
          angle = Math.asin(height / distance) * (180 / Math.PI);

        if (pos.top < this.dragging.top) {
          angle = this.dragging.left < pos.left ? 90 - angle : 270 + angle;
        }
        else {
          angle = this.dragging.left < pos.left ? angle + 90 : 270 - angle;
        }

        if (this.view === 'Hour') {
          val = Math.round(angle / 30);

          if (this.computedFormat24h === true) {
            if (distance < this.dragging.dist) {
              if (val !== 0) {
                val += 12;
              }
            }
            else if (val === 0) {
              val = 12;
            }
          }
          else if (this.isAM === true && val === 12) {
            val = 0;
          }
          else if (this.isAM === false && val !== 12) {
            val += 12;
          }

          if (val === 24) {
            val = 0;
          }
        }
        else {
          val = Math.round(angle / 6);

          if (val === 60) {
            val = 0;
          }
        }

        if (this.dragCache === val) {
          return
        }

        var opt = this[((this.view.toLowerCase()) + "InSelection")];

        if (opt !== void 0 && opt(val) !== true) {
          return
        }

        this.dragCache = val;
        this[("__set" + (this.view))](val);
      },

      __onKeyupHour: function __onKeyupHour (e) {
        if (e.keyCode === 13) { // ENTER
          this.view = 'Hour';
        }
        else {
          var
            wrap = this.computedFormat24h === true ? 24 : 12,
            offset = this.computedFormat24h !== true && this.isAM === false ? 12 : 0;

          if (e.keyCode === 37) { // ARROW LEFT
            this.__setHour(offset + (24 + this.innerModel.hour - 1) % wrap);
          }
          else if (e.keyCode === 39) { // ARROW RIGHT
            this.__setHour(offset + (24 + this.innerModel.hour + 1) % wrap);
          }
        }
      },

      __onKeyupMinute: function __onKeyupMinute (e) {
        if (e.keyCode === 13) { // ENTER
          this.view = 'Minute';
        }
        else if (e.keyCode === 37) { // ARROW LEFT
          this.__setMinute((60 + this.innerModel.minute - 1) % 60);
        }
        else if (e.keyCode === 39) { // ARROW RIGHT
          this.__setMinute((60 + this.innerModel.minute + 1) % 60);
        }
      },

      __onKeyupSecond: function __onKeyupSecond (e) {
        if (e.keyCode === 13) { // ENTER
          this.view = 'Second';
        }
        else if (e.keyCode === 37) { // ARROW LEFT
          this.__setSecond((60 + this.innerModel.second - 1) % 60);
        }
        else if (e.keyCode === 39) { // ARROW RIGHT
          this.__setSecond((60 + this.innerModel.second + 1) % 60);
        }
      },

      __getHeader: function __getHeader (h) {
        var this$1 = this;

        var label = [
          h('div', {
            staticClass: 'q-time__link',
            class: this.view === 'Hour' ? 'q-time__link--active' : 'cursor-pointer',
            attrs: { tabindex: this.computedTabindex },
            on: {
              click: function () { this$1.view = 'Hour'; },
              keyup: this.__onKeyupHour
            }
          }, [ this.stringModel.hour ]),
          h('div', [ ':' ]),
          h(
            'div',
            this.minLink === true
              ? {
                staticClass: 'q-time__link',
                class: this.view === 'Minute' ? 'q-time__link--active' : 'cursor-pointer',
                attrs: { tabindex: this.computedTabindex },
                on: {
                  click: function () { this$1.view = 'Minute'; },
                  keyup: this.__onKeyupMinute
                }
              }
              : { staticClass: 'q-time__link' },
            [ this.stringModel.minute ]
          )
        ];

        if (this.withSeconds === true) {
          label.push(
            h('div', [ ':' ]),
            h(
              'div',
              this.secLink === true
                ? {
                  staticClass: 'q-time__link',
                  class: this.view === 'Second' ? 'q-time__link--active' : 'cursor-pointer',
                  attrs: { tabindex: this.computedTabindex },
                  on: {
                    click: function () { this$1.view = 'Second'; },
                    keyup: this.__onKeyupSecond
                  }
                }
                : { staticClass: 'q-time__link' },
              [ this.stringModel.second ]
            )
          );
        }

        return h('div', {
          staticClass: 'q-time__header flex flex-center no-wrap',
          class: this.headerClass
        }, [
          h('div', {
            staticClass: 'q-time__header-label row items-center no-wrap',
            attrs: { dir: 'ltr' }
          }, label),

          this.computedFormat24h === false ? h('div', {
            staticClass: 'q-time__header-ampm column items-between no-wrap'
          }, [
            h('div', {
              staticClass: 'q-time__link',
              class: this.isAM === true ? 'q-time__link--active' : 'cursor-pointer',
              attrs: { tabindex: this.computedTabindex },
              on: {
                click: this.__setAm,
                keyup: function (e) { e.keyCode === 13 && this$1.__setAm(); }
              }
            }, [ 'AM' ]),

            h('div', {
              staticClass: 'q-time__link',
              class: this.isAM !== true ? 'q-time__link--active' : 'cursor-pointer',
              attrs: { tabindex: this.computedTabindex },
              on: {
                click: this.__setPm,
                keyup: function (e) { e.keyCode === 13 && this$1.__setPm(); }
              }
            }, [ 'PM' ])
          ]) : null
        ])
      },

      __getClock: function __getClock (h) {
        var this$1 = this;

        var
          view = this.view.toLowerCase(),
          current = this.innerModel[view],
          f24 = this.view === 'Hour' && this.computedFormat24h === true
            ? ' fmt24'
            : '';

        return h('div', {
          staticClass: 'q-time__content col relative-position'
        }, [
          h('transition', {
            props: { name: 'q-transition--scale' }
          }, [
            h('div', {
              key: 'clock' + this.view,
              staticClass: 'q-time__container-parent absolute-full'
            }, [
              h('div', {
                ref: 'clock',
                staticClass: 'q-time__container-child fit overflow-hidden'
              }, [
                h('div', {
                  staticClass: 'q-time__clock cursor-pointer non-selectable',
                  on: {
                    click: this.__click
                  },
                  directives: [{
                    name: 'touch-pan',
                    value: this.__drag,
                    modifiers: {
                      stop: true,
                      prevent: true,
                      mouse: true
                    }
                  }]
                }, [
                  h('div', { staticClass: 'q-time__clock-circle fit' }, [
                    this.innerModel[view] !== null
                      ? h('div', {
                        staticClass: 'q-time__clock-pointer',
                        style: this.pointerStyle,
                        class: this.color !== void 0 ? ("text-" + (this.color)) : null
                      })
                      : null,

                    this.positions.map(function (pos) { return h('div', {
                      staticClass: ("q-time__clock-position row flex-center" + f24 + " q-time__clock-pos-" + (pos.index)),
                      class: pos.val === current
                        ? this$1.headerClass.concat(' q-time__clock-position--active')
                        : (pos.disable ? 'q-time__clock-position--disable' : null)
                    }, [ h('span', [ pos.label ]) ]); })
                  ])
                ])
              ])
            ])
          ]),

          this.nowBtn === true ? h(QBtn, {
            staticClass: 'q-time__now-button absolute',
            props: {
              icon: this.$q.iconSet.datetime.now,
              unelevated: true,
              size: 'sm',
              round: true,
              color: this.color,
              textColor: this.textColor,
              tabindex: this.computedTabindex
            },
            on: {
              click: this.__setNow
            }
          }) : null
        ])
      },

      __setHour: function __setHour (hour) {
        if (this.innerModel.hour !== hour) {
          this.innerModel.hour = hour;
          this.innerModel.minute = null;
          this.innerModel.second = null;
        }
      },

      __setMinute: function __setMinute (minute) {
        if (this.innerModel.minute !== minute) {
          this.innerModel.minute = minute;
          this.innerModel.second = null;
          this.withSeconds !== true && this.__updateValue({ minute: minute });
        }
      },

      __setSecond: function __setSecond (second) {
        this.innerModel.second !== second && this.__updateValue({ second: second });
      },

      __setAm: function __setAm () {
        if (this.isAM) { return }

        this.isAM = true;

        if (this.innerModel.hour === null) { return }
        this.innerModel.hour -= 12;
        this.__verifyAndUpdate();
      },

      __setPm: function __setPm () {
        if (!this.isAM) { return }

        this.isAM = false;

        if (this.innerModel.hour === null) { return }
        this.innerModel.hour += 12;
        this.__verifyAndUpdate();
      },

      __setNow: function __setNow () {
        this.__updateValue(Object.assign({}, this.__getCurrentDate(),
          this.__getCurrentTime()));
        this.view = 'Hour';
      },

      __verifyAndUpdate: function __verifyAndUpdate () {
        if (this.hourInSelection !== void 0 && this.hourInSelection(this.innerModel.hour) !== true) {
          this.innerModel = __splitDate();
          this.isAM = true;
          this.view = 'Hour';
          return
        }

        if (this.minuteInSelection !== void 0 && this.minuteInSelection(this.innerModel.minute) !== true) {
          this.innerModel.minute = null;
          this.innerModel.second = null;
          this.view = 'Minute';
          return
        }

        if (this.withSeconds === true && this.secondInSelection !== void 0 && this.secondInSelection(this.innerModel.second) !== true) {
          this.innerModel.second = null;
          this.view = 'Second';
          return
        }

        if (this.innerModel.hour === null || this.innerModel.minute === null || (this.withSeconds === true && this.innerModel.second === null)) {
          return
        }

        this.__updateValue({});
      },

      __getComputedMask: function __getComputedMask () {
        return this.calendar !== 'persian' && this.mask !== null
          ? this.mask
          : ("HH:mm" + (this.withSeconds === true ? ':ss' : ''))
      },

      __updateValue: function __updateValue (obj) {
        var date = Object.assign({}, this.innerModel,
          obj);

        var val = this.calendar === 'persian'
          ? pad(date.hour) + ':' +
            pad(date.minute) +
            (this.withSeconds === true ? ':' + pad(date.second) : '')
          : formatDate(
            new Date(
              date.year,
              date.month === null ? null : date.month - 1,
              date.day,
              date.hour,
              date.minute,
              date.second,
              date.millisecond
            ),
            this.computedMask,
            this.computedLocale,
            date.year
          );

        if (val !== this.value) {
          this.$emit('input', val);
        }
      }
    },

    render: function render (h) {
      return h('div', {
        staticClass: 'q-time',
        class: this.classes,
        on: this.$listeners,
        attrs: { tabindex: -1 }
      }, [
        this.__getHeader(h),
        this.__getClock(h)
      ])
    }
  });

  var registered = 0;

  function onWheel (e) {
    if (shouldPreventScroll(e)) {
      stopAndPrevent(e);
    }
  }

  function shouldPreventScroll (e) {
    if (e.target === document.body || e.target.classList.contains('q-layout__backdrop')) {
      return true
    }

    var
      path = getEventPath(e),
      shift = e.shiftKey && !e.deltaX,
      scrollY = !shift && Math.abs(e.deltaX) <= Math.abs(e.deltaY),
      delta = shift || scrollY ? e.deltaY : e.deltaX;

    for (var index = 0; index < path.length; index++) {
      var el = path[index];

      if (hasScrollbar(el, scrollY)) {
        return scrollY
          ? (
            delta < 0 && el.scrollTop === 0
              ? true
              : delta > 0 && el.scrollTop + el.clientHeight === el.scrollHeight
          )
          : (
            delta < 0 && el.scrollLeft === 0
              ? true
              : delta > 0 && el.scrollLeft + el.clientWidth === el.scrollWidth
          )
      }
    }

    return true
  }

  function prevent$1 (register) {
    registered += register ? 1 : -1;
    if (registered > 1) { return }

    var action = register ? 'add' : 'remove';

    if (Platform.is.mobile) {
      document.body.classList[action]('q-body--prevent-scroll');
    }
    else if (Platform.is.desktop) {
      // ref. https://developers.google.com/web/updates/2017/01/scrolling-intervention
      window[(action + "EventListener")]('wheel', onWheel, listenOpts.notPassive);
    }
  }

  var PreventScrollMixin = {
    methods: {
      __preventScroll: function __preventScroll (state) {
        if (this.preventedScroll === void 0 && state !== true) {
          return
        }

        if (state !== this.preventedScroll) {
          this.preventedScroll = state;
          prevent$1(state);
        }
      }
    }
  };

  var maximizedModals = 0;

  var positionClass = {
    standard: 'fixed-full flex-center',
    top: 'fixed-top justify-center',
    bottom: 'fixed-bottom justify-center',
    right: 'fixed-right items-center',
    left: 'fixed-left items-center'
  };

  var transitions = {
    top: ['down', 'up'],
    bottom: ['up', 'down'],
    right: ['left', 'right'],
    left: ['right', 'left']
  };

  var QDialog = Vue.extend({
    name: 'QDialog',

    mixins: [ ModelToggleMixin, PortalMixin, PreventScrollMixin ],

    modelToggle: {
      history: true
    },

    props: {
      persistent: Boolean,
      autoClose: Boolean,

      noEscDismiss: Boolean,
      noBackdropDismiss: Boolean,
      noRouteDismiss: Boolean,
      noRefocus: Boolean,
      noFocus: Boolean,

      seamless: Boolean,

      maximized: Boolean,
      fullWidth: Boolean,
      fullHeight: Boolean,

      square: Boolean,

      position: {
        type: String,
        default: 'standard',
        validator: function validator (val) {
          return val === 'standard' || ['top', 'bottom', 'left', 'right'].includes(val)
        }
      },

      transitionShow: {
        type: String,
        default: 'scale'
      },
      transitionHide: {
        type: String,
        default: 'scale'
      }
    },

    data: function data () {
      return {
        transitionState: this.showing
      }
    },

    watch: {
      $route: function $route () {
        this.persistent !== true &&
          this.noRouteDismiss !== true &&
          this.seamless !== true &&
          this.hide();
      },

      showing: function showing (val) {
        var this$1 = this;

        if (this.position !== 'standard' || this.transitionShow !== this.transitionHide) {
          this.$nextTick(function () {
            this$1.transitionState = val;
          });
        }
      },

      maximized: function maximized (newV, oldV) {
        if (this.showing === true) {
          this.__updateState(false, oldV);
          this.__updateState(true, newV);
        }
      },

      seamless: function seamless (v) {
        this.showing === true && this.__preventScroll(!v);
      },

      useBackdrop: function useBackdrop (v) {
        if (this.$q.platform.is.desktop === true) {
          var action = (v === true ? 'add' : 'remove') + "EventListener";
          document.body[action]('focusin', this.__onFocusChange);
        }
      }
    },

    computed: {
      classes: function classes () {
        return "q-dialog__inner--" + (this.maximized === true ? 'maximized' : 'minimized') + " " +
          "q-dialog__inner--" + (this.position) + " " + (positionClass[this.position]) +
          (this.fullWidth === true ? ' q-dialog__inner--fullwidth' : '') +
          (this.fullHeight === true ? ' q-dialog__inner--fullheight' : '') +
          (this.square === true ? ' q-dialog__inner--square' : '')
      },

      transition: function transition () {
        return 'q-transition--' + (
          this.position === 'standard'
            ? (this.transitionState === true ? this.transitionHide : this.transitionShow)
            : 'slide-' + transitions[this.position][this.transitionState === true ? 1 : 0]
        )
      },

      useBackdrop: function useBackdrop () {
        return this.showing === true && this.seamless !== true
      }
    },

    methods: {
      focus: function focus () {
        var node = this.__getInnerNode();

        if (node === void 0 || node.contains(document.activeElement) === true) {
          return
        }

        if (this.$q.platform.is.ios) {
          // workaround the iOS hover/touch issue
          this.avoidAutoClose = true;
          node.click();
          this.avoidAutoClose = false;
        }

        node = node.querySelector('[autofocus]') || node;
        node.focus();
      },

      shake: function shake () {
        this.focus();

        var node = this.__getInnerNode();

        if (node !== void 0) {
          node.classList.remove('q-animate--scale');
          node.classList.add('q-animate--scale');
          clearTimeout(this.shakeTimeout);
          this.shakeTimeout = setTimeout(function () {
            node.classList.remove('q-animate--scale');
          }, 170);
        }
      },

      __getInnerNode: function __getInnerNode () {
        return this.__portal !== void 0 && this.__portal.$refs !== void 0
          ? this.__portal.$refs.inner
          : void 0
      },

      __show: function __show (evt) {
        var this$1 = this;

        clearTimeout(this.timer);

        this.__refocusTarget = this.noRefocus === false
          ? document.activeElement
          : void 0;

        this.$el.dispatchEvent(create('popup-show', { bubbles: true }));

        this.__updateState(true, this.maximized);

        EscapeKey.register(this, function () {
          if (this$1.seamless !== true) {
            if (this$1.persistent === true || this$1.noEscDismiss === true) {
              this$1.maximized !== true && this$1.shake();
            }
            else {
              this$1.$emit('escape-key');
              this$1.hide();
            }
          }
        });

        this.__showPortal();

        if (this.noFocus !== true) {
          document.activeElement.blur();

          this.$nextTick(function () {
            this$1.focus();
          });
        }

        if (this.$q.platform.is.desktop === true && this.useBackdrop === true) {
          document.body.addEventListener('focusin', this.__onFocusChange);
        }

        this.timer = setTimeout(function () {
          this$1.$emit('show', evt);
        }, 300);
      },

      __hide: function __hide (evt) {
        var this$1 = this;

        this.__cleanup(true);

        if (this.__refocusTarget !== void 0) {
          this.__refocusTarget.focus();
        }

        this.$el.dispatchEvent(create('popup-hide', { bubbles: true }));

        this.timer = setTimeout(function () {
          this$1.__hidePortal();
          this$1.$emit('hide', evt);
        }, 300);
      },

      __cleanup: function __cleanup (hiding) {
        clearTimeout(this.timer);
        clearTimeout(this.shakeTimeout);

        if (this.$q.platform.is.desktop === true && this.seamless !== true) {
          document.body.removeEventListener('focusin', this.__onFocusChange);
        }

        if (hiding === true || this.showing === true) {
          EscapeKey.pop(this);
          this.__updateState(false, this.maximized);
        }
      },

      __updateState: function __updateState (opening, maximized) {
        if (this.seamless !== true) {
          this.__preventScroll(opening);
        }

        if (maximized === true) {
          if (opening === true) {
            maximizedModals < 1 && document.body.classList.add('q-body--dialog');
          }
          else if (maximizedModals < 2) {
            document.body.classList.remove('q-body--dialog');
          }
          maximizedModals += opening === true ? 1 : -1;
        }
      },

      __onAutoClose: function __onAutoClose (e) {
        if (this.avoidAutoClose !== true) {
          this.hide(e);
          this.$listeners.click !== void 0 && this.$emit('click', e);
        }
      },

      __onBackdropClick: function __onBackdropClick (e) {
        if (this.persistent !== true && this.noBackdropDismiss !== true) {
          this.hide(e);
        }
        else {
          this.shake();
        }
      },

      __onFocusChange: function __onFocusChange (e) {
        var node = this.__getInnerNode();

        if (
          node !== void 0 &&
          this.__portal.$el !== void 0 &&
          // we don't have another portal opened:
          this.__portal.$el.nextElementSibling === null &&
          this.__portal.$el.contains(e.target) !== true
        ) {
          node.focus();
        }
      },

      __render: function __render (h) {
        var on = Object.assign({}, this.$listeners,
          {input: stop});

        if (this.autoClose === true) {
          on.click = this.__onAutoClose;
        }

        return h('div', {
          staticClass: 'q-dialog fullscreen no-pointer-events',
          class: this.contentClass,
          style: this.contentStyle,
          attrs: this.$attrs
        }, [
          h('transition', {
            props: { name: 'q-transition--fade' }
          }, this.useBackdrop === true ? [
            h('div', {
              staticClass: 'q-dialog__backdrop fixed-full',
              on: {
                touchmove: stopAndPrevent, // prevent iOS page scroll
                click: this.__onBackdropClick
              }
            })
          ] : null),

          h('transition', {
            props: { name: this.transition }
          }, [
            this.showing === true ? h('div', {
              ref: 'inner',
              staticClass: 'q-dialog__inner flex no-pointer-events',
              class: this.classes,
              attrs: { tabindex: -1 },
              on: on
            }, slot(this, 'default')) : null
          ])
        ])
      },

      __onPortalClose: function __onPortalClose (evt) {
        this.hide(evt);
      }
    },

    mounted: function mounted () {
      this.value === true && this.show();
    },

    beforeDestroy: function beforeDestroy () {
      this.__cleanup();
    }
  });

  var ValidateMixin = {
    props: {
      value: {},

      error: {
        type: Boolean,
        default: null
      },
      errorMessage: String,
      noErrorIcon: Boolean,

      rules: Array,
      lazyRules: Boolean
    },

    data: function data () {
      return {
        isDirty: false,
        innerError: false,
        innerErrorMessage: void 0
      }
    },

    watch: {
      value: function value (v) {
        if (this.rules === void 0) {
          return
        }
        if (this.lazyRules === true && this.isDirty === false) {
          return
        }

        this.validate(v);
      },

      focused: function focused (focused$1) {
        focused$1 === false && this.__triggerValidation();
      }
    },

    computed: {
      hasError: function hasError () {
        return this.error === true || this.innerError === true
      },

      computedErrorMessage: function computedErrorMessage () {
        return typeof this.errorMessage === 'string' && this.errorMessage.length > 0
          ? this.errorMessage
          : this.innerErrorMessage
      }
    },

    mounted: function mounted () {
      this.validateIndex = 0;
      this.focused === void 0 && this.$el.addEventListener('focusout', this.__triggerValidation);
    },

    beforeDestroy: function beforeDestroy () {
      this.focused === void 0 && this.$el.removeEventListener('focusout', this.__triggerValidation);
    },

    methods: {
      resetValidation: function resetValidation () {
        this.validateIndex++;
        this.innerLoading = false;
        this.isDirty = false;
        this.innerError = false;
        this.innerErrorMessage = void 0;
      },

      /*
       * Return value
       *   - true (validation succeeded)
       *   - false (validation failed)
       *   - Promise (pending async validation)
       */
      validate: function validate (val) {
        var this$1 = this;
        if ( val === void 0 ) val = this.value;

        if (!this.rules || this.rules.length === 0) {
          return true
        }

        this.validateIndex++;

        if (this.innerLoading !== true && this.lazyRules !== true) {
          this.isDirty = true;
        }

        var update = function (err, msg) {
          if (this$1.innerError !== err) {
            this$1.innerError = err;
          }

          var m = msg || void 0;
          if (this$1.innerErrorMessage !== m) {
            this$1.innerErrorMessage = m;
          }

          if (this$1.innerLoading !== false) {
            this$1.innerLoading = false;
          }
        };

        var promises = [];

        for (var i = 0; i < this.rules.length; i++) {
          var rule = this.rules[i];
          var res = (void 0);

          if (typeof rule === 'function') {
            res = rule(val);
          }
          else if (typeof rule === 'string' && testPattern[rule] !== void 0) {
            res = testPattern[rule](val);
          }

          if (res === false || typeof res === 'string') {
            update(true, res);
            return false
          }
          else if (res !== true && res !== void 0) {
            promises.push(res);
          }
        }

        if (promises.length === 0) {
          update(false);
          return true
        }

        if (this.innerLoading !== true) {
          this.innerLoading = true;
        }

        var index = this.validateIndex;

        return Promise.all(promises).then(
          function (res) {
            if (index === this$1.validateIndex) {
              if (res === void 0 || Array.isArray(res) === false || res.length === 0) {
                update(false);
                return true
              }
              else {
                var msg = res.find(function (r) { return r === false || typeof r === 'string'; });
                update(msg !== void 0, msg);
                return msg === void 0
              }
            }
            return true
          },
          function (e) {
            if (index === this$1.validateIndex) {
              console.error(e);
              update(true);
              return false
            }
          }
        )
      },

      __triggerValidation: function __triggerValidation () {
        if (this.isDirty === false && this.rules !== void 0) {
          this.isDirty = true;
          this.validate(this.value);
        }
      }
    }
  };

  var QField = Vue.extend({
    name: 'QField',

    inheritAttrs: false,

    mixins: [ ValidateMixin ],

    props: {
      label: String,
      stackLabel: Boolean,
      hint: String,
      hideHint: Boolean,
      prefix: String,
      suffix: String,

      color: String,
      bgColor: String,
      dark: Boolean,

      filled: Boolean,
      outlined: Boolean,
      borderless: Boolean,
      standout: [Boolean, String],

      square: Boolean,

      loading: Boolean,

      bottomSlots: Boolean,
      hideBottomSpace: Boolean,

      rounded: Boolean,
      dense: Boolean,
      itemAligned: Boolean,

      counter: Boolean,

      clearable: Boolean,
      clearIcon: String,

      disable: Boolean,
      readonly: Boolean,

      autofocus: Boolean,

      maxlength: [Number, String],
      maxValues: [Number, String] // do not add to JSON, internally needed by QSelect
    },

    data: function data () {
      return {
        focused: false,

        // used internally by validation for QInput
        // or menu handling for QSelect
        innerLoading: false
      }
    },

    computed: {
      editable: function editable () {
        return this.disable !== true && this.readonly !== true
      },

      hasValue: function hasValue () {
        var value = this.__getControl === void 0 ? this.value : this.innerValue;

        return value !== void 0 &&
          value !== null &&
          ('' + value).length > 0
      },

      computedCounter: function computedCounter () {
        if (this.counter !== false) {
          var len = typeof this.value === 'string' || typeof this.value === 'number'
            ? ('' + this.value).length
            : (Array.isArray(this.value) === true ? this.value.length : 0);
          var max = this.maxlength !== void 0 ? this.maxlength : this.maxValues;

          return len + (max !== void 0 ? ' / ' + max : '')
        }
      },

      floatingLabel: function floatingLabel () {
        return this.hasError === true ||
          this.stackLabel === true ||
          this.focused === true ||
          (
            this.inputValue !== void 0 && this.hideSelected === true
              ? this.inputValue.length > 0
              : this.hasValue === true
          ) ||
          (
            this.displayValue !== void 0 &&
            this.displayValue !== null &&
            ('' + this.displayValue).length > 0
          )
      },

      shouldRenderBottom: function shouldRenderBottom () {
        return this.bottomSlots === true ||
          this.hint !== void 0 ||
          this.rules !== void 0 ||
          this.counter === true ||
          this.error !== null
      },

      classes: function classes () {
        var obj;

        return ( obj = {}, obj[this.fieldClass] = this.fieldClass !== void 0, obj[("q-field--" + (this.styleType))] = true, obj['q-field--rounded'] = this.rounded, obj['q-field--square'] = this.square, obj['q-field--focused'] = this.focused === true || this.hasError === true, obj['q-field--float'] = this.floatingLabel, obj['q-field--labeled'] = this.label !== void 0, obj['q-field--dense'] = this.dense, obj['q-field--item-aligned q-item-type'] = this.itemAligned, obj['q-field--dark'] = this.dark, obj['q-field--auto-height'] = this.__getControl === void 0, obj['q-field--with-bottom'] = this.hideBottomSpace !== true && this.shouldRenderBottom === true, obj['q-field--error'] = this.hasError, obj['q-field--readonly'] = this.readonly, obj['q-field--disabled'] = this.disable, obj )
      },

      styleType: function styleType () {
        if (this.filled === true) { return 'filled' }
        if (this.outlined === true) { return 'outlined' }
        if (this.borderless === true) { return 'borderless' }
        if (this.standout) { return 'standout' }
        return 'standard'
      },

      contentClass: function contentClass () {
        var cls = [];

        if (this.hasError === true) {
          cls.push('text-negative');
        }
        else if (typeof this.standout === 'string' && this.standout.length > 0 && this.focused === true) {
          return this.standout
        }
        else if (this.color !== void 0) {
          cls.push('text-' + this.color);
        }

        if (this.bgColor !== void 0) {
          cls.push(("bg-" + (this.bgColor)));
        }

        return cls
      }
    },

    methods: {
      focus: function focus () {
        if (this.showPopup !== void 0 && this.$q.platform.is.desktop !== true) {
          this.showPopup();
          return
        }

        this.__focus();
      },

      blur: function blur () {
        var el = document.activeElement;
        this.$el.contains(el) && el.blur();
      },

      __focus: function __focus () {
        var target = this.$refs.target;
        if (target !== void 0) {
          target.matches('[tabindex]') || (target = target.querySelector('[tabindex]'));
          target !== null && target.focus();
        }
      },

      __getContent: function __getContent (h) {
        var node = [];

        this.$scopedSlots.prepend !== void 0 && node.push(
          h('div', {
            staticClass: 'q-field__prepend q-field__marginal row no-wrap items-center',
            key: 'prepend'
          }, this.$scopedSlots.prepend())
        );

        node.push(
          h('div', {
            staticClass: 'q-field__control-container col relative-position row no-wrap q-anchor--skip'
          }, this.__getControlContainer(h))
        );

        this.$scopedSlots.append !== void 0 && node.push(
          h('div', {
            staticClass: 'q-field__append q-field__marginal row no-wrap items-center',
            key: 'append'
          }, this.$scopedSlots.append())
        );

        this.hasError === true && this.noErrorIcon === false && node.push(
          this.__getInnerAppendNode(h, 'error', [
            h(QIcon, { props: { name: this.$q.iconSet.field.error, color: 'negative' } })
          ])
        );

        if (this.loading === true || this.innerLoading === true) {
          node.push(
            this.__getInnerAppendNode(
              h,
              'inner-loading-append',
              this.$scopedSlots.loading !== void 0
                ? this.$scopedSlots.loading()
                : [ h(QSpinner, { props: { color: this.color } }) ]
            )
          );
        }
        else if (this.clearable === true && this.hasValue === true && this.editable === true) {
          node.push(
            this.__getInnerAppendNode(h, 'inner-clearable-append', [
              h(QIcon, {
                staticClass: 'cursor-pointer',
                props: { name: this.clearIcon || this.$q.iconSet.field.clear },
                on: {
                  click: this.__clearValue
                }
              })
            ])
          );
        }

        this.__getInnerAppend !== void 0 && node.push(
          this.__getInnerAppendNode(h, 'inner-append', this.__getInnerAppend(h))
        );

        this.__getPopup !== void 0 && node.push(
          this.__getPopup(h)
        );

        return node
      },

      __getControlContainer: function __getControlContainer (h) {
        var node = [];

        this.prefix !== void 0 && this.prefix !== null && node.push(
          h('div', {
            staticClass: 'q-field__prefix no-pointer-events row items-center'
          }, [ this.prefix ])
        );

        if (this.__getControl !== void 0) {
          node.push(
            this.__getControl(h)
          );
        }
        // internal usage only:
        else if (this.$scopedSlots.rawControl !== void 0) {
          node.push(this.$scopedSlots.rawControl());
        }
        else if (this.$scopedSlots.control !== void 0) {
          node.push(
            h('div', {
              ref: 'target',
              staticClass: 'q-field__native row',
              attrs: Object.assign({}, this.$attrs,
                {autofocus: this.autofocus})
            }, this.$scopedSlots.control())
          );
        }

        this.label !== void 0 && node.push(
          h('div', {
            staticClass: 'q-field__label no-pointer-events absolute ellipsis'
          }, [ this.label ])
        );

        this.suffix !== void 0 && this.suffix !== null && node.push(
          h('div', {
            staticClass: 'q-field__suffix no-pointer-events row items-center'
          }, [ this.suffix ])
        );

        return node.concat(
          this.__getDefaultSlot !== void 0
            ? this.__getDefaultSlot(h)
            : slot(this, 'default')
        )
      },

      __getBottom: function __getBottom (h) {
        var msg, key;

        if (this.hasError === true) {
          if (this.computedErrorMessage !== void 0) {
            msg = [ h('div', [ this.computedErrorMessage ]) ];
            key = this.computedErrorMessage;
          }
          else {
            msg = slot(this, 'error');
            key = 'q--slot-error';
          }
        }
        else if (this.hideHint !== true || this.focused === true) {
          if (this.hint !== void 0) {
            msg = [ h('div', [ this.hint ]) ];
            key = this.hint;
          }
          else {
            msg = slot(this, 'hint');
            key = 'q--slot-hint';
          }
        }

        var hasCounter = this.counter === true || this.$scopedSlots.counter !== void 0;

        if (this.hideBottomSpace === true && hasCounter === false && msg === void 0) {
          return
        }

        var main = h('div', {
          key: key,
          staticClass: 'q-field__messages col'
        }, msg);

        return h('div', {
          staticClass: 'q-field__bottom row items-start q-field__bottom--' +
            (this.hideBottomSpace !== true ? 'animated' : 'stale')
        }, [
          this.hideBottomSpace === true
            ? main
            : h('transition', { props: { name: 'q-transition--field-message' } }, [
              main
            ]),

          hasCounter === true
            ? h('div', {
              staticClass: 'q-field__counter'
            }, this.$scopedSlots.counter !== void 0 ? this.$scopedSlots.counter() : [ this.computedCounter ])
            : null
        ])
      },

      __getInnerAppendNode: function __getInnerAppendNode (h, key, content) {
        return content === null ? null : h('div', {
          staticClass: 'q-field__append q-field__marginal row no-wrap items-center q-anchor--skip',
          key: key
        }, content)
      },

      __onControlPopupShow: function __onControlPopupShow (e) {
        this.hasPopupOpen = true;
        this.__onControlFocusin(e);
      },

      __onControlPopupHide: function __onControlPopupHide (e) {
        this.hasPopupOpen = false;
        this.__onControlFocusout(e);
      },

      __onControlFocusin: function __onControlFocusin (e) {
        if (this.editable === true && this.focused === false) {
          this.focused = true;
          this.$emit('focus', e);
        }
      },

      __onControlFocusout: function __onControlFocusout (e, then) {
        var this$1 = this;

        clearTimeout(this.focusoutTimer);
        this.focusoutTimer = setTimeout(function () {
          if (
            document.hasFocus() === true && (
              this$1.hasPopupOpen === true ||
              this$1.$refs === void 0 ||
              this$1.$refs.control === void 0 ||
              this$1.$refs.control.contains(document.activeElement) !== false
            )
          ) {
            return
          }

          if (this$1.focused === true) {
            this$1.focused = false;
            this$1.$emit('blur', e);
          }

          then !== void 0 && then();
        });
      },

      __clearValue: function __clearValue (e) {
        stop(e);
        this.$emit('input', null);
      }
    },

    render: function render (h) {
      this.__onPreRender !== void 0 && this.__onPreRender();
      this.__onPostRender !== void 0 && this.$nextTick(this.__onPostRender);

      return h('div', {
        staticClass: 'q-field row no-wrap items-start',
        class: this.classes
      }, [
        this.$scopedSlots.before !== void 0 ? h('div', {
          staticClass: 'q-field__before q-field__marginal row no-wrap items-center'
        }, this.$scopedSlots.before()) : null,

        h('div', {
          staticClass: 'q-field__inner relative-position col self-stretch column justify-center'
        }, [
          h('div', {
            ref: 'control',
            staticClass: 'q-field__control relative-position row no-wrap',
            class: this.contentClass,
            attrs: { tabindex: -1 },
            on: this.controlEvents
          }, this.__getContent(h)),

          this.shouldRenderBottom === true
            ? this.__getBottom(h)
            : null
        ]),

        this.$scopedSlots.after !== void 0 ? h('div', {
          staticClass: 'q-field__after q-field__marginal row no-wrap items-center'
        }, this.$scopedSlots.after()) : null
      ])
    },

    created: function created () {
      this.__onPreRender !== void 0 && this.__onPreRender();

      this.controlEvents = this.__getControlEvents !== void 0
        ? this.__getControlEvents()
        : {
          focus: this.focus,
          focusin: this.__onControlFocusin,
          focusout: this.__onControlFocusout,
          'popup-show': this.__onControlPopupShow,
          'popup-hide': this.__onControlPopupHide
        };
    },

    mounted: function mounted () {
      this.autofocus === true && setTimeout(this.focus);
    },

    beforeDestroy: function beforeDestroy () {
      clearTimeout(this.focusoutTimer);
    }
  });

  // leave NAMED_MASKS at top of file (code referenced from docs)
  var NAMED_MASKS = {
    date: '####/##/##',
    datetime: '####/##/## ##:##',
    time: '##:##',
    fulltime: '##:##:##',
    phone: '(###) ### - ####',
    card: '#### #### #### ####'
  };

  var TOKENS = {
    '#': { pattern: '[\\d]', negate: '[^\\d]' },

    S: { pattern: '[a-zA-Z]', negate: '[^a-zA-Z]' },
    N: { pattern: '[0-9a-zA-Z]', negate: '[^0-9a-zA-Z]' },

    A: { pattern: '[a-zA-Z]', negate: '[^a-zA-Z]', transform: function (v) { return v.toLocaleUpperCase(); } },
    a: { pattern: '[a-zA-Z]', negate: '[^a-zA-Z]', transform: function (v) { return v.toLocaleLowerCase(); } },

    X: { pattern: '[0-9a-zA-Z]', negate: '[^0-9a-zA-Z]', transform: function (v) { return v.toLocaleUpperCase(); } },
    x: { pattern: '[0-9a-zA-Z]', negate: '[^0-9a-zA-Z]', transform: function (v) { return v.toLocaleLowerCase(); } }
  };

  var KEYS = Object.keys(TOKENS);
  KEYS.forEach(function (key) {
    TOKENS[key].regex = new RegExp(TOKENS[key].pattern);
  });

  var
    tokenRegexMask = new RegExp('\\\\([^.*+?^${}()|([\\]])|([.*+?^${}()|[\\]])|([' + KEYS.join('') + '])|(.)', 'g'),
    escRegex = /[.*+?^${}()|[\]\\]/g;

  var MARKER = String.fromCharCode(1);

  var MaskMixin = {
    props: {
      mask: String,
      reverseFillMask: Boolean,
      fillMask: [Boolean, String],
      unmaskedValue: Boolean
    },

    watch: {
      type: function type () {
        this.__updateMaskInternals();
      },

      mask: function mask (v) {
        if (v !== void 0) {
          this.__updateMaskValue(this.innerValue, true);
        }
        else {
          var val = this.__unmask(this.innerValue);
          this.__updateMaskInternals();
          this.value !== val && this.$emit('input', val);
        }
      },

      fillMask: function fillMask () {
        this.hasMask === true && this.__updateMaskValue(this.innerValue, true);
      },

      reverseFillMask: function reverseFillMask () {
        this.hasMask === true && this.__updateMaskValue(this.innerValue, true);
      },

      unmaskedValue: function unmaskedValue () {
        this.hasMask === true && this.__updateMaskValue(this.innerValue);
      }
    },

    methods: {
      __getInitialMaskedValue: function __getInitialMaskedValue () {
        this.__updateMaskInternals();

        if (this.hasMask === true) {
          var masked = this.__mask(this.__unmask(this.value));

          return this.fillMask !== false
            ? this.__fillWithMask(masked)
            : masked
        }

        return this.value
      },

      __getPaddedMaskMarked: function __getPaddedMaskMarked (size) {
        if (size < this.maskMarked.length) {
          return this.maskMarked.slice(-size)
        }

        var
          maskMarked = this.maskMarked,
          padPos = maskMarked.indexOf(MARKER),
          pad = '';

        if (padPos > -1) {
          for (var i = size - maskMarked.length; i > 0; i--) {
            pad += MARKER;
          }

          maskMarked = maskMarked.slice(0, padPos) + pad + maskMarked.slice(padPos);
        }

        return maskMarked
      },

      __updateMaskInternals: function __updateMaskInternals () {
        var this$1 = this;

        this.hasMask = this.mask !== void 0 &&
          this.mask.length > 0 &&
          ['text', 'search', 'url', 'tel', 'password'].includes(this.type);

        if (this.hasMask === false) {
          this.computedUnmask = void 0;
          this.maskMarked = '';
          this.maskReplaced = '';
          return
        }

        var
          computedMask = NAMED_MASKS[this.mask] === void 0
            ? this.mask
            : NAMED_MASKS[this.mask],
          fillChar = typeof this.fillMask === 'string' && this.fillMask.length > 0
            ? this.fillMask.slice(0, 1)
            : '_',
          fillCharEscaped = fillChar.replace(escRegex, '\\$&'),
          unmask = [],
          extract = [],
          mask = [];

        var
          firstMatch = this.reverseFillMask === true,
          unmaskChar = '',
          negateChar = '';

        computedMask.replace(tokenRegexMask, function (_, char1, esc, token, char2) {
          if (token !== void 0) {
            var c = TOKENS[token];
            mask.push(c);
            negateChar = c.negate;
            if (firstMatch === true) {
              extract.push('(?:' + negateChar + '+?)?(' + c.pattern + '+)?(?:' + negateChar + '+?)?(' + c.pattern + '+)?');
              firstMatch = false;
            }
            extract.push('(?:' + negateChar + '+?)?(' + c.pattern + ')?');
          }
          else if (esc !== void 0) {
            unmaskChar = '\\' + (esc === '\\' ? '' : esc);
            mask.push(esc);
            unmask.push('([^' + unmaskChar + ']+)?' + unmaskChar + '?');
          }
          else {
            var c$1 = char1 !== void 0 ? char1 : char2;
            unmaskChar = c$1 === '\\' ? '\\\\\\\\' : c$1.replace(escRegex, '\\\\$&');
            mask.push(c$1);
            unmask.push('([^' + unmaskChar + ']+)?' + unmaskChar + '?');
          }
        });

        var
          unmaskMatcher = new RegExp(
            '^' +
            unmask.join('') +
            '(' + (unmaskChar === '' ? '.' : '[^' + unmaskChar + ']') + '+)?' +
            '$'
          ),
          extractLast = extract.length - 1,
          extractMatcher = extract.map(function (re, index) {
            if (index === 0 && this$1.reverseFillMask === true) {
              return new RegExp('^' + fillCharEscaped + '*' + re)
            }
            else if (index === extractLast) {
              return new RegExp(
                '^' + re +
                '(' + (negateChar === '' ? '.' : negateChar) + '+)?' +
                (this$1.reverseFillMask === true ? '$' : fillCharEscaped + '*')
              )
            }

            return new RegExp('^' + re)
          });

        this.computedMask = mask;
        this.computedUnmask = function (val) {
          var unmaskMatch = unmaskMatcher.exec(val);
          if (unmaskMatch !== null) {
            val = unmaskMatch.slice(1).join('');
          }

          var
            extractMatch = [],
            extractMatcherLength = extractMatcher.length;

          for (var i = 0, str = val; i < extractMatcherLength; i++) {
            var m = extractMatcher[i].exec(str);

            if (m === null) {
              break
            }

            str = str.slice(m.shift().length);
            extractMatch.push.apply(extractMatch, m);
          }
          if (extractMatch.length > 0) {
            return extractMatch.join('')
          }

          return val
        };
        this.maskMarked = mask.map(function (v) { return typeof v === 'string' ? v : MARKER; }).join('');
        this.maskReplaced = this.maskMarked.split(MARKER).join(fillChar);
      },

      __updateMaskValue: function __updateMaskValue (rawVal, updateMaskInternals) {
        var this$1 = this;

        var
          inp = this.$refs.input,
          oldCursor = this.reverseFillMask === true
            ? inp.value.length - inp.selectionEnd
            : inp.selectionEnd,
          unmasked = this.__unmask(rawVal);

        // Update here so unmask uses the original fillChar
        updateMaskInternals === true && this.__updateMaskInternals();

        var
          masked = this.fillMask !== false
            ? this.__fillWithMask(this.__mask(unmasked))
            : this.__mask(unmasked),
          changed = this.innerValue !== masked;

        // We want to avoid "flickering" so we set value immediately
        inp.value !== masked && (inp.value = masked);

        changed === true && (this.innerValue = masked);

        this.$nextTick(function () {
          if (this$1.reverseFillMask === true) {
            if (changed === true) {
              var cursor = Math.max(0, masked.length - (masked === this$1.maskReplaced ? 0 : oldCursor + 1));
              this$1.__moveCursorRightReverse(inp, cursor, cursor);
            }
            else {
              var cursor$1 = masked.length - oldCursor;
              inp.setSelectionRange(cursor$1, cursor$1);
            }
          }
          else if (changed === true) {
            if (masked === this$1.maskReplaced) {
              this$1.__moveCursorLeft(inp, 0, 0);
            }
            else {
              var cursor$2 = Math.max(0, this$1.maskMarked.indexOf(MARKER), oldCursor - 1);
              this$1.__moveCursorRight(inp, cursor$2, cursor$2);
            }
          }
          else {
            this$1.__moveCursorLeft(inp, oldCursor, oldCursor);
          }
        });

        var val = this.unmaskedValue === true
          ? this.__unmask(masked)
          : masked;

        this.value !== val && this.__emitValue(val, true);
      },

      __moveCursorLeft: function __moveCursorLeft (inp, start, end, selection) {
        var noMarkBefore = this.maskMarked.slice(start - 1).indexOf(MARKER) === -1;
        var i = Math.max(0, start - 1);

        for (; i >= 0; i--) {
          if (this.maskMarked[i] === MARKER) {
            start = i;
            noMarkBefore === true && start++;
            break
          }
        }

        if (
          i < 0 &&
          this.maskMarked[start] !== void 0 &&
          this.maskMarked[start] !== MARKER
        ) {
          return this.__moveCursorRight(inp, 0, 0)
        }

        start >= 0 && inp.setSelectionRange(
          start,
          selection === true ? end : start, 'backward'
        );
      },

      __moveCursorRight: function __moveCursorRight (inp, start, end, selection) {
        var limit = inp.value.length;
        var i = Math.min(limit, end + 1);

        for (; i <= limit; i++) {
          if (this.maskMarked[i] === MARKER) {
            end = i;
            break
          }
          else if (this.maskMarked[i - 1] === MARKER) {
            end = i;
          }
        }

        if (
          i > limit &&
          this.maskMarked[end - 1] !== void 0 &&
          this.maskMarked[end - 1] !== MARKER
        ) {
          return this.__moveCursorLeft(inp, limit, limit)
        }

        inp.setSelectionRange(selection ? start : end, end, 'forward');
      },

      __moveCursorLeftReverse: function __moveCursorLeftReverse (inp, start, end, selection) {
        var
          maskMarked = this.__getPaddedMaskMarked(inp.value.length);
        var i = Math.max(0, start - 1);

        for (; i >= 0; i--) {
          if (maskMarked[i - 1] === MARKER) {
            start = i;
            break
          }
          else if (maskMarked[i] === MARKER) {
            start = i;
            if (i === 0) {
              break
            }
          }
        }

        if (
          i < 0 &&
          maskMarked[start] !== void 0 &&
          maskMarked[start] !== MARKER
        ) {
          return this.__moveCursorRightReverse(inp, 0, 0)
        }

        start >= 0 && inp.setSelectionRange(
          start,
          selection === true ? end : start, 'backward'
        );
      },

      __moveCursorRightReverse: function __moveCursorRightReverse (inp, start, end, selection) {
        var
          limit = inp.value.length,
          maskMarked = this.__getPaddedMaskMarked(limit),
          noMarkBefore = maskMarked.slice(0, end + 1).indexOf(MARKER) === -1;
        var i = Math.min(limit, end + 1);

        for (; i <= limit; i++) {
          if (maskMarked[i - 1] === MARKER) {
            end = i;
            end > 0 && noMarkBefore === true && end--;
            break
          }
        }

        if (
          i > limit &&
          maskMarked[end - 1] !== void 0 &&
          maskMarked[end - 1] !== MARKER
        ) {
          return this.__moveCursorLeftReverse(inp, limit, limit)
        }

        inp.setSelectionRange(selection === true ? start : end, end, 'forward');
      },

      __onMaskedKeydown: function __onMaskedKeydown (e) {
        var
          inp = this.$refs.input,
          start = inp.selectionStart,
          end = inp.selectionEnd;

        if (e.keyCode === 37 || e.keyCode === 39) { // Left / Right
          var fn = this['__moveCursor' + (e.keyCode === 39 ? 'Right' : 'Left') + (this.reverseFillMask === true ? 'Reverse' : '')];

          e.preventDefault();
          fn(inp, start, end, e.shiftKey);
        }
        else if (
          e.keyCode === 8 && // Backspace
          this.reverseFillMask !== true &&
          start === end
        ) {
          this.__moveCursorLeft(inp, start, end, true);
        }
        else if (
          e.keyCode === 46 && // Delete
          this.reverseFillMask === true &&
          start === end
        ) {
          this.__moveCursorRightReverse(inp, start, end, true);
        }

        this.$emit('keydown', e);
      },

      __mask: function __mask (val) {
        if (val === void 0 || val === null || val === '') { return '' }

        if (this.reverseFillMask === true) {
          return this.__maskReverse(val)
        }

        var mask = this.computedMask;

        var valIndex = 0, output = '';

        for (var maskIndex = 0; maskIndex < mask.length; maskIndex++) {
          var
            valChar = val[valIndex],
            maskDef = mask[maskIndex];

          if (typeof maskDef === 'string') {
            output += maskDef;
            valChar === maskDef && valIndex++;
          }
          else if (valChar !== void 0 && maskDef.regex.test(valChar)) {
            output += maskDef.transform !== void 0
              ? maskDef.transform(valChar)
              : valChar;
            valIndex++;
          }
          else {
            return output
          }
        }

        return output
      },

      __maskReverse: function __maskReverse (val) {
        var
          mask = this.computedMask,
          firstTokenIndex = this.maskMarked.indexOf(MARKER);

        var valIndex = val.length - 1, output = '';

        for (var maskIndex = mask.length - 1; maskIndex >= 0; maskIndex--) {
          var maskDef = mask[maskIndex];

          var valChar = val[valIndex];

          if (typeof maskDef === 'string') {
            output = maskDef + output;
            valChar === maskDef && valIndex--;
          }
          else if (valChar !== void 0 && maskDef.regex.test(valChar)) {
            do {
              output = (maskDef.transform !== void 0 ? maskDef.transform(valChar) : valChar) + output;
              valIndex--;
              valChar = val[valIndex];
            // eslint-disable-next-line no-unmodified-loop-condition
            } while (firstTokenIndex === maskIndex && valChar !== void 0 && maskDef.regex.test(valChar))
          }
          else {
            return output
          }
        }

        return output
      },

      __unmask: function __unmask (val) {
        return typeof val !== 'string' || this.computedUnmask === void 0
          ? val
          : this.computedUnmask(val)
      },

      __fillWithMask: function __fillWithMask (val) {
        if (this.maskReplaced.length - val.length <= 0) {
          return val
        }

        return this.reverseFillMask === true && val.length > 0
          ? this.maskReplaced.slice(0, -val.length) + val
          : val + this.maskReplaced.slice(val.length)
      }
    }
  };

  var QInput = Vue.extend({
    name: 'QInput',

    mixins: [ QField, MaskMixin ],

    props: {
      value: [String, Number],

      type: {
        type: String,
        default: 'text'
      },

      debounce: [String, Number],

      maxlength: [Number, String],
      autogrow: Boolean, // makes a textarea

      inputClass: [Array, String, Object],
      inputStyle: [Array, String, Object]
    },

    watch: {
      value: function value (v) {
        if (this.hasMask === true) {
          if (this.stopValueWatcher === true) {
            this.stopValueWatcher = false;
            return
          }

          this.__updateMaskValue(v);
        }
        else if (this.innerValue !== v) {
          this.innerValue = v;

          if (
            this.type === 'number' &&
            this.hasOwnProperty('tempValue') === true
          ) {
            if (this.typedNumber === true) {
              this.typedNumber = false;
            }
            else {
              delete this.tempValue;
            }
          }
        }

        // textarea only
        this.autogrow === true && this.$nextTick(this.__adjustHeightDebounce);
      },

      autogrow: function autogrow (autogrow$1) {
        // textarea only
        if (autogrow$1 === true) {
          this.$nextTick(this.__adjustHeightDebounce);
        }
        // if it has a number of rows set respect it
        else if (this.$attrs.rows > 0 && this.$refs.input !== void 0) {
          var inp = this.$refs.input;
          inp.style.height = 'auto';
        }
      }
    },

    data: function data () {
      return { innerValue: this.__getInitialMaskedValue() }
    },

    computed: {
      isTextarea: function isTextarea () {
        return this.type === 'textarea' || this.autogrow === true
      },

      fieldClass: function fieldClass () {
        return "q-" + (this.isTextarea === true ? 'textarea' : 'input') +
          (this.autogrow === true ? ' q-textarea--autogrow' : '')
      }
    },

    methods: {
      focus: function focus () {
        this.$refs.input !== void 0 && this.$refs.input.focus();
      },

      select: function select () {
        this.$refs.input !== void 0 && this.$refs.input.select();
      },

      __onInput: function __onInput (e) {
        if (e && e.target && e.target.composing === true) {
          return
        }

        if (this.type === 'file') {
          this.$emit('input', e.target.files);
          return
        }

        var val = e.target.value;

        if (this.hasMask === true) {
          this.__updateMaskValue(val);
        }
        else {
          this.__emitValue(val);
        }

        // we need to trigger it immediately too,
        // to avoid "flickering"
        this.autogrow === true && this.__adjustHeight();
      },

      __emitValue: function __emitValue (val, stopWatcher) {
        var this$1 = this;

        var fn = function () {
          if (
            this$1.type !== 'number' &&
            this$1.hasOwnProperty('tempValue') === true
          ) {
            delete this$1.tempValue;
          }

          if (this$1.value !== val) {
            stopWatcher === true && (this$1.stopValueWatcher = true);
            this$1.$emit('input', val);
          }
        };

        if (this.type === 'number') {
          this.typedNumber = true;
          this.tempValue = val;
        }

        if (this.debounce !== void 0) {
          clearTimeout(this.emitTimer);
          this.tempValue = val;
          this.emitTimer = setTimeout(fn, this.debounce);
        }
        else {
          fn();
        }
      },

      // textarea only
      __adjustHeight: function __adjustHeight () {
        var inp = this.$refs.input;
        if (inp !== void 0) {
          inp.style.height = '1px';
          inp.style.height = inp.scrollHeight + 'px';
        }
      },

      __onCompositionStart: function __onCompositionStart (e) {
        e.target.composing = true;
      },

      __onCompositionUpdate: function __onCompositionUpdate (e) {
        if (typeof e.data === 'string' && e.data.codePointAt(0) < 256) {
          e.target.composing = false;
        }
      },

      __onCompositionEnd: function __onCompositionEnd (e) {
        if (e.target.composing !== true) { return }
        e.target.composing = false;

        this.__onInput(e);
      },

      __onChange: function __onChange (e) {
        this.__onCompositionEnd(e);
        this.$emit('change', e);
      },

      __getControl: function __getControl (h) {
        var on = Object.assign({}, this.$listeners,
          {input: this.__onInput,
          // Safari < 10.2 & UIWebView doesn't fire compositionend when
          // switching focus before confirming composition choice
          // this also fixes the issue where some browsers e.g. iOS Chrome
          // fires "change" instead of "input" on autocomplete.
          change: this.__onChange,
          compositionstart: this.__onCompositionStart,
          compositionend: this.__onCompositionEnd,
          focus: stop,
          blur: stop});

        if (this.$q.platform.is.android === true) {
          on.compositionupdate = this.__onCompositionUpdate;
        }

        if (this.hasMask === true) {
          on.keydown = this.__onMaskedKeydown;
        }

        var attrs = Object.assign({}, {tabindex: 0,
          autofocus: this.autofocus,
          rows: this.type === 'textarea' ? 6 : void 0,
          'aria-label': this.label},
          this.$attrs,
          {type: this.type,
          maxlength: this.maxlength,
          disabled: this.editable !== true});

        if (this.autogrow === true) {
          attrs.rows = 1;
        }

        return h(this.isTextarea === true ? 'textarea' : 'input', {
          ref: 'input',
          staticClass: 'q-field__native q-placeholder',
          style: this.inputStyle,
          class: this.inputClass,
          attrs: attrs,
          on: on,
          domProps: this.type !== 'file'
            ? {
              value: this.hasOwnProperty('tempValue') === true
                ? this.tempValue
                : this.innerValue
            }
            : null
        })
      }
    },

    created: function created () {
      // textarea only
      this.__adjustHeightDebounce = debounce(this.__adjustHeight, 100);
    },

    mounted: function mounted () {
      // textarea only
      this.autogrow === true && this.__adjustHeight();
    },

    beforeDestroy: function beforeDestroy () {
      clearTimeout(this.emitTimer);
    }
  });

  var QTooltip = Vue.extend({
    name: 'QTooltip',

    mixins: [ AnchorMixin, ModelToggleMixin, PortalMixin, TransitionMixin ],

    props: {
      maxHeight: {
        type: String,
        default: null
      },
      maxWidth: {
        type: String,
        default: null
      },

      transitionShow: {
        default: 'jump-down'
      },
      transitionHide: {
        default: 'jump-up'
      },

      anchor: {
        type: String,
        default: 'bottom middle',
        validator: validatePosition
      },
      self: {
        type: String,
        default: 'top middle',
        validator: validatePosition
      },
      offset: {
        type: Array,
        default: function () { return [14, 14]; },
        validator: validateOffset
      },

      delay: {
        type: Number,
        default: 0
      }
    },

    watch: {
      $route: function $route () {
        this.hide();
      }
    },

    computed: {
      anchorOrigin: function anchorOrigin () {
        return parsePosition(this.anchor)
      },

      selfOrigin: function selfOrigin () {
        return parsePosition(this.self)
      }
    },

    methods: {
      __show: function __show (evt) {
        var this$1 = this;

        clearTimeout(this.timer);

        this.scrollTarget = getScrollTarget(this.anchorEl);
        this.scrollTarget.addEventListener('scroll', this.hide, listenOpts.passive);
        if (this.scrollTarget !== window) {
          window.addEventListener('scroll', this.updatePosition, listenOpts.passive);
        }

        this.__showPortal();

        this.timer = setTimeout(function () {
          this$1.updatePosition();

          this$1.timer = setTimeout(function () {
            this$1.$emit('show', evt);
          }, 300);
        }, 0);
      },

      __hide: function __hide (evt) {
        var this$1 = this;

        this.__anchorCleanup();

        this.timer = setTimeout(function () {
          this$1.__hidePortal();
          this$1.$emit('hide', evt);
        }, 300);
      },

      __anchorCleanup: function __anchorCleanup () {
        clearTimeout(this.timer);

        if (this.scrollTarget) {
          this.scrollTarget.removeEventListener('scroll', this.updatePosition, listenOpts.passive);
          if (this.scrollTarget !== window) {
            window.removeEventListener('scroll', this.updatePosition, listenOpts.passive);
          }
        }
      },

      updatePosition: function updatePosition () {
        var this$1 = this;

        var el = this.__portal.$el;

        if (el.nodeType === 8) { // IE replaces the comment with delay
          setTimeout(function () {
            this$1.__portal !== void 0 && this$1.__portal.showing === true && this$1.updatePosition();
          }, 25);
          return
        }

        setPosition({
          el: el,
          offset: this.offset,
          anchorEl: this.anchorEl,
          anchorOrigin: this.anchorOrigin,
          selfOrigin: this.selfOrigin,
          maxHeight: this.maxHeight,
          maxWidth: this.maxWidth
        });
      },

      __delayShow: function __delayShow (evt) {
        var this$1 = this;

        clearTimeout(this.timer);
        this.$q.platform.is.mobile === true && document.body.classList.add('non-selectable');
        this.timer = setTimeout(function () {
          this$1.show(evt);
        }, this.delay);
      },

      __delayHide: function __delayHide (evt) {
        clearTimeout(this.timer);
        this.$q.platform.is.mobile === true && document.body.classList.remove('non-selectable');
        this.hide(evt);
      },

      __unconfigureAnchorEl: function __unconfigureAnchorEl () {
        var this$1 = this;

        // mobile hover ref https://stackoverflow.com/a/22444532
        if (this.$q.platform.is.mobile) {
          this.anchorEl.removeEventListener('touchstart', this.__delayShow)
          ;['touchcancel', 'touchmove', 'click'].forEach(function (evt) {
            this$1.anchorEl.removeEventListener(evt, this$1.__delayHide);
          });
        }
        else {
          this.anchorEl.removeEventListener('mouseenter', this.__delayShow);
        }

        if (this.$q.platform.is.ios !== true) {
          this.anchorEl.removeEventListener('mouseleave', this.__delayHide);
        }
      },

      __configureAnchorEl: function __configureAnchorEl () {
        var this$1 = this;

        // mobile hover ref https://stackoverflow.com/a/22444532
        if (this.$q.platform.is.mobile) {
          this.anchorEl.addEventListener('touchstart', this.__delayShow)
          ;['touchcancel', 'touchmove', 'click'].forEach(function (evt) {
            this$1.anchorEl.addEventListener(evt, this$1.__delayHide);
          });
        }
        else {
          this.anchorEl.addEventListener('mouseenter', this.__delayShow);
        }

        if (this.$q.platform.is.ios !== true) {
          this.anchorEl.addEventListener('mouseleave', this.__delayHide);
        }
      },

      __render: function __render (h) {
        return h('transition', {
          props: { name: this.transition }
        }, [
          this.showing === true ? h('div', {
            staticClass: 'q-tooltip no-pointer-events',
            class: this.contentClass,
            style: this.contentStyle
          }, slot(this, 'default')) : null
        ])
      }
    }
  });

  var QList = Vue.extend({
    name: 'QList',

    props: {
      bordered: Boolean,
      dense: Boolean,
      separator: Boolean,
      dark: Boolean,
      padding: Boolean
    },

    computed: {
      classes: function classes () {
        return {
          'q-list--bordered': this.bordered,
          'q-list--dense': this.dense,
          'q-list--separator': this.separator,
          'q-list--dark': this.dark,
          'q-list--padding': this.padding
        }
      }
    },

    render: function render (h) {
      return h('div', {
        staticClass: 'q-list',
        class: this.classes,
        on: this.$listeners
      }, slot(this, 'default'))
    }
  });

  var QItem = Vue.extend({
    name: 'QItem',

    mixins: [ RouterLinkMixin ],

    props: {
      active: Boolean,
      dark: Boolean,

      clickable: Boolean,
      dense: Boolean,
      insetLevel: Number,

      tabindex: [String, Number],
      tag: {
        type: String,
        default: 'div'
      },

      focused: Boolean,
      manualFocus: Boolean
    },

    computed: {
      isClickable: function isClickable () {
        return this.disable !== true && (
          this.clickable === true ||
          this.hasRouterLink === true ||
          this.tag === 'a' ||
          this.tag === 'label'
        )
      },

      classes: function classes () {
        var obj;

        return ( obj = {
          'q-item--clickable q-link cursor-pointer': this.isClickable,
          'q-focusable q-hoverable': this.isClickable === true && this.manualFocus === false,

          'q-manual-focusable': this.isClickable === true && this.manualFocus === true,
          'q-manual-focusable--focused': this.isClickable === true && this.focused === true,

          'q-item--dense': this.dense,
          'q-item--dark': this.dark,
          'q-item--active': this.active
        }, obj[this.activeClass] = this.active === true && this.hasRouterLink !== true && this.activeClass !== void 0, obj['disabled'] = this.disable, obj )
      },

      style: function style () {
        if (this.insetLevel !== void 0) {
          return {
            paddingLeft: (16 + this.insetLevel * 56) + 'px'
          }
        }
      }
    },

    methods: {
      __getContent: function __getContent (h) {
        var child = [].concat(slot(this, 'default'));
        this.isClickable === true && child.unshift(h('div', { staticClass: 'q-focus-helper', attrs: { tabindex: -1 }, ref: 'blurTarget' }));
        return child
      },

      __onClick: function __onClick (e) {
        if (this.isClickable === true) {
          if (e.qKeyEvent !== true && this.$refs.blurTarget !== void 0) {
            this.$refs.blurTarget.focus();
          }

          this.$emit('click', e);
        }
      },

      __onKeyup: function __onKeyup (e) {
        if (e.keyCode === 13 && this.isClickable === true) {
          stopAndPrevent(e);

          // for ripple
          e.qKeyEvent = true;

          // for click trigger
          var evt = new MouseEvent('click', e);
          evt.qKeyEvent = true;
          this.$el.dispatchEvent(evt);
        }

        this.$emit('keyup', e);
      }
    },

    render: function render (h) {
      var data = {
        staticClass: 'q-item q-item-type row no-wrap',
        class: this.classes,
        style: this.style
      };

      var evtProp = this.hasRouterLink === true ? 'nativeOn' : 'on';
      data[evtProp] = Object.assign({}, this.$listeners,
        {click: this.__onClick,
        keyup: this.__onKeyup});

      if (this.isClickable === true) {
        data.attrs = {
          tabindex: this.tabindex || '0'
        };
      }

      if (this.hasRouterLink === true) {
        data.tag = 'a';
        data.props = this.routerLinkProps;

        return h('router-link', data, this.__getContent(h))
      }

      return h(
        this.tag,
        data,
        this.__getContent(h)
      )
    }
  });

  var QItemSection = Vue.extend({
    name: 'QItemSection',

    props: {
      avatar: Boolean,
      thumbnail: Boolean,
      side: Boolean,
      top: Boolean,
      noWrap: Boolean
    },

    computed: {
      classes: function classes () {
        var obj;

        var side = this.avatar || this.side || this.thumbnail;

        return ( obj = {
          'q-item__section--top': this.top,
          'q-item__section--avatar': this.avatar,
          'q-item__section--thumbnail': this.thumbnail,
          'q-item__section--side': side,
          'q-item__section--nowrap': this.noWrap,
          'q-item__section--main': !side
        }, obj[("justify-" + (this.top ? 'start' : 'center'))] = true, obj )
      }
    },

    render: function render (h) {
      return h('div', {
        staticClass: 'q-item__section column',
        class: this.classes,
        on: this.$listeners
      }, slot(this, 'default'))
    }
  });

  function run (e, btn, vm) {
    if (btn.handler) {
      btn.handler(e, vm, vm.caret);
    }
    else {
      vm.runCmd(btn.cmd, btn.param);
    }
  }

  function __getGroup (h, children) {
    return h('div', {
      staticClass: 'q-editor__toolbar-group'
    }, children)
  }

  function getBtn (h, vm, btn, clickHandler, active) {
    if ( active === void 0 ) active = false;

    var
      toggled = active || (btn.type === 'toggle'
        ? (btn.toggled ? btn.toggled(vm) : btn.cmd && vm.caret.is(btn.cmd, btn.param))
        : false),
      child = [],
      events = {
        click: function click (e) {
          clickHandler && clickHandler();
          run(e, btn, vm);
        }
      };

    if (btn.tip && vm.$q.platform.is.desktop) {
      var Key = btn.key
        ? h('div', [h('small', ("(CTRL + " + (String.fromCharCode(btn.key)) + ")"))])
        : null;
      child.push(
        h(QTooltip, { props: { delay: 1000 } }, [
          h('div', { domProps: { innerHTML: btn.tip } }),
          Key
        ])
      );
    }

    return h(QBtn, {
      props: Object.assign({}, vm.buttonProps,
        {icon: btn.icon,
        color: toggled ? btn.toggleColor || vm.toolbarToggleColor : btn.color || vm.toolbarColor,
        textColor: toggled && !vm.toolbarPush ? null : btn.textColor || vm.toolbarTextColor,
        label: btn.label,
        disable: btn.disable ? (typeof btn.disable === 'function' ? btn.disable(vm) : true) : false,
        size: 'sm'}),
      on: events
    }, child)
  }

  function getDropdown (h, vm, btn) {
    var
      label = btn.label,
      icon = btn.icon,
      onlyIcons = btn.list === 'only-icons',
      contentClass,
      Items;

    function closeDropdown () {
      Dropdown.componentInstance.hide();
    }

    if (onlyIcons) {
      Items = btn.options.map(function (btn) {
        var active = btn.type === void 0
          ? vm.caret.is(btn.cmd, btn.param)
          : false;

        if (active) {
          label = btn.tip;
          icon = btn.icon;
        }
        return getBtn(h, vm, btn, closeDropdown, active)
      });
      contentClass = vm.toolbarBackgroundClass;
      Items = [
        __getGroup(h, Items)
      ];
    }
    else {
      var activeClass = vm.toolbarToggleColor !== void 0
        ? ("text-" + (vm.toolbarToggleColor))
        : null;
      var inactiveClass = vm.toolbarTextColor !== void 0
        ? ("text-" + (vm.toolbarTextColor))
        : null;

      Items = btn.options.map(function (btn) {
        var disable = btn.disable ? btn.disable(vm) : false;
        var active = btn.type === void 0
          ? vm.caret.is(btn.cmd, btn.param)
          : false;

        if (active) {
          label = btn.tip;
          icon = btn.icon;
        }

        var htmlTip = btn.htmlTip;

        return h(
          QItem,
          {
            props: { active: active, activeClass: activeClass, clickable: true, disable: disable, dense: true },
            on: {
              click: function click (e) {
                closeDropdown();
                vm.$refs.content && vm.$refs.content.focus();
                vm.caret.restore();
                run(e, btn, vm);
              }
            }
          },
          [
            btn.list === 'no-icons'
              ? null
              : h(QItemSection, {
                class: active ? activeClass : inactiveClass,
                props: { side: true }
              }, [
                h(QIcon, { props: { name: btn.icon } })
              ]),

            h(QItemSection, [
              htmlTip
                ? h('div', {
                  domProps: { innerHTML: btn.htmlTip }
                })
                : (btn.tip ? h('div', [ btn.tip ]) : null)
            ])
          ]
        )
      });
      contentClass = [vm.toolbarBackgroundClass, inactiveClass];
      Items = [
        h(QList, [ Items ])
      ];
    }

    var highlight = btn.highlight && label !== btn.label;
    var Dropdown = h(
      QBtnDropdown,
      {
        props: Object.assign({}, vm.buttonProps,
          {noCaps: true,
          noWrap: true,
          color: highlight ? vm.toolbarToggleColor : vm.toolbarColor,
          textColor: highlight && !vm.toolbarPush ? null : vm.toolbarTextColor,
          label: btn.fixedLabel ? btn.label : label,
          icon: btn.fixedIcon ? btn.icon : icon,
          contentClass: contentClass})
      },
      Items
    );
    return Dropdown
  }

  function getToolbar (h, vm) {
    if (vm.caret) {
      return vm.buttons.map(function (group) { return __getGroup(
        h,
        group.map(function (btn) {
          if (btn.type === 'slot') {
            return slot(vm, btn.slot)
          }

          if (btn.type === 'dropdown') {
            return getDropdown(h, vm, btn)
          }

          return getBtn(h, vm, btn)
        })
      ); })
    }
  }

  function getFonts (defaultFont, defaultFontLabel, defaultFontIcon, fonts) {
    if ( fonts === void 0 ) fonts = {};

    var aliases = Object.keys(fonts);
    if (aliases.length === 0) {
      return {}
    }

    var def = {
      default_font: {
        cmd: 'fontName',
        param: defaultFont,
        icon: defaultFontIcon,
        tip: defaultFontLabel
      }
    };

    aliases.forEach(function (alias) {
      var name = fonts[alias];
      def[alias] = {
        cmd: 'fontName',
        param: name,
        icon: defaultFontIcon,
        tip: name,
        htmlTip: ("<font face=\"" + name + "\">" + name + "</font>")
      };
    });

    return def
  }

  function getLinkEditor (h, vm) {
    if (vm.caret) {
      var color = vm.toolbarColor || vm.toolbarTextColor;
      var link = vm.editLinkUrl;
      var updateLink = function () {
        vm.caret.restore();
        if (link !== vm.editLinkUrl) {
          document.execCommand('createLink', false, link === '' ? ' ' : link);
        }
        vm.editLinkUrl = null;
      };

      return [
        h('div', { staticClass: 'q-mx-xs', 'class': ("text-" + color) }, [((vm.$q.lang.editor.url) + ": ")]),
        h(QInput, {
          key: 'qedt_btm_input',
          staticClass: 'q-ma-none q-pa-none col q-editor-input',
          props: {
            value: link,
            color: color,
            autofocus: true,
            borderless: true,
            dense: true
          },
          on: {
            input: function (val) { link = val; },
            keydown: function (event) {
              switch (event.keyCode) {
                case 13: // ENTER key
                  prevent(event);
                  return updateLink()
                case 27: // ESCAPE key
                  prevent(event);
                  vm.caret.restore();
                  if (!vm.editLinkUrl || vm.editLinkUrl === 'https://') {
                    document.execCommand('unlink');
                  }
                  vm.editLinkUrl = null;
                  break
              }
            }
          }
        }),
        __getGroup(h, [
          h(QBtn, {
            key: 'qedt_btm_rem',
            attrs: { tabindex: -1 },
            props: Object.assign({}, vm.buttonProps,
              {label: vm.$q.lang.label.remove,
              noCaps: true}),
            on: {
              click: function () {
                vm.caret.restore();
                document.execCommand('unlink');
                vm.editLinkUrl = null;
              }
            }
          }),
          h(QBtn, {
            key: 'qedt_btm_upd',
            props: Object.assign({}, vm.buttonProps,
              {label: vm.$q.lang.label.update,
              noCaps: true}),
            on: {
              click: updateLink
            }
          })
        ])
      ]
    }
  }

  function getBlockElement (el, parent) {
    if (parent && el === parent) {
      return null
    }

    var
      style = window.getComputedStyle
        ? window.getComputedStyle(el)
        : el.currentStyle,
      display = style.display;

    if (display === 'block' || display === 'table') {
      return el
    }

    return getBlockElement(el.parentNode)
  }

  function isChildOf (el, parent) {
    if (!el) {
      return false
    }
    while ((el = el.parentNode)) {
      if (el === document.body) {
        return false
      }
      if (el === parent) {
        return true
      }
    }
    return false
  }

  var urlRegex = /^https?:\/\//;

  var Caret = function Caret (el, vm) {
    this.el = el;
    this.vm = vm;
  };

  var prototypeAccessors = { selection: { configurable: true },hasSelection: { configurable: true },range: { configurable: true },parent: { configurable: true },blockParent: { configurable: true } };

  prototypeAccessors.selection.get = function () {
    if (!this.el) {
      return
    }
    var sel = document.getSelection();
    // only when the selection in element
    if (isChildOf(sel.anchorNode, this.el) && isChildOf(sel.focusNode, this.el)) {
      return sel
    }
  };

  prototypeAccessors.hasSelection.get = function () {
    return this.selection
      ? this.selection.toString().length > 0
      : null
  };

  prototypeAccessors.range.get = function () {
    var sel = this.selection;

    if (!sel) {
      return
    }

    return sel.rangeCount
      ? sel.getRangeAt(0)
      : null
  };

  prototypeAccessors.parent.get = function () {
    var range = this.range;
    if (!range) {
      return
    }

    var node = range.startContainer;
    return node.nodeType === document.ELEMENT_NODE
      ? node
      : node.parentNode
  };

  prototypeAccessors.blockParent.get = function () {
    var parent = this.parent;
    if (!parent) {
      return
    }
    return getBlockElement(parent, this.el)
  };

  Caret.prototype.save = function save (range) {
      if ( range === void 0 ) range = this.range;

    this._range = range;
  };

  Caret.prototype.restore = function restore (range) {
      if ( range === void 0 ) range = this._range;

    var
      r = document.createRange(),
      sel = document.getSelection();

    if (range) {
      r.setStart(range.startContainer, range.startOffset);
      r.setEnd(range.endContainer, range.endOffset);
      sel.removeAllRanges();
      sel.addRange(r);
    }
    else {
      sel.selectAllChildren(this.el);
      sel.collapseToEnd();
    }
  };

  Caret.prototype.hasParent = function hasParent (name, spanLevel) {
    var el = spanLevel
      ? this.parent
      : this.blockParent;

    return el
      ? el.nodeName.toLowerCase() === name.toLowerCase()
      : false
  };

  Caret.prototype.hasParents = function hasParents (list) {
    var el = this.parent;
    return el
      ? list.includes(el.nodeName.toLowerCase())
      : false
  };

  Caret.prototype.is = function is (cmd, param) {
    switch (cmd) {
      case 'formatBlock':
        if (param === 'DIV' && this.parent === this.el) {
          return true
        }
        return this.hasParent(param, param === 'PRE')
      case 'link':
        return this.hasParent('A', true)
      case 'fontSize':
        return document.queryCommandValue(cmd) === param
      case 'fontName':
        var res = document.queryCommandValue(cmd);
        return res === ("\"" + param + "\"") || res === param
      case 'fullscreen':
        return this.vm.inFullscreen
      case void 0:
        return false
      default:
        var state = document.queryCommandState(cmd);
        return param ? state === param : state
    }
  };

  Caret.prototype.getParentAttribute = function getParentAttribute (attrib) {
    if (this.parent) {
      return this.parent.getAttribute(attrib)
    }
  };

  Caret.prototype.can = function can (name) {
    if (name === 'outdent') {
      return this.hasParents(['blockquote', 'li'])
    }
    if (name === 'indent') {
      var parentName = this.parent ? this.parent.nodeName.toLowerCase() : false;
      if (parentName === 'blockquote') {
        return false
      }
      if (parentName === 'li') {
        var previousEl = this.parent.previousSibling;
        return previousEl && previousEl.nodeName.toLowerCase() === 'li'
      }
      return false
    }
    if (name === 'link') {
      return this.selection || this.is('link')
    }
  };

  Caret.prototype.apply = function apply (cmd, param, done) {
      var this$1 = this;
      if ( done === void 0 ) done = function () {};

    if (cmd === 'formatBlock') {
      if (['BLOCKQUOTE', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6'].includes(param) && this.is(cmd, param)) {
        cmd = 'outdent';
        param = null;
      }

      if (param === 'PRE' && this.is(cmd, 'PRE')) {
        param = 'P';
      }
    }
    else if (cmd === 'print') {
      done();
      var win = window.open();
      win.document.write(("\n        <!doctype html>\n        <html>\n          <head>\n            <title>Print - " + (document.title) + "</title>\n          </head>\n          <body>\n            <div>" + (this.el.innerHTML) + "</div>\n          </body>\n        </html>\n      "));
      win.print();
      win.close();
      return
    }
    else if (cmd === 'link') {
      var link = this.getParentAttribute('href');
      if (!link) {
        var selection = this.selectWord(this.selection);
        var url = selection ? selection.toString() : '';
        if (!url.length) {
          return
        }
        this.vm.editLinkUrl = urlRegex.test(url) ? url : 'https://';
        document.execCommand('createLink', false, this.vm.editLinkUrl);
      }
      else {
        this.vm.editLinkUrl = link;
      }
      this.vm.$nextTick(function () {
        this$1.range.selectNodeContents(this$1.parent);
        this$1.save();
      });
      return
    }
    else if (cmd === 'fullscreen') {
      this.vm.toggleFullscreen();
      done();
      return
    }

    if (this.vm.$q.platform.is.ie === true || this.vm.$q.platform.is.edge === true) {
      // workaround for IE/Edge, otherwise it messes up
      // the DOM of toolbar
      var dummyDiv = document.createElement('div');
      this.vm.$refs.content.appendChild(dummyDiv);
      document.execCommand(cmd, false, param);
      dummyDiv.remove();
    }
    else {
      document.execCommand(cmd, false, param);
    }

    done();
  };

  Caret.prototype.selectWord = function selectWord (sel) {
    if (!sel || !sel.isCollapsed) {
      return sel
    }

    // Detect if selection is backwards
    var range = document.createRange();
    range.setStart(sel.anchorNode, sel.anchorOffset);
    range.setEnd(sel.focusNode, sel.focusOffset);
    var direction = range.collapsed ? ['backward', 'forward'] : ['forward', 'backward'];
    range.detach();

    // modify() works on the focus of the selection
    var
      endNode = sel.focusNode,
      endOffset = sel.focusOffset;
    sel.collapse(sel.anchorNode, sel.anchorOffset);
    sel.modify('move', direction[0], 'character');
    sel.modify('move', direction[1], 'word');
    sel.extend(endNode, endOffset);
    sel.modify('extend', direction[1], 'character');
    sel.modify('extend', direction[0], 'word');

    return sel
  };

  Object.defineProperties( Caret.prototype, prototypeAccessors );

  var
    toString = Object.prototype.toString,
    hasOwn = Object.prototype.hasOwnProperty,
    class2type = {};

  'Boolean Number String Function Array Date RegExp Object'.split(' ').forEach(function (name) {
    class2type['[object ' + name + ']'] = name.toLowerCase();
  });

  function type (obj) {
    return obj === null ? String(obj) : class2type[toString.call(obj)] || 'object'
  }

  function isPlainObject (obj) {
    if (!obj || type(obj) !== 'object') {
      return false
    }

    if (obj.constructor &&
      !hasOwn.call(obj, 'constructor') &&
      !hasOwn.call(obj.constructor.prototype, 'isPrototypeOf')) {
      return false
    }

    var key;
    for (key in obj) {}

    return key === undefined || hasOwn.call(obj, key)
  }

  function extend () {
    var arguments$1 = arguments;

    var
      options, name, src, copy, copyIsArray, clone,
      target = arguments[0] || {},
      i = 1,
      length = arguments.length,
      deep = false;

    if (typeof target === 'boolean') {
      deep = target;
      target = arguments[1] || {};
      i = 2;
    }

    if (Object(target) !== target && type(target) !== 'function') {
      target = {};
    }

    if (length === i) {
      target = this;
      i--;
    }

    for (; i < length; i++) {
      if ((options = arguments$1[i]) !== null) {
        for (name in options) {
          src = target[name];
          copy = options[name];

          if (target === copy) {
            continue
          }

          if (deep && copy && (isPlainObject(copy) || (copyIsArray = type(copy) === 'array'))) {
            if (copyIsArray) {
              copyIsArray = false;
              clone = src && type(src) === 'array' ? src : [];
            }
            else {
              clone = src && isPlainObject(src) ? src : {};
            }

            target[name] = extend(deep, clone, copy);
          }
          else if (copy !== undefined) {
            target[name] = copy;
          }
        }
      }
    }

    return target
  }

  var QEditor = Vue.extend({
    name: 'QEditor',

    mixins: [ FullscreenMixin ],

    props: {
      value: {
        type: String,
        required: true
      },
      readonly: Boolean,
      disable: Boolean,
      minHeight: {
        type: String,
        default: '10rem'
      },
      maxHeight: String,
      height: String,
      definitions: Object,
      fonts: Object,

      toolbar: {
        type: Array,
        validator: function (v) { return v.length === 0 || v.every(function (group) { return group.length; }); },
        default: function default$1 () {
          return [
            ['left', 'center', 'right', 'justify'],
            ['bold', 'italic', 'underline', 'strike'],
            ['undo', 'redo']
          ]
        }
      },
      toolbarColor: String,
      toolbarBg: String,
      toolbarTextColor: String,
      toolbarToggleColor: {
        type: String,
        default: 'primary'
      },
      toolbarOutline: Boolean,
      toolbarPush: Boolean,
      toolbarRounded: Boolean,

      contentStyle: Object,
      contentClass: [Object, Array, String],

      square: Boolean,
      flat: Boolean,
      dense: Boolean
    },

    computed: {
      editable: function editable () {
        return !this.readonly && !this.disable
      },

      hasToolbar: function hasToolbar () {
        return this.toolbar && this.toolbar.length > 0
      },

      toolbarBackgroundClass: function toolbarBackgroundClass () {
        if (this.toolbarBg) {
          return ("bg-" + (this.toolbarBg))
        }
      },

      buttonProps: function buttonProps () {
        var flat = this.toolbarOutline !== true &&
          this.toolbarPush !== true;

        return {
          type: 'a',
          flat: flat,
          noWrap: true,
          outline: this.toolbarOutline,
          push: this.toolbarPush,
          rounded: this.toolbarRounded,
          dense: true,
          color: this.toolbarColor,
          disable: !this.editable,
          size: 'sm'
        }
      },

      buttonDef: function buttonDef () {
        var
          e = this.$q.lang.editor,
          i = this.$q.iconSet.editor;

        return {
          bold: { cmd: 'bold', icon: i.bold, tip: e.bold, key: 66 },
          italic: { cmd: 'italic', icon: i.italic, tip: e.italic, key: 73 },
          strike: { cmd: 'strikeThrough', icon: i.strikethrough, tip: e.strikethrough, key: 83 },
          underline: { cmd: 'underline', icon: i.underline, tip: e.underline, key: 85 },
          unordered: { cmd: 'insertUnorderedList', icon: i.unorderedList, tip: e.unorderedList },
          ordered: { cmd: 'insertOrderedList', icon: i.orderedList, tip: e.orderedList },
          subscript: { cmd: 'subscript', icon: i.subscript, tip: e.subscript, htmlTip: 'x<subscript>2</subscript>' },
          superscript: { cmd: 'superscript', icon: i.superscript, tip: e.superscript, htmlTip: 'x<superscript>2</superscript>' },
          link: { cmd: 'link', disable: function (vm) { return vm.caret && !vm.caret.can('link'); }, icon: i.hyperlink, tip: e.hyperlink, key: 76 },
          fullscreen: { cmd: 'fullscreen', icon: i.toggleFullscreen, tip: e.toggleFullscreen, key: 70 },

          quote: { cmd: 'formatBlock', param: 'BLOCKQUOTE', icon: i.quote, tip: e.quote, key: 81 },
          left: { cmd: 'justifyLeft', icon: i.left, tip: e.left },
          center: { cmd: 'justifyCenter', icon: i.center, tip: e.center },
          right: { cmd: 'justifyRight', icon: i.right, tip: e.right },
          justify: { cmd: 'justifyFull', icon: i.justify, tip: e.justify },

          print: { type: 'no-state', cmd: 'print', icon: i.print, tip: e.print, key: 80 },
          outdent: { type: 'no-state', disable: function (vm) { return vm.caret && !vm.caret.can('outdent'); }, cmd: 'outdent', icon: i.outdent, tip: e.outdent },
          indent: { type: 'no-state', disable: function (vm) { return vm.caret && !vm.caret.can('indent'); }, cmd: 'indent', icon: i.indent, tip: e.indent },
          removeFormat: { type: 'no-state', cmd: 'removeFormat', icon: i.removeFormat, tip: e.removeFormat },
          hr: { type: 'no-state', cmd: 'insertHorizontalRule', icon: i.hr, tip: e.hr },
          undo: { type: 'no-state', cmd: 'undo', icon: i.undo, tip: e.undo, key: 90 },
          redo: { type: 'no-state', cmd: 'redo', icon: i.redo, tip: e.redo, key: 89 },

          h1: { cmd: 'formatBlock', param: 'H1', icon: i.header, tip: e.header1, htmlTip: ("<h1 class=\"q-ma-none\">" + (e.header1) + "</h1>") },
          h2: { cmd: 'formatBlock', param: 'H2', icon: i.header, tip: e.header2, htmlTip: ("<h2 class=\"q-ma-none\">" + (e.header2) + "</h2>") },
          h3: { cmd: 'formatBlock', param: 'H3', icon: i.header, tip: e.header3, htmlTip: ("<h3 class=\"q-ma-none\">" + (e.header3) + "</h3>") },
          h4: { cmd: 'formatBlock', param: 'H4', icon: i.header, tip: e.header4, htmlTip: ("<h4 class=\"q-ma-none\">" + (e.header4) + "</h4>") },
          h5: { cmd: 'formatBlock', param: 'H5', icon: i.header, tip: e.header5, htmlTip: ("<h5 class=\"q-ma-none\">" + (e.header5) + "</h5>") },
          h6: { cmd: 'formatBlock', param: 'H6', icon: i.header, tip: e.header6, htmlTip: ("<h6 class=\"q-ma-none\">" + (e.header6) + "</h6>") },
          p: { cmd: 'formatBlock', param: 'DIV', icon: i.header, tip: e.paragraph },
          code: { cmd: 'formatBlock', param: 'PRE', icon: i.code, htmlTip: ("<code>" + (e.code) + "</code>") },

          'size-1': { cmd: 'fontSize', param: '1', icon: i.size, tip: e.size1, htmlTip: ("<font size=\"1\">" + (e.size1) + "</font>") },
          'size-2': { cmd: 'fontSize', param: '2', icon: i.size, tip: e.size2, htmlTip: ("<font size=\"2\">" + (e.size2) + "</font>") },
          'size-3': { cmd: 'fontSize', param: '3', icon: i.size, tip: e.size3, htmlTip: ("<font size=\"3\">" + (e.size3) + "</font>") },
          'size-4': { cmd: 'fontSize', param: '4', icon: i.size, tip: e.size4, htmlTip: ("<font size=\"4\">" + (e.size4) + "</font>") },
          'size-5': { cmd: 'fontSize', param: '5', icon: i.size, tip: e.size5, htmlTip: ("<font size=\"5\">" + (e.size5) + "</font>") },
          'size-6': { cmd: 'fontSize', param: '6', icon: i.size, tip: e.size6, htmlTip: ("<font size=\"6\">" + (e.size6) + "</font>") },
          'size-7': { cmd: 'fontSize', param: '7', icon: i.size, tip: e.size7, htmlTip: ("<font size=\"7\">" + (e.size7) + "</font>") }
        }
      },

      buttons: function buttons () {
        var this$1 = this;

        var userDef = this.definitions || {};
        var def = this.definitions || this.fonts
          ? extend(
            true,
            {},
            this.buttonDef,
            userDef,
            getFonts(
              this.defaultFont,
              this.$q.lang.editor.defaultFont,
              this.$q.iconSet.editor.font,
              this.fonts
            )
          )
          : this.buttonDef;

        return this.toolbar.map(
          function (group) { return group.map(function (token) {
            if (token.options) {
              return {
                type: 'dropdown',
                icon: token.icon,
                label: token.label,
                size: 'sm',
                dense: true,
                fixedLabel: token.fixedLabel,
                fixedIcon: token.fixedIcon,
                highlight: token.highlight,
                list: token.list,
                options: token.options.map(function (item) { return def[item]; })
              }
            }

            var obj = def[token];

            if (obj) {
              return obj.type === 'no-state' || (userDef[token] && (
                obj.cmd === void 0 || (this$1.buttonDef[obj.cmd] && this$1.buttonDef[obj.cmd].type === 'no-state')
              ))
                ? obj
                : Object.assign({ type: 'toggle' }, obj)
            }
            else {
              return {
                type: 'slot',
                slot: token
              }
            }
          }); }
        )
      },

      keys: function keys () {
        var
          k = {},
          add = function (btn) {
            if (btn.key) {
              k[btn.key] = {
                cmd: btn.cmd,
                param: btn.param
              };
            }
          };

        this.buttons.forEach(function (group) {
          group.forEach(function (token) {
            if (token.options) {
              token.options.forEach(add);
            }
            else {
              add(token);
            }
          });
        });
        return k
      },

      innerStyle: function innerStyle () {
        return this.inFullscreen
          ? this.contentStyle
          : [
            {
              minHeight: this.minHeight,
              height: this.height,
              maxHeight: this.maxHeight
            },
            this.contentStyle
          ]
      },
      innerClass: function innerClass () {
        return [
          this.contentClass,
          { col: this.inFullscreen, 'overflow-auto': this.inFullscreen || this.maxHeight }
        ]
      }
    },

    data: function data () {
      return {
        editWatcher: true,
        editLinkUrl: null
      }
    },

    watch: {
      value: function value (v) {
        if (this.editWatcher) {
          this.$refs.content.innerHTML = v;
        }
        else {
          this.editWatcher = true;
        }
      }
    },

    methods: {
      __onInput: function __onInput () {
        if (this.editWatcher) {
          var val = this.$refs.content.innerHTML;
          if (val !== this.value) {
            this.editWatcher = false;
            this.$emit('input', val);
          }
        }
      },

      __onKeydown: function __onKeydown (e) {
        this.$emit('keydown', e);

        if (!e.ctrlKey) {
          this.refreshToolbar();
          this.$q.platform.is.ie && this.$nextTick(this.__onInput);
          return
        }

        var key = e.keyCode;
        var target = this.keys[key];
        if (target !== void 0) {
          var cmd = target.cmd;
          var param = target.param;
          stopAndPrevent(e);
          this.runCmd(cmd, param, false);
        }
      },

      __onClick: function __onClick (e) {
        this.refreshToolbar();
        this.$emit('click', e);
      },

      __onBlur: function __onBlur () {
        this.caret.save();
        this.$emit('blur');
      },

      runCmd: function runCmd (cmd, param, update) {
        var this$1 = this;
        if ( update === void 0 ) update = true;

        this.focus();
        this.caret.apply(cmd, param, function () {
          this$1.focus();
          if (this$1.$q.platform.is.ie === true || this$1.$q.platform.is.edge === true) {
            this$1.$nextTick(this$1.__onInput);
          }
          if (update) {
            this$1.refreshToolbar();
          }
        });
      },

      refreshToolbar: function refreshToolbar () {
        var this$1 = this;

        setTimeout(function () {
          this$1.editLinkUrl = null;
          this$1.$forceUpdate();
        }, 1);
      },

      focus: function focus () {
        this.$refs.content.focus();
      },

      getContentEl: function getContentEl () {
        return this.$refs.content
      }
    },

    created: function created () {
      if (isSSR === false) {
        document.execCommand('defaultParagraphSeparator', false, 'div');
        this.defaultFont = window.getComputedStyle(document.body).fontFamily;
      }
    },

    mounted: function mounted () {
      this.caret = new Caret(this.$refs.content, this);
      this.$refs.content.innerHTML = this.value;
      this.refreshToolbar();
    },

    render: function render (h) {
      var toolbars;

      if (this.hasToolbar) {
        var bars = [];

        bars.push(
          h('div', {
            key: 'qedt_top',
            staticClass: 'q-editor__toolbar row no-wrap scroll-x',
            class: this.toolbarBackgroundClass
          }, getToolbar(h, this))
        );

        this.editLinkUrl !== null && bars.push(
          h('div', {
            key: 'qedt_btm',
            staticClass: 'q-editor__toolbar row no-wrap items-center scroll-x',
            class: this.toolbarBackgroundClass
          }, getLinkEditor(h, this))
        );

        toolbars = h('div', {
          key: 'toolbar_ctainer',
          staticClass: 'q-editor__toolbars-container'
        }, bars);
      }

      return h(
        'div',
        {
          staticClass: 'q-editor',
          style: {
            height: this.inFullscreen ? '100vh' : null
          },
          'class': {
            disabled: this.disable,
            'fullscreen column': this.inFullscreen,
            'q-editor--square no-border-radius': this.square,
            'q-editor--flat': this.flat,
            'q-editor--dense': this.dense
          }
        },
        [
          toolbars,

          h(
            'div',
            {
              ref: 'content',
              staticClass: "q-editor__content",
              style: this.innerStyle,
              class: this.innerClass,
              attrs: { contenteditable: this.editable },
              domProps: isSSR
                ? { innerHTML: this.value }
                : undefined,
              on: Object.assign({}, this.$listeners,
                {input: this.__onInput,
                keydown: this.__onKeydown,
                click: this.__onClick,
                blur: this.__onBlur})
            }
          )
        ]
      )
    }
  });

  var FabMixin = {
    props: {
      type: {
        type: String,
        default: 'a'
      },
      outline: Boolean,
      push: Boolean,
      flat: Boolean,
      color: String,
      textColor: String,
      glossy: Boolean,

      disable: Boolean
    }
  };

  var QFab = Vue.extend({
    name: 'QFab',

    mixins: [ FabMixin, ModelToggleMixin ],

    provide: function provide () {
      var this$1 = this;

      return {
        __qFabClose: function (evt) {
          this$1.hide(evt);
          this$1.$refs.trigger && this$1.$refs.trigger.$el && this$1.$refs.trigger.$el.focus();
        }
      }
    },

    props: {
      icon: String,
      activeIcon: String,
      direction: {
        type: String,
        default: 'right',
        validator: function (v) { return ['up', 'right', 'down', 'left'].includes(v); }
      },
      persistent: Boolean
    },

    watch: {
      $route: function $route () {
        this.persistent !== true && this.hide();
      }
    },

    render: function render (h) {
      var tooltip = this.$scopedSlots.tooltip !== void 0
        ? this.$scopedSlots.tooltip()
        : [];

      return h('div', {
        staticClass: 'q-fab z-fab row inline justify-center',
        class: this.showing === true ? 'q-fab--opened' : null,
        on: this.$listeners
      }, [
        h(QBtn, {
          ref: 'trigger',
          props: Object.assign({}, this.$props,
            {icon: void 0,
            fab: true}),
          on: {
            click: this.toggle
          }
        }, tooltip.concat([
          h(QIcon, {
            staticClass: 'q-fab__icon absolute-full',
            props: { name: this.icon || this.$q.iconSet.fab.icon }
          }),
          h(QIcon, {
            staticClass: 'q-fab__active-icon absolute-full',
            props: { name: this.activeIcon || this.$q.iconSet.fab.activeIcon }
          })
        ])),

        h('div', {
          staticClass: 'q-fab__actions flex no-wrap inline items-center',
          class: ("q-fab__actions--" + (this.direction))
        }, slot(this, 'default'))
      ])
    },

    created: function created () {
      if (this.value === true && this.disable !== true) {
        this.showing = true;
      }
    }
  });

  var QFabAction = Vue.extend({
    name: 'QFabAction',

    mixins: [ FabMixin ],

    props: {
      icon: {
        type: String,
        required: true
      },

      to: [String, Object],
      replace: Boolean
    },

    inject: {
      __qFabClose: {
        default: function default$1 () {
          console.error('QFabAction needs to be child of QFab');
        }
      }
    },

    methods: {
      click: function click (e) {
        this.__qFabClose();
        this.$emit('click', e);
      }
    },

    render: function render (h) {
      return h(QBtn, {
        props: Object.assign({}, this.$props,
          {fabMini: true}),
        on: Object.assign({}, this.$listeners,
          {click: this.click})
      }, slot(this, 'default'))
    }
  });

  var QForm = Vue.extend({
    name: 'QForm',

    props: {
      autofocus: Boolean,
      noErrorFocus: Boolean,
      noResetFocus: Boolean
    },

    mounted: function mounted () {
      this.validateIndex = 0;
      this.autofocus === true && this.focus();
    },

    methods: {
      validate: function validate (shouldFocus) {
        var this$1 = this;

        var promises = [];
        var focus = typeof shouldFocus === 'boolean'
          ? shouldFocus
          : this.noErrorFocus !== true;

        this.validateIndex++;

        var components = getAllChildren(this);
        var emit = function (res) {
          this$1.$emit('validation-' + (res === true ? 'success' : 'error'));
        };

        var loop = function ( i ) {
          var comp = components[i];

          if (typeof comp.validate === 'function') {
            var valid = comp.validate();

            if (typeof valid.then === 'function') {
              promises.push(
                valid.then(
                  function (v) { return ({ valid: v, comp: comp }); },
                  function (error) { return ({ valid: false, comp: comp, error: error }); }
                )
              );
            }
            else if (valid !== true) {
              emit(false);

              if (focus === true && typeof comp.focus === 'function') {
                comp.focus();
              }

              return { v: Promise.resolve(false) }
            }
          }
        };

        for (var i = 0; i < components.length; i++) {
          var returned = loop( i );

          if ( returned ) return returned.v;
        }

        if (promises.length === 0) {
          emit(true);
          return Promise.resolve(true)
        }

        var index = this.validateIndex;

        return Promise.all(promises).then(
          function (res) {
            if (index === this$1.validateIndex) {
              var ref = res[0];
              var valid = ref.valid;
              var comp = ref.comp;

              emit(valid);

              if (
                focus === true &&
                valid !== true &&
                typeof comp.focus === 'function'
              ) {
                comp.focus();
              }

              return valid
            }
          }
        )
      },

      resetValidation: function resetValidation () {
        this.validateIndex++;

        getAllChildren(this).forEach(function (comp) {
          if (typeof comp.resetValidation === 'function') {
            comp.resetValidation();
          }
        });
      },

      submit: function submit (evt) {
        var this$1 = this;

        evt !== void 0 && stopAndPrevent(evt);

        this.validate().then(function (val) {
          val === true && this$1.$emit('submit', evt);
        });
      },

      reset: function reset (evt) {
        var this$1 = this;

        evt !== void 0 && stopAndPrevent(evt);

        this.$emit('reset');

        this.$nextTick(function () { // allow userland to reset values before
          this$1.resetValidation();
          if (this$1.autofocus === true && this$1.noResetFocus !== true) {
            this$1.focus();
          }
        });
      },

      focus: function focus () {
        var target = this.$el.querySelector('[autofocus]') || this.$el.querySelector('[tabindex]');
        target !== null && target.focus();
      }
    },

    render: function render (h) {
      return h('form', {
        staticClass: 'q-form',
        on: Object.assign({}, this.$listeners,
          {submit: this.submit,
          reset: this.reset})
      }, slot(this, 'default'))
    }
  });

  var QImg = Vue.extend({
    name: 'QImg',

    props: {
      src: String,
      srcset: String,
      sizes: String,
      alt: String,

      placeholderSrc: String,

      basic: Boolean,
      contain: Boolean,
      position: {
        type: String,
        default: '50% 50%'
      },
      ratio: [String, Number],
      transition: {
        type: String,
        default: 'fade'
      },

      spinnerColor: String,
      spinnerSize: String
    },

    data: function data () {
      return {
        currentSrc: '',
        image: null,
        isLoading: !!this.src,
        hasError: false,
        naturalRatio: void 0
      }
    },

    watch: {
      src: function src () {
        this.__load();
      },

      srcset: function srcset (val) {
        this.__updateWatcher(val);
      }
    },

    computed: {
      aspectRatio: function aspectRatio () {
        return this.ratio || this.naturalRatio
      },

      padding: function padding () {
        return this.aspectRatio !== void 0
          ? (1 / this.aspectRatio) * 100 + '%'
          : void 0
      },

      url: function url () {
        return this.currentSrc || this.placeholderSrc || void 0
      },

      attrs: function attrs () {
        var att = { role: 'img' };
        if (this.alt !== void 0) {
          att['aria-label'] = this.alt;
        }
        return att
      }
    },

    methods: {
      __onLoad: function __onLoad (img) {
        this.isLoading = false;
        this.hasError = false;
        this.__computeRatio(img);
        this.__updateSrc();
        this.__updateWatcher(this.srcset);
        this.$emit('load', this.currentSrc);
      },

      __onError: function __onError (err) {
        clearTimeout(this.ratioTimer);
        this.isLoading = false;
        this.hasError = true;
        this.currentSrc = '';
        this.$emit('error', err);
      },

      __updateSrc: function __updateSrc () {
        if (this.image !== void 0 && this.isLoading === false) {
          var src = this.image.currentSrc || this.image.src;
          if (this.currentSrc !== src) {
            this.currentSrc = src;
          }
        }
      },

      __updateWatcher: function __updateWatcher (srcset) {
        if (srcset) {
          if (this.unwatch === void 0) {
            this.unwatch = this.$watch('$q.screen.width', this.__updateSrc);
          }
        }
        else if (this.unwatch !== void 0) {
          this.unwatch();
          this.unwatch = void 0;
        }
      },

      __load: function __load () {
        var this$1 = this;

        clearTimeout(this.ratioTimer);
        this.hasError = false;

        if (!this.src) {
          this.isLoading = false;
          this.image = void 0;
          this.currentSrc = '';
          return
        }

        this.isLoading = true;

        var img = new Image();
        this.image = img;

        img.onerror = function (err) {
          // if we are still rendering same image
          if (this$1.image === img && this$1.destroyed !== true) {
            this$1.__onError(err);
          }
        };

        img.onload = function () {
          if (this$1.destroyed === true) {
            return
          }

          // if we are still rendering same image
          if (this$1.image === img) {
            if (img.decode !== void 0) {
              img
                .decode()
                .catch(function (err) {
                  if (this$1.image === img && this$1.destroyed !== true) {
                    this$1.__onError(err);
                  }
                })
                .then(function () {
                  if (this$1.image === img && this$1.destroyed !== true) {
                    this$1.__onLoad(img);
                  }
                });
            }
            else {
              this$1.__onLoad(img);
            }
          }
        };

        img.src = this.src;

        if (this.srcset) {
          img.srcset = this.srcset;
        }

        if (this.sizes) {
          img.sizes = this.sizes;
        }
      },

      __computeRatio: function __computeRatio (img) {
        var this$1 = this;

        var naturalHeight = img.naturalHeight;
        var naturalWidth = img.naturalWidth;

        if (naturalHeight || naturalWidth) {
          this.naturalRatio = naturalHeight === 0
            ? 1
            : naturalWidth / naturalHeight;
        }
        else {
          this.ratioTimer = setTimeout(function () {
            if (this$1.image === img && this$1.destroyed !== true) {
              this$1.__computeRatio(img);
            }
          }, 100);
        }
      },

      __getImage: function __getImage (h) {
        var content = this.url !== void 0 ? h('div', {
          key: this.url,
          staticClass: 'q-img__image absolute-full',
          style: {
            backgroundImage: ("url(\"" + (this.url) + "\")"),
            backgroundSize: this.contain ? 'contain' : 'cover',
            backgroundPosition: this.position
          }
        }) : null;

        return this.basic === true
          ? content
          : h('transition', {
            props: { name: 'q-transition--' + this.transition }
          }, [ content ])
      },

      __getContent: function __getContent (h) {
        var slotVm = slot(this, this.hasError === true ? 'error' : 'default');

        if (this.basic === true) {
          return h('div', {
            key: 'content',
            staticClass: 'q-img__content absolute-full'
          }, slotVm)
        }

        var content = this.isLoading === true
          ? h('div', {
            key: 'placeholder',
            staticClass: 'q-img__loading absolute-full flex flex-center'
          }, this.$scopedSlots.loading !== void 0 ? this.$scopedSlots.loading() : [
            h(QSpinner, {
              props: {
                color: this.spinnerColor,
                size: this.spinnerSize
              }
            })
          ])
          : h('div', {
            key: 'content',
            staticClass: 'q-img__content absolute-full'
          }, slotVm);

        return h('transition', {
          props: { name: 'q-transition--fade' }
        }, [ content ])
      }
    },

    render: function render (h) {
      return h('div', {
        staticClass: 'q-img overflow-hidden',
        attrs: this.attrs,
        on: this.$listeners
      }, [
        h('div', {
          style: { paddingBottom: this.padding }
        }),
        this.__getImage(h),
        this.__getContent(h)
      ])
    },

    beforeMount: function beforeMount () {
      if (this.placeholderSrc !== void 0 && this.ratio === void 0) {
        var img = new Image();
        img.src = this.placeholderSrc;
        this.__computeRatio(img);
      }
      this.isLoading === true && this.__load();
    },

    beforeDestroy: function beforeDestroy () {
      this.destroyed = true;
      clearTimeout(this.ratioTimer);
      this.unwatch !== void 0 && this.unwatch();
    }
  });

  var QInfiniteScroll = Vue.extend({
    name: 'QInfiniteScroll',

    props: {
      offset: {
        type: Number,
        default: 500
      },
      scrollTarget: {},
      disable: Boolean,
      reverse: Boolean
    },

    data: function data () {
      return {
        index: 0,
        fetching: false,
        working: true
      }
    },

    watch: {
      disable: function disable (val) {
        if (val === true) {
          this.stop();
        }
        else {
          this.resume();
        }
      },

      scrollTarget: function scrollTarget () {
        this.updateScrollTarget();
      }
    },

    methods: {
      poll: function poll () {
        if (this.disable === true || this.fetching === true || this.working === false) {
          return
        }

        var
          scrollHeight = getScrollHeight(this.scrollContainer),
          scrollPosition = getScrollPosition(this.scrollContainer),
          containerHeight = height(this.scrollContainer);

        if (this.reverse === false) {
          if (scrollPosition + containerHeight + this.offset >= scrollHeight) {
            this.trigger();
          }
        }
        else {
          if (scrollPosition < this.offset) {
            this.trigger();
          }
        }
      },

      trigger: function trigger () {
        var this$1 = this;

        if (this.disable === true || this.fetching === true || this.working === false) {
          return
        }

        this.index++;
        this.fetching = true;

        var heightBefore = getScrollHeight(this.scrollContainer);

        this.$emit('load', this.index, function (stop) {
          if (this$1.working === true) {
            this$1.fetching = false;
            this$1.$nextTick(function () {
              if (this$1.reverse === true) {
                var
                  heightAfter = getScrollHeight(this$1.scrollContainer),
                  scrollPosition = getScrollPosition(this$1.scrollContainer),
                  heightDifference = heightAfter - heightBefore;

                this$1.scrollContainer.scrollTop = scrollPosition + heightDifference;
              }

              if (stop === true) {
                this$1.stop();
              }
              else {
                this$1.$el.closest('body') && this$1.poll();
              }
            });
          }
        });
      },

      reset: function reset () {
        this.index = 0;
      },

      resume: function resume () {
        if (this.working === false) {
          this.working = true;
          this.scrollContainer.addEventListener('scroll', this.poll, listenOpts.passive);
        }
        this.immediatePoll();
      },

      stop: function stop () {
        if (this.working === true) {
          this.working = false;
          this.fetching = false;
          this.scrollContainer.removeEventListener('scroll', this.poll, listenOpts.passive);
        }
      },

      updateScrollTarget: function updateScrollTarget () {
        if (this.scrollContainer && this.working === true) {
          this.scrollContainer.removeEventListener('scroll', this.poll, listenOpts.passive);
        }

        if (typeof this.scrollTarget === 'string') {
          this.scrollContainer = document.querySelector(this.scrollTarget);
          if (this.scrollContainer === null) {
            console.error(("InfiniteScroll: scroll target container \"" + (this.scrollTarget) + "\" not found"), this);
            return
          }
        }
        else {
          this.scrollContainer = this.scrollTarget === document.defaultView || this.scrollTarget instanceof Element
            ? this.scrollTarget
            : getScrollTarget(this.$el);
        }

        if (this.working === true) {
          this.scrollContainer.addEventListener('scroll', this.poll, listenOpts.passive);
        }
      }
    },

    mounted: function mounted () {
      this.immediatePoll = this.poll;
      this.poll = debounce(this.poll, 100);

      this.updateScrollTarget();
      this.immediatePoll();

      if (this.reverse === true) {
        var
          scrollHeight = getScrollHeight(this.scrollContainer),
          containerHeight = height(this.scrollContainer);

        this.scrollContainer.scrollTop = scrollHeight - containerHeight;
      }
    },

    beforeDestroy: function beforeDestroy () {
      if (this.working === true) {
        this.scrollContainer.removeEventListener('scroll', this.poll, listenOpts.passive);
      }
    },

    render: function render (h) {
      var content = this.$scopedSlots.default !== void 0
        ? this.$scopedSlots.default()
        : [];
      var body = this.fetching === true
        ? [ h('div', { staticClass: 'q-infinite-scroll__loading' }, slot(this, 'loading')) ]
        : [];

      return h(
        'div',
        { staticClass: 'q-infinite-scroll' },
        this.reverse === false
          ? content.concat(body)
          : body.concat(content)
      )
    }
  });

  var QInnerLoading = Vue.extend({
    name: 'QInnerLoading',

    mixins: [ TransitionMixin ],

    props: {
      showing: Boolean,
      color: String,

      size: {
        type: [String, Number],
        default: 42
      },

      dark: Boolean
    },

    render: function render (h) {
      var content = this.$scopedSlots.default !== void 0
        ? this.$scopedSlots.default()
        : [
          h(QSpinner, {
            props: {
              size: this.size,
              color: this.color
            }
          })
        ];

      return h('transition', {
        props: { name: this.transition }
      }, [
        this.showing === true ? h('div', {
          staticClass: 'q-inner-loading absolute-full column flex-center',
          class: this.dark ? 'q-inner-loading--dark' : null,
          on: this.$listeners
        }, content) : null
      ])
    }
  });

  // PGDOWN, LEFT, DOWN, PGUP, RIGHT, UP
  var keyCodes$1 = [34, 37, 40, 33, 39, 38];

  var QKnob = Vue.extend({
    name: 'QKnob',

    mixins: [{
      props: QCircularProgress.options.props
    }],

    directives: {
      TouchPan: TouchPan
    },

    props: {
      step: {
        type: Number,
        default: 1,
        validator: function (v) { return v >= 0; }
      },

      tabindex: {
        type: [Number, String],
        default: 0
      },

      disable: Boolean,
      readonly: Boolean
    },

    data: function data () {
      return {
        model: this.value,
        dragging: false
      }
    },

    watch: {
      value: function value (value$1) {
        if (value$1 < this.min) {
          this.model = this.min;
        }
        else if (value$1 > this.max) {
          this.model = this.max;
        }
        else {
          if (value$1 !== this.model) {
            this.model = value$1;
          }
          return
        }

        if (this.model !== this.value) {
          this.$emit('input', this.model);
          this.$emit('change', this.model);
        }
      }
    },

    computed: {
      classes: function classes () {
        return {
          disabled: this.disable,
          'q-knob--editable': this.editable
        }
      },

      editable: function editable () {
        return !this.disable && !this.readonly
      },

      decimals: function decimals () {
        return (String(this.step).trim('0').split('.')[1] || '').length
      },

      computedStep: function computedStep () {
        return this.step === 0 ? 1 : this.step
      }
    },

    methods: {
      __pan: function __pan (event) {
        if (event.isFinal) {
          this.__updatePosition(event.evt, true);
          this.dragging = false;
        }
        else if (event.isFirst) {
          var ref = this.$el.getBoundingClientRect();
          var top = ref.top;
          var left = ref.left;
          var width = ref.width;
          var height = ref.height;
          this.centerPosition = {
            top: top + height / 2,
            left: left + width / 2
          };
          this.dragging = true;
          this.__updatePosition(event.evt);
        }
        else {
          this.__updatePosition(event.evt);
        }
      },

      __click: function __click (evt) {
        var ref = this.$el.getBoundingClientRect();
        var top = ref.top;
        var left = ref.left;
        var width = ref.width;
        var height = ref.height;
        this.centerPosition = {
          top: top + height / 2,
          left: left + width / 2
        };
        this.__updatePosition(evt, true);
      },

      __keydown: function __keydown (evt) {
        if (!keyCodes$1.includes(evt.keyCode)) {
          return
        }

        stopAndPrevent(evt);

        var
          step = ([34, 33].includes(evt.keyCode) ? 10 : 1) * this.computedStep,
          offset = [34, 37, 40].includes(evt.keyCode) ? -step : step;

        this.model = between(
          parseFloat((this.model + offset).toFixed(this.decimals)),
          this.min,
          this.max
        );

        this.__updateValue();
      },

      __keyup: function __keyup (evt) {
        if (keyCodes$1.includes(evt.keyCode)) {
          this.__updateValue(true);
        }
      },

      __updatePosition: function __updatePosition (evt, change) {
        var
          center = this.centerPosition,
          pos = position(evt),
          height = Math.abs(pos.top - center.top),
          distance = Math.sqrt(
            Math.pow( height, 2 ) +
            Math.pow( Math.abs(pos.left - center.left), 2 )
          );

        var angle = Math.asin(height / distance) * (180 / Math.PI);

        if (pos.top < center.top) {
          angle = center.left < pos.left ? 90 - angle : 270 + angle;
        }
        else {
          angle = center.left < pos.left ? angle + 90 : 270 - angle;
        }

        if (this.angle) {
          angle = normalizeToInterval(angle - this.angle, 0, 360);
        }

        if (this.$q.lang.rtl) {
          angle = 360 - angle;
        }

        var model = this.min + (angle / 360) * (this.max - this.min);

        if (this.step !== 0) {
          var
            step = this.computedStep,
            modulo = model % step;

          model = model - modulo +
            (Math.abs(modulo) >= step / 2 ? (modulo < 0 ? -1 : 1) * step : 0);

          model = parseFloat(model.toFixed(this.decimals));
        }

        model = between(model, this.min, this.max);

        this.$emit('drag-value', model);

        if (this.model !== model) {
          this.model = model;
        }

        this.__updateValue(change);
      },

      __updateValue: function __updateValue (change) {
        this.value !== this.model && this.$emit('input', this.model);
        change === true && this.$emit('change', this.model);
      }
    },

    render: function render (h) {
      var data = {
        staticClass: 'q-knob non-selectable',
        class: this.classes,

        props: Object.assign({}, this.$props,
          {value: this.model,
          instantFeedback: this.dragging})
      };

      if (this.editable === true) {
        data.attrs = { tabindex: this.tabindex };
        data.on = {
          click: this.__click,
          keydown: this.__keydown,
          keyup: this.__keyup
        };
        data.directives = [{
          name: 'touch-pan',
          value: this.__pan,
          modifiers: {
            prevent: true,
            stop: true,
            mouse: true
          }
        }];
      }

      return h(QCircularProgress, data, slot(this, 'default'))
    }
  });

  var QScrollObserver = Vue.extend({
    name: 'QScrollObserver',

    props: {
      debounce: [String, Number],
      horizontal: Boolean
    },

    render: function render () {}, // eslint-disable-line

    data: function data () {
      return {
        pos: 0,
        dir: this.horizontal === true ? 'right' : 'down',
        dirChanged: false,
        dirChangePos: 0
      }
    },

    methods: {
      getPosition: function getPosition () {
        return {
          position: this.pos,
          direction: this.dir,
          directionChanged: this.dirChanged,
          inflexionPosition: this.dirChangePos
        }
      },

      trigger: function trigger (immediately) {
        if (immediately === true || this.debounce === 0 || this.debounce === '0') {
          this.__emit();
        }
        else if (!this.timer) {
          this.timer = this.debounce
            ? setTimeout(this.__emit, this.debounce)
            : requestAnimationFrame(this.__emit);
        }
      },

      __emit: function __emit () {
        var
          pos = Math.max(0, (this.horizontal === true ? getHorizontalScrollPosition(this.target) : getScrollPosition(this.target))),
          delta = pos - this.pos,
          dir = this.horizontal
            ? delta < 0 ? 'left' : 'right'
            : delta < 0 ? 'up' : 'down';
        this.dirChanged = this.dir !== dir;
        if (this.dirChanged) {
          this.dir = dir;
          this.dirChangePos = this.pos;
        }

        this.timer = null;
        this.pos = pos;
        this.$emit('scroll', this.getPosition());
      }
    },

    mounted: function mounted () {
      this.target = getScrollTarget(this.$el.parentNode);
      this.target.addEventListener('scroll', this.trigger, listenOpts.passive);
      this.trigger(true);
    },

    beforeDestroy: function beforeDestroy () {
      clearTimeout(this.timer);
      cancelAnimationFrame(this.timer);
      this.target.removeEventListener('scroll', this.trigger, listenOpts.passive);
    }
  });

  var QLayout = Vue.extend({
    name: 'QLayout',

    provide: function provide () {
      return {
        layout: this
      }
    },

    props: {
      container: Boolean,
      view: {
        type: String,
        default: 'hhh lpr fff',
        validator: function (v) { return /^(h|l)h(h|r) lpr (f|l)f(f|r)$/.test(v.toLowerCase()); }
      }
    },

    data: function data () {
      return {
        // page related
        height: onSSR === true ? 0 : window.innerHeight,
        width: onSSR === true || this.container === true ? 0 : window.innerWidth,

        // container only prop
        containerHeight: 0,
        scrollbarWidth: onSSR === true ? 0 : getScrollbarWidth(),

        header: {
          size: 0,
          offset: 0,
          space: false
        },
        right: {
          size: 300,
          offset: 0,
          space: false
        },
        footer: {
          size: 0,
          offset: 0,
          space: false
        },
        left: {
          size: 300,
          offset: 0,
          space: false
        },

        scroll: {
          position: 0,
          direction: 'down'
        }
      }
    },

    computed: {
      rows: function rows () {
        var rows = this.view.toLowerCase().split(' ');
        return {
          top: rows[0].split(''),
          middle: rows[1].split(''),
          bottom: rows[2].split('')
        }
      },

      style: function style () {
        return this.container === true
          ? null
          : { minHeight: this.$q.screen.height + 'px' }
      },

      // used by container only
      targetStyle: function targetStyle () {
        var obj;

        if (this.scrollbarWidth !== 0) {
          return ( obj = {}, obj[this.$q.lang.rtl === true ? 'left' : 'right'] = ((this.scrollbarWidth) + "px"), obj )
        }
      },

      targetChildStyle: function targetChildStyle () {
        var obj;

        if (this.scrollbarWidth !== 0) {
          return ( obj = {}, obj[this.$q.lang.rtl === true ? 'right' : 'left'] = 0, obj[this.$q.lang.rtl === true ? 'left' : 'right'] = ("-" + (this.scrollbarWidth) + "px"), obj.width = ("calc(100% + " + (this.scrollbarWidth) + "px)"), obj )
        }
      }
    },

    created: function created () {
      this.instances = {};
    },

    render: function render (h) {
      var layout = h('div', {
        staticClass: 'q-layout q-layout--' +
          (this.container === true ? 'containerized' : 'standard'),
        style: this.style
      }, [
        h(QScrollObserver, {
          on: { scroll: this.__onPageScroll }
        }),
        h(QResizeObserver, {
          on: { resize: this.__onPageResize }
        })
      ].concat(
        slot(this, 'default')
      ));

      return this.container === true
        ? h('div', {
          staticClass: 'q-layout-container overflow-hidden'
        }, [
          h(QResizeObserver, {
            on: { resize: this.__onContainerResize }
          }),
          h('div', {
            staticClass: 'absolute-full',
            style: this.targetStyle
          }, [
            h('div', {
              staticClass: 'scroll',
              style: this.targetChildStyle
            }, [ layout ])
          ])
        ])
        : layout
    },

    methods: {
      __animate: function __animate () {
        var this$1 = this;

        if (this.timer !== void 0) {
          clearTimeout(this.timer);
        }
        else {
          document.body.classList.add('q-body--layout-animate');
        }
        this.timer = setTimeout(function () {
          document.body.classList.remove('q-body--layout-animate');
          this$1.timer = void 0;
        }, 150);
      },

      __onPageScroll: function __onPageScroll (data) {
        this.scroll = data;
        this.$listeners.scroll !== void 0 && this.$emit('scroll', data);
      },

      __onPageResize: function __onPageResize (ref) {
        var height = ref.height;
        var width = ref.width;

        var resized = false;

        if (this.height !== height) {
          resized = true;
          this.height = height;
          if (this.$listeners['scroll-height'] !== void 0) {
            this.$emit('scroll-height', height);
          }
          this.__updateScrollbarWidth();
        }
        if (this.width !== width) {
          resized = true;
          this.width = width;
        }

        if (resized === true && this.$listeners.resize !== void 0) {
          this.$emit('resize', { height: height, width: width });
        }
      },

      __onContainerResize: function __onContainerResize (ref) {
        var height = ref.height;

        if (this.containerHeight !== height) {
          this.containerHeight = height;
          this.__updateScrollbarWidth();
        }
      },

      __updateScrollbarWidth: function __updateScrollbarWidth () {
        if (this.container === true) {
          var width = this.height > this.containerHeight
            ? getScrollbarWidth()
            : 0;

          if (this.scrollbarWidth !== width) {
            this.scrollbarWidth = width;
          }
        }
      }
    }
  });

  var duration = 150;

  var QDrawer = Vue.extend({
    name: 'QDrawer',

    inject: {
      layout: {
        default: function default$1 () {
          console.error('QDrawer needs to be child of QLayout');
        }
      }
    },

    mixins: [ ModelToggleMixin, PreventScrollMixin ],

    directives: {
      TouchPan: TouchPan
    },

    props: {
      overlay: Boolean,
      side: {
        type: String,
        default: 'left',
        validator: function (v) { return ['left', 'right'].includes(v); }
      },
      width: {
        type: Number,
        default: 300
      },
      mini: Boolean,
      miniToOverlay: Boolean,
      miniWidth: {
        type: Number,
        default: 57
      },
      breakpoint: {
        type: Number,
        default: 1023
      },
      behavior: {
        type: String,
        validator: function (v) { return ['default', 'desktop', 'mobile'].includes(v); },
        default: 'default'
      },
      bordered: Boolean,
      elevated: Boolean,
      persistent: Boolean,
      showIfAbove: Boolean,
      contentStyle: [String, Object, Array],
      contentClass: [String, Object, Array],
      noSwipeOpen: Boolean,
      noSwipeClose: Boolean
    },

    data: function data () {
      var this$1 = this;

      var
        largeScreenState = this.showIfAbove === true || (
          this.value !== void 0 ? this.value : true
        ),
        showing = this.behavior !== 'mobile' && this.breakpoint < this.layout.width && this.overlay === false
          ? largeScreenState
          : false;

      if (this.value !== void 0 && this.value !== showing) {
        // setTimeout needed otherwise
        // it breaks Vue state
        setTimeout(function () {
          this$1.$emit('input', showing);
        });
      }

      return {
        showing: showing,
        belowBreakpoint: (
          this.behavior === 'mobile' ||
          (this.behavior !== 'desktop' && this.breakpoint >= this.layout.width)
        ),
        largeScreenState: largeScreenState,
        mobileOpened: false
      }
    },

    watch: {
      belowBreakpoint: function belowBreakpoint (val) {
        if (this.mobileOpened === true) {
          return
        }

        if (val === true) { // from lg to xs
          if (this.overlay === false) {
            this.largeScreenState = this.showing;
          }
          // ensure we close it for small screen
          this.hide(false);
        }
        else if (this.overlay === false) { // from xs to lg
          this[this.largeScreenState ? 'show' : 'hide'](false);
        }
      },

      side: function side (_, oldSide) {
        this.layout[oldSide].space = false;
        this.layout[oldSide].offset = 0;
      },

      behavior: function behavior (val) {
        this.__updateLocal('belowBreakpoint', (
          val === 'mobile' ||
          (val !== 'desktop' && this.breakpoint >= this.layout.width)
        ));
      },

      breakpoint: function breakpoint (val) {
        this.__updateLocal('belowBreakpoint', (
          this.behavior === 'mobile' ||
          (this.behavior !== 'desktop' && val >= this.layout.width)
        ));
      },

      'layout.width': function layout_width (val) {
        this.__updateLocal('belowBreakpoint', (
          this.behavior === 'mobile' ||
          (this.behavior !== 'desktop' && this.breakpoint >= val)
        ));
      },

      'layout.scrollbarWidth': function layout_scrollbarWidth () {
        this.applyPosition(this.showing === true ? 0 : void 0);
      },

      offset: function offset (val) {
        this.__update('offset', val);
      },

      onLayout: function onLayout (val) {
        this.$listeners['on-layout'] !== void 0 && this.$emit('on-layout', val);
        this.__update('space', val);
      },

      $route: function $route () {
        if (
          this.persistent !== true &&
          (this.mobileOpened === true || this.onScreenOverlay === true)
        ) {
          this.hide();
        }
      },

      rightSide: function rightSide () {
        this.applyPosition();
      },

      size: function size (val) {
        this.applyPosition();
        this.__updateSizeOnLayout(this.miniToOverlay, val);
      },

      miniToOverlay: function miniToOverlay (val) {
        this.__updateSizeOnLayout(val, this.size);
      },

      '$q.lang.rtl': function $q_lang_rtl () {
        this.applyPosition();
      },

      mini: function mini () {
        if (this.value === true) {
          this.__animateMini();
          this.layout.__animate();
        }
      }
    },

    computed: {
      rightSide: function rightSide () {
        return this.side === 'right'
      },

      offset: function offset () {
        return this.showing === true && this.mobileOpened === false && this.overlay === false
          ? (this.miniToOverlay === true ? this.miniWidth : this.size)
          : 0
      },

      size: function size () {
        return this.isMini === true
          ? this.miniWidth
          : this.width
      },

      fixed: function fixed () {
        return this.overlay === true ||
          this.miniToOverlay === true ||
          this.layout.view.indexOf(this.rightSide ? 'R' : 'L') > -1
      },

      onLayout: function onLayout () {
        return this.showing === true && this.mobileView === false && this.overlay === false
      },

      onScreenOverlay: function onScreenOverlay () {
        return this.showing === true && this.mobileView === false && this.overlay === true
      },

      backdropClass: function backdropClass () {
        return this.showing === false ? 'no-pointer-events' : null
      },

      mobileView: function mobileView () {
        return this.belowBreakpoint === true || this.mobileOpened === true
      },

      headerSlot: function headerSlot () {
        return this.rightSide === true
          ? this.layout.rows.top[2] === 'r'
          : this.layout.rows.top[0] === 'l'
      },

      footerSlot: function footerSlot () {
        return this.rightSide === true
          ? this.layout.rows.bottom[2] === 'r'
          : this.layout.rows.bottom[0] === 'l'
      },

      aboveStyle: function aboveStyle () {
        var css = {};

        if (this.layout.header.space === true && this.headerSlot === false) {
          if (this.fixed === true) {
            css.top = (this.layout.header.offset) + "px";
          }
          else if (this.layout.header.space === true) {
            css.top = (this.layout.header.size) + "px";
          }
        }

        if (this.layout.footer.space === true && this.footerSlot === false) {
          if (this.fixed === true) {
            css.bottom = (this.layout.footer.offset) + "px";
          }
          else if (this.layout.footer.space === true) {
            css.bottom = (this.layout.footer.size) + "px";
          }
        }

        return css
      },

      style: function style () {
        var style = { width: ((this.size) + "px") };
        return this.mobileView === true
          ? style
          : Object.assign(style, this.aboveStyle)
      },

      classes: function classes () {
        return "q-drawer--" + (this.side) +
          (this.bordered === true ? ' q-drawer--bordered' : '') +
          (
            this.mobileView === true
              ? ' fixed q-drawer--on-top q-drawer--mobile q-drawer--top-padding'
              : " q-drawer--" + (this.isMini === true ? 'mini' : 'standard') +
              (this.fixed === true || this.onLayout !== true ? ' fixed' : '') +
              (this.overlay === true || this.miniToOverlay === true ? ' q-drawer--on-top' : '') +
              (this.headerSlot === true ? ' q-drawer--top-padding' : '')
          )
      },

      stateDirection: function stateDirection () {
        return (this.$q.lang.rtl === true ? -1 : 1) * (this.rightSide === true ? 1 : -1)
      },

      isMini: function isMini () {
        return this.mini === true && this.mobileView !== true
      },

      onNativeEvents: function onNativeEvents () {
        var this$1 = this;

        if (this.mobileView !== true) {
          return {
            '!click': function (e) { this$1.$emit('click', e); },
            mouseover: function (e) { this$1.$emit('mouseover', e); },
            mouseout: function (e) { this$1.$emit('mouseout', e); },
            mouseenter: function (e) { this$1.$emit('mouseenter', e); },
            mouseleave: function (e) { this$1.$emit('mouseleave', e); }
          }
        }
      }
    },

    methods: {
      applyPosition: function applyPosition (position) {
        var this$1 = this;

        if (position === void 0) {
          this.$nextTick(function () {
            position = this$1.showing === true ? 0 : this$1.size;

            this$1.applyPosition(this$1.stateDirection * position);
          });
        }
        else if (this.$refs.content !== void 0) {
          if (
            this.layout.container === true &&
            this.rightSide === true &&
            (this.mobileView === true || Math.abs(position) === this.size)
          ) {
            position += this.stateDirection * this.layout.scrollbarWidth;
          }
          this.$refs.content.style.transform = "translate3d(" + position + "px, 0, 0)";
        }
      },

      applyBackdrop: function applyBackdrop (x) {
        if (this.$refs.backdrop !== void 0) {
          this.$refs.backdrop.style.backgroundColor =
            this.lastBackdropBg = "rgba(0,0,0," + (x * 0.4) + ")";
        }
      },

      __setScrollable: function __setScrollable (v) {
        if (this.layout.container !== true) {
          document.body.classList[v === true ? 'add' : 'remove']('q-body--drawer-toggle');
        }
      },

      __animateMini: function __animateMini () {
        var this$1 = this;

        if (this.timerMini !== void 0) {
          clearTimeout(this.timerMini);
        }
        else if (this.$el !== void 0) {
          this.$el.classList.add('q-drawer--mini-animate');
        }
        this.timerMini = setTimeout(function () {
          this$1.$el !== void 0 && this$1.$el.classList.remove('q-drawer--mini-animate');
          this$1.timerMini = void 0;
        }, 150);
      },

      __openByTouch: function __openByTouch (evt) {
        if (this.showing !== false) {
          // some browsers might capture and trigger this
          // even if Drawer has just been opened (but animation is still pending)
          return
        }

        var
          width = this.size,
          position = between(evt.distance.x, 0, width);

        if (evt.isFinal === true) {
          var
            el = this.$refs.content,
            opened = position >= Math.min(75, width);

          el.classList.remove('no-transition');

          if (opened === true) {
            this.show();
          }
          else {
            this.layout.__animate();
            this.applyBackdrop(0);
            this.applyPosition(this.stateDirection * width);
            el.classList.remove('q-drawer--delimiter');
          }

          return
        }

        this.applyPosition(
          (this.$q.lang.rtl === true ? !this.rightSide : this.rightSide)
            ? Math.max(width - position, 0)
            : Math.min(0, position - width)
        );
        this.applyBackdrop(
          between(position / width, 0, 1)
        );

        if (evt.isFirst === true) {
          var el$1 = this.$refs.content;
          el$1.classList.add('no-transition');
          el$1.classList.add('q-drawer--delimiter');
        }
      },

      __closeByTouch: function __closeByTouch (evt) {
        if (this.showing !== true) {
          // some browsers might capture and trigger this
          // even if Drawer has just been closed (but animation is still pending)
          return
        }

        var
          width = this.size,
          dir = evt.direction === this.side,
          position = (this.$q.lang.rtl === true ? !dir : dir)
            ? between(evt.distance.x, 0, width)
            : 0;

        if (evt.isFinal === true) {
          var opened = Math.abs(position) < Math.min(75, width);
          this.$refs.content.classList.remove('no-transition');

          if (opened === true) {
            this.layout.__animate();
            this.applyBackdrop(1);
            this.applyPosition(0);
          }
          else {
            this.hide();
          }

          return
        }

        this.applyPosition(this.stateDirection * position);
        this.applyBackdrop(between(1 - position / width, 0, 1));

        if (evt.isFirst === true) {
          this.$refs.content.classList.add('no-transition');
        }
      },

      __show: function __show (evt) {
        var this$1 = this;
        if ( evt === void 0 ) evt = true;

        evt !== false && this.layout.__animate();
        this.applyPosition(0);

        var otherSide = this.layout.instances[this.rightSide === true ? 'left' : 'right'];
        if (otherSide !== void 0 && otherSide.mobileOpened === true) {
          otherSide.hide(false);
        }

        if (this.belowBreakpoint === true) {
          this.mobileOpened = true;
          this.applyBackdrop(1);
          if (this.layout.container !== true) {
            this.__preventScroll(true);
          }
        }
        else {
          this.__setScrollable(true);
        }

        clearTimeout(this.timer);
        this.timer = setTimeout(function () {
          this$1.__setScrollable(false);
          this$1.$emit('show', evt);
        }, duration);
      },

      __hide: function __hide (evt) {
        var this$1 = this;
        if ( evt === void 0 ) evt = true;

        evt !== false && this.layout.__animate();

        if (this.mobileOpened === true) {
          this.mobileOpened = false;
        }

        this.applyPosition(this.stateDirection * this.size);
        this.applyBackdrop(0);

        this.__cleanup();

        clearTimeout(this.timer);
        this.timer = setTimeout(function () {
          this$1.$emit('hide', evt);
        }, duration);
      },

      __cleanup: function __cleanup () {
        this.__preventScroll(false);
        this.__setScrollable(false);
      },

      __update: function __update (prop, val) {
        if (this.layout[this.side][prop] !== val) {
          this.layout[this.side][prop] = val;
        }
      },

      __updateLocal: function __updateLocal (prop, val) {
        if (this[prop] !== val) {
          this[prop] = val;
        }
      },

      __updateSizeOnLayout: function __updateSizeOnLayout (miniToOverlay, size) {
        this.__update('size', miniToOverlay === true ? this.miniWidth : size);
      }
    },

    created: function created () {
      this.layout.instances[this.side] = this;
      this.__updateSizeOnLayout(this.miniToOverlay, this.size);
      this.__update('space', this.onLayout);
      this.__update('offset', this.offset);
    },

    mounted: function mounted () {
      this.$listeners['on-layout'] !== void 0 && this.$emit('on-layout', this.onLayout);
      this.applyPosition(this.showing === true ? 0 : void 0);
    },

    beforeDestroy: function beforeDestroy () {
      clearTimeout(this.timer);
      clearTimeout(this.timerMini);
      this.showing === true && this.__cleanup();
      if (this.layout.instances[this.side] === this) {
        this.layout.instances[this.side] = void 0;
        this.__update('size', 0);
        this.__update('offset', 0);
        this.__update('space', false);
      }
    },

    render: function render (h) {
      var directives = [{
        name: 'touch-pan',
        modifiers: {
          horizontal: true,
          mouse: true,
          mouseAllDir: true
        },
        value: this.__closeByTouch
      }];

      var child = [
        this.noSwipeOpen !== true && this.belowBreakpoint === true
          ? h('div', {
            staticClass: ("q-drawer__opener fixed-" + (this.side)),
            directives: [{
              name: 'touch-pan',
              modifiers: {
                horizontal: true,
                mouse: true,
                mouseAllDir: true
              },
              value: this.__openByTouch
            }]
          })
          : null,

        this.mobileView === true ? h('div', {
          ref: 'backdrop',
          staticClass: 'fullscreen q-drawer__backdrop',
          class: this.backdropClass,
          style: this.lastBackdropBg !== void 0
            ? { backgroundColor: this.lastBackdropBg }
            : null,
          on: { click: this.hide },
          directives: directives
        }) : null
      ];

      var content = [
        h('div', {
          staticClass: 'q-drawer__content fit ' + (this.layout.container === true ? 'overflow-auto' : 'scroll'),
          class: this.contentClass,
          style: this.contentStyle
        }, this.isMini === true && this.$scopedSlots.mini !== void 0 ? this.$scopedSlots.mini() : slot(this, 'default'))
      ];

      if (this.elevated === true && this.showing === true) {
        content.push(
          h('div', {
            staticClass: 'q-layout__shadow absolute-full overflow-hidden no-pointer-events'
          })
        );
      }

      return h('div', {
        staticClass: 'q-drawer-container'
      }, child.concat([
        h('aside', {
          ref: 'content',
          staticClass: "q-drawer",
          class: this.classes,
          style: this.style,
          on: this.onNativeEvents,
          directives: this.mobileView === true && this.noSwipeClose !== true
            ? directives
            : void 0
        }, content)
      ]))
    }
  });

  var QFooter = Vue.extend({
    name: 'QFooter',

    mixins: [ CanRenderMixin ],

    inject: {
      layout: {
        default: function default$1 () {
          console.error('QFooter needs to be child of QLayout');
        }
      }
    },

    props: {
      value: {
        type: Boolean,
        default: true
      },
      reveal: Boolean,
      bordered: Boolean,
      elevated: Boolean
    },

    data: function data () {
      return {
        size: 0,
        revealed: true,
        windowHeight: onSSR || this.layout.container ? 0 : window.innerHeight
      }
    },

    watch: {
      value: function value (val) {
        this.__update('space', val);
        this.__updateLocal('revealed', true);
        this.layout.__animate();
      },

      offset: function offset (val) {
        this.__update('offset', val);
      },

      reveal: function reveal (val) {
        val === false && this.__updateLocal('revealed', this.value);
      },

      revealed: function revealed (val) {
        this.layout.__animate();
        this.$emit('reveal', val);
      },

      'layout.scroll': function layout_scroll () {
        this.__updateRevealed();
      },

      'layout.height': function layout_height () {
        this.__updateRevealed();
      },

      size: function size () {
        this.__updateRevealed();
      },

      '$q.screen.height': function $q_screen_height (val) {
        this.layout.container !== true && this.__updateLocal('windowHeight', val);
      }
    },

    computed: {
      fixed: function fixed () {
        return this.reveal === true ||
          this.layout.view.indexOf('F') > -1 ||
          this.layout.container === true
      },

      containerHeight: function containerHeight () {
        return this.layout.container === true
          ? this.layout.containerHeight
          : this.windowHeight
      },

      offset: function offset () {
        if (this.canRender !== true || this.value !== true) {
          return 0
        }
        if (this.fixed === true) {
          return this.revealed === true ? this.size : 0
        }
        var offset = this.layout.scroll.position + this.containerHeight + this.size - this.layout.height;
        return offset > 0 ? offset : 0
      },

      classes: function classes () {
        return (
          (this.fixed === true ? 'fixed' : 'absolute') + '-bottom') +
          (this.value === true || this.fixed === true ? '' : ' hidden') +
          (this.bordered === true ? ' q-footer--bordered' : '') +
          (
            this.canRender !== true || this.value !== true || (this.fixed === true && this.revealed !== true)
              ? ' q-footer--hidden'
              : ''
          )
      },

      style: function style () {
        var
          view = this.layout.rows.bottom,
          css = {};

        if (view[0] === 'l' && this.layout.left.space === true) {
          css[this.$q.lang.rtl ? 'right' : 'left'] = (this.layout.left.size) + "px";
        }
        if (view[2] === 'r' && this.layout.right.space === true) {
          css[this.$q.lang.rtl ? 'left' : 'right'] = (this.layout.right.size) + "px";
        }

        return css
      }
    },

    render: function render (h) {
      var child = [
        h(QResizeObserver, {
          props: { debounce: 0 },
          on: { resize: this.__onResize }
        })
      ];

      this.elevated === true && child.push(
        h('div', {
          staticClass: 'q-layout__shadow absolute-full overflow-hidden no-pointer-events'
        })
      );

      return h('footer', {
        staticClass: 'q-footer q-layout__section--marginal',
        class: this.classes,
        style: this.style,
        on: Object.assign({}, this.$listeners,
          {input: stop})
      }, child.concat(slot(this, 'default')))
    },

    created: function created () {
      this.layout.instances.footer = this;
      this.__update('space', this.value);
      this.__update('offset', this.offset);
    },

    beforeDestroy: function beforeDestroy () {
      if (this.layout.instances.footer === this) {
        this.layout.instances.footer = void 0;
        this.__update('size', 0);
        this.__update('offset', 0);
        this.__update('space', false);
      }
    },

    methods: {
      __onResize: function __onResize (ref) {
        var height = ref.height;

        this.__updateLocal('size', height);
        this.__update('size', height);
      },

      __update: function __update (prop, val) {
        if (this.layout.footer[prop] !== val) {
          this.layout.footer[prop] = val;
        }
      },

      __updateLocal: function __updateLocal (prop, val) {
        if (this[prop] !== val) {
          this[prop] = val;
        }
      },

      __updateRevealed: function __updateRevealed () {
        if (this.reveal !== true) { return }

        var ref = this.layout.scroll;
        var direction = ref.direction;
        var position = ref.position;
        var inflexionPosition = ref.inflexionPosition;

        this.__updateLocal('revealed', (
          direction === 'up' ||
          position - inflexionPosition < 100 ||
          this.layout.height - this.containerHeight - position - this.size < 300
        ));
      }
    }
  });

  var QHeader = Vue.extend({
    name: 'QHeader',

    mixins: [ CanRenderMixin ],

    inject: {
      layout: {
        default: function default$1 () {
          console.error('QHeader needs to be child of QLayout');
        }
      }
    },

    props: {
      value: {
        type: Boolean,
        default: true
      },
      reveal: Boolean,
      revealOffset: {
        type: Number,
        default: 250
      },
      bordered: Boolean,
      elevated: Boolean
    },

    data: function data () {
      return {
        size: 0,
        revealed: true
      }
    },

    watch: {
      value: function value (val) {
        this.__update('space', val);
        this.__updateLocal('revealed', true);
        this.layout.__animate();
      },

      offset: function offset (val) {
        this.__update('offset', val);
      },

      reveal: function reveal (val) {
        val === false && this.__updateLocal('revealed', this.value);
      },

      revealed: function revealed (val) {
        this.layout.__animate();
        this.$emit('reveal', val);
      },

      'layout.scroll': function layout_scroll (scroll) {
        this.reveal === true && this.__updateLocal('revealed',
          scroll.direction === 'up' ||
          scroll.position <= this.revealOffset ||
          scroll.position - scroll.inflexionPosition < 100
        );
      }
    },

    computed: {
      fixed: function fixed () {
        return this.reveal === true ||
          this.layout.view.indexOf('H') > -1 ||
          this.layout.container === true
      },

      offset: function offset () {
        if (this.canRender !== true || this.value !== true) {
          return 0
        }
        if (this.fixed === true) {
          return this.revealed === true ? this.size : 0
        }
        var offset = this.size - this.layout.scroll.position;
        return offset > 0 ? offset : 0
      },

      classes: function classes () {
        return (
          this.fixed === true ? 'fixed' : 'absolute') + '-top' +
          (this.bordered === true ? ' q-header--bordered' : '') +
          (
            this.canRender !== true || this.value !== true || (this.fixed === true && this.revealed !== true)
              ? ' q-header--hidden'
              : ''
          )
      },

      style: function style () {
        var
          view = this.layout.rows.top,
          css = {};

        if (view[0] === 'l' && this.layout.left.space === true) {
          css[this.$q.lang.rtl ? 'right' : 'left'] = (this.layout.left.size) + "px";
        }
        if (view[2] === 'r' && this.layout.right.space === true) {
          css[this.$q.lang.rtl ? 'left' : 'right'] = (this.layout.right.size) + "px";
        }

        return css
      }
    },

    render: function render (h) {
      var child = [
        h(QResizeObserver, {
          props: { debounce: 0 },
          on: { resize: this.__onResize }
        })
      ].concat(
        slot(this, 'default')
      );

      this.elevated === true && child.push(
        h('div', {
          staticClass: 'q-layout__shadow absolute-full overflow-hidden no-pointer-events'
        })
      );

      return h('header', {
        staticClass: 'q-header q-layout__section--marginal',
        class: this.classes,
        style: this.style,
        on: Object.assign({}, this.$listeners,
          {input: stop})
      }, child)
    },

    created: function created () {
      this.layout.instances.header = this;
      this.__update('space', this.value);
      this.__update('offset', this.offset);
    },

    beforeDestroy: function beforeDestroy () {
      if (this.layout.instances.header === this) {
        this.layout.instances.header = void 0;
        this.__update('size', 0);
        this.__update('offset', 0);
        this.__update('space', false);
      }
    },

    methods: {
      __onResize: function __onResize (ref) {
        var height = ref.height;

        this.__updateLocal('size', height);
        this.__update('size', height);
      },

      __update: function __update (prop, val) {
        if (this.layout.header[prop] !== val) {
          this.layout.header[prop] = val;
        }
      },

      __updateLocal: function __updateLocal (prop, val) {
        if (this[prop] !== val) {
          this[prop] = val;
        }
      }
    }
  });

  var QPage = Vue.extend({
    name: 'QPage',

    inject: {
      pageContainer: {
        default: function default$1 () {
          console.error('QPage needs to be child of QPageContainer');
        }
      },
      layout: {}
    },

    props: {
      padding: Boolean,
      styleFn: Function
    },

    computed: {
      style: function style () {
        var offset =
          (this.layout.header.space === true ? this.layout.header.size : 0) +
          (this.layout.footer.space === true ? this.layout.footer.size : 0);

        if (typeof this.styleFn === 'function') {
          return this.styleFn(offset)
        }

        var height = this.layout.container === true
          ? this.layout.containerHeight
          : this.$q.screen.height;

        return { minHeight: (height - offset) + 'px' }
      },

      classes: function classes () {
        if (this.padding === true) {
          return 'q-layout-padding'
        }
      }
    },

    render: function render (h) {
      return h('main', {
        staticClass: 'q-page',
        style: this.style,
        class: this.classes,
        on: this.$listeners
      }, slot(this, 'default'))
    }
  });

  var QPageContainer = Vue.extend({
    name: 'QPageContainer',

    inject: {
      layout: {
        default: function default$1 () {
          console.error('QPageContainer needs to be child of QLayout');
        }
      }
    },

    provide: {
      pageContainer: true
    },

    computed: {
      style: function style () {
        var css = {};

        if (this.layout.header.space === true) {
          css.paddingTop = (this.layout.header.size) + "px";
        }
        if (this.layout.right.space === true) {
          css[("padding" + (this.$q.lang.rtl === true ? 'Left' : 'Right'))] = (this.layout.right.size) + "px";
        }
        if (this.layout.footer.space === true) {
          css.paddingBottom = (this.layout.footer.size) + "px";
        }
        if (this.layout.left.space === true) {
          css[("padding" + (this.$q.lang.rtl === true ? 'Right' : 'Left'))] = (this.layout.left.size) + "px";
        }

        return css
      }
    },

    render: function render (h) {
      return h('div', {
        staticClass: 'q-page-container',
        style: this.style,
        on: this.$listeners
      }, slot(this, 'default'))
    }
  });

  var QPageSticky = Vue.extend({
    name: 'QPageSticky',

    inject: {
      layout: {
        default: function default$1 () {
          console.error('QPageSticky needs to be child of QLayout');
        }
      }
    },

    props: {
      position: {
        type: String,
        default: 'bottom-right',
        validator: function (v) { return [
          'top-right', 'top-left',
          'bottom-right', 'bottom-left',
          'top', 'right', 'bottom', 'left'
        ].includes(v); }
      },
      offset: {
        type: Array,
        validator: function (v) { return v.length === 2; }
      },
      expand: Boolean
    },

    computed: {
      attach: function attach () {
        var pos = this.position;

        return {
          top: pos.indexOf('top') > -1,
          right: pos.indexOf('right') > -1,
          bottom: pos.indexOf('bottom') > -1,
          left: pos.indexOf('left') > -1,
          vertical: pos === 'top' || pos === 'bottom',
          horizontal: pos === 'left' || pos === 'right'
        }
      },

      top: function top () {
        return this.layout.header.offset
      },

      right: function right () {
        return this.layout.right.offset
      },

      bottom: function bottom () {
        return this.layout.footer.offset
      },

      left: function left () {
        return this.layout.left.offset
      },

      style: function style () {
        var
          posX = 0,
          posY = 0;

        var
          attach = this.attach,
          dir = this.$q.lang.rtl === true ? -1 : 1;

        if (attach.top === true && this.top !== 0) {
          posY = (this.top) + "px";
        }
        else if (attach.bottom === true && this.bottom !== 0) {
          posY = (-this.bottom) + "px";
        }

        if (attach.left === true && this.left !== 0) {
          posX = (dir * this.left) + "px";
        }
        else if (attach.right === true && this.right !== 0) {
          posX = (-dir * this.right) + "px";
        }

        var css = { transform: ("translate3d(" + posX + ", " + posY + ", 0)") };

        if (this.offset) {
          css.margin = (this.offset[1]) + "px " + (this.offset[0]) + "px";
        }

        if (attach.vertical === true) {
          if (this.left !== 0) {
            css[this.$q.lang.rtl === true ? 'right' : 'left'] = (this.left) + "px";
          }
          if (this.right !== 0) {
            css[this.$q.lang.rtl === true ? 'left' : 'right'] = (this.right) + "px";
          }
        }
        else if (attach.horizontal === true) {
          if (this.top !== 0) {
            css.top = (this.top) + "px";
          }
          if (this.bottom !== 0) {
            css.bottom = (this.bottom) + "px";
          }
        }

        return css
      },

      classes: function classes () {
        return ("fixed-" + (this.position) + " q-page-sticky--" + (this.expand === true ? 'expand' : 'shrink'))
      }
    },

    render: function render (h) {
      var content = slot(this, 'default');

      return h('div', {
        staticClass: 'q-page-sticky row flex-center',
        class: this.classes,
        style: this.style
      },
      this.expand === true
        ? content
        : [ h('div', content) ]
      )
    }
  });

  var QItemLabel = Vue.extend({
    name: 'QItemLabel',

    props: {
      overline: Boolean,
      caption: Boolean,
      header: Boolean,
      lines: [Number, String]
    },

    computed: {
      classes: function classes () {
        return {
          'q-item__label--overline text-overline': this.overline,
          'q-item__label--caption text-caption': this.caption,
          'q-item__label--header': this.header,
          'ellipsis': parseInt(this.lines, 10) === 1
        }
      },

      style: function style () {
        if (this.lines !== void 0 && parseInt(this.lines, 10) > 1) {
          return {
            overflow: 'hidden',
            display: '-webkit-box',
            '-webkit-box-orient': 'vertical',
            '-webkit-line-clamp': this.lines
          }
        }
      }
    },

    render: function render (h) {
      return h('div', {
        staticClass: 'q-item__label',
        style: this.style,
        class: this.classes,
        on: this.$listeners
      }, slot(this, 'default'))
    }
  });

  var QSlideTransition = Vue.extend({
    name: 'QSlideTransition',

    props: {
      appear: Boolean,
      duration: {
        type: Number,
        default: 300
      }
    },

    methods: {
      __begin: function __begin (el, height, done) {
        el.style.overflowY = 'hidden';
        if (height !== void 0) {
          el.style.height = height + "px";
        }
        el.style.transition = "height " + (this.duration) + "ms cubic-bezier(.25, .8, .50, 1)";

        this.animating = true;
        this.done = done;
      },

      __end: function __end (el, event) {
        el.style.overflowY = null;
        el.style.height = null;
        el.style.transition = null;
        this.__cleanup();
        event !== this.lastEvent && this.$emit(event);
      },

      __cleanup: function __cleanup () {
        this.done && this.done();
        this.done = null;
        this.animating = false;

        clearTimeout(this.timer);
        clearTimeout(this.timerFallback);
        this.el.removeEventListener('transitionend', this.animListener);
        this.animListener = null;
      }
    },

    beforeDestroy: function beforeDestroy () {
      this.animating && this.__cleanup();
    },

    render: function render (h) {
      var this$1 = this;

      return h('transition', {
        props: {
          css: false,
          appear: this.appear
        },
        on: {
          enter: function (el, done) {
            var pos = 0;
            this$1.el = el;

            if (this$1.animating === true) {
              this$1.__cleanup();
              pos = el.offsetHeight === el.scrollHeight ? 0 : void 0;
            }
            else {
              this$1.lastEvent = 'hide';
            }

            this$1.__begin(el, pos, done);

            this$1.timer = setTimeout(function () {
              el.style.height = (el.scrollHeight) + "px";
              this$1.animListener = function () {
                this$1.__end(el, 'show');
              };
              el.addEventListener('transitionend', this$1.animListener);
              this$1.timerFallback = setTimeout(this$1.animListener, this$1.duration * 1.1);
            }, 100);
          },
          leave: function (el, done) {
            var pos;
            this$1.el = el;

            if (this$1.animating === true) {
              this$1.__cleanup();
            }
            else {
              this$1.lastEvent = 'show';
              pos = el.scrollHeight;
            }

            this$1.__begin(el, pos, done);

            this$1.timer = setTimeout(function () {
              el.style.height = 0;
              this$1.animListener = function () {
                this$1.__end(el, 'hide');
              };
              el.addEventListener('transitionend', this$1.animListener);
              this$1.timerFallback = setTimeout(this$1.animListener, this$1.duration * 1.1);
            }, 100);
          }
        }
      }, slot(this, 'default'))
    }
  });

  var QSeparator = Vue.extend({
    name: 'QSeparator',

    props: {
      dark: Boolean,
      spaced: Boolean,
      inset: [Boolean, String],
      vertical: Boolean,
      color: String
    },

    computed: {
      classes: function classes () {
        var obj;

        return ( obj = {}, obj[("bg-" + (this.color))] = this.color, obj['q-separator--dark'] = this.dark, obj['q-separator--spaced'] = this.spaced, obj['q-separator--inset'] = this.inset === true, obj['q-separator--item-inset'] = this.inset === 'item', obj['q-separator--item-thumbnail-inset'] = this.inset === 'item-thumbnail', obj[("q-separator--" + (this.vertical ? 'vertical self-stretch' : 'horizontal col-grow'))] = true, obj )
      }
    },

    render: function render (h) {
      return h('hr', {
        staticClass: 'q-separator',
        class: this.classes
      })
    }
  });

  var eventName = 'q:expansion-item:close';

  var QExpansionItem = Vue.extend({
    name: 'QExpansionItem',

    mixins: [ RouterLinkMixin, ModelToggleMixin ],

    props: {
      icon: String,

      label: String,
      labelLines: [ Number, String ],

      caption: String,
      captionLines: [ Number, String ],

      dark: Boolean,
      dense: Boolean,

      expandIcon: String,
      expandIconClass: String,
      duration: Number,

      headerInsetLevel: Number,
      contentInsetLevel: Number,

      expandSeparator: Boolean,
      defaultOpened: Boolean,
      expandIconToggle: Boolean,
      switchToggleSide: Boolean,
      denseToggle: Boolean,
      group: String,
      popup: Boolean,

      headerStyle: [Array, String, Object],
      headerClass: [Array, String, Object]
    },

    watch: {
      showing: function showing (val) {
        if (val === true && this.group) {
          this.$root.$emit(eventName, this);
        }
      }
    },

    computed: {
      classes: function classes () {
        return "q-expansion-item--" + (this.showing === true ? 'expanded' : 'collapsed') +
          " q-expansion-item--" + (this.popup === true ? 'popup' : 'standard')
      },

      contentStyle: function contentStyle () {
        if (this.contentInsetLevel !== void 0) {
          return {
            paddingLeft: (this.contentInsetLevel * 56) + 'px'
          }
        }
      },

      isClickable: function isClickable () {
        return this.hasRouterLink === true || this.expandIconToggle !== true
      },

      expansionIcon: function expansionIcon () {
        return this.expandIcon || (this.denseToggle ? this.$q.iconSet.expansionItem.denseIcon : this.$q.iconSet.expansionItem.icon)
      },

      activeToggleIcon: function activeToggleIcon () {
        return this.disable !== true && (this.hasRouterLink === true || this.expandIconToggle === true)
      }
    },

    methods: {
      __onHeaderClick: function __onHeaderClick (e) {
        this.hasRouterLink !== true && this.toggle(e);
        this.$emit('click', e);
      },

      __toggleIconKeyboard: function __toggleIconKeyboard (e) {
        e.keyCode === 13 && this.__toggleIcon(e, true);
      },

      __toggleIcon: function __toggleIcon (e, keyboard) {
        keyboard !== true && this.$refs.blurTarget !== void 0 && this.$refs.blurTarget.focus();
        this.toggle(e);
        stopAndPrevent(e);
      },

      __eventHandler: function __eventHandler (comp) {
        if (this.group && this !== comp && comp.group === this.group) {
          this.hide();
        }
      },

      __getToggleIcon: function __getToggleIcon (h) {
        return h(QItemSection, {
          staticClass: ("cursor-pointer" + (this.denseToggle === true && this.switchToggleSide === true ? ' items-end' : '')),
          class: this.expandIconClass,
          props: {
            side: this.switchToggleSide !== true,
            avatar: this.switchToggleSide
          },
          on: this.activeToggleIcon === true ? {
            click: this.__toggleIcon,
            keyup: this.__toggleIconKeyboard
          } : void 0
        }, [
          h(QIcon, {
            staticClass: 'q-expansion-item__toggle-icon q-focusable',
            class: {
              'rotate-180': this.showing,
              invisible: this.disable
            },
            props: {
              name: this.expansionIcon
            },
            attrs: this.activeToggleIcon === true
              ? { tabindex: 0 }
              : void 0
          }, [
            h('div', {
              staticClass: 'q-focus-helper q-focus-helper--round',
              attrs: { tabindex: -1 },
              ref: 'blurTarget'
            })
          ])
        ])
      },

      __getHeader: function __getHeader (h) {
        var child;

        if (this.$scopedSlots.header !== void 0) {
          child = [].concat(this.$scopedSlots.header());
        }
        else {
          child = [
            h(QItemSection, [
              h(QItemLabel, {
                props: { lines: this.labelLines }
              }, [ this.label || '' ]),

              this.caption
                ? h(QItemLabel, {
                  props: { lines: this.captionLines, caption: true }
                }, [ this.caption ])
                : null
            ])
          ];

          this.icon && child[this.switchToggleSide === true ? 'push' : 'unshift'](
            h(QItemSection, {
              props: {
                side: this.switchToggleSide === true,
                avatar: this.switchToggleSide !== true
              }
            }, [
              h(QIcon, {
                props: { name: this.icon }
              })
            ])
          );
        }

        child[this.switchToggleSide === true ? 'unshift' : 'push'](this.__getToggleIcon(h));

        var data = {
          ref: 'item',
          style: this.headerStyle,
          class: this.headerClass,
          props: {
            dark: this.dark,
            disable: this.disable,
            dense: this.dense,
            insetLevel: this.headerInsetLevel
          }
        };

        if (this.isClickable === true) {
          var evtProp = this.hasRouterLink === true ? 'nativeOn' : 'on';

          data.props.clickable = true;
          data[evtProp] = Object.assign({}, this.$listeners,
            {click: this.__onHeaderClick});

          this.hasRouterLink === true && Object.assign(
            data.props,
            this.routerLinkProps
          );
        }

        return h(QItem, data, child)
      },

      __getContent: function __getContent (h) {
        var node = [
          this.__getHeader(h),

          h(QSlideTransition, {
            props: { duration: this.duration }
          }, [
            h('div', {
              staticClass: 'q-expansion-item__content relative-position',
              style: this.contentStyle,
              directives: [{ name: 'show', value: this.showing }]
            }, slot(this, 'default'))
          ])
        ];

        if (this.expandSeparator) {
          node.push(
            h(QSeparator, {
              staticClass: 'q-expansion-item__border q-expansion-item__border--top absolute-top',
              props: { dark: this.dark }
            }),
            h(QSeparator, {
              staticClass: 'q-expansion-item__border q-expansion-item__border--bottom absolute-bottom',
              props: { dark: this.dark }
            })
          );
        }

        return node
      }
    },

    render: function render (h) {
      return h('div', {
        staticClass: 'q-expansion-item q-item-type',
        class: this.classes
      }, [
        h(
          'div',
          { staticClass: 'q-expansion-item__container relative-position' },
          this.__getContent(h)
        )
      ])
    },

    created: function created () {
      this.$root.$on(eventName, this.__eventHandler);

      if (this.value === true) {
        this.showing = true;
      }
      else if (this.defaultOpened === true) {
        this.$emit('input', true);
        this.showing = true;
      }
    },

    beforeDestroy: function beforeDestroy () {
      this.$root.$off(eventName, this.__eventHandler);
    }
  });

  var QSlideItem = Vue.extend({
    name: 'QSlideItem',

    props: {
      leftColor: String,
      rightColor: String
    },

    directives: {
      TouchPan: TouchPan
    },

    methods: {
      reset: function reset () {
        this.$refs.content.style.transform = "translate3d(0,0,0)";
      },

      __pan: function __pan (evt) {
        var this$1 = this;

        var node = this.$refs.content;

        if (evt.isFirst) {
          this.__dir = null;
          this.__size = { left: 0, right: 0 };
          this.__scale = 0;
          node.classList.add('no-transition');

          if (this.$scopedSlots.left !== void 0) {
            var slot = this.$refs.leftContent;
            slot.style.transform = "scale3d(1,1,1)";
            this.__size.left = slot.getBoundingClientRect().width;
          }

          if (this.$scopedSlots.right !== void 0) {
            var slot$1 = this.$refs.rightContent;
            slot$1.style.transform = "scale3d(1,1,1)";
            this.__size.right = slot$1.getBoundingClientRect().width;
          }
        }
        else if (evt.isFinal) {
          node.classList.remove('no-transition');

          if (this.__scale === 1) {
            node.style.transform = "translate3d(" + (this.__dir * 100) + "%,0,0)";
            this.timer = setTimeout(function () {
              this$1.$emit(this$1.__showing, { reset: this$1.reset });
              this$1.$emit('action', { side: this$1.__showing, reset: this$1.reset });
            }, 230);
          }
          else {
            node.style.transform = "translate3d(0,0,0)";
          }

          return
        }

        if (
          (this.$scopedSlots.left === void 0 && evt.direction === 'right') ||
          (this.$scopedSlots.right === void 0 && evt.direction === 'left')
        ) {
          node.style.transform = "translate3d(0,0,0)";
          return
        }

        var
          dir = evt.direction === 'left' ? -1 : 1,
          showing = dir * (this.$q.lang.rtl === true ? -1 : 1) === 1 ? 'left' : 'right',
          otherDir = showing === 'left' ? 'right' : 'left',
          dist = evt.distance.x,
          scale = Math.max(0, Math.min(1, (dist - 40) / this.__size[showing])),
          content = this.$refs[(showing + "Content")];

        if (this.__dir !== dir) {
          this.$refs[otherDir] !== void 0 && (this.$refs[otherDir].style.visibility = 'hidden');
          this.$refs[showing] !== void 0 && (this.$refs[showing].style.visibility = 'visible');
          this.__showing = showing;
          this.__dir = dir;
        }

        this.__scale = scale;
        node.style.transform = "translate3d(" + (dist * dir) + "px,0,0)";

        if (dir === 1) {
          content.style.transform = "scale3d(" + scale + "," + scale + ",1)";
        }
        else {
          content.style.transform = "scale3d(" + scale + "," + scale + ",1)";
        }
      }
    },

    render: function render (h) {
      var
        content = [],
        left = this.$scopedSlots.left !== void 0,
        right = this.$scopedSlots.right !== void 0;

      if (left) {
        content.push(
          h('div', {
            ref: 'left',
            staticClass: 'q-slide-item__left absolute-full row no-wrap items-center justify-start',
            class: this.leftColor ? ("bg-" + (this.leftColor)) : ''
          }, [
            h('div', { ref: 'leftContent' }, slot(this, 'left'))
          ])
        );
      }

      if (right) {
        content.push(
          h('div', {
            ref: 'right',
            staticClass: 'q-slide-item__right absolute-full row no-wrap items-center justify-end',
            class: this.rightColor ? ("bg-" + (this.rightColor)) : ''
          }, [
            h('div', { ref: 'rightContent' }, slot(this, 'right'))
          ])
        );
      }

      content.push(
        h('div', {
          ref: 'content',
          staticClass: 'q-slide-item__content',
          directives: left || right ? [{
            name: 'touch-pan',
            value: this.__pan,
            modifiers: {
              horizontal: true,
              prevent: true,
              stop: true,
              mouse: true,
              mouseAllDir: true
            }
          }] : null
        }, slot(this, 'default'))
      );

      return h('div', {
        staticClass: 'q-slide-item q-item-type overflow-hidden'
      }, content)
    },

    beforeDestroy: function beforeDestroy () {
      clearTimeout(this.timer);
    }
  });

  var QNoSsr = Vue.extend({
    name: 'QNoSsr',

    mixins: [ CanRenderMixin ],

    props: {
      tag: {
        type: String,
        default: 'div'
      },
      placeholder: String
    },

    render: function render (h) {
      if (this.canRender === true) {
        var node = slot(this, 'default');
        return node === void 0
          ? node
          : (node.length > 1 ? h(this.tag, node) : node[0])
      }

      if (this.$scopedSlots.placeholder !== void 0) {
        var node$1 = slot(this, 'placeholder');
        return node$1 === void 0
          ? node$1
          : (
            node$1.length > 1
              ? h(this.tag, { staticClass: 'q-no-ssr-placeholder' }, node$1)
              : node$1[0]
          )
      }

      if (this.placeholder !== void 0) {
        return h(this.tag, { staticClass: 'q-no-ssr-placeholder' }, [
          this.placeholder
        ])
      }
    }
  });

  var QRadio = Vue.extend({
    name: 'QRadio',

    props: {
      value: {
        required: true
      },
      val: {
        required: true
      },

      label: String,
      leftLabel: Boolean,

      color: String,
      keepColor: Boolean,
      dark: Boolean,
      dense: Boolean,

      disable: Boolean,
      tabindex: [String, Number]
    },

    computed: {
      isTrue: function isTrue () {
        return this.value === this.val
      },

      classes: function classes () {
        return {
          'disabled': this.disable,
          'q-radio--dark': this.dark,
          'q-radio--dense': this.dense,
          'reverse': this.leftLabel
        }
      },

      innerClass: function innerClass () {
        if (this.isTrue === true) {
          return 'q-radio__inner--active' +
            (this.color !== void 0 ? ' text-' + this.color : '')
        }
        else if (this.keepColor === true && this.color !== void 0) {
          return 'text-' + this.color
        }
      },

      computedTabindex: function computedTabindex () {
        return this.disable === true ? -1 : this.tabindex || 0
      }
    },

    methods: {
      set: function set (e) {
        e !== void 0 && stopAndPrevent(e);
        if (this.disable !== true && this.isTrue !== true) {
          this.$emit('input', this.val);
        }
      },

      __keyDown: function __keyDown (e) {
        if (e.keyCode === 13 || e.keyCode === 32) {
          this.set(e);
        }
      }
    },

    render: function render (h) {
      return h('div', {
        staticClass: 'q-radio cursor-pointer no-outline row inline no-wrap items-center',
        class: this.classes,
        attrs: { tabindex: this.computedTabindex },
        on: {
          click: this.set,
          keydown: this.__keyDown
        }
      }, [
        h('div', {
          staticClass: 'q-radio__inner relative-position',
          class: this.innerClass
        }, [
          this.disable !== true
            ? h('input', {
              staticClass: 'q-radio__native q-ma-none q-pa-none invisible',
              attrs: { type: 'checkbox' },
              on: { change: this.set }
            })
            : null,

          h('div', {
            staticClass: 'q-radio__bg absolute'
          }, [
            h('div', { staticClass: 'q-radio__outer-circle absolute-full' }),
            h('div', { staticClass: 'q-radio__inner-circle absolute-full' })
          ])
        ]),

        this.label !== void 0 || this.$scopedSlots.default !== void 0
          ? h('div', {
            staticClass: 'q-radio__label q-anchor--skip'
          }, (this.label !== void 0 ? [ this.label ] : []).concat(slot(this, 'default')))
          : null
      ])
    }
  });

  var QToggle = Vue.extend({
    name: 'QToggle',

    mixins: [ CheckboxMixin ],

    props: {
      icon: String,
      checkedIcon: String,
      uncheckedIcon: String
    },

    computed: {
      classes: function classes () {
        return {
          'disabled': this.disable,
          'q-toggle--dark': this.dark,
          'q-toggle--dense': this.dense,
          'reverse': this.leftLabel
        }
      },

      innerClass: function innerClass () {
        if (this.isTrue === true) {
          return 'q-toggle__inner--active' +
            (this.color !== void 0 ? ' text-' + this.color : '')
        }
        else if (this.keepColor === true && this.color !== void 0) {
          return 'text-' + this.color
        }
      },

      computedIcon: function computedIcon () {
        return (this.isTrue === true ? this.checkedIcon : this.uncheckedIcon) || this.icon
      }
    },

    render: function render (h) {
      return h('div', {
        staticClass: 'q-toggle cursor-pointer no-outline row inline no-wrap items-center',
        class: this.classes,
        attrs: { tabindex: this.computedTabindex },
        on: {
          click: this.toggle,
          keydown: this.__keyDown
        }
      }, [
        h('div', {
          staticClass: 'q-toggle__inner relative-position',
          class: this.innerClass
        }, [
          this.disable !== true
            ? h('input', {
              staticClass: 'q-toggle__native absolute q-ma-none q-pa-none invisible',
              attrs: { type: 'toggle' },
              on: { change: this.toggle }
            })
            : null,

          h('div', { staticClass: 'q-toggle__track' }),
          h('div', { staticClass: 'q-toggle__thumb-container absolute' }, [
            h('div', {
              staticClass: 'q-toggle__thumb row flex-center'
            }, this.computedIcon !== void 0
              ? [ h(QIcon, { props: { name: this.computedIcon } }) ]
              : null
            )
          ])
        ]),

        h('div', {
          staticClass: 'q-toggle__label q-anchor--skip'
        }, (this.label !== void 0 ? [ this.label ] : []).concat(slot(this, 'default')))
      ])
    }
  });

  var components = {
    radio: QRadio,
    checkbox: QCheckbox,
    toggle: QToggle
  };

  var QOptionGroup = Vue.extend({
    name: 'QOptionGroup',

    props: {
      value: {
        required: true
      },
      options: {
        type: Array,
        validator: function validator (opts) {
          return opts.every(function (opt) { return 'value' in opt && 'label' in opt; })
        }
      },

      type: {
        default: 'radio',
        validator: function (v) { return ['radio', 'checkbox', 'toggle'].includes(v); }
      },

      color: String,
      keepColor: Boolean,
      dark: Boolean,
      dense: Boolean,

      leftLabel: Boolean,
      inline: Boolean,
      disable: Boolean
    },

    computed: {
      component: function component () {
        return components[this.type]
      },

      model: function model () {
        return Array.isArray(this.value) ? this.value.slice() : this.value
      }
    },

    methods: {
      __update: function __update (value) {
        this.$emit('input', value);
      }
    },

    created: function created () {
      var isArray = Array.isArray(this.value);

      if (this.type === 'radio') {
        if (isArray) {
          console.error('q-option-group: model should not be array');
        }
      }
      else if (!isArray) {
        console.error('q-option-group: model should be array in your case');
      }
    },

    render: function render (h) {
      var this$1 = this;

      return h('div', {
        staticClass: 'q-option-group q-gutter-x-sm',
        class: this.inline ? 'q-option-group--inline' : null
      }, this.options.map(function (opt) { return h('div', [
        h(this$1.component, {
          props: {
            value: this$1.value,
            val: opt.value,
            disable: this$1.disable || opt.disable,
            label: opt.label,
            leftLabel: this$1.leftLabel || opt.leftLabel,
            color: opt.color || this$1.color,
            checkedIcon: opt.checkedIcon,
            uncheckedIcon: opt.uncheckedIcon,
            dark: opt.dark || this$1.dark,
            dense: this$1.dense,
            keepColor: opt.keepColor || this$1.keepColor
          },
          on: {
            input: this$1.__update
          }
        })
      ]); }))
    }
  });

  var QPageScroller = Vue.extend({
    name: 'QPageScroller',

    mixins: [ QPageSticky ],

    props: {
      scrollOffset: {
        type: Number,
        default: 1000
      },

      duration: {
        type: Number,
        default: 300
      },

      offset: {
        default: function () { return [18, 18]; }
      }
    },

    inject: {
      layout: {
        default: function default$1 () {
          console.error('QPageScroller needs to be used within a QLayout');
        }
      }
    },

    data: function data () {
      return {
        showing: this.__isVisible(this.layout.scroll.position)
      }
    },

    watch: {
      'layout.scroll.position': function layout_scroll_position (val) {
        var newVal = this.__isVisible(val);
        if (this.showing !== newVal) {
          this.showing = newVal;
        }
      }
    },

    methods: {
      __isVisible: function __isVisible (val) {
        return val > this.scrollOffset
      },

      __onClick: function __onClick (e) {
        var target = this.layout.container === true
          ? getScrollTarget(this.$el)
          : getScrollTarget(this.layout.$el);

        setScrollPosition(target, 0, this.duration);
        this.$listeners.click !== void 0 && this.$emit('click', e);
      }
    },

    render: function render (h) {
      return h('transition', {
        props: { name: 'q-transition--fade' }
      },
      this.showing === true
        ? [
          h('div', {
            staticClass: 'q-page-scroller',
            on: Object.assign({}, this.$listeners,
              {click: this.__onClick})
          }, [
            QPageSticky.options.render.call(this, h)
          ])
        ]
        : null
      )
    }
  });

  var QPagination = Vue.extend({
    name: 'QPagination',

    props: {
      value: {
        type: Number,
        required: true
      },
      min: {
        type: Number,
        default: 1
      },
      max: {
        type: Number,
        required: true
      },

      color: {
        type: String,
        default: 'primary'
      },
      textColor: String,

      inputStyle: [Array, String, Object],
      inputClass: [Array, String, Object],

      size: String,

      disable: Boolean,

      input: Boolean,
      boundaryLinks: {
        type: Boolean,
        default: null
      },
      boundaryNumbers: {
        type: Boolean,
        default: null
      },
      directionLinks: {
        type: Boolean,
        default: null
      },
      ellipses: {
        type: Boolean,
        default: null
      },
      maxPages: {
        type: Number,
        default: 0,
        validator: function (v) { return v >= 0; }
      }
    },

    data: function data () {
      return {
        newPage: null
      }
    },

    watch: {
      min: function min (value) {
        this.model = this.value;
      },

      max: function max (value) {
        this.model = this.value;
      }
    },

    computed: {
      model: {
        get: function get () {
          return this.value
        },
        set: function set (val) {
          if (this.disable || !val || isNaN(val)) {
            return
          }
          var value = between(parseInt(val, 10), this.min, this.max);
          this.$emit('input', value);
        }
      },

      inputPlaceholder: function inputPlaceholder () {
        return this.model + ' / ' + this.max
      },

      __boundaryLinks: function __boundaryLinks () {
        return this.__getBool(this.boundaryLinks, this.input)
      },

      __boundaryNumbers: function __boundaryNumbers () {
        return this.__getBool(this.boundaryNumbers, !this.input)
      },

      __directionLinks: function __directionLinks () {
        return this.__getBool(this.directionLinks, this.input)
      },

      __ellipses: function __ellipses () {
        return this.__getBool(this.ellipses, !this.input)
      },

      icons: function icons () {
        var ico = [
          this.$q.iconSet.pagination.first,
          this.$q.iconSet.pagination.prev,
          this.$q.iconSet.pagination.next,
          this.$q.iconSet.pagination.last
        ];
        return this.$q.lang.rtl ? ico.reverse() : ico
      }
    },

    methods: {
      set: function set (value) {
        this.model = value;
      },

      setByOffset: function setByOffset (offset) {
        this.model = this.model + offset;
      },

      __update: function __update () {
        this.model = this.newPage;
        this.newPage = null;
      },

      __getBool: function __getBool (val, otherwise) {
        return [true, false].includes(val)
          ? val
          : otherwise
      },

      __getBtn: function __getBtn (h, data, props) {
        data.props = Object.assign({}, {color: this.color,
          flat: true,
          size: this.size},
          props);
        return h(QBtn, data)
      }
    },

    render: function render (h) {
      var this$1 = this;

      var
        contentStart = [],
        contentEnd = [],
        contentMiddle = [];

      if (this.__boundaryLinks) {
        contentStart.push(this.__getBtn(h, {
          key: 'bls',
          on: {
            click: function () { return this$1.set(this$1.min); }
          }
        }, {
          disable: this.disable || this.value <= this.min,
          icon: this.icons[0]
        }));
        contentEnd.unshift(this.__getBtn(h, {
          key: 'ble',
          on: {
            click: function () { return this$1.set(this$1.max); }
          }
        }, {
          disable: this.disable || this.value >= this.max,
          icon: this.icons[3]
        }));
      }

      if (this.__directionLinks) {
        contentStart.push(this.__getBtn(h, {
          key: 'bdp',
          on: {
            click: function () { return this$1.setByOffset(-1); }
          }
        }, {
          disable: this.disable || this.value <= this.min,
          icon: this.icons[1]
        }));
        contentEnd.unshift(this.__getBtn(h, {
          key: 'bdn',
          on: {
            click: function () { return this$1.setByOffset(1); }
          }
        }, {
          disable: this.disable || this.value >= this.max,
          icon: this.icons[2]
        }));
      }

      if (this.input === true) {
        contentMiddle.push(h(QInput, {
          staticClass: 'inline',
          style: {
            width: ((this.inputPlaceholder.length / 2) + "em")
          },
          props: {
            type: 'number',
            dense: true,
            value: this.newPage,
            disable: this.disable,
            borderless: true,
            inputClass: this.inputClass,
            inputStyle: this.inputStyle
          },
          attrs: {
            placeholder: this.inputPlaceholder,
            min: this.min,
            max: this.max
          },
          on: {
            input: function (value) { return (this$1.newPage = value); },
            keyup: function (e) { return (e.keyCode === 13 && this$1.__update()); },
            blur: function () { return this$1.__update(); }
          }
        }));
      }
      else { // is type select
        var
          maxPages = Math.max(
            this.maxPages,
            1 + (this.__ellipses ? 2 : 0) + (this.__boundaryNumbers ? 2 : 0)
          ),
          pgFrom = this.min,
          pgTo = this.max,
          ellipsesStart = false,
          ellipsesEnd = false,
          boundaryStart = false,
          boundaryEnd = false;

        if (this.maxPages && maxPages < (this.max - this.min + 1)) {
          maxPages = 1 + Math.floor(maxPages / 2) * 2;
          pgFrom = Math.max(this.min, Math.min(this.max - maxPages + 1, this.value - Math.floor(maxPages / 2)));
          pgTo = Math.min(this.max, pgFrom + maxPages - 1);
          if (this.__boundaryNumbers) {
            boundaryStart = true;
            pgFrom += 1;
          }
          if (this.__ellipses && pgFrom > (this.min + (this.__boundaryNumbers ? 1 : 0))) {
            ellipsesStart = true;
            pgFrom += 1;
          }
          if (this.__boundaryNumbers) {
            boundaryEnd = true;
            pgTo -= 1;
          }
          if (this.__ellipses && pgTo < (this.max - (this.__boundaryNumbers ? 1 : 0))) {
            ellipsesEnd = true;
            pgTo -= 1;
          }
        }
        var style = {
          minWidth: ((Math.max(2, String(this.max).length)) + "em")
        };
        if (boundaryStart) {
          var active = this.min === this.value;
          contentStart.push(this.__getBtn(h, {
            key: 'bns',
            style: style,
            on: {
              click: function () { return this$1.set(this$1.min); }
            }
          }, {
            disable: this.disable,
            flat: !active,
            textColor: active ? this.textColor : null,
            label: this.min,
            ripple: false
          }));
        }
        if (boundaryEnd) {
          var active$1 = this.max === this.value;
          contentEnd.unshift(this.__getBtn(h, {
            key: 'bne',
            style: style,
            on: {
              click: function () { return this$1.set(this$1.max); }
            }
          }, {
            disable: this.disable,
            flat: !active$1,
            textColor: active$1 ? this.textColor : null,
            label: this.max,
            ripple: false
          }));
        }
        if (ellipsesStart) {
          contentStart.push(this.__getBtn(h, {
            key: 'bes',
            style: style,
            on: {
              click: function () { return this$1.set(pgFrom - 1); }
            }
          }, {
            disable: this.disable,
            label: '…'
          }));
        }
        if (ellipsesEnd) {
          contentEnd.unshift(this.__getBtn(h, {
            key: 'bee',
            style: style,
            on: {
              click: function () { return this$1.set(pgTo + 1); }
            }
          }, {
            disable: this.disable,
            label: '…'
          }));
        }
        var loop = function ( i ) {
          var active$2 = i === this$1.value;
          contentMiddle.push(this$1.__getBtn(h, {
            key: ("bpg" + i),
            style: style,
            on: {
              click: function () { return this$1.set(i); }
            }
          }, {
            disable: this$1.disable,
            flat: !active$2,
            textColor: active$2 ? this$1.textColor : null,
            label: i,
            ripple: false
          }));
        };

        for (var i = pgFrom; i <= pgTo; i++) loop( i );
      }

      return h('div', {
        staticClass: 'q-pagination row no-wrap items-center',
        class: { disabled: this.disable },
        on: this.$listeners
      }, [
        contentStart,

        h('div', {
          staticClass: 'row justify-center',
          on: this.input === true
            ? { input: stop }
            : {}
        }, [
          contentMiddle
        ]),

        contentEnd
      ])
    }
  });

  function frameDebounce (fn) {
    var wait = false, frame;

    function debounced () {
      var this$1 = this;
      var args = [], len = arguments.length;
      while ( len-- ) args[ len ] = arguments[ len ];

      if (wait) { return }

      wait = true;
      frame = requestAnimationFrame(function () {
        fn.apply(this$1, args);
        wait = false;
      });
    }

    debounced.cancel = function () {
      window.cancelAnimationFrame(frame);
      wait = false;
    };

    return debounced
  }

  var QParallax = Vue.extend({
    name: 'QParallax',

    props: {
      src: String,
      height: {
        type: Number,
        default: 500
      },
      speed: {
        type: Number,
        default: 1,
        validator: function (v) { return v >= 0 && v <= 1; }
      }
    },

    data: function data () {
      return {
        scrolling: false,
        percentScrolled: 0
      }
    },

    watch: {
      height: function height () {
        this.__updatePos();
      }
    },

    methods: {
      __update: function __update (percentage) {
        this.percentScrolled = percentage;
        this.$listeners.scroll !== void 0 && this.$emit('scroll', percentage);
      },

      __onResize: function __onResize () {
        if (this.scrollTarget) {
          this.mediaHeight = this.media.naturalHeight || this.media.videoHeight || height(this.media);
          this.__updatePos();
        }
      },

      __updatePos: function __updatePos () {
        var containerTop, containerHeight, containerBottom, top, bottom;

        if (this.scrollTarget === window) {
          containerTop = 0;
          containerHeight = window.innerHeight;
          containerBottom = containerHeight;
        }
        else {
          containerTop = offset(this.scrollTarget).top;
          containerHeight = height(this.scrollTarget);
          containerBottom = containerTop + containerHeight;
        }

        top = offset(this.$el).top;
        bottom = top + this.height;

        if (bottom > containerTop && top < containerBottom) {
          var percent = (containerBottom - top) / (this.height + containerHeight);
          this.__setPos((this.mediaHeight - this.height) * percent * this.speed);
          this.__update(percent);
        }
      },

      __setPos: function __setPos (offset) {
        // apply it immediately without any delay
        this.media.style.transform = "translate3D(-50%," + (Math.round(offset)) + "px, 0)";
      }
    },

    render: function render (h) {
      return h('div', {
        staticClass: 'q-parallax',
        style: { height: ((this.height) + "px") },
        on: this.$listeners
      }, [
        h('div', {
          ref: 'mediaParent',
          staticClass: 'q-parallax__media absolute-full'
        }, this.$scopedSlots.media !== void 0 ? this.$scopedSlots.media() : [
          h('img', {
            ref: 'media',
            attrs: {
              src: this.src
            }
          })
        ]),

        h(
          'div',
          { staticClass: 'q-parallax__content absolute-full column flex-center' },
          this.$scopedSlots.content !== void 0
            ? this.$scopedSlots.content({ percentScrolled: this.percentScrolled })
            : slot(this, 'default')
        )
      ])
    },

    beforeMount: function beforeMount () {
      this.__setPos = frameDebounce(this.__setPos);
    },

    mounted: function mounted () {
      this.__update = frameDebounce(this.__update);
      this.resizeHandler = frameDebounce(this.__onResize);

      this.media = this.$scopedSlots.media !== void 0
        ? this.$refs.mediaParent.children[0]
        : this.$refs.media;

      this.media.onload = this.media.onloadstart = this.media.loadedmetadata = this.__onResize;

      this.scrollTarget = getScrollTarget(this.$el);

      window.addEventListener('resize', this.resizeHandler, listenOpts.passive);
      this.scrollTarget.addEventListener('scroll', this.__updatePos, listenOpts.passive);

      this.__onResize();
    },

    beforeDestroy: function beforeDestroy () {
      window.removeEventListener('resize', this.resizeHandler, listenOpts.passive);
      this.scrollTarget.removeEventListener('scroll', this.__updatePos, listenOpts.passive);
      this.media.onload = this.media.onloadstart = this.media.loadedmetadata = null;
    }
  });

  function clone$1 (data) {
    var s = JSON.stringify(data);
    if (s) {
      return JSON.parse(s)
    }
  }

  var QPopupEdit = Vue.extend({
    name: 'QPopupEdit',

    props: {
      value: {
        required: true
      },
      title: String,
      buttons: Boolean,
      labelSet: String,
      labelCancel: String,

      persistent: Boolean,
      color: {
        type: String,
        default: 'primary'
      },
      validate: {
        type: Function,
        default: function () { return true; }
      },

      contentClass: String,
      contentStyle: [String, Array, Object],

      disable: Boolean
    },

    data: function data () {
      return {
        initialValue: ''
      }
    },

    computed: {
      classes: function classes () {
        return 'q-popup-edit' +
          (this.contentClass ? ' ' + this.contentClass : '')
      }
    },

    methods: {
      set: function set () {
        if (this.__hasChanged()) {
          if (this.validate(this.value) === false) {
            return
          }
          this.$emit('save', this.value, this.initialValue);
        }
        this.__close();
      },

      cancel: function cancel () {
        if (this.__hasChanged()) {
          this.$emit('cancel', this.value, this.initialValue);
          this.$emit('input', this.initialValue);
        }
        this.__close();
      },

      __hasChanged: function __hasChanged () {
        return !isDeepEqual(this.value, this.initialValue)
      },

      __close: function __close () {
        this.validated = true;
        this.$refs.menu.hide();
      },

      __reposition: function __reposition () {
        var this$1 = this;

        this.$nextTick(function () {
          this$1.$refs.menu.updatePosition();
        });
      },

      __getContent: function __getContent (h) {
        var
          child = [].concat(slot(this, 'default')),
          title = this.$scopedSlots.title !== void 0
            ? this.$scopedSlots.title()
            : this.title;

        title && child.unshift(
          h('div', { staticClass: 'q-dialog__title q-mt-sm q-mb-sm' }, [ title ])
        );

        this.buttons === true && child.push(
          h('div', { staticClass: 'q-popup-edit__buttons row justify-center no-wrap' }, [
            h(QBtn, {
              props: {
                flat: true,
                color: this.color,
                label: this.labelCancel || this.$q.lang.label.cancel
              },
              on: { click: this.cancel }
            }),
            h(QBtn, {
              props: {
                flat: true,
                color: this.color,
                label: this.labelSet || this.$q.lang.label.set
              },
              on: { click: this.set }
            })
          ])
        );

        return child
      }
    },

    render: function render (h) {
      var this$1 = this;

      if (this.disable === true) { return }

      return h(QMenu, {
        ref: 'menu',
        props: {
          contentClass: this.classes,
          contentStyle: this.contentStyle,
          cover: true,
          persistent: this.persistent,
          noFocus: true
        },
        on: {
          show: function () {
            this$1.$emit('show');
            this$1.validated = false;
            this$1.initialValue = clone$1(this$1.value);
            this$1.watcher = this$1.$watch('value', this$1.__reposition);
          },
          'before-hide': function () {
            this$1.watcher();

            if (this$1.validated === false && this$1.__hasChanged()) {
              this$1.$emit('cancel', this$1.value, this$1.initialValue);
              this$1.$emit('input', this$1.initialValue);
            }
          },
          hide: function () {
            this$1.$emit('hide');
          },
          keyup: function (e) {
            e.keyCode === 13 && this$1.set();
          }
        }
      }, this.__getContent(h))
    }
  });

  var QPopupProxy = Vue.extend({
    name: 'QPopupProxy',

    mixins: [ AnchorMixin ],

    props: {
      breakpoint: {
        type: [String, Number],
        default: 450
      }
    },

    data: function data () {
      var breakpoint = parseInt(this.breakpoint, 10);
      return {
        type: this.$q.screen.width < breakpoint || this.$q.screen.height < breakpoint
          ? 'dialog'
          : 'menu'
      }
    },

    computed: {
      parsedBreakpoint: function parsedBreakpoint () {
        return parseInt(this.breakpoint, 10)
      }
    },

    watch: {
      '$q.screen.width': function $q_screen_width (width) {
        if (this.$refs.popup.showing !== true) {
          this.__updateType(width, this.$q.screen.height, this.parsedBreakpoint);
        }
      },

      '$q.screen.height': function $q_screen_height (height) {
        if (this.$refs.popup.showing !== true) {
          this.__updateType(this.$q.screen.width, height, this.parsedBreakpoint);
        }
      },

      breakpoint: function breakpoint (breakpoint$1) {
        if (this.$refs.popup.showing !== true) {
          this.__updateType(this.$q.screen.width, this.$q.screen.height, parseInt(breakpoint$1, 10));
        }
      }
    },

    methods: {
      toggle: function toggle (evt) {
        this.$refs.popup.toggle(evt);
      },

      show: function show (evt) {
        this.$refs.popup.show(evt);
      },

      hide: function hide (evt) {
        this.$refs.popup.hide(evt);
      },

      __onHide: function __onHide (evt) {
        this.__updateType(this.$q.screen.width, this.$q.screen.height, this.parsedBreakpoint);
        this.$emit('hide', evt);
      },

      __updateType: function __updateType (width, height, breakpoint) {
        var type = width < breakpoint || height < breakpoint
          ? 'dialog'
          : 'menu';

        if (this.type !== type) {
          this.type = type;
        }
      }
    },

    render: function render (h) {
      var child = slot(this, 'default');

      var props = (
        this.type === 'menu' &&
        child !== void 0 &&
        child[0] !== void 0 &&
        child[0].componentOptions !== void 0 &&
        child[0].componentOptions.Ctor !== void 0 &&
        child[0].componentOptions.Ctor.sealedOptions !== void 0 &&
        ['QDate', 'QTime', 'QCarousel', 'QColor'].includes(
          child[0].componentOptions.Ctor.sealedOptions.name
        )
      ) ? { cover: true, maxHeight: '99vh' } : {};

      var data = {
        ref: 'popup',
        props: Object.assign(props, this.$attrs),
        on: Object.assign({}, this.$listeners,
          {hide: this.__onHide})
      };

      var component;

      if (this.type === 'dialog') {
        component = QDialog;
      }
      else {
        component = QMenu;
        data.props.contextMenu = this.contextMenu;
        data.props.noParentEvent = true;
      }

      return h(component, data, slot(this, 'default'))
    }
  });

  function width$1 (val) {
    return { transform: ("scale3d(" + val + ",1,1)") }
  }

  var QLinearProgress = Vue.extend({
    name: 'QLinearProgress',

    props: {
      value: {
        type: Number,
        default: 0
      },
      buffer: Number,

      color: String,
      trackColor: String,
      dark: Boolean,

      reverse: Boolean,
      stripe: Boolean,
      indeterminate: Boolean,
      query: Boolean,
      rounded: Boolean
    },

    computed: {
      motion: function motion () {
        return this.indeterminate || this.query
      },

      classes: function classes () {
        var obj;

        return ( obj = {}, obj[("text-" + (this.color))] = this.color !== void 0, obj['q-linear-progress--reverse'] = this.reverse === true || this.query === true, obj['rounded-borders'] = this.rounded === true, obj )
      },

      trackStyle: function trackStyle () {
        return width$1(this.buffer !== void 0 ? this.buffer : 1)
      },

      trackClass: function trackClass () {
        return 'q-linear-progress__track--' + (this.dark === true ? 'dark' : 'light') +
          (this.trackColor !== void 0 ? (" bg-" + (this.trackColor)) : '')
      },

      modelStyle: function modelStyle () {
        return width$1(this.motion ? 1 : this.value)
      },

      modelClasses: function modelClasses () {
        return ("q-linear-progress__model--" + (this.motion ? 'in' : '') + "determinate")
      },

      stripeStyle: function stripeStyle () {
        return { width: (this.value * 100) + '%' }
      }
    },

    render: function render (h) {
      return h('div', {
        staticClass: 'q-linear-progress',
        class: this.classes,
        on: this.$listeners
      }, [
        h('div', {
          staticClass: 'q-linear-progress__track absolute-full',
          style: this.trackStyle,
          class: this.trackClass
        }),

        h('div', {
          staticClass: 'q-linear-progress__model absolute-full',
          style: this.modelStyle,
          class: this.modelClasses
        }),

        this.stripe === true && this.motion === false ? h('div', {
          staticClass: 'q-linear-progress__stripe absolute-full',
          style: this.stripeStyle
        }) : null
      ].concat(slot(this, 'default')))
    }
  });

  var
    PULLER_HEIGHT = 40,
    OFFSET_TOP = 20;

  var QPullToRefresh = Vue.extend({
    name: 'QPullToRefresh',

    directives: {
      TouchPan: TouchPan
    },

    props: {
      color: String,
      icon: String,
      noMouse: Boolean,
      disable: Boolean
    },

    data: function data () {
      return {
        state: 'pull',
        pullRatio: 0,
        pulling: false,
        pullPosition: -PULLER_HEIGHT,
        animating: false,
        positionCSS: {}
      }
    },

    computed: {
      style: function style () {
        return {
          opacity: this.pullRatio,
          transform: ("translate3d(0, " + (this.pullPosition) + "px, 0) rotate3d(0, 0, 1, " + (this.pullRatio * 360) + "deg)")
        }
      }
    },

    methods: {
      trigger: function trigger () {
        var this$1 = this;

        this.$emit('refresh', function () {
          this$1.__animateTo({ pos: -PULLER_HEIGHT, ratio: 0 }, function () {
            this$1.state = 'pull';
          });
        });
      },

      updateScrollTarget: function updateScrollTarget () {
        this.scrollContainer = getScrollTarget(this.$el);
      },

      __pull: function __pull (event) {
        if (event.isFinal) {
          if (this.pulling) {
            this.pulling = false;

            if (this.state === 'pulled') {
              this.state = 'refreshing';
              this.__animateTo({ pos: OFFSET_TOP });
              this.trigger();
            }
            else if (this.state === 'pull') {
              this.__animateTo({ pos: -PULLER_HEIGHT, ratio: 0 });
            }
          }

          return
        }

        if (this.animating || this.state === 'refreshing') {
          return false
        }

        if (event.isFirst) {
          if (getScrollPosition(this.scrollContainer) !== 0) {
            if (this.pulling) {
              this.pulling = false;
              this.state = 'pull';
              this.__animateTo({ pos: -PULLER_HEIGHT, ratio: 0 });
            }

            return false
          }

          this.pulling = true;

          var ref = this.$el.getBoundingClientRect();
          var top = ref.top;
          var left = ref.left;
          this.positionCSS = {
            top: top + 'px',
            left: left + 'px',
            width: window.getComputedStyle(this.$el).getPropertyValue('width')
          };
        }

        prevent(event.evt);

        var distance = Math.min(140, Math.max(0, event.distance.y));
        this.pullPosition = distance - PULLER_HEIGHT;
        this.pullRatio = between(distance / (OFFSET_TOP + PULLER_HEIGHT), 0, 1);

        var state = this.pullPosition > OFFSET_TOP ? 'pulled' : 'pull';
        if (this.state !== state) {
          this.state = state;
        }
      },

      __animateTo: function __animateTo (ref, done) {
        var this$1 = this;
        var pos = ref.pos;
        var ratio = ref.ratio;

        this.animating = true;
        this.pullPosition = pos;

        if (ratio !== void 0) {
          this.pullRatio = ratio;
        }

        clearTimeout(this.timer);
        this.timer = setTimeout(function () {
          this$1.animating = false;
          done && done();
        }, 300);
      }
    },

    mounted: function mounted () {
      this.updateScrollTarget();
    },

    beforeDestroy: function beforeDestroy () {
      clearTimeout(this.timer);
    },

    render: function render (h) {
      return h('div', {
        staticClass: 'q-pull-to-refresh overflow-hidden',
        directives: this.disable === true
          ? null
          : [{
            name: 'touch-pan',
            modifiers: {
              down: true,
              mightPrevent: true,
              mouse: !this.noMouse
            },
            value: this.__pull
          }]
      }, [
        h('div', {
          staticClass: 'q-pull-to-refresh__content',
          class: this.pulling ? 'no-pointer-events' : null
        }, slot(this, 'default')),

        h('div', {
          staticClass: 'q-pull-to-refresh__puller-container fixed row flex-center no-pointer-events z-top',
          style: this.positionCSS
        }, [
          h('div', {
            staticClass: 'q-pull-to-refresh__puller row flex-center',
            style: this.style,
            class: this.animating ? 'q-pull-to-refresh__puller--animating' : null
          }, [
            this.state !== 'refreshing'
              ? h(QIcon, {
                props: {
                  name: this.icon || this.$q.iconSet.pullToRefresh.icon,
                  color: this.color,
                  size: '32px'
                }
              })
              : h(QSpinner, {
                props: {
                  size: '24px',
                  color: this.color
                }
              })
          ])
        ])
      ])
    }
  });

  var dragType = {
    MIN: 0,
    RANGE: 1,
    MAX: 2
  };

  var QRange = Vue.extend({
    name: 'QRange',

    mixins: [ SliderMixin ],

    props: {
      value: {
        type: Object,
        default: function () { return ({
          min: 0,
          max: 0
        }); },
        validator: function validator (val) {
          return 'min' in val && 'max' in val
        }
      },

      dragRange: Boolean,
      dragOnlyRange: Boolean,

      leftLabelColor: String,
      rightLabelColor: String,

      leftLabelValue: [String, Number],
      rightLabelValue: [String, Number]
    },

    data: function data () {
      return {
        model: Object.assign({}, this.value),
        curMinRatio: 0,
        curMaxRatio: 0
      }
    },

    watch: {
      'value.min': function value_min (val) {
        this.model.min = val;
      },

      'value.max': function value_max (val) {
        this.model.max = val;
      },

      min: function min (value) {
        if (this.model.min < value) {
          this.model.min = value;
        }
        if (this.model.max < value) {
          this.model.max = value;
        }
      },

      max: function max (value) {
        if (this.model.min > value) {
          this.model.min = value;
        }
        if (this.model.max > value) {
          this.model.max = value;
        }
      }
    },

    computed: {
      ratioMin: function ratioMin () {
        return this.active === true ? this.curMinRatio : this.modelMinRatio
      },

      ratioMax: function ratioMax () {
        return this.active === true ? this.curMaxRatio : this.modelMaxRatio
      },

      modelMinRatio: function modelMinRatio () {
        return (this.model.min - this.min) / (this.max - this.min)
      },

      modelMaxRatio: function modelMaxRatio () {
        return (this.model.max - this.min) / (this.max - this.min)
      },

      trackStyle: function trackStyle () {
        var obj;

        return ( obj = {}, obj[this.horizProp] = 100 * this.ratioMin + '%', obj.width = 100 * (this.ratioMax - this.ratioMin) + '%', obj )
      },

      minThumbStyle: function minThumbStyle () {
        var obj;

        return ( obj = {}, obj[this.horizProp] = (100 * this.ratioMin) + '%', obj['z-index'] = this.__nextFocus === 'min' ? 2 : void 0, obj )
      },

      maxThumbStyle: function maxThumbStyle () {
        var obj;

        return ( obj = {}, obj[this.horizProp] = (100 * this.ratioMax) + '%', obj )
      },

      minThumbClass: function minThumbClass () {
        return this.preventFocus === false && this.focus === 'min' ? 'q-slider--focus' : null
      },

      maxThumbClass: function maxThumbClass () {
        return this.preventFocus === false && this.focus === 'max' ? 'q-slider--focus' : null
      },

      events: function events () {
        var this$1 = this;

        if (this.editable === true) {
          if (this.$q.platform.is.mobile === true) {
            return { click: this.__mobileClick }
          }

          var evt = { mousedown: this.__activate };

          this.dragOnlyRange === true && Object.assign(evt, {
            focus: function () { this$1.__focus('both'); },
            blur: this.__blur,
            keydown: this.__keydown,
            keyup: this.__keyup
          });

          return evt
        }
      },

      minEvents: function minEvents () {
        var this$1 = this;

        if (this.editable && !this.$q.platform.is.mobile && this.dragOnlyRange !== true) {
          return {
            focus: function () { this$1.__focus('min'); },
            blur: this.__blur,
            keydown: this.__keydown,
            keyup: this.__keyup
          }
        }
      },

      maxEvents: function maxEvents () {
        var this$1 = this;

        if (this.editable && !this.$q.platform.is.mobile && this.dragOnlyRange !== true) {
          return {
            focus: function () { this$1.__focus('max'); },
            blur: this.__blur,
            keydown: this.__keydown,
            keyup: this.__keyup
          }
        }
      },

      minPinClass: function minPinClass () {
        var color = this.leftLabelColor || this.labelColor;
        if (color) {
          return ("text-" + color)
        }
      },

      maxPinClass: function maxPinClass () {
        var color = this.rightLabelColor || this.labelColor;
        if (color) {
          return ("text-" + color)
        }
      },

      minLabel: function minLabel () {
        return this.leftLabelValue !== void 0
          ? this.leftLabelValue
          : this.model.min
      },

      maxLabel: function maxLabel () {
        return this.rightLabelValue !== void 0
          ? this.rightLabelValue
          : this.model.max
      }
    },

    methods: {
      __updateValue: function __updateValue (change) {
        if (this.model.min !== this.value.min || this.model.max !== this.value.max) {
          this.$emit('input', this.model);
        }
        change === true && this.$emit('change', this.model);
      },

      __getDragging: function __getDragging (event) {
        var ref = this.$el.getBoundingClientRect();
        var left = ref.left;
        var width = ref.width;
        var sensitivity = this.dragOnlyRange ? 0 : this.$refs.minThumb.offsetWidth / (2 * width),
          diff = this.max - this.min;

        var dragging = {
          left: left,
          width: width,
          valueMin: this.model.min,
          valueMax: this.model.max,
          ratioMin: (this.value.min - this.min) / diff,
          ratioMax: (this.value.max - this.min) / diff
        };

        var
          ratio = getRatio(event, dragging, this.$q.lang.rtl),
          type;

        if (this.dragOnlyRange !== true && ratio < dragging.ratioMin + sensitivity) {
          type = dragType.MIN;
        }
        else if (this.dragOnlyRange === true || ratio < dragging.ratioMax - sensitivity) {
          if (this.dragRange || this.dragOnlyRange) {
            type = dragType.RANGE;
            Object.assign(dragging, {
              offsetRatio: ratio,
              offsetModel: getModel(ratio, this.min, this.max, this.step, this.decimals),
              rangeValue: dragging.valueMax - dragging.valueMin,
              rangeRatio: dragging.ratioMax - dragging.ratioMin
            });
          }
          else {
            type = dragging.ratioMax - ratio < ratio - dragging.ratioMin
              ? dragType.MAX
              : dragType.MIN;
          }
        }
        else {
          type = dragType.MAX;
        }

        dragging.type = type;
        this.__nextFocus = void 0;

        return dragging
      },

      __updatePosition: function __updatePosition (event, dragging) {
        if ( dragging === void 0 ) dragging = this.dragging;

        var
          ratio = getRatio(event, dragging, this.$q.lang.rtl),
          model = getModel(ratio, this.min, this.max, this.step, this.decimals),
          pos;

        switch (dragging.type) {
          case dragType.MIN:
            if (ratio <= dragging.ratioMax) {
              pos = {
                minR: ratio,
                maxR: dragging.ratioMax,
                min: model,
                max: dragging.valueMax
              };
              this.__nextFocus = 'min';
            }
            else {
              pos = {
                minR: dragging.ratioMax,
                maxR: ratio,
                min: dragging.valueMax,
                max: model
              };
              this.__nextFocus = 'max';
            }
            break

          case dragType.MAX:
            if (ratio >= dragging.ratioMin) {
              pos = {
                minR: dragging.ratioMin,
                maxR: ratio,
                min: dragging.valueMin,
                max: model
              };
              this.__nextFocus = 'max';
            }
            else {
              pos = {
                minR: ratio,
                maxR: dragging.ratioMin,
                min: model,
                max: dragging.valueMin
              };
              this.__nextFocus = 'min';
            }
            break

          case dragType.RANGE:
            var
              ratioDelta = ratio - dragging.offsetRatio,
              minR = between(dragging.ratioMin + ratioDelta, 0, 1 - dragging.rangeRatio),
              modelDelta = model - dragging.offsetModel,
              min = between(dragging.valueMin + modelDelta, this.min, this.max - dragging.rangeValue);

            pos = {
              minR: minR,
              maxR: minR + dragging.rangeRatio,
              min: parseFloat(min.toFixed(this.decimals)),
              max: parseFloat((min + dragging.rangeValue).toFixed(this.decimals))
            };
            break
        }

        this.model = {
          min: pos.min,
          max: pos.max
        };

        if (this.snap !== true || this.step === 0) {
          this.curMinRatio = pos.minR;
          this.curMaxRatio = pos.maxR;
        }
        else {
          var diff = this.max - this.min;
          this.curMinRatio = (this.model.min - this.min) / diff;
          this.curMaxRatio = (this.model.max - this.min) / diff;
        }
      },

      __focus: function __focus (which) {
        this.focus = which;
      },

      __keydown: function __keydown (evt) {
        var obj;

        if (!keyCodes.includes(evt.keyCode)) {
          return
        }

        stopAndPrevent(evt);

        var
          step = ([34, 33].includes(evt.keyCode) ? 10 : 1) * this.computedStep,
          offset = [34, 37, 40].includes(evt.keyCode) ? -step : step;

        if (this.dragOnlyRange) {
          var interval = this.dragOnlyRange ? this.model.max - this.model.min : 0;

          var min = between(
            parseFloat((this.model.min + offset).toFixed(this.decimals)),
            this.min,
            this.max - interval
          );

          this.model = {
            min: min,
            max: parseFloat((min + interval).toFixed(this.decimals))
          };
        }
        else if (this.focus === false) {
          return
        }
        else {
          var which = this.focus;

          this.model = Object.assign({}, this.model,
            ( obj = {}, obj[which] = between(
              parseFloat((this.model[which] + offset).toFixed(this.decimals)),
              which === 'min' ? this.min : this.model.min,
              which === 'max' ? this.max : this.model.max
            ), obj ));
        }

        this.__updateValue();
      },

      __getThumb: function __getThumb (h, which) {
        return h('div', {
          ref: which + 'Thumb',
          staticClass: 'q-slider__thumb-container absolute non-selectable',
          style: this[which + 'ThumbStyle'],
          class: this[which + 'ThumbClass'],
          on: this[which + 'Events'],
          attrs: { tabindex: this.dragOnlyRange !== true ? this.computedTabindex : null }
        }, [
          h('svg', {
            staticClass: 'q-slider__thumb absolute',
            attrs: { width: '21', height: '21' }
          }, [
            h('circle', {
              attrs: {
                cx: '10.5',
                cy: '10.5',
                r: '7.875'
              }
            })
          ]),

          this.label === true || this.labelAlways === true ? h('div', {
            staticClass: 'q-slider__pin absolute flex flex-center',
            class: this[which + 'PinClass']
          }, [
            h('div', { staticClass: 'q-slider__pin-value-marker' }, [
              h('div', { staticClass: 'q-slider__pin-value-marker-bg' }),
              h('div', { staticClass: 'q-slider__pin-value-marker-text' }, [
                this[which + 'Label']
              ])
            ])
          ]) : null,

          h('div', { staticClass: 'q-slider__focus-ring' })
        ])
      }
    },

    render: function render (h) {
      return h('div', {
        staticClass: 'q-slider',
        attrs: {
          role: 'slider',
          'aria-valuemin': this.min,
          'aria-valuemax': this.max,
          'data-step': this.step,
          'aria-disabled': this.disable,
          tabindex: this.dragOnlyRange && !this.$q.platform.is.mobile
            ? this.computedTabindex
            : null
        },
        class: this.classes,
        on: this.events,
        directives: this.editable ? [{
          name: 'touch-pan',
          value: this.__pan,
          modifiers: {
            horizontal: true,
            prevent: true,
            stop: true,
            mouse: true,
            mouseAllDir: true
          }
        }] : null
      }, [
        h('div', { staticClass: 'q-slider__track-container absolute overflow-hidden' }, [
          h('div', {
            staticClass: 'q-slider__track absolute-full',
            style: this.trackStyle
          }),

          this.markers === true
            ? h('div', {
              staticClass: 'q-slider__track-markers absolute-full fit',
              style: this.markerStyle
            })
            : null
        ]),

        this.__getThumb(h, 'min'),
        this.__getThumb(h, 'max')
      ])
    }
  });

  var QRating = Vue.extend({
    name: 'QRating',

    props: {
      value: {
        type: Number,
        required: true
      },

      max: {
        type: [String, Number],
        default: 5
      },

      icon: String,
      color: String,
      size: String,

      noReset: Boolean,

      readonly: Boolean,
      disable: Boolean
    },

    data: function data () {
      return {
        mouseModel: 0
      }
    },

    computed: {
      editable: function editable () {
        return !this.readonly && !this.disable
      },

      classes: function classes () {
        return "q-rating--" + (this.editable === true ? '' : 'non-') + "editable" +
          (this.disable === true ? ' disabled' : '') +
          (this.color !== void 0 ? (" text-" + (this.color)) : '')
      },

      style: function style () {
        if (this.size !== void 0) {
          return { fontSize: this.size }
        }
      }
    },

    methods: {
      __set: function __set (value) {
        if (this.editable === true) {
          var
            model = between(parseInt(value, 10), 1, parseInt(this.max, 10)),
            newVal = this.noReset !== true && this.value === model ? 0 : model;

          newVal !== this.value && this.$emit('input', newVal);
          this.mouseModel = 0;
        }
      },

      __setHoverValue: function __setHoverValue (value) {
        if (this.editable === true) {
          this.mouseModel = value;
        }
      },

      __keyup: function __keyup (e, i) {
        switch (e.keyCode) {
          case 13:
          case 32:
            this.__set(i);
            return stopAndPrevent(e)
          case 37: // LEFT ARROW
          case 40: // DOWN ARROW
            if (this.$refs[("rt" + (i - 1))]) {
              this.$refs[("rt" + (i - 1))].focus();
            }
            return stopAndPrevent(e)
          case 39: // RIGHT ARROW
          case 38: // UP ARROW
            if (this.$refs[("rt" + (i + 1))]) {
              this.$refs[("rt" + (i + 1))].focus();
            }
            return stopAndPrevent(e)
        }
      }
    },

    render: function render (h) {
      var this$1 = this;

      var
        child = [],
        tabindex = this.editable === true ? 0 : null;

      var loop = function ( i ) {
        child.push(
          h(QIcon, {
            key: i,
            ref: ("rt" + i),
            staticClass: 'q-rating__icon',
            class: {
              'q-rating__icon--active': (!this$1.mouseModel && this$1.value >= i) || (this$1.mouseModel && this$1.mouseModel >= i),
              'q-rating__icon--exselected': this$1.mouseModel && this$1.value >= i && this$1.mouseModel < i,
              'q-rating__icon--hovered': this$1.mouseModel === i
            },
            props: { name: this$1.icon || this$1.$q.iconSet.rating.icon },
            attrs: { tabindex: tabindex },
            on: {
              click: function () { return this$1.__set(i); },
              mouseover: function () { return this$1.__setHoverValue(i); },
              mouseout: function () { this$1.mouseModel = 0; },
              focus: function () { return this$1.__setHoverValue(i); },
              blur: function () { this$1.mouseModel = 0; },
              keyup: function (e) { this$1.__keyup(e, i); }
            }
          })
        );
      };

      for (var i = 1; i <= this$1.max; i++) loop( i );

      return h('div', {
        staticClass: 'q-rating row inline items-center',
        class: this.classes,
        style: this.style,
        on: this.$listeners
      }, child)
    }
  });

  var QScrollArea = Vue.extend({
    name: 'QScrollArea',

    directives: {
      TouchPan: TouchPan
    },

    props: {
      thumbStyle: {
        type: Object,
        default: function () { return ({}); }
      },
      contentStyle: {
        type: Object,
        default: function () { return ({}); }
      },
      contentActiveStyle: {
        type: Object,
        default: function () { return ({}); }
      },
      delay: {
        type: [String, Number],
        default: 1000
      },
      horizontal: Boolean
    },

    data: function data () {
      return {
        active: false,
        hover: false,
        containerWidth: 0,
        containerHeight: 0,
        scrollPosition: 0,
        scrollSize: 0
      }
    },

    computed: {
      thumbHidden: function thumbHidden () {
        return this.scrollSize <= this.containerSize ||
          (this.active === false && this.hover === false)
      },

      thumbSize: function thumbSize () {
        return Math.round(
          between(
            this.containerSize * this.containerSize / this.scrollSize,
            50,
            this.containerSize
          )
        )
      },

      style: function style () {
        var pos = this.scrollPercentage * (this.containerSize - this.thumbSize);
        return Object.assign(
          {},
          this.thumbStyle,
          this.horizontal === true
            ? {
              left: (pos + "px"),
              width: ((this.thumbSize) + "px") }
            : {
              top: (pos + "px"),
              height: ((this.thumbSize) + "px")
            }
        )
      },

      mainStyle: function mainStyle () {
        return this.thumbHidden === true
          ? this.contentStyle
          : this.contentActiveStyle
      },

      scrollPercentage: function scrollPercentage () {
        var p = between(this.scrollPosition / (this.scrollSize - this.containerSize), 0, 1);
        return Math.round(p * 10000) / 10000
      },

      direction: function direction () {
        return this.horizontal === true
          ? 'right'
          : 'down'
      },

      containerSize: function containerSize () {
        return this.horizontal === true
          ? this.containerWidth
          : this.containerHeight
      },

      dirProps: function dirProps () {
        return this.horizontal === true
          ? { el: 'scrollLeft', wheel: 'x' }
          : { el: 'scrollTop', wheel: 'y' }
      },

      thumbClass: function thumbClass () {
        return "q-scrollarea__thumb--" + (this.horizontal === true ? 'h absolute-bottom' : 'v absolute-right') +
          (this.thumbHidden === true ? ' q-scrollarea__thumb--invisible' : '')
      }
    },

    methods: {
      getScrollTarget: function getScrollTarget () {
        return this.$refs.target
      },

      getScrollPosition: function getScrollPosition () {
        return this.$q.platform.is.desktop === true
          ? this.scrollPosition
          : this.$refs.target[this.dirProps.el]
      },

      setScrollPosition: function setScrollPosition$1 (offset, duration) {
        var fn = this.horizontal === true
          ? setHorizontalScrollPosition
          : setScrollPosition;

        fn(this.$refs.target, offset, duration);
      },

      __updateContainer: function __updateContainer (ref) {
        var height = ref.height;
        var width = ref.width;

        if (this.containerWidth !== width) {
          this.containerWidth = width;
          this.__setActive(true, true);
        }

        if (this.containerHeight !== height) {
          this.containerHeight = height;
          this.__setActive(true, true);
        }
      },

      __updateScroll: function __updateScroll (ref) {
        var position = ref.position;

        if (this.scrollPosition !== position) {
          this.scrollPosition = position;
          this.__setActive(true, true);
        }
      },

      __updateScrollSize: function __updateScrollSize (ref) {
        var height = ref.height;
        var width = ref.width;

        if (this.horizontal) {
          if (this.scrollSize !== width) {
            this.scrollSize = width;
            this.__setActive(true, true);
          }
        }
        else {
          if (this.scrollSize !== height) {
            this.scrollSize = height;
            this.__setActive(true, true);
          }
        }
      },

      __panThumb: function __panThumb (e) {
        if (e.isFirst === true) {
          this.refPos = this.scrollPosition;
          this.__setActive(true, true);
        }

        if (e.isFinal === true) {
          this.__setActive(false);
        }

        var multiplier = (this.scrollSize - this.containerSize) / (this.containerSize - this.thumbSize);
        var distance = this.horizontal ? e.distance.x : e.distance.y;
        var pos = this.refPos + (e.direction === this.direction ? 1 : -1) * distance * multiplier;
        this.__setScroll(pos);
      },

      __panContainer: function __panContainer (e) {
        if (e.isFirst === true) {
          this.refPos = this.scrollPosition;
          this.__setActive(true, true);
        }
        if (e.isFinal === true) {
          this.__setActive(false);
        }

        var distance = e.distance[this.horizontal === true ? 'x' : 'y'];
        var pos = this.refPos +
          (e.direction === this.direction ? -1 : 1) * distance;

        this.__setScroll(pos);

        if (pos > 0 && pos + this.containerSize < this.scrollSize) {
          prevent(e.evt);
        }
      },

      __mouseWheel: function __mouseWheel (e) {
        var el = this.$refs.target;

        el[this.dirProps.el] += getMouseWheelDistance(e)[this.dirProps.wheel];

        if (
          el[this.dirProps.el] > 0 &&
          el[this.dirProps.el] + this.containerSize < this.scrollSize
        ) {
          prevent(e);
        }
      },

      __setActive: function __setActive (active, timer) {
        clearTimeout(this.timer);

        if (active === this.active) {
          if (active && this.timer) {
            this.__startTimer();
          }
          return
        }

        if (active) {
          this.active = true;
          if (timer) {
            this.__startTimer();
          }
        }
        else {
          this.active = false;
        }
      },

      __startTimer: function __startTimer () {
        var this$1 = this;

        this.timer = setTimeout(function () {
          this$1.active = false;
          this$1.timer = null;
        }, this.delay);
      },

      __setScroll: function __setScroll (offset) {
        this.$refs.target[this.dirProps.el] = offset;
      }
    },

    render: function render (h) {
      var this$1 = this;

      if (this.$q.platform.is.desktop !== true) {
        return h('div', {
          staticClass: 'q-scroll-area',
          style: this.contentStyle
        }, [
          h('div', {
            ref: 'target',
            staticClass: 'scroll relative-position fit'
          }, slot(this, 'default'))
        ])
      }

      return h('div', {
        staticClass: 'q-scrollarea',
        on: {
          mouseenter: function () { this$1.hover = true; },
          mouseleave: function () { this$1.hover = false; }
        }
      }, [
        h('div', {
          ref: 'target',
          staticClass: 'scroll relative-position overflow-hidden fit',
          on: {
            wheel: this.__mouseWheel
          },
          directives: [{
            name: 'touch-pan',
            modifiers: {
              vertical: !this.horizontal,
              horizontal: this.horizontal,
              mightPrevent: true
            },
            value: this.__panContainer
          }]
        }, [
          h('div', {
            staticClass: 'absolute',
            style: this.mainStyle,
            class: ("full-" + (this.horizontal === true ? 'height' : 'width'))
          }, [
            h(QResizeObserver, {
              on: { resize: this.__updateScrollSize }
            }),
            slot(this, 'default')
          ]),

          h(QScrollObserver, {
            props: { horizontal: this.horizontal },
            on: { scroll: this.__updateScroll }
          })
        ]),

        h(QResizeObserver, {
          on: { resize: this.__updateContainer }
        }),

        h('div', {
          staticClass: 'q-scrollarea__thumb',
          style: this.style,
          class: this.thumbClass,
          directives: this.thumbHidden === true ? null : [{
            name: 'touch-pan',
            modifiers: {
              vertical: !this.horizontal,
              horizontal: this.horizontal,
              prevent: true,
              mouse: true,
              mouseAllDir: true
            },
            value: this.__panThumb
          }]
        })
      ])
    }
  });

  var validateNewValueMode = function (v) { return ['add', 'add-unique', 'toggle'].includes(v); };

  var
    optionsSliceSize = 31,
    optionDefaultHeight = 24,
    optionsListMaxPadding = 100000;

  var QSelect = Vue.extend({
    name: 'QSelect',

    mixins: [ QField ],

    props: {
      value: {
        required: true
      },

      multiple: Boolean,

      displayValue: [String, Number],
      displayValueSanitize: Boolean,
      dropdownIcon: String,

      options: {
        type: Array,
        default: function () { return []; }
      },

      optionValue: [Function, String],
      optionLabel: [Function, String],
      optionDisable: [Function, String],

      hideSelected: Boolean,
      hideDropdownIcon: Boolean,
      fillInput: Boolean,

      maxValues: [Number, String],

      optionsDense: Boolean,
      optionsDark: Boolean,
      optionsSelectedClass: String,
      optionsCover: Boolean,
      optionsSanitize: Boolean,

      popupContentClass: String,
      popupContentStyle: [String, Array, Object],

      useInput: Boolean,
      useChips: Boolean,

      newValueMode: {
        type: String,
        validator: validateNewValueMode
      },

      mapOptions: Boolean,
      emitValue: Boolean,

      inputDebounce: {
        type: [Number, String],
        default: 500
      },

      transitionShow: {
        type: String,
        default: 'fade'
      },

      transitionHide: {
        type: String,
        default: 'fade'
      }
    },

    data: function data () {
      return {
        menu: false,
        dialog: false,
        optionIndex: -1,
        optionsSliceRange: { from: 0, to: 0 },
        inputValue: ''
      }
    },

    watch: {
      innerValue: {
        handler: function handler () {
          if (
            this.useInput === true &&
            this.fillInput === true &&
            this.multiple !== true &&
            // Prevent re-entering in filter while filtering
            // Also prevent clearing inputValue while filtering
            this.innerLoading !== true &&
            ((this.dialog !== true && this.menu !== true) || this.hasValue !== true)
          ) {
            this.__resetInputValue();
            if (this.dialog === true || this.menu === true) {
              this.filter('');
            }
          }
        },
        immediate: true
      },

      menu: function menu (show) {
        this.__updateMenu(show);
      },

      options: {
        handler: function handler (options) {
          var optionsLength = Array.isArray(options) === false ? 0 : options.length;
          var optionsHeights = new Array(optionsLength);

          for (var i = optionsLength - 1; i >= 0; i--) {
            optionsHeights[i] = optionDefaultHeight;
          }

          this.optionsHeights = optionsHeights;
          this.optionsHeight = optionsLength * optionDefaultHeight;
          this.optionsMarginTop = this.optionsHeight;
        },
        immediate: true
      }
    },

    computed: {
      fieldClass: function fieldClass () {
        return ("q-select q-field--auto-height q-select--with" + (this.useInput !== true ? 'out' : '') + "-input")
      },

      menuClass: function menuClass () {
        return (this.optionsDark === true ? 'q-select__menu--dark' : '') +
          (this.popupContentClass ? ' ' + this.popupContentClass : '')
      },

      innerValue: function innerValue () {
        var this$1 = this;

        var
          mapNull = this.mapOptions === true && this.multiple !== true,
          val = this.value !== void 0 && (this.value !== null || mapNull === true)
            ? (this.multiple === true ? this.value : [ this.value ])
            : [];

        return this.mapOptions === true && Array.isArray(this.options) === true
          ? (
            this.value === null && mapNull === true
              ? val.map(function (v) { return this$1.__getOption(v); }).filter(function (v) { return v !== null; })
              : val.map(function (v) { return this$1.__getOption(v); })
          )
          : val
      },

      noOptions: function noOptions () {
        return this.options === void 0 || this.options === null || this.options.length === 0
      },

      selectedString: function selectedString () {
        var this$1 = this;

        return this.innerValue
          .map(function (opt) { return this$1.__getOptionLabel(opt); })
          .join(', ')
      },

      displayAsText: function displayAsText () {
        return this.displayValueSanitize === true || (
          this.displayValue === void 0 && (
            this.optionsSanitize === true ||
            this.innerValue.some(function (opt) { return opt !== null && opt.sanitize === true; })
          )
        )
      },

      selectedScope: function selectedScope () {
        var this$1 = this;

        var tabindex = this.focused === true ? 0 : -1;

        return this.innerValue.map(function (opt, i) { return ({
          index: i,
          opt: opt,
          sanitize: this$1.optionsSanitize === true || opt.sanitize === true,
          selected: true,
          removeAtIndex: this$1.__removeAtIndexAndFocus,
          toggleOption: this$1.toggleOption,
          tabindex: tabindex
        }); })
      },

      optionScope: function optionScope () {
        var this$1 = this;

        return this.options.slice(this.optionsSliceRange.from, this.optionsSliceRange.to).map(function (opt, i) {
          var disable = this$1.__isDisabled(opt);
          var index = this$1.optionsSliceRange.from + i;

          var itemProps = {
            clickable: true,
            active: false,
            activeClass: this$1.optionsSelectedClass,
            manualFocus: true,
            focused: false,
            disable: disable,
            tabindex: -1,
            dense: this$1.optionsDense,
            dark: this$1.optionsDark
          };

          if (disable !== true) {
            this$1.__isSelected(opt) === true && (itemProps.active = true);
            this$1.optionIndex === index && (itemProps.focused = true);
          }

          var itemEvents = {
            click: function () { this$1.toggleOption(opt); }
          };

          if (this$1.$q.platform.is.desktop === true) {
            itemEvents.mousemove = function () { this$1.setOptionIndex(index); };
          }

          return {
            index: index,
            opt: opt,
            sanitize: this$1.optionsSanitize === true || opt.sanitize === true,
            selected: itemProps.active,
            focused: itemProps.focused,
            toggleOption: this$1.toggleOption,
            setOptionIndex: this$1.setOptionIndex,
            itemProps: itemProps,
            itemEvents: itemEvents
          }
        })
      },

      dropdownArrowIcon: function dropdownArrowIcon () {
        return this.dropdownIcon !== void 0
          ? this.dropdownIcon
          : this.$q.iconSet.arrow.dropdown
      },

      squaredMenu: function squaredMenu () {
        return this.optionsCover === false &&
          this.outlined !== true &&
          this.standout !== true &&
          this.borderless !== true &&
          this.rounded !== true
      }
    },

    methods: {
      removeAtIndex: function removeAtIndex (index) {
        if (index > -1 && index < this.innerValue.length) {
          if (this.multiple === true) {
            var model = [].concat(this.value);
            this.$emit('remove', { index: index, value: model.splice(index, 1) });
            this.$emit('input', model);
          }
          else {
            this.$emit('input', null);
          }
        }
      },

      __removeAtIndexAndFocus: function __removeAtIndexAndFocus (index) {
        this.removeAtIndex(index);
        this.focus();
      },

      add: function add (opt, unique) {
        var val = this.emitValue === true
          ? this.__getOptionValue(opt)
          : opt;

        if (this.multiple !== true) {
          this.$emit('input', val);
          return
        }

        if (this.innerValue.length === 0) {
          this.$emit('add', { index: 0, value: val });
          this.$emit('input', this.multiple === true ? [ val ] : val);
          return
        }

        if (unique === true && this.__isSelected(opt) === true) {
          return
        }

        var model = [].concat(this.value);

        if (this.maxValues !== void 0 && model.length >= this.maxValues) {
          return
        }

        this.$emit('add', { index: model.length, value: val });
        model.push(val);
        this.$emit('input', model);
      },

      toggleOption: function toggleOption (opt) {
        var this$1 = this;

        if (this.editable !== true || opt === void 0 || this.__isDisabled(opt) === true) {
          return
        }

        var optValue = this.__getOptionValue(opt);

        this.multiple !== true && this.updateInputValue(
          this.fillInput === true ? this.__getOptionLabel(opt) : '',
          true
        );
        this.__focus();

        if (this.multiple !== true) {
          this.hidePopup();

          if (isDeepEqual(this.__getOptionValue(this.value), optValue) !== true) {
            this.$emit('input', this.emitValue === true ? optValue : opt);
          }
          return
        }

        if (this.innerValue.length === 0) {
          var val = this.emitValue === true ? optValue : opt;
          this.$emit('add', { index: 0, value: val });
          this.$emit('input', this.multiple === true ? [ val ] : val);
          return
        }

        var
          model = [].concat(this.value),
          index = this.value.findIndex(function (v) { return isDeepEqual(this$1.__getOptionValue(v), optValue); });

        if (index > -1) {
          this.$emit('remove', { index: index, value: model.splice(index, 1) });
        }
        else {
          if (this.maxValues !== void 0 && model.length >= this.maxValues) {
            return
          }

          var val$1 = this.emitValue === true ? optValue : opt;

          this.$emit('add', { index: model.length, value: val$1 });
          model.push(val$1);
        }

        this.$emit('input', model);
      },

      setOptionIndex: function setOptionIndex (index) {
        if (this.$q.platform.is.desktop !== true) { return }

        var val = index > -1 && index < this.options.length
          ? index
          : -1;

        if (this.optionIndex !== val) {
          this.optionIndex = val;
        }
      },

      __getOption: function __getOption (value) {
        var this$1 = this;

        return this.options.find(function (opt) { return isDeepEqual(this$1.__getOptionValue(opt), value); }) || value
      },

      __getOptionValue: function __getOptionValue (opt) {
        if (typeof this.optionValue === 'function') {
          return this.optionValue(opt)
        }
        if (Object(opt) === opt) {
          return typeof this.optionValue === 'string'
            ? opt[this.optionValue]
            : opt.value
        }
        return opt
      },

      __getOptionLabel: function __getOptionLabel (opt) {
        if (typeof this.optionLabel === 'function') {
          return this.optionLabel(opt)
        }
        if (Object(opt) === opt) {
          return typeof this.optionLabel === 'string'
            ? opt[this.optionLabel]
            : opt.label
        }
        return opt
      },

      __isDisabled: function __isDisabled (opt) {
        if (typeof this.optionDisable === 'function') {
          return this.optionDisable(opt) === true
        }
        if (Object(opt) === opt) {
          return typeof this.optionDisable === 'string'
            ? opt[this.optionDisable] === true
            : opt.disable === true
        }
        return false
      },

      __isSelected: function __isSelected (opt) {
        var this$1 = this;

        var val = this.__getOptionValue(opt);
        return this.innerValue
          .find(function (v) { return isDeepEqual(this$1.__getOptionValue(v), val); }) !== void 0
      },

      __onTargetKeydown: function __onTargetKeydown (e) {
        var this$1 = this;

        // escape, tab
        if (e.keyCode === 27 || e.keyCode === 9) {
          this.__closeMenu();
          return
        }

        if (e.target !== this.$refs.target) { return }

        // down
        if (
          e.keyCode === 40 &&
          this.innerLoading !== true &&
          this.menu === false
        ) {
          stopAndPrevent(e);
          this.showPopup();
          return
        }

        // delete
        if (
          e.keyCode === 8 &&
          this.multiple === true &&
          this.inputValue.length === 0 &&
          Array.isArray(this.value)
        ) {
          this.removeAtIndex(this.value.length - 1);
          return
        }

        // up, down
        var optionsLength = this.options.length;

        if (e.keyCode === 38 || e.keyCode === 40) {
          stopAndPrevent(e);

          if (this.menu === true) {
            var index = this.optionIndex;
            do {
              index = normalizeToInterval(
                index + (e.keyCode === 38 ? -1 : 1),
                -1,
                optionsLength - 1
              );
            }
            while (index !== -1 && index !== this.optionIndex && this.__isDisabled(this.options[index]) === true)

            if (this.optionIndex !== index) {
              this.__setPreventNextScroll();

              this.optionIndex = index;

              this.__hydrateOptions({ target: this.__getMenuContentEl() }, index);
            }
          }
        }

        // enter
        if (e.target !== this.$refs.target || e.keyCode !== 13) { return }

        stopAndPrevent(e);

        if (this.optionIndex > -1 && this.optionIndex < optionsLength) {
          this.toggleOption(this.options[this.optionIndex]);
          return
        }

        if (
          this.inputValue.length > 0 &&
          (this.newValueMode !== void 0 || this.$listeners['new-value'] !== void 0)
        ) {
          var done = function (val, mode) {
            if (mode) {
              if (validateNewValueMode(mode) !== true) {
                console.error('QSelect: invalid new value mode - ' + mode);
                return
              }
            }
            else {
              mode = this$1.newValueMode;
            }

            if (val !== void 0 && val !== null) {
              this$1[mode === 'toggle' ? 'toggleOption' : 'add'](
                val,
                mode === 'add-unique'
              );
            }

            this$1.updateInputValue('', this$1.multiple !== true);
          };

          if (this.$listeners['new-value'] !== void 0) {
            this.$emit('new-value', this.inputValue, done);

            if (this.multiple !== true) {
              return
            }
          }
          else {
            done(this.inputValue);
          }
        }

        if (this.menu === true) {
          this.dialog !== true && this.__closeMenu();
        }
        else if (this.innerLoading !== true) {
          this.showPopup();
        }
      },

      __getMenuContentEl: function __getMenuContentEl () {
        return this.hasDialog === true
          ? this.$refs.menuContent
          : (
            this.$refs.menu !== void 0
              ? this.$refs.menu.__portal.$el
              : void 0
          )
      },

      __hydrateOptions: function __hydrateOptions (ev, toIndex) {
        var this$1 = this;

        clearTimeout(this.hidrateTimer);

        if (ev === void 0 || (this.preventNextScroll === true && toIndex === void 0)) {
          return
        }

        var
          delayNextScroll = this.delayNextScroll === true && toIndex === void 0,
          target = delayNextScroll === true || ev.target === void 0 || ev.target.nodeType === 8 ? void 0 : ev.target,
          content = target === void 0 ? null : target.querySelector('.q-select__options--content');

        if (content === null) {
          this.hidrateTimer = setTimeout(function () {
            this$1.__hydrateOptions({ target: this$1.__getMenuContentEl() }, toIndex);
          }, 10);

          return
        }

        var
          scrollTop = target.scrollTop,
          viewHeight = target.clientHeight,
          child = content.children[toIndex - this.optionsSliceRange.from],
          childPosTop = child === void 0 ? -1 : content.offsetTop + child.offsetTop,
          childPosBottom = child === void 0 ? -1 : childPosTop + child.clientHeight,
          fromScroll = toIndex === void 0;

        if (fromScroll === true) {
          var toIndexMax = this.options.length - 1;

          toIndex = -1;
          for (var i = Math.trunc(scrollTop + viewHeight / 2); i >= 0 && toIndex < toIndexMax;) {
            toIndex++;
            i -= this.optionsHeights[toIndex];
          }
        }

        toIndex = toIndex < 0 ? 0 : toIndex;

        // destination option is not in view
        if (childPosTop < scrollTop || childPosBottom > scrollTop + viewHeight) {
          this.__setOptionsSliceRange(toIndex, target, fromScroll);
        }
      },

      __setPreventNextScroll: function __setPreventNextScroll (delay) {
        var this$1 = this;

        clearTimeout(this.preventNextScrollTimer);

        this.preventNextScroll = delay !== true;
        this.delayNextScroll = delay === true;

        this.preventNextScrollTimer = setTimeout(function () {
          this$1.preventNextScroll = false;
          this$1.delayNextScroll = false;
        }, 10);
      },

      __setOptionsSliceRange: function __setOptionsSliceRange (toIndex, target, fromScroll) {
        var this$1 = this;

        var
          from = Math.max(0, Math.min(toIndex - Math.round(optionsSliceSize / 2), this.options.length - optionsSliceSize)),
          to = from + optionsSliceSize,
          repositionScroll = fromScroll !== true || from < this.optionsSliceRange.from;

        if (from === this.optionsSliceRange.from && to === this.optionsSliceRange.to) {
          if (fromScroll === true) {
            return
          }
        }
        else {
          this.__setPreventNextScroll(fromScroll);
          this.optionsSliceRange = { from: from, to: to };
        }

        this.$nextTick(function () {
          var content = target === void 0 ? null : target.querySelector('.q-select__options--content');

          if (content === null) {
            return
          }

          var children = content.children;

          var marginTopDiff = 0;

          for (var i = children.length - 1; i >= 0; i--) {
            var diff = children[i].clientHeight - this$1.optionsHeights[from + i];

            if (diff !== 0) {
              marginTopDiff += diff;
              this$1.optionsHeights[from + i] += diff;
            }
          }

          var
            marginTop = this$1.optionsHeights.slice(from).reduce(function (acc, h) { return acc + h; }, 0),
            height = marginTop + this$1.optionsHeights.slice(0, from).reduce(function (acc, h) { return acc + h; }, 0),
            padding = this$1.optionsHeight % optionsListMaxPadding + height - this$1.optionsHeight;

          if (this$1.optionsMarginTop !== marginTop || this$1.optionsHeight !== height) {
            this$1.optionsMarginTop = marginTop;
            this$1.optionsHeight = height;

            this$1.__setPreventNextScroll(fromScroll);
            // content.previousSibling is the last padding block
            content.previousSibling.style.cssText = padding >= 0 ? ("height: " + padding + "px; margin-top: 0px") : ("height: 0px; margin-top: " + padding + "px");
            content.style.marginTop = "-" + marginTop + "px";
          }

          if (repositionScroll === true) {
            if (fromScroll !== true) {
              this$1.__setPreventNextScroll(fromScroll);
              target.scrollTop = this$1.optionsHeights.slice(0, toIndex).reduce(function (acc, h) { return acc + h; }, 0) + (
                this$1.$q.platform.is.mobile === true
                  ? 0
                  : Math.trunc(this$1.optionsHeights[toIndex] / 2 - target.clientHeight / 2)
              );
            }
            else if (marginTopDiff !== 0) {
              this$1.__setPreventNextScroll(fromScroll);
              target.scrollTop += marginTopDiff;
            }
          }
        });
      },

      __getSelection: function __getSelection (h, fromDialog) {
        var this$1 = this;
        var obj;

        if (this.hideSelected === true) {
          return fromDialog !== true && this.hasDialog === true
            ? [
              h('span', {
                domProps: {
                  'textContent': this.inputValue
                }
              })
            ]
            : []
        }

        if (this.$scopedSlots['selected-item'] !== void 0) {
          return this.selectedScope.map(function (scope) { return this$1.$scopedSlots['selected-item'](scope); })
        }

        if (this.$scopedSlots.selected !== void 0) {
          return this.$scopedSlots.selected()
        }

        if (this.useChips === true) {
          var tabindex = this.focused === true ? 0 : -1;

          return this.selectedScope.map(function (scope, i) {
            var obj;

            return h(QChip, {
            key: 'option-' + i,
            props: {
              removable: this$1.__isDisabled(scope.opt) !== true,
              dense: true,
              textColor: this$1.color,
              tabindex: tabindex
            },
            on: {
              remove: function remove () { scope.removeAtIndex(i); }
            }
          }, [
            h('span', {
              domProps: ( obj = {}, obj[scope.sanitize === true ? 'textContent' : 'innerHTML'] = this$1.__getOptionLabel(scope.opt), obj )
            })
          ]);
          })
        }

        return [
          h('span', {
            domProps: ( obj = {}, obj[this.displayAsText ? 'textContent' : 'innerHTML'] = this.displayValue !== void 0
                ? this.displayValue
                : this.selectedString, obj )
          })
        ]
      },

      __getControl: function __getControl (h, fromDialog) {
        var data = { attrs: {} };
        var child = this.__getSelection(h, fromDialog);

        if (this.useInput === true && (fromDialog === true || this.hasDialog === false)) {
          child.push(this.__getInput(h));
        }
        else if (this.editable === true) {
          data = {
            ref: 'target',
            attrs: {
              tabindex: 0,
              autofocus: this.autofocus
            },
            on: {
              keydown: this.__onTargetKeydown
            }
          };
        }

        Object.assign(data.attrs, this.$attrs);
        data.staticClass = 'q-field__native row items-center';

        return h('div', data, child)
      },

      __getOptions: function __getOptions (h) {
        var this$1 = this;

        var fn = this.$scopedSlots.option !== void 0
          ? this.$scopedSlots.option
          : function (scope) {
            var obj;

            return h(QItem, {
            key: scope.index,
            props: scope.itemProps,
            on: scope.itemEvents
          }, [
            h(QItemSection, [
              h(QItemLabel, {
                domProps: ( obj = {}, obj[scope.sanitize === true ? 'textContent' : 'innerHTML'] = this$1.__getOptionLabel(scope.opt), obj )
              })
            ])
          ]);
        };

        var list = [];

        for (var i = Math.trunc(this.optionsHeight / optionsListMaxPadding); i > 0; i--) {
          list.push(h('div', { staticClass: 'q-select__options--padding', style: { height: (optionsListMaxPadding + "px") } }));
        }
        list.push(h('div', { staticClass: 'q-select__options--padding', style: { height: ((this.optionsHeight % optionsListMaxPadding) + "px") } }));

        list.push(h('div', {
          staticClass: 'q-select__options--content',
          style: {
            marginTop: ("-" + (this.optionsMarginTop) + "px")
          }
        }, this.optionScope.map(fn)));

        return list
      },

      __getInnerAppend: function __getInnerAppend (h) {
        return this.loading !== true && this.innerLoading !== true && this.hideDropdownIcon !== true
          ? [
            h(QIcon, {
              staticClass: 'q-select__dropdown-icon',
              props: { name: this.dropdownArrowIcon }
            })
          ]
          : null
      },

      __onCompositionStart: function __onCompositionStart (e) {
        e.target.composing = true;
      },

      __onCompositionUpdate: function __onCompositionUpdate (e) {
        if (typeof e.data === 'string' && e.data.codePointAt(0) < 256) {
          e.target.composing = false;
        }
      },

      __onCompositionEnd: function __onCompositionEnd (e) {
        if (e.target.composing !== true) { return }
        e.target.composing = false;

        this.__onInputValue(e);
      },

      __getInput: function __getInput (h) {
        var on = {
          input: this.__onInputValue,
          // Safari < 10.2 & UIWebView doesn't fire compositionend when
          // switching focus before confirming composition choice
          // this also fixes the issue where some browsers e.g. iOS Chrome
          // fires "change" instead of "input" on autocomplete.
          change: this.__onCompositionEnd,
          compositionstart: this.__onCompositionStart,
          compositionend: this.__onCompositionEnd,
          keydown: this.__onTargetKeydown
        };

        if (this.$q.platform.is.android === true) {
          on.compositionupdate = this.__onCompositionUpdate;
        }

        return h('input', {
          ref: 'target',
          staticClass: 'q-select__input q-placeholder col',
          class: this.hideSelected !== true && this.innerValue.length > 0
            ? 'q-select__input--padding'
            : null,
          domProps: { value: this.inputValue },
          attrs: Object.assign({}, {tabindex: 0,
            autofocus: this.autofocus},
            this.$attrs,
            {disabled: this.editable !== true}),
          on: on
        })
      },

      __onInputValue: function __onInputValue (e) {
        var this$1 = this;

        clearTimeout(this.inputTimer);

        if (e && e.target && e.target.composing === true) {
          return
        }

        this.inputValue = e.target.value || '';

        if (this.$listeners.filter !== void 0) {
          this.inputTimer = setTimeout(function () {
            this$1.filter(this$1.inputValue, true);
          }, this.inputDebounce);
        }
      },

      updateInputValue: function updateInputValue (val, noFiltering) {
        if (this.useInput === true) {
          if (this.inputValue !== val) {
            this.inputValue = val;
          }

          noFiltering !== true && this.filter(val);
        }
      },

      filter: function filter (val, userInput) {
        var this$1 = this;

        if (this.$listeners.filter === void 0 || this.focused !== true) {
          return
        }

        if (this.innerLoading === true) {
          this.$emit('filter-abort');
        }
        else {
          this.innerLoading = true;
        }

        if (
          val !== '' &&
          this.multiple !== true &&
          this.innerValue.length > 0 &&
          userInput !== true &&
          val === this.__getOptionLabel(this.innerValue[0])
        ) {
          val = '';
        }

        var filterId = setTimeout(function () {
          this$1.menu === true && (this$1.menu = false);
        }, 10);
        clearTimeout(this.filterId);
        this.filterId = filterId;

        this.$emit(
          'filter',
          val,
          function (fn) {
            if (this$1.focused === true && this$1.filterId === filterId) {
              clearTimeout(this$1.filterId);
              typeof fn === 'function' && fn();
              this$1.$nextTick(function () {
                this$1.innerLoading = false;
                if (this$1.menu === true) {
                  this$1.__updateMenu();
                }
                else {
                  this$1.menu = true;
                }
              });
            }
          },
          function () {
            if (this$1.focused === true && this$1.filterId === filterId) {
              clearTimeout(this$1.filterId);
              this$1.innerLoading = false;
            }
            this$1.menu === true && (this$1.menu = false);
          }
        );
      },

      __getControlEvents: function __getControlEvents () {
        var this$1 = this;

        var focusout = function (e) {
          this$1.__onControlFocusout(e, function () {
            this$1.__resetInputValue();
            this$1.__closeMenu();
          });
        };

        return {
          focus: function (e) {
            this$1.hasDialog !== true && this$1.focus(e);
          },
          focusin: this.__onControlFocusin,
          focusout: focusout,
          'popup-show': this.__onControlPopupShow,
          'popup-hide': function (e) {
            this$1.hasPopupOpen = false;
            focusout(e);
          },
          click: function (e) {
            if (this$1.hasDialog !== true && this$1.menu === true) {
              this$1.__closeMenu();
            }
            else {
              this$1.showPopup(e);
            }
          }
        }
      },

      __getPopup: function __getPopup (h) {
        if (
          this.editable !== false && (
            this.dialog === true || // dialog always has menu displayed, so need to render it
            this.noOptions !== true ||
            this.$scopedSlots['no-option'] !== void 0
          )
        ) {
          return this[("__get" + (this.hasDialog === true ? 'Dialog' : 'Menu'))](h)
        }
      },

      __getMenu: function __getMenu (h) {
        var child = this.noOptions === true
          ? (
            this.$scopedSlots['no-option'] !== void 0
              ? this.$scopedSlots['no-option']({ inputValue: this.inputValue })
              : null
          )
          : this.__getOptions(h);

        return h(QMenu, {
          ref: 'menu',
          props: {
            value: this.menu,
            fit: true,
            cover: this.optionsCover === true && this.noOptions !== true && this.useInput !== true,
            contentClass: this.menuClass,
            contentStyle: this.popupContentStyle,
            noParentEvent: true,
            noRefocus: true,
            noFocus: true,
            square: this.squaredMenu,
            transitionShow: this.transitionShow,
            transitionHide: this.transitionHide
          },
          on: {
            '&scroll': this.__hydrateOptions,
            'before-hide': this.__closeMenu
          }
        }, child)
      },

      __getDialog: function __getDialog (h) {
        var this$1 = this;

        var content = [
          h(QField, {
            staticClass: ("col-auto " + (this.fieldClass)),
            props: Object.assign({}, this.$props,
              {dark: this.optionsDark,
              square: true,
              loading: this.innerLoading,
              filled: true,
              stackLabel: this.inputValue.length > 0}),
            on: Object.assign({}, this.$listeners,
              {focus: stop,
              blur: stop}),
            scopedSlots: Object.assign({}, this.$scopedSlots,
              {rawControl: function () { return this$1.__getControl(h, true); },
              before: void 0,
              after: void 0})
          })
        ];

        this.menu === true && content.push(
          h('div', {
            ref: 'menuContent',
            staticClass: 'scroll',
            class: this.popupContentClass,
            style: this.popupContentStyle,
            on: {
              click: prevent,
              '&scroll': this.__hydrateOptions
            }
          }, (
            this.noOptions === true
              ? (
                this.$scopedSlots['no-option'] !== void 0
                  ? this.$scopedSlots['no-option']({ inputValue: this.inputValue })
                  : null
              )
              : this.__getOptions(h)
          ))
        );

        return h(QDialog, {
          props: {
            value: this.dialog,
            noRefocus: true,
            noFocus: true,
            position: this.useInput === true ? 'top' : void 0
          },
          on: {
            'before-hide': function () {
              this$1.focused = false;
            },
            hide: function (e) {
              this$1.hidePopup();
              this$1.$emit('blur', e);
              this$1.__resetInputValue();
            },
            show: function () {
              this$1.$refs.target.focus();
            }
          }
        }, [
          h('div', {
            staticClass: 'q-select__dialog' + (this.optionsDark === true ? ' q-select__menu--dark' : '')
          }, content)
        ])
      },

      __closeMenu: function __closeMenu () {
        this.menu = false;

        if (this.focused === false) {
          clearTimeout(this.filterId);
          this.filterId = void 0;

          if (this.innerLoading === true) {
            this.$emit('filter-abort');
            this.innerLoading = false;
          }
        }
      },

      showPopup: function showPopup (e) {
        if (this.hasDialog === true) {
          this.__onControlFocusin(e);
          this.dialog = true;
        }
        else {
          this.focus(e);
        }

        if (this.$listeners.filter !== void 0) {
          this.filter(this.inputValue);
        }
        else if (this.noOptions !== true || this.$scopedSlots['no-option'] !== void 0) {
          this.menu = true;
        }
      },

      hidePopup: function hidePopup () {
        this.dialog = false;
        this.__closeMenu();
      },

      __resetInputValue: function __resetInputValue () {
        this.useInput === true && this.updateInputValue(
          this.multiple !== true && this.fillInput === true && this.innerValue.length > 0
            ? this.__getOptionLabel(this.innerValue[0]) || ''
            : '',
          true
        );
      },

      __updateMenu: function __updateMenu (show) {
        var this$1 = this;

        var optionIndex = -1;

        if (show === true) {
          if (this.innerValue.length > 0) {
            var val = this.__getOptionValue(this.innerValue[0]);
            optionIndex = this.options.findIndex(function (v) { return isDeepEqual(this$1.__getOptionValue(v), val); });
          }

          this.__setPreventNextScroll(true);
          this.optionsSliceRange = { from: 0, to: 0 };
          this.__hydrateOptions({ target: this.__getMenuContentEl() }, optionIndex);
        }

        this.optionIndex = optionIndex;
      },

      __onPreRender: function __onPreRender () {
        this.hasDialog = this.$q.platform.is.mobile !== true
          ? false
          : (
            this.useInput === true
              ? this.$scopedSlots['no-option'] !== void 0 || this.$listeners.filter !== void 0
              : true
          );
      },

      __onPostRender: function __onPostRender () {
        if (this.dialog === false && this.$refs.menu !== void 0) {
          this.$refs.menu.updatePosition();
        }
      },

      updateMenuPosition: function updateMenuPosition () {
        this.__onPostRender();
      }
    },

    mounted: function mounted () {
      this.__setOptionsSliceRange = this.$q.platform.is.ios === true || this.$q.platform.is.safari === true
        ? frameDebounce(this.__setOptionsSliceRange)
        : debounce(this.__setOptionsSliceRange, 50);
    },

    beforeDestroy: function beforeDestroy () {
      clearTimeout(this.inputTimer);
      clearTimeout(this.hidrateTimer);
    }
  });

  var QSpace = Vue.extend({
    name: 'QSpace',

    render: function render (h) {
      return h('div', {
        staticClass: 'q-space'
      })
    }
  });

  var QSpinnerAudio = Vue.extend({
    name: 'QSpinnerAudio',

    mixins: [mixin],

    render: function render (h) {
      return h('svg', {
        staticClass: 'q-spinner',
        class: this.classes,
        on: this.$listeners,
        attrs: {
          'fill': 'currentColor',
          'width': this.size,
          'height': this.size,
          'viewBox': '0 0 55 80',
          'xmlns': 'http://www.w3.org/2000/svg'
        }
      }, [
        h('g', {
          attrs: {
            'transform': 'matrix(1 0 0 -1 0 80)'
          }
        }, [
          h('rect', {
            attrs: {
              'width': '10',
              'height': '20',
              'rx': '3'
            }
          }, [
            h('animate', {
              attrs: {
                'attributeName': 'height',
                'begin': '0s',
                'dur': '4.3s',
                'values': '20;45;57;80;64;32;66;45;64;23;66;13;64;56;34;34;2;23;76;79;20',
                'calcMode': 'linear',
                'repeatCount': 'indefinite'
              }
            })
          ]),
          h('rect', {
            attrs: {
              'x': '15',
              'width': '10',
              'height': '80',
              'rx': '3'
            }
          }, [
            h('animate', {
              attrs: {
                'attributeName': 'height',
                'begin': '0s',
                'dur': '2s',
                'values': '80;55;33;5;75;23;73;33;12;14;60;80',
                'calcMode': 'linear',
                'repeatCount': 'indefinite'
              }
            })
          ]),
          h('rect', {
            attrs: {
              'x': '30',
              'width': '10',
              'height': '50',
              'rx': '3'
            }
          }, [
            h('animate', {
              attrs: {
                'attributeName': 'height',
                'begin': '0s',
                'dur': '1.4s',
                'values': '50;34;78;23;56;23;34;76;80;54;21;50',
                'calcMode': 'linear',
                'repeatCount': 'indefinite'
              }
            })
          ]),
          h('rect', {
            attrs: {
              'x': '45',
              'width': '10',
              'height': '30',
              'rx': '3'
            }
          }, [
            h('animate', {
              attrs: {
                'attributeName': 'height',
                'begin': '0s',
                'dur': '2s',
                'values': '30;45;13;80;56;72;45;76;34;23;67;30',
                'calcMode': 'linear',
                'repeatCount': 'indefinite'
              }
            })
          ])
        ])
      ])
    }
  });

  var QSpinnerBall = Vue.extend({
    name: 'QSpinnerBall',

    mixins: [mixin],

    render: function render (h) {
      return h('svg', {
        staticClass: 'q-spinner',
        class: this.classes,
        on: this.$listeners,
        attrs: {
          'stroke': 'currentColor',
          'width': this.size,
          'height': this.size,
          'viewBox': '0 0 57 57',
          'xmlns': 'http://www.w3.org/2000/svg'
        }
      }, [
        h('g', {
          attrs: {
            'transform': 'translate(1 1)',
            'stroke-width': '2',
            'fill': 'none',
            'fill-rule': 'evenodd'
          }
        }, [
          h('circle', {
            attrs: {
              'cx': '5',
              'cy': '50',
              'r': '5'
            }
          }, [
            h('animate', {
              attrs: {
                'attributeName': 'cy',
                'begin': '0s',
                'dur': '2.2s',
                'values': '50;5;50;50',
                'calcMode': 'linear',
                'repeatCount': 'indefinite'
              }
            }),
            h('animate', {
              attrs: {
                'attributeName': 'cx',
                'begin': '0s',
                'dur': '2.2s',
                'values': '5;27;49;5',
                'calcMode': 'linear',
                'repeatCount': 'indefinite'
              }
            })
          ]),
          h('circle', {
            attrs: {
              'cx': '27',
              'cy': '5',
              'r': '5'
            }
          }, [
            h('animate', {
              attrs: {
                'attributeName': 'cy',
                'begin': '0s',
                'dur': '2.2s',
                'from': '5',
                'to': '5',
                'values': '5;50;50;5',
                'calcMode': 'linear',
                'repeatCount': 'indefinite'
              }
            }),
            h('animate', {
              attrs: {
                'attributeName': 'cx',
                'begin': '0s',
                'dur': '2.2s',
                'from': '27',
                'to': '27',
                'values': '27;49;5;27',
                'calcMode': 'linear',
                'repeatCount': 'indefinite'
              }
            })
          ]),
          h('circle', {
            attrs: {
              'cx': '49',
              'cy': '50',
              'r': '5'
            }
          }, [
            h('animate', {
              attrs: {
                'attributeName': 'cy',
                'begin': '0s',
                'dur': '2.2s',
                'values': '50;50;5;50',
                'calcMode': 'linear',
                'repeatCount': 'indefinite'
              }
            }),
            h('animate', {
              attrs: {
                'attributeName': 'cx',
                'from': '49',
                'to': '49',
                'begin': '0s',
                'dur': '2.2s',
                'values': '49;5;27;49',
                'calcMode': 'linear',
                'repeatCount': 'indefinite'
              }
            })
          ])
        ])
      ])
    }
  });

  var QSpinnerBars = Vue.extend({
    name: 'QSpinnerBars',

    mixins: [mixin],

    render: function render (h) {
      return h('svg', {
        staticClass: 'q-spinner',
        class: this.classes,
        on: this.$listeners,
        attrs: {
          'fill': 'currentColor',
          'width': this.size,
          'height': this.size,
          'viewBox': '0 0 135 140',
          'xmlns': 'http://www.w3.org/2000/svg'
        }
      }, [
        h('rect', {
          attrs: {
            'y': '10',
            'width': '15',
            'height': '120',
            'rx': '6'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'height',
              'begin': '0.5s',
              'dur': '1s',
              'values': '120;110;100;90;80;70;60;50;40;140;120',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          }),
          h('animate', {
            attrs: {
              'attributeName': 'y',
              'begin': '0.5s',
              'dur': '1s',
              'values': '10;15;20;25;30;35;40;45;50;0;10',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('rect', {
          attrs: {
            'x': '30',
            'y': '10',
            'width': '15',
            'height': '120',
            'rx': '6'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'height',
              'begin': '0.25s',
              'dur': '1s',
              'values': '120;110;100;90;80;70;60;50;40;140;120',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          }),
          h('animate', {
            attrs: {
              'attributeName': 'y',
              'begin': '0.25s',
              'dur': '1s',
              'values': '10;15;20;25;30;35;40;45;50;0;10',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('rect', {
          attrs: {
            'x': '60',
            'width': '15',
            'height': '140',
            'rx': '6'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'height',
              'begin': '0s',
              'dur': '1s',
              'values': '120;110;100;90;80;70;60;50;40;140;120',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          }),
          h('animate', {
            attrs: {
              'attributeName': 'y',
              'begin': '0s',
              'dur': '1s',
              'values': '10;15;20;25;30;35;40;45;50;0;10',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('rect', {
          attrs: {
            'x': '90',
            'y': '10',
            'width': '15',
            'height': '120',
            'rx': '6'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'height',
              'begin': '0.25s',
              'dur': '1s',
              'values': '120;110;100;90;80;70;60;50;40;140;120',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          }),
          h('animate', {
            attrs: {
              'attributeName': 'y',
              'begin': '0.25s',
              'dur': '1s',
              'values': '10;15;20;25;30;35;40;45;50;0;10',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('rect', {
          attrs: {
            'x': '120',
            'y': '10',
            'width': '15',
            'height': '120',
            'rx': '6'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'height',
              'begin': '0.5s',
              'dur': '1s',
              'values': '120;110;100;90;80;70;60;50;40;140;120',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          }),
          h('animate', {
            attrs: {
              'attributeName': 'y',
              'begin': '0.5s',
              'dur': '1s',
              'values': '10;15;20;25;30;35;40;45;50;0;10',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          })
        ])
      ])
    }
  });

  var QSpinnerComment = Vue.extend({
    name: 'QSpinnerComment',

    mixins: [mixin],

    render: function render (h) {
      return h('svg', {
        staticClass: 'q-spinner',
        class: this.classes,
        on: this.$listeners,
        attrs: {
          'width': this.size,
          'height': this.size,
          'xmlns': 'http://www.w3.org/2000/svg',
          'viewBox': '0 0 100 100',
          'preserveAspectRatio': 'xMidYMid'
        }
      }, [
        h('rect', {
          attrs: {
            'x': '0',
            'y': '0',
            'width': '100',
            'height': '100',
            'fill': 'none'
          }
        }),
        h('path', {
          attrs: {
            'd': 'M78,19H22c-6.6,0-12,5.4-12,12v31c0,6.6,5.4,12,12,12h37.2c0.4,3,1.8,5.6,3.7,7.6c2.4,2.5,5.1,4.1,9.1,4 c-1.4-2.1-2-7.2-2-10.3c0-0.4,0-0.8,0-1.3h8c6.6,0,12-5.4,12-12V31C90,24.4,84.6,19,78,19z',
            'fill': 'currentColor'
          }
        }),
        h('circle', {
          attrs: {
            'cx': '30',
            'cy': '47',
            'r': '5',
            'fill': '#fff'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'opacity',
              'from': '0',
              'to': '1',
              'values': '0;1;1',
              'keyTimes': '0;0.2;1',
              'dur': '1s',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('circle', {
          attrs: {
            'cx': '50',
            'cy': '47',
            'r': '5',
            'fill': '#fff'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'opacity',
              'from': '0',
              'to': '1',
              'values': '0;0;1;1',
              'keyTimes': '0;0.2;0.4;1',
              'dur': '1s',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('circle', {
          attrs: {
            'cx': '70',
            'cy': '47',
            'r': '5',
            'fill': '#fff'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'opacity',
              'from': '0',
              'to': '1',
              'values': '0;0;1;1',
              'keyTimes': '0;0.4;0.6;1',
              'dur': '1s',
              'repeatCount': 'indefinite'
            }
          })
        ])
      ])
    }
  });

  var QSpinnerCube = Vue.extend({
    name: 'QSpinnerCube',

    mixins: [mixin],

    render: function render (h) {
      return h('svg', {
        staticClass: 'q-spinner',
        class: this.classes,
        on: this.$listeners,
        attrs: {
          'width': this.size,
          'height': this.size,
          'xmlns': 'http://www.w3.org/2000/svg',
          'viewBox': '0 0 100 100',
          'preserveAspectRatio': 'xMidYMid'
        }
      }, [
        h('rect', {
          attrs: {
            'x': '0',
            'y': '0',
            'width': '100',
            'height': '100',
            'fill': 'none'
          }
        }),
        h('g', {
          attrs: {
            'transform': 'translate(25 25)'
          }
        }, [
          h('rect', {
            attrs: {
              'x': '-20',
              'y': '-20',
              'width': '40',
              'height': '40',
              'fill': 'currentColor',
              'opacity': '0.9'
            }
          }, [
            h('animateTransform', {
              attrs: {
                'attributeName': 'transform',
                'type': 'scale',
                'from': '1.5',
                'to': '1',
                'repeatCount': 'indefinite',
                'begin': '0s',
                'dur': '1s',
                'calcMode': 'spline',
                'keySplines': '0.2 0.8 0.2 0.8',
                'keyTimes': '0;1'
              }
            })
          ])
        ]),
        h('g', {
          attrs: {
            'transform': 'translate(75 25)'
          }
        }, [
          h('rect', {
            attrs: {
              'x': '-20',
              'y': '-20',
              'width': '40',
              'height': '40',
              'fill': 'currentColor',
              'opacity': '0.8'
            }
          }, [
            h('animateTransform', {
              attrs: {
                'attributeName': 'transform',
                'type': 'scale',
                'from': '1.5',
                'to': '1',
                'repeatCount': 'indefinite',
                'begin': '0.1s',
                'dur': '1s',
                'calcMode': 'spline',
                'keySplines': '0.2 0.8 0.2 0.8',
                'keyTimes': '0;1'
              }
            })
          ])
        ]),
        h('g', {
          attrs: {
            'transform': 'translate(25 75)'
          }
        }, [
          h('rect', {
            staticClass: 'cube',
            attrs: {
              'x': '-20',
              'y': '-20',
              'width': '40',
              'height': '40',
              'fill': 'currentColor',
              'opacity': '0.7'
            }
          }, [
            h('animateTransform', {
              attrs: {
                'attributeName': 'transform',
                'type': 'scale',
                'from': '1.5',
                'to': '1',
                'repeatCount': 'indefinite',
                'begin': '0.3s',
                'dur': '1s',
                'calcMode': 'spline',
                'keySplines': '0.2 0.8 0.2 0.8',
                'keyTimes': '0;1'
              }
            })
          ])
        ]),
        h('g', {
          attrs: {
            'transform': 'translate(75 75)'
          }
        }, [
          h('rect', {
            staticClass: 'cube',
            attrs: {
              'x': '-20',
              'y': '-20',
              'width': '40',
              'height': '40',
              'fill': 'currentColor',
              'opacity': '0.6'
            }
          }, [
            h('animateTransform', {
              attrs: {
                'attributeName': 'transform',
                'type': 'scale',
                'from': '1.5',
                'to': '1',
                'repeatCount': 'indefinite',
                'begin': '0.2s',
                'dur': '1s',
                'calcMode': 'spline',
                'keySplines': '0.2 0.8 0.2 0.8',
                'keyTimes': '0;1'
              }
            })
          ])
        ])
      ])
    }
  });

  var QSpinnerDots = Vue.extend({
    name: 'QSpinnerDots',

    mixins: [mixin],

    render: function render (h) {
      return h('svg', {
        staticClass: 'q-spinner',
        class: this.classes,
        on: this.$listeners,
        attrs: {
          'fill': 'currentColor',
          'width': this.size,
          'height': this.size,
          'viewBox': '0 0 120 30',
          'xmlns': 'http://www.w3.org/2000/svg'
        }
      }, [
        h('circle', {
          attrs: {
            'cx': '15',
            'cy': '15',
            'r': '15'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'r',
              'from': '15',
              'to': '15',
              'begin': '0s',
              'dur': '0.8s',
              'values': '15;9;15',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          }),
          h('animate', {
            attrs: {
              'attributeName': 'fill-opacity',
              'from': '1',
              'to': '1',
              'begin': '0s',
              'dur': '0.8s',
              'values': '1;.5;1',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('circle', {
          attrs: {
            'cx': '60',
            'cy': '15',
            'r': '9',
            'fill-opacity': '.3'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'r',
              'from': '9',
              'to': '9',
              'begin': '0s',
              'dur': '0.8s',
              'values': '9;15;9',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          }),
          h('animate', {
            attrs: {
              'attributeName': 'fill-opacity',
              'from': '.5',
              'to': '.5',
              'begin': '0s',
              'dur': '0.8s',
              'values': '.5;1;.5',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('circle', {
          attrs: {
            'cx': '105',
            'cy': '15',
            'r': '15'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'r',
              'from': '15',
              'to': '15',
              'begin': '0s',
              'dur': '0.8s',
              'values': '15;9;15',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          }),
          h('animate', {
            attrs: {
              'attributeName': 'fill-opacity',
              'from': '1',
              'to': '1',
              'begin': '0s',
              'dur': '0.8s',
              'values': '1;.5;1',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          })
        ])
      ])
    }
  });

  var QSpinnerFacebook = Vue.extend({
    name: 'QSpinnerFacebook',

    mixins: [mixin],

    render: function render (h) {
      return h('svg', {
        staticClass: 'q-spinner',
        class: this.classes,
        on: this.$listeners,
        attrs: {
          'width': this.size,
          'height': this.size,
          'viewBox': '0 0 100 100',
          'xmlns': 'http://www.w3.org/2000/svg',
          'preserveAspectRatio': 'xMidYMid'
        }
      }, [
        h('g', {
          attrs: {
            'transform': 'translate(20 50)'
          }
        }, [
          h('rect', {
            attrs: {
              'x': '-10',
              'y': '-30',
              'width': '20',
              'height': '60',
              'fill': 'currentColor',
              'opacity': '0.6'
            }
          }, [
            h('animateTransform', {
              attrs: {
                'attributeName': 'transform',
                'type': 'scale',
                'from': '2',
                'to': '1',
                'begin': '0s',
                'repeatCount': 'indefinite',
                'dur': '1s',
                'calcMode': 'spline',
                'keySplines': '0.1 0.9 0.4 1',
                'keyTimes': '0;1',
                'values': '2;1'
              }
            })
          ])
        ]),
        h('g', {
          attrs: {
            'transform': 'translate(50 50)'
          }
        }, [
          h('rect', {
            attrs: {
              'x': '-10',
              'y': '-30',
              'width': '20',
              'height': '60',
              'fill': 'currentColor',
              'opacity': '0.8'
            }
          }, [
            h('animateTransform', {
              attrs: {
                'attributeName': 'transform',
                'type': 'scale',
                'from': '2',
                'to': '1',
                'begin': '0.1s',
                'repeatCount': 'indefinite',
                'dur': '1s',
                'calcMode': 'spline',
                'keySplines': '0.1 0.9 0.4 1',
                'keyTimes': '0;1',
                'values': '2;1'
              }
            })
          ])
        ]),
        h('g', {
          attrs: {
            'transform': 'translate(80 50)'
          }
        }, [
          h('rect', {
            attrs: {
              'x': '-10',
              'y': '-30',
              'width': '20',
              'height': '60',
              'fill': 'currentColor',
              'opacity': '0.9'
            }
          }, [
            h('animateTransform', {
              attrs: {
                'attributeName': 'transform',
                'type': 'scale',
                'from': '2',
                'to': '1',
                'begin': '0.2s',
                'repeatCount': 'indefinite',
                'dur': '1s',
                'calcMode': 'spline',
                'keySplines': '0.1 0.9 0.4 1',
                'keyTimes': '0;1',
                'values': '2;1'
              }
            })
          ])
        ])
      ])
    }
  });

  var QSpinnerGears = Vue.extend({
    name: 'QSpinnerGears',

    mixins: [mixin],

    render: function render (h) {
      return h('svg', {
        staticClass: 'q-spinner',
        class: this.classes,
        on: this.$listeners,
        attrs: {
          'width': this.size,
          'height': this.size,
          'viewBox': '0 0 100 100',
          'preserveAspectRatio': 'xMidYMid',
          'xmlns': 'http://www.w3.org/2000/svg'
        }
      }, [
        h('g', {
          attrs: {
            'transform': 'translate(-20,-20)'
          }
        }, [
          h('path', {
            attrs: {
              'd': 'M79.9,52.6C80,51.8,80,50.9,80,50s0-1.8-0.1-2.6l-5.1-0.4c-0.3-2.4-0.9-4.6-1.8-6.7l4.2-2.9c-0.7-1.6-1.6-3.1-2.6-4.5 L70,35c-1.4-1.9-3.1-3.5-4.9-4.9l2.2-4.6c-1.4-1-2.9-1.9-4.5-2.6L59.8,27c-2.1-0.9-4.4-1.5-6.7-1.8l-0.4-5.1C51.8,20,50.9,20,50,20 s-1.8,0-2.6,0.1l-0.4,5.1c-2.4,0.3-4.6,0.9-6.7,1.8l-2.9-4.1c-1.6,0.7-3.1,1.6-4.5,2.6l2.1,4.6c-1.9,1.4-3.5,3.1-5,4.9l-4.5-2.1 c-1,1.4-1.9,2.9-2.6,4.5l4.1,2.9c-0.9,2.1-1.5,4.4-1.8,6.8l-5,0.4C20,48.2,20,49.1,20,50s0,1.8,0.1,2.6l5,0.4 c0.3,2.4,0.9,4.7,1.8,6.8l-4.1,2.9c0.7,1.6,1.6,3.1,2.6,4.5l4.5-2.1c1.4,1.9,3.1,3.5,5,4.9l-2.1,4.6c1.4,1,2.9,1.9,4.5,2.6l2.9-4.1 c2.1,0.9,4.4,1.5,6.7,1.8l0.4,5.1C48.2,80,49.1,80,50,80s1.8,0,2.6-0.1l0.4-5.1c2.3-0.3,4.6-0.9,6.7-1.8l2.9,4.2 c1.6-0.7,3.1-1.6,4.5-2.6L65,69.9c1.9-1.4,3.5-3,4.9-4.9l4.6,2.2c1-1.4,1.9-2.9,2.6-4.5L73,59.8c0.9-2.1,1.5-4.4,1.8-6.7L79.9,52.6 z M50,65c-8.3,0-15-6.7-15-15c0-8.3,6.7-15,15-15s15,6.7,15,15C65,58.3,58.3,65,50,65z',
              'fill': 'currentColor'
            }
          }, [
            h('animateTransform', {
              attrs: {
                'attributeName': 'transform',
                'type': 'rotate',
                'from': '90 50 50',
                'to': '0 50 50',
                'dur': '1s',
                'repeatCount': 'indefinite'
              }
            })
          ])
        ]),
        h('g', {
          attrs: {
            'transform': 'translate(20,20) rotate(15 50 50)'
          }
        }, [
          h('path', {
            attrs: {
              'd': 'M79.9,52.6C80,51.8,80,50.9,80,50s0-1.8-0.1-2.6l-5.1-0.4c-0.3-2.4-0.9-4.6-1.8-6.7l4.2-2.9c-0.7-1.6-1.6-3.1-2.6-4.5 L70,35c-1.4-1.9-3.1-3.5-4.9-4.9l2.2-4.6c-1.4-1-2.9-1.9-4.5-2.6L59.8,27c-2.1-0.9-4.4-1.5-6.7-1.8l-0.4-5.1C51.8,20,50.9,20,50,20 s-1.8,0-2.6,0.1l-0.4,5.1c-2.4,0.3-4.6,0.9-6.7,1.8l-2.9-4.1c-1.6,0.7-3.1,1.6-4.5,2.6l2.1,4.6c-1.9,1.4-3.5,3.1-5,4.9l-4.5-2.1 c-1,1.4-1.9,2.9-2.6,4.5l4.1,2.9c-0.9,2.1-1.5,4.4-1.8,6.8l-5,0.4C20,48.2,20,49.1,20,50s0,1.8,0.1,2.6l5,0.4 c0.3,2.4,0.9,4.7,1.8,6.8l-4.1,2.9c0.7,1.6,1.6,3.1,2.6,4.5l4.5-2.1c1.4,1.9,3.1,3.5,5,4.9l-2.1,4.6c1.4,1,2.9,1.9,4.5,2.6l2.9-4.1 c2.1,0.9,4.4,1.5,6.7,1.8l0.4,5.1C48.2,80,49.1,80,50,80s1.8,0,2.6-0.1l0.4-5.1c2.3-0.3,4.6-0.9,6.7-1.8l2.9,4.2 c1.6-0.7,3.1-1.6,4.5-2.6L65,69.9c1.9-1.4,3.5-3,4.9-4.9l4.6,2.2c1-1.4,1.9-2.9,2.6-4.5L73,59.8c0.9-2.1,1.5-4.4,1.8-6.7L79.9,52.6 z M50,65c-8.3,0-15-6.7-15-15c0-8.3,6.7-15,15-15s15,6.7,15,15C65,58.3,58.3,65,50,65z',
              'fill': 'currentColor'
            }
          }, [
            h('animateTransform', {
              attrs: {
                'attributeName': 'transform',
                'type': 'rotate',
                'from': '0 50 50',
                'to': '90 50 50',
                'dur': '1s',
                'repeatCount': 'indefinite'
              }
            })
          ])
        ])
      ])
    }
  });

  var QSpinnerGrid = Vue.extend({
    name: 'QSpinnerGrid',

    mixins: [mixin],

    render: function render (h) {
      return h('svg', {
        staticClass: 'q-spinner',
        class: this.classes,
        on: this.$listeners,
        attrs: {
          'fill': 'currentColor',
          'width': this.size,
          'height': this.size,
          'viewBox': '0 0 105 105',
          'xmlns': 'http://www.w3.org/2000/svg'
        }
      }, [
        h('circle', {
          attrs: {
            'cx': '12.5',
            'cy': '12.5',
            'r': '12.5'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'fill-opacity',
              'begin': '0s',
              'dur': '1s',
              'values': '1;.2;1',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('circle', {
          attrs: {
            'cx': '12.5',
            'cy': '52.5',
            'r': '12.5',
            'fill-opacity': '.5'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'fill-opacity',
              'begin': '100ms',
              'dur': '1s',
              'values': '1;.2;1',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('circle', {
          attrs: {
            'cx': '52.5',
            'cy': '12.5',
            'r': '12.5'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'fill-opacity',
              'begin': '300ms',
              'dur': '1s',
              'values': '1;.2;1',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('circle', {
          attrs: {
            'cx': '52.5',
            'cy': '52.5',
            'r': '12.5'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'fill-opacity',
              'begin': '600ms',
              'dur': '1s',
              'values': '1;.2;1',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('circle', {
          attrs: {
            'cx': '92.5',
            'cy': '12.5',
            'r': '12.5'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'fill-opacity',
              'begin': '800ms',
              'dur': '1s',
              'values': '1;.2;1',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('circle', {
          attrs: {
            'cx': '92.5',
            'cy': '52.5',
            'r': '12.5'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'fill-opacity',
              'begin': '400ms',
              'dur': '1s',
              'values': '1;.2;1',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('circle', {
          attrs: {
            'cx': '12.5',
            'cy': '92.5',
            'r': '12.5'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'fill-opacity',
              'begin': '700ms',
              'dur': '1s',
              'values': '1;.2;1',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('circle', {
          attrs: {
            'cx': '52.5',
            'cy': '92.5',
            'r': '12.5'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'fill-opacity',
              'begin': '500ms',
              'dur': '1s',
              'values': '1;.2;1',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('circle', {
          attrs: {
            'cx': '92.5',
            'cy': '92.5',
            'r': '12.5'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'fill-opacity',
              'begin': '200ms',
              'dur': '1s',
              'values': '1;.2;1',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          })
        ])
      ])
    }
  });

  var QSpinnerHearts = Vue.extend({
    name: 'QSpinnerHearts',

    mixins: [mixin],

    render: function render (h) {
      return h('svg', {
        staticClass: 'q-spinner',
        class: this.classes,
        on: this.$listeners,
        attrs: {
          'fill': 'currentColor',
          'width': this.size,
          'height': this.size,
          'viewBox': '0 0 140 64',
          'xmlns': 'http://www.w3.org/2000/svg'
        }
      }, [
        h('path', {
          attrs: {
            'd': 'M30.262 57.02L7.195 40.723c-5.84-3.976-7.56-12.06-3.842-18.063 3.715-6 11.467-7.65 17.306-3.68l4.52 3.76 2.6-5.274c3.716-6.002 11.47-7.65 17.304-3.68 5.84 3.97 7.56 12.054 3.842 18.062L34.49 56.118c-.897 1.512-2.793 1.915-4.228.9z',
            'fill-opacity': '.5'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'fill-opacity',
              'begin': '0s',
              'dur': '1.4s',
              'values': '0.5;1;0.5',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('path', {
          attrs: {
            'd': 'M105.512 56.12l-14.44-24.272c-3.716-6.008-1.996-14.093 3.843-18.062 5.835-3.97 13.588-2.322 17.306 3.68l2.6 5.274 4.52-3.76c5.84-3.97 13.593-2.32 17.308 3.68 3.718 6.003 1.998 14.088-3.842 18.064L109.74 57.02c-1.434 1.014-3.33.61-4.228-.9z',
            'fill-opacity': '.5'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'fill-opacity',
              'begin': '0.7s',
              'dur': '1.4s',
              'values': '0.5;1;0.5',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('path', {
          attrs: {
            'd': 'M67.408 57.834l-23.01-24.98c-5.864-6.15-5.864-16.108 0-22.248 5.86-6.14 15.37-6.14 21.234 0L70 16.168l4.368-5.562c5.863-6.14 15.375-6.14 21.235 0 5.863 6.14 5.863 16.098 0 22.247l-23.007 24.98c-1.43 1.556-3.757 1.556-5.188 0z'
          }
        })
      ])
    }
  });

  var QSpinnerHourglass = Vue.extend({
    name: 'QSpinnerHourglass',

    mixins: [mixin],

    render: function render (h) {
      return h('svg', {
        staticClass: 'q-spinner',
        class: this.classes,
        on: this.$listeners,
        attrs: {
          'width': this.size,
          'height': this.size,
          'viewBox': '0 0 100 100',
          'preserveAspectRatio': 'xMidYMid',
          'xmlns': 'http://www.w3.org/2000/svg'
        }
      }, [
        h('g', [
          h('path', {
            staticClass: 'glass',
            attrs: {
              'fill': 'none',
              'stroke': 'currentColor',
              'stroke-width': '5',
              'stroke-miterlimit': '10',
              'd': 'M58.4,51.7c-0.9-0.9-1.4-2-1.4-2.3s0.5-0.4,1.4-1.4 C70.8,43.8,79.8,30.5,80,15.5H70H30H20c0.2,15,9.2,28.1,21.6,32.3c0.9,0.9,1.4,1.2,1.4,1.5s-0.5,1.6-1.4,2.5 C29.2,56.1,20.2,69.5,20,85.5h10h40h10C79.8,69.5,70.8,55.9,58.4,51.7z'
            }
          }),
          h('clipPath', {
            attrs: {
              'id': 'uil-hourglass-clip1'
            }
          }, [
            h('rect', {
              staticClass: 'clip',
              attrs: {
                'x': '15',
                'y': '20',
                'width': '70',
                'height': '25'
              }
            }, [
              h('animate', {
                attrs: {
                  'attributeName': 'height',
                  'from': '25',
                  'to': '0',
                  'dur': '1s',
                  'repeatCount': 'indefinite',
                  'vlaues': '25;0;0',
                  'keyTimes': '0;0.5;1'
                }
              }),
              h('animate', {
                attrs: {
                  'attributeName': 'y',
                  'from': '20',
                  'to': '45',
                  'dur': '1s',
                  'repeatCount': 'indefinite',
                  'vlaues': '20;45;45',
                  'keyTimes': '0;0.5;1'
                }
              })
            ])
          ]),
          h('clipPath', {
            attrs: {
              'id': 'uil-hourglass-clip2'
            }
          }, [
            h('rect', {
              staticClass: 'clip',
              attrs: {
                'x': '15',
                'y': '55',
                'width': '70',
                'height': '25'
              }
            }, [
              h('animate', {
                attrs: {
                  'attributeName': 'height',
                  'from': '0',
                  'to': '25',
                  'dur': '1s',
                  'repeatCount': 'indefinite',
                  'vlaues': '0;25;25',
                  'keyTimes': '0;0.5;1'
                }
              }),
              h('animate', {
                attrs: {
                  'attributeName': 'y',
                  'from': '80',
                  'to': '55',
                  'dur': '1s',
                  'repeatCount': 'indefinite',
                  'vlaues': '80;55;55',
                  'keyTimes': '0;0.5;1'
                }
              })
            ])
          ]),
          h('path', {
            staticClass: 'sand',
            attrs: {
              'd': 'M29,23c3.1,11.4,11.3,19.5,21,19.5S67.9,34.4,71,23H29z',
              'clip-path': 'url(#uil-hourglass-clip1)',
              'fill': 'currentColor'
            }
          }),
          h('path', {
            staticClass: 'sand',
            attrs: {
              'd': 'M71.6,78c-3-11.6-11.5-20-21.5-20s-18.5,8.4-21.5,20H71.6z',
              'clip-path': 'url(#uil-hourglass-clip2)',
              'fill': 'currentColor'
            }
          }),
          h('animateTransform', {
            attrs: {
              'attributeName': 'transform',
              'type': 'rotate',
              'from': '0 50 50',
              'to': '180 50 50',
              'repeatCount': 'indefinite',
              'dur': '1s',
              'values': '0 50 50;0 50 50;180 50 50',
              'keyTimes': '0;0.7;1'
            }
          })
        ])
      ])
    }
  });

  var QSpinnerInfinity = Vue.extend({
    name: 'QSpinnerInfinity',

    mixins: [mixin],

    render: function render (h) {
      return h('svg', {
        staticClass: 'q-spinner',
        class: this.classes,
        on: this.$listeners,
        attrs: {
          'width': this.size,
          'height': this.size,
          'viewBox': '0 0 100 100',
          'preserveAspectRatio': 'xMidYMid'
        }
      }, [
        h('path', {
          attrs: {
            'd': 'M24.3,30C11.4,30,5,43.3,5,50s6.4,20,19.3,20c19.3,0,32.1-40,51.4-40C88.6,30,95,43.3,95,50s-6.4,20-19.3,20C56.4,70,43.6,30,24.3,30z',
            'fill': 'none',
            'stroke': 'currentColor',
            'stroke-width': '8',
            'stroke-dasharray': '10.691205342610678 10.691205342610678',
            'stroke-dashoffset': '0'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'stroke-dashoffset',
              'from': '0',
              'to': '21.382410685221355',
              'begin': '0',
              'dur': '2s',
              'repeatCount': 'indefinite',
              'fill': 'freeze'
            }
          })
        ])
      ])
    }
  });

  var QSpinnerIos = Vue.extend({
    name: 'QSpinnerIos',

    mixins: [mixin],

    render: function render (h) {
      return h('svg', {
        staticClass: 'q-spinner',
        class: this.classes,
        on: this.$listeners,
        attrs: {
          'width': this.size,
          'height': this.size,
          'stroke': 'currentColor',
          'fill': 'currentColor',
          'viewBox': '0 0 64 64'
        }
      }, [
        h('g', {
          attrs: {
            'stroke-width': '4',
            'stroke-linecap': 'round'
          }
        }, [
          h('line', {
            attrs: {
              'y1': '17',
              'y2': '29',
              'transform': 'translate(32,32) rotate(180)'
            }
          }, [
            h('animate', {
              attrs: {
                'attributeName': 'stroke-opacity',
                'dur': '750ms',
                'values': '1;.85;.7;.65;.55;.45;.35;.25;.15;.1;0;1',
                'repeatCount': 'indefinite'
              }
            })
          ]),
          h('line', {
            attrs: {
              'y1': '17',
              'y2': '29',
              'transform': 'translate(32,32) rotate(210)'
            }
          }, [
            h('animate', {
              attrs: {
                'attributeName': 'stroke-opacity',
                'dur': '750ms',
                'values': '0;1;.85;.7;.65;.55;.45;.35;.25;.15;.1;0',
                'repeatCount': 'indefinite'
              }
            })
          ]),
          h('line', {
            attrs: {
              'y1': '17',
              'y2': '29',
              'transform': 'translate(32,32) rotate(240)'
            }
          }, [
            h('animate', {
              attrs: {
                'attributeName': 'stroke-opacity',
                'dur': '750ms',
                'values': '.1;0;1;.85;.7;.65;.55;.45;.35;.25;.15;.1',
                'repeatCount': 'indefinite'
              }
            })
          ]),
          h('line', {
            attrs: {
              'y1': '17',
              'y2': '29',
              'transform': 'translate(32,32) rotate(270)'
            }
          }, [
            h('animate', {
              attrs: {
                'attributeName': 'stroke-opacity',
                'dur': '750ms',
                'values': '.15;.1;0;1;.85;.7;.65;.55;.45;.35;.25;.15',
                'repeatCount': 'indefinite'
              }
            })
          ]),
          h('line', {
            attrs: {
              'y1': '17',
              'y2': '29',
              'transform': 'translate(32,32) rotate(300)'
            }
          }, [
            h('animate', {
              attrs: {
                'attributeName': 'stroke-opacity',
                'dur': '750ms',
                'values': '.25;.15;.1;0;1;.85;.7;.65;.55;.45;.35;.25',
                'repeatCount': 'indefinite'
              }
            })
          ]),
          h('line', {
            attrs: {
              'y1': '17',
              'y2': '29',
              'transform': 'translate(32,32) rotate(330)'
            }
          }, [
            h('animate', {
              attrs: {
                'attributeName': 'stroke-opacity',
                'dur': '750ms',
                'values': '.35;.25;.15;.1;0;1;.85;.7;.65;.55;.45;.35',
                'repeatCount': 'indefinite'
              }
            })
          ]),
          h('line', {
            attrs: {
              'y1': '17',
              'y2': '29',
              'transform': 'translate(32,32) rotate(0)'
            }
          }, [
            h('animate', {
              attrs: {
                'attributeName': 'stroke-opacity',
                'dur': '750ms',
                'values': '.45;.35;.25;.15;.1;0;1;.85;.7;.65;.55;.45',
                'repeatCount': 'indefinite'
              }
            })
          ]),
          h('line', {
            attrs: {
              'y1': '17',
              'y2': '29',
              'transform': 'translate(32,32) rotate(30)'
            }
          }, [
            h('animate', {
              attrs: {
                'attributeName': 'stroke-opacity',
                'dur': '750ms',
                'values': '.55;.45;.35;.25;.15;.1;0;1;.85;.7;.65;.55',
                'repeatCount': 'indefinite'
              }
            })
          ]),
          h('line', {
            attrs: {
              'y1': '17',
              'y2': '29',
              'transform': 'translate(32,32) rotate(60)'
            }
          }, [
            h('animate', {
              attrs: {
                'attributeName': 'stroke-opacity',
                'dur': '750ms',
                'values': '.65;.55;.45;.35;.25;.15;.1;0;1;.85;.7;.65',
                'repeatCount': 'indefinite'
              }
            })
          ]),
          h('line', {
            attrs: {
              'y1': '17',
              'y2': '29',
              'transform': 'translate(32,32) rotate(90)'
            }
          }, [
            h('animate', {
              attrs: {
                'attributeName': 'stroke-opacity',
                'dur': '750ms',
                'values': '.7;.65;.55;.45;.35;.25;.15;.1;0;1;.85;.7',
                'repeatCount': 'indefinite'
              }
            })
          ]),
          h('line', {
            attrs: {
              'y1': '17',
              'y2': '29',
              'transform': 'translate(32,32) rotate(120)'
            }
          }, [
            h('animate', {
              attrs: {
                'attributeName': 'stroke-opacity',
                'dur': '750ms',
                'values': '.85;.7;.65;.55;.45;.35;.25;.15;.1;0;1;.85',
                'repeatCount': 'indefinite'
              }
            })
          ]),
          h('line', {
            attrs: {
              'y1': '17',
              'y2': '29',
              'transform': 'translate(32,32) rotate(150)'
            }
          }, [
            h('animate', {
              attrs: {
                'attributeName': 'stroke-opacity',
                'dur': '750ms',
                'values': '1;.85;.7;.65;.55;.45;.35;.25;.15;.1;0;1',
                'repeatCount': 'indefinite'
              }
            })
          ])
        ])
      ])
    }
  });

  var QSpinnerOval = Vue.extend({
    name: 'QSpinnerOval',

    mixins: [mixin],

    render: function render (h) {
      return h('svg', {
        staticClass: 'q-spinner',
        class: this.classes,
        on: this.$listeners,
        attrs: {
          'stroke': 'currentColor',
          'width': this.size,
          'height': this.size,
          'viewBox': '0 0 38 38',
          'xmlns': 'http://www.w3.org/2000/svg'
        }
      }, [
        h('g', {
          attrs: {
            'transform': 'translate(1 1)',
            'stroke-width': '2',
            'fill': 'none',
            'fill-rule': 'evenodd'
          }
        }, [
          h('circle', {
            attrs: {
              'stroke-opacity': '.5',
              'cx': '18',
              'cy': '18',
              'r': '18'
            }
          }),
          h('path', {
            attrs: {
              'd': 'M36 18c0-9.94-8.06-18-18-18'
            }
          }, [
            h('animateTransform', {
              attrs: {
                'attributeName': 'transform',
                'type': 'rotate',
                'from': '0 18 18',
                'to': '360 18 18',
                'dur': '1s',
                'repeatCount': 'indefinite'
              }
            })
          ])
        ])
      ])
    }
  });

  var QSpinnerPie = Vue.extend({
    name: 'QSpinnerPie',

    mixins: [mixin],

    render: function render (h) {
      return h('svg', {
        staticClass: 'q-spinner',
        class: this.classes,
        on: this.$listeners,
        attrs: {
          'width': this.size,
          'height': this.size,
          'viewBox': '0 0 100 100',
          'preserveAspectRatio': 'xMidYMid',
          'xmlns': 'http://www.w3.org/2000/svg'
        }
      }, [
        h('path', {
          attrs: {
            'd': 'M0 50A50 50 0 0 1 50 0L50 50L0 50',
            'fill': 'currentColor',
            'opacity': '0.5'
          }
        }, [
          h('animateTransform', {
            attrs: {
              'attributeName': 'transform',
              'type': 'rotate',
              'from': '0 50 50',
              'to': '360 50 50',
              'dur': '0.8s',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('path', {
          attrs: {
            'd': 'M50 0A50 50 0 0 1 100 50L50 50L50 0',
            'fill': 'currentColor',
            'opacity': '0.5'
          }
        }, [
          h('animateTransform', {
            attrs: {
              'attributeName': 'transform',
              'type': 'rotate',
              'from': '0 50 50',
              'to': '360 50 50',
              'dur': '1.6s',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('path', {
          attrs: {
            'd': 'M100 50A50 50 0 0 1 50 100L50 50L100 50',
            'fill': 'currentColor',
            'opacity': '0.5'
          }
        }, [
          h('animateTransform', {
            attrs: {
              'attributeName': 'transform',
              'type': 'rotate',
              'from': '0 50 50',
              'to': '360 50 50',
              'dur': '2.4s',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('path', {
          attrs: {
            'd': 'M50 100A50 50 0 0 1 0 50L50 50L50 100',
            'fill': 'currentColor',
            'opacity': '0.5'
          }
        }, [
          h('animateTransform', {
            attrs: {
              'attributeName': 'transform',
              'type': 'rotate',
              'from': '0 50 50',
              'to': '360 50 50',
              'dur': '3.2s',
              'repeatCount': 'indefinite'
            }
          })
        ])
      ])
    }
  });

  var QSpinnerPuff = Vue.extend({
    name: 'QSpinnerPuff',

    mixins: [mixin],

    render: function render (h) {
      return h('svg', {
        staticClass: 'q-spinner',
        class: this.classes,
        on: this.$listeners,
        attrs: {
          'stroke': 'currentColor',
          'width': this.size,
          'height': this.size,
          'viewBox': '0 0 44 44',
          'xmlns': 'http://www.w3.org/2000/svg'
        }
      }, [
        h('g', {
          attrs: {
            'fill': 'none',
            'fill-rule': 'evenodd',
            'stroke-width': '2'
          }
        }, [
          h('circle', {
            attrs: {
              'cx': '22',
              'cy': '22',
              'r': '1'
            }
          }, [
            h('animate', {
              attrs: {
                'attributeName': 'r',
                'begin': '0s',
                'dur': '1.8s',
                'values': '1; 20',
                'calcMode': 'spline',
                'keyTimes': '0; 1',
                'keySplines': '0.165, 0.84, 0.44, 1',
                'repeatCount': 'indefinite'
              }
            }),
            h('animate', {
              attrs: {
                'attributeName': 'stroke-opacity',
                'begin': '0s',
                'dur': '1.8s',
                'values': '1; 0',
                'calcMode': 'spline',
                'keyTimes': '0; 1',
                'keySplines': '0.3, 0.61, 0.355, 1',
                'repeatCount': 'indefinite'
              }
            })
          ]),
          h('circle', {
            attrs: {
              'cx': '22',
              'cy': '22',
              'r': '1'
            }
          }, [
            h('animate', {
              attrs: {
                'attributeName': 'r',
                'begin': '-0.9s',
                'dur': '1.8s',
                'values': '1; 20',
                'calcMode': 'spline',
                'keyTimes': '0; 1',
                'keySplines': '0.165, 0.84, 0.44, 1',
                'repeatCount': 'indefinite'
              }
            }),
            h('animate', {
              attrs: {
                'attributeName': 'stroke-opacity',
                'begin': '-0.9s',
                'dur': '1.8s',
                'values': '1; 0',
                'calcMode': 'spline',
                'keyTimes': '0; 1',
                'keySplines': '0.3, 0.61, 0.355, 1',
                'repeatCount': 'indefinite'
              }
            })
          ])
        ])
      ])
    }
  });

  var QSpinnerRadio = Vue.extend({
    name: 'QSpinnerRadio',

    mixins: [mixin],

    render: function render (h) {
      return h('svg', {
        staticClass: 'q-spinner',
        class: this.classes,
        on: this.$listeners,
        attrs: {
          'width': this.size,
          'height': this.size,
          'viewBox': '0 0 100 100',
          'preserveAspectRatio': 'xMidYMid',
          'xmlns': 'http://www.w3.org/2000/svg'
        }
      }, [
        h('g', {
          attrs: {
            'transform': 'scale(0.55)'
          }
        }, [
          h('circle', {
            attrs: {
              'cx': '30',
              'cy': '150',
              'r': '30',
              'fill': 'currentColor'
            }
          }, [
            h('animate', {
              attrs: {
                'attributeName': 'opacity',
                'from': '0',
                'to': '1',
                'dur': '1s',
                'begin': '0',
                'repeatCount': 'indefinite',
                'keyTimes': '0;0.5;1',
                'values': '0;1;1'
              }
            })
          ]),
          h('path', {
            attrs: {
              'd': 'M90,150h30c0-49.7-40.3-90-90-90v30C63.1,90,90,116.9,90,150z',
              'fill': 'currentColor'
            }
          }, [
            h('animate', {
              attrs: {
                'attributeName': 'opacity',
                'from': '0',
                'to': '1',
                'dur': '1s',
                'begin': '0.1',
                'repeatCount': 'indefinite',
                'keyTimes': '0;0.5;1',
                'values': '0;1;1'
              }
            })
          ]),
          h('path', {
            attrs: {
              'd': 'M150,150h30C180,67.2,112.8,0,30,0v30C96.3,30,150,83.7,150,150z',
              'fill': 'currentColor'
            }
          }, [
            h('animate', {
              attrs: {
                'attributeName': 'opacity',
                'from': '0',
                'to': '1',
                'dur': '1s',
                'begin': '0.2',
                'repeatCount': 'indefinite',
                'keyTimes': '0;0.5;1',
                'values': '0;1;1'
              }
            })
          ])
        ])
      ])
    }
  });

  var QSpinnerRings = Vue.extend({
    name: 'QSpinnerRings',

    mixins: [mixin],

    render: function render (h) {
      return h('svg', {
        staticClass: 'q-spinner',
        class: this.classes,
        on: this.$listeners,
        attrs: {
          'stroke': 'currentColor',
          'width': this.size,
          'height': this.size,
          'viewBox': '0 0 45 45',
          'xmlns': 'http://www.w3.org/2000/svg'
        }
      }, [
        h('g', {
          attrs: {
            'fill': 'none',
            'fill-rule': 'evenodd',
            'transform': 'translate(1 1)',
            'stroke-width': '2'
          }
        }, [
          h('circle', {
            attrs: {
              'cx': '22',
              'cy': '22',
              'r': '6'
            }
          }, [
            h('animate', {
              attrs: {
                'attributeName': 'r',
                'begin': '1.5s',
                'dur': '3s',
                'values': '6;22',
                'calcMode': 'linear',
                'repeatCount': 'indefinite'
              }
            }),
            h('animate', {
              attrs: {
                'attributeName': 'stroke-opacity',
                'begin': '1.5s',
                'dur': '3s',
                'values': '1;0',
                'calcMode': 'linear',
                'repeatCount': 'indefinite'
              }
            }),
            h('animate', {
              attrs: {
                'attributeName': 'stroke-width',
                'begin': '1.5s',
                'dur': '3s',
                'values': '2;0',
                'calcMode': 'linear',
                'repeatCount': 'indefinite'
              }
            })
          ]),
          h('circle', {
            attrs: {
              'cx': '22',
              'cy': '22',
              'r': '6'
            }
          }, [
            h('animate', {
              attrs: {
                'attributeName': 'r',
                'begin': '3s',
                'dur': '3s',
                'values': '6;22',
                'calcMode': 'linear',
                'repeatCount': 'indefinite'
              }
            }),
            h('animate', {
              attrs: {
                'attributeName': 'stroke-opacity',
                'begin': '3s',
                'dur': '3s',
                'values': '1;0',
                'calcMode': 'linear',
                'repeatCount': 'indefinite'
              }
            }),
            h('animate', {
              attrs: {
                'attributeName': 'stroke-width',
                'begin': '3s',
                'dur': '3s',
                'values': '2;0',
                'calcMode': 'linear',
                'repeatCount': 'indefinite'
              }
            })
          ]),
          h('circle', {
            attrs: {
              'cx': '22',
              'cy': '22',
              'r': '8'
            }
          }, [
            h('animate', {
              attrs: {
                'attributeName': 'r',
                'begin': '0s',
                'dur': '1.5s',
                'values': '6;1;2;3;4;5;6',
                'calcMode': 'linear',
                'repeatCount': 'indefinite'
              }
            })
          ])
        ])
      ])
    }
  });

  var QSpinnerTail = Vue.extend({
    name: 'QSpinnerTail',

    mixins: [mixin],

    render: function render (h) {
      return h('svg', {
        staticClass: 'q-spinner',
        class: this.classes,
        on: this.$listeners,
        attrs: {
          'width': this.size,
          'height': this.size,
          'viewBox': '0 0 38 38',
          'xmlns': 'http://www.w3.org/2000/svg'
        }
      }, [
        h('defs', [
          h('linearGradient', {
            attrs: {
              'x1': '8.042%',
              'y1': '0%',
              'x2': '65.682%',
              'y2': '23.865%',
              'id': 'a'
            }
          }, [
            h('stop', {
              attrs: {
                'stop-color': 'currentColor',
                'stop-opacity': '0',
                'offset': '0%'
              }
            }),
            h('stop', {
              attrs: {
                'stop-color': 'currentColor',
                'stop-opacity': '.631',
                'offset': '63.146%'
              }
            }),
            h('stop', {
              attrs: {
                'stop-color': 'currentColor',
                'offset': '100%'
              }
            })
          ])
        ]),
        h('g', {
          attrs: {
            'transform': 'translate(1 1)',
            'fill': 'none',
            'fill-rule': 'evenodd'
          }
        }, [
          h('path', {
            attrs: {
              'd': 'M36 18c0-9.94-8.06-18-18-18',
              'stroke': 'url(#a)',
              'stroke-width': '2'
            }
          }, [
            h('animateTransform', {
              attrs: {
                'attributeName': 'transform',
                'type': 'rotate',
                'from': '0 18 18',
                'to': '360 18 18',
                'dur': '0.9s',
                'repeatCount': 'indefinite'
              }
            })
          ]),
          h('circle', {
            attrs: {
              'fill': 'currentColor',
              'cx': '36',
              'cy': '18',
              'r': '1'
            }
          }, [
            h('animateTransform', {
              attrs: {
                'attributeName': 'transform',
                'type': 'rotate',
                'from': '0 18 18',
                'to': '360 18 18',
                'dur': '0.9s',
                'repeatCount': 'indefinite'
              }
            })
          ])
        ])
      ])
    }
  });

  var QSplitter = Vue.extend({
    name: 'QSplitter',

    directives: {
      TouchPan: TouchPan
    },

    props: {
      value: {
        type: Number,
        required: true
      },
      horizontal: Boolean,

      limits: {
        type: Array,
        default: function () { return [10, 90]; },
        validator: function (v) {
          if (v.length !== 2) { return false }
          if (typeof v[0] !== 'number' || typeof v[1] !== 'number') { return false }
          return v[0] >= 0 && v[0] <= v[1] && v[1] <= 100
        }
      },

      disable: Boolean,

      dark: Boolean,

      beforeClass: [Array, String, Object],
      afterClass: [Array, String, Object],

      separatorClass: [Array, String, Object],
      separatorStyle: [Array, String, Object]
    },

    watch: {
      value: {
        immediate: true,
        handler: function handler (v) {
          this.__normalize(v, this.limits);
        }
      },

      limits: {
        deep: true,
        handler: function handler (v) {
          this.__normalize(this.value, v);
        }
      }
    },

    computed: {
      classes: function classes () {
        return (this.horizontal ? 'column' : 'row') +
          " q-splitter--" + (this.horizontal ? 'horizontal' : 'vertical') +
          " q-splitter--" + (this.disable === true ? 'disabled' : 'workable') +
          (this.dark === true ? ' q-splitter--dark' : '')
      },

      prop: function prop () {
        return this.horizontal ? 'height' : 'width'
      },

      beforeStyle: function beforeStyle () {
        var obj;

        return ( obj = {}, obj[this.prop] = this.value + '%', obj )
      },

      afterStyle: function afterStyle () {
        var obj;

        return ( obj = {}, obj[this.prop] = (100 - this.value) + '%', obj )
      }
    },

    methods: {
      __pan: function __pan (evt) {
        if (evt.isFirst) {
          this.__size = this.$el.getBoundingClientRect()[this.prop];
          this.__value = this.value;
          this.__dir = this.horizontal ? 'up' : 'left';
          this.__rtlDir = this.horizontal ? 1 : (this.$q.lang.rtl === true ? -1 : 1);

          this.$el.classList.add('q-splitter--active');
          return
        }

        if (evt.isFinal) {
          if (this.__normalized !== this.value) {
            this.$emit('input', this.__normalized);
          }

          this.$el.classList.remove('q-splitter--active');
          return
        }

        var val = this.__value +
          this.__rtlDir *
          (evt.direction === this.__dir ? -100 : 100) *
          evt.distance[this.horizontal ? 'y' : 'x'] / this.__size;

        this.__normalized = Math.min(this.limits[1], Math.max(this.limits[0], val));
        this.$refs.before.style[this.prop] = this.__normalized + '%';
        this.$refs.after.style[this.prop] = (100 - this.__normalized) + '%';
      },

      __normalize: function __normalize (val, limits) {
        if (val < limits[0]) {
          this.$emit('input', limits[0]);
        }
        else if (val > limits[1]) {
          this.$emit('input', limits[1]);
        }
      }
    },

    render: function render (h) {
      return h('div', {
        staticClass: 'q-splitter no-wrap',
        class: this.classes,
        on: this.$listeners
      }, [
        h('div', {
          ref: 'before',
          staticClass: 'q-splitter__panel q-splitter__before',
          style: this.beforeStyle,
          class: this.beforeClass,
          on: { input: stop }
        }, slot(this, 'before')),

        h('div', {
          staticClass: 'q-splitter__separator',
          style: this.separatorStyle,
          class: this.separatorClass
        }, [
          h('div', {
            staticClass: 'absolute-full q-splitter__separator-area',
            directives: this.disable === true ? void 0 : [{
              name: 'touch-pan',
              value: this.__pan,
              modifiers: {
                horizontal: !this.horizontal,
                vertical: this.horizontal,
                prevent: true,
                stop: true,
                mouse: true,
                mouseAllDir: true
              }
            }]
          }, slot(this, 'separator'))
        ]),

        h('div', {
          ref: 'after',
          staticClass: 'q-splitter__panel q-splitter__after',
          style: this.afterStyle,
          class: this.afterClass,
          on: { input: stop }
        }, slot(this, 'after'))
      ].concat(slot(this, 'default')))
    }
  });

  var StepHeader = Vue.extend({
    name: 'StepHeader',

    directives: {
      Ripple: Ripple
    },

    props: {
      stepper: {},
      step: {}
    },

    computed: {
      isActive: function isActive () {
        return this.stepper.value === this.step.name
      },

      isDisable: function isDisable () {
        var opt = this.step.disable;
        return opt === true || opt === ''
      },

      isError: function isError () {
        var opt = this.step.error;
        return opt === true || opt === ''
      },

      isDone: function isDone () {
        var opt = this.step.done;
        return !this.isDisable && (opt === true || opt === '')
      },

      headerNav: function headerNav () {
        var
          opt = this.step.headerNav,
          nav = opt === true || opt === '' || opt === void 0;

        return !this.isDisable && this.stepper.headerNav && (this.isActive || nav)
      },

      hasPrefix: function hasPrefix () {
        return this.step.prefix && !this.isActive && !this.isError && !this.isDone
      },

      icon: function icon () {
        if (this.isActive) {
          return this.step.activeIcon || this.stepper.activeIcon || this.$q.iconSet.stepper.active
        }
        if (this.isError) {
          return this.step.errorIcon || this.stepper.errorIcon || this.$q.iconSet.stepper.error
        }
        if (!this.isDisable && this.isDone) {
          return this.step.doneIcon || this.stepper.doneIcon || this.$q.iconSet.stepper.done
        }

        return this.step.icon || this.stepper.inactiveIcon
      },

      color: function color () {
        if (this.isActive) {
          return this.step.activeColor || this.stepper.activeColor || this.step.color
        }
        if (this.isError) {
          return this.step.errorColor || this.stepper.errorColor
        }
        if (!this.disable && this.isDone) {
          return this.step.doneColor || this.stepper.doneColor || this.step.color || this.stepper.inactiveColor
        }

        return this.step.color || this.stepper.inactiveColor
      },

      classes: function classes () {
        var obj;

        return ( obj = {}, obj[("text-" + (this.color))] = this.color, obj['q-stepper__tab--error'] = this.isError, obj['q-stepper__tab--active'] = this.isActive, obj['q-stepper__tab--done'] = this.isDone, obj['q-stepper__tab--navigation q-focusable q-hoverable'] = this.headerNav, obj['q-stepper__tab--disabled'] = this.isDisable, obj )
      }
    },

    methods: {
      activate: function activate () {
        this.$refs.blurTarget !== void 0 && this.$refs.blurTarget.focus();
        !this.isActive && this.stepper.goTo(this.step.name);
      },
      keyup: function keyup (e) {
        e.keyCode === 13 && !this.isActive && this.stepper.goTo(this.step.name);
      }
    },

    render: function render (h) {
      var data = {
        staticClass: 'q-stepper__tab col-grow flex items-center no-wrap relative-position',
        class: this.classes,
        directives: this.stepper.headerNav ? [{
          name: 'ripple',
          value: this.headerNav
        }] : null
      };

      if (this.headerNav) {
        data.on = {
          click: this.activate,
          keyup: this.keyup
        };
        data.attrs = { tabindex: this.isDisable === true ? -1 : this.$attrs.tabindex || 0 };
      }

      return h('div', data, [
        h('div', { staticClass: 'q-focus-helper', attrs: { tabindex: -1 }, ref: 'blurTarget' }),

        h('div', { staticClass: 'q-stepper__dot row flex-center q-stepper__line relative-position' }, [
          h('span', { staticClass: 'row flex-center' }, [
            this.hasPrefix === true
              ? this.step.prefix
              : h(QIcon, { props: { name: this.icon } })
          ])
        ]),

        this.step.title
          ? h('div', {
            staticClass: 'q-stepper__label q-stepper__line relative-position'
          }, [
            h('div', { staticClass: 'q-stepper__title' }, [ this.step.title ]),
            this.step.caption
              ? h('div', { staticClass: 'q-stepper__caption' }, [ this.step.caption ])
              : null
          ])
          : null
      ])
    }
  });

  var StepWrapper = Vue.extend({
    name: 'QStepWrapper',

    render: function render (h) {
      return h('div', {
        staticClass: 'q-stepper__step-content'
      }, [
        h('div', {
          staticClass: 'q-stepper__step-inner'
        }, slot(this, 'default'))
      ])
    }
  });

  var QStep = Vue.extend({
    name: 'QStep',

    inject: {
      stepper: {
        default: function default$1 () {
          console.error('QStep needs to be child of QStepper');
        }
      }
    },

    mixins: [ PanelChildMixin ],

    props: {
      icon: String,
      color: String,
      title: {
        type: String,
        required: true
      },
      caption: String,
      prefix: [ String, Number ],

      doneIcon: String,
      doneColor: String,
      activeIcon: String,
      activeColor: String,
      errorIcon: String,
      errorColor: String,

      headerNav: {
        type: Boolean,
        default: true
      },
      done: Boolean,
      error: Boolean
    },

    computed: {
      isActive: function isActive () {
        return this.stepper.value === this.name
      }
    },

    render: function render (h) {
      var vertical = this.stepper.vertical;
      var content = vertical === true && this.stepper.keepAlive === true
        ? h(
          'keep-alive',
          this.isActive === true
            ? [ h(StepWrapper, { key: this.name }, slot(this, 'default')) ]
            : void 0
        )
        : (
          vertical !== true || this.isActive === true
            ? StepWrapper.options.render.call(this, h)
            : void 0
        );

      return h(
        'div',
        {
          staticClass: 'q-stepper__step',
          on: this.$listeners
        },
        vertical === true
          ? [
            h(StepHeader, {
              props: {
                stepper: this.stepper,
                step: this
              }
            }),

            this.stepper.animated === true
              ? h(QSlideTransition, [ content ])
              : content
          ]
          : [ content ]
      )
    }
  });

  var QStepper = Vue.extend({
    name: 'QStepper',

    provide: function provide () {
      return {
        stepper: this
      }
    },

    mixins: [ PanelParentMixin ],

    props: {
      dark: Boolean,

      flat: Boolean,
      bordered: Boolean,
      vertical: Boolean,
      alternativeLabels: Boolean,
      headerNav: Boolean,
      contracted: Boolean,

      inactiveColor: String,
      inactiveIcon: String,
      doneIcon: String,
      doneColor: String,
      activeIcon: String,
      activeColor: String,
      errorIcon: String,
      errorColor: String
    },

    computed: {
      classes: function classes () {
        return "q-stepper--" + (this.vertical ? 'vertical' : 'horizontal') +
          (this.flat || this.dark ? ' q-stepper--flat no-shadow' : '') +
          (this.bordered || (this.dark && !this.flat) ? ' q-stepper--bordered' : '') +
          (this.contracted === true ? ' q-stepper--contracted' : '') +
          (this.dark === true ? ' q-stepper--dark' : '')
      }
    },

    methods: {
      __getContent: function __getContent (h) {
        var this$1 = this;
        var obj;

        var top = slot(this, 'message');

        if (this.vertical === true) {
          this.__isValidPanelName(this.value) && this.__updatePanelIndex();

          return (top !== void 0 ? top : []).concat([
            h('div', {
              staticClass: 'q-stepper__content',
              // stop propagation of content emitted @input
              // which would tamper with Panel's model
              on: { input: stop }
            }, slot(this, 'default'))
          ])
        }

        return [
          h('div', {
            staticClass: 'q-stepper__header row items-stretch justify-between',
            class: ( obj = {}, obj[("q-stepper__header--" + (this.alternativeLabels ? 'alternative' : 'standard') + "-labels")] = true, obj['q-stepper__header--border'] = !this.flat || this.bordered, obj )
          }, this.__getAllPanels().map(function (panel) {
            var step = panel.componentOptions.propsData;

            return h(StepHeader, {
              key: step.name,
              props: {
                stepper: this$1,
                step: step
              }
            })
          }))
        ].concat((top !== void 0 ? top : [])).concat([
          h('div', {
            staticClass: 'q-stepper__content q-panel-parent',
            directives: this.panelDirectives
          }, [
            this.__getPanelContent(h)
          ])
        ])
      },

      __render: function __render (h) {
        return h('div', {
          staticClass: 'q-stepper',
          class: this.classes,
          on: this.$listeners
        }, this.__getContent(h).concat(slot(this, 'navigation')))
      }
    }
  });

  var QStepperNavigation = Vue.extend({
    name: 'QStepperNavigation',

    render: function render (h) {
      return h('div', {
        staticClass: 'q-stepper__nav',
        on: this.$listeners
      }, slot(this, 'default'))
    }
  });

  var Top = {
    computed: {
      marginalsProps: function marginalsProps () {
        return {
          pagination: this.computedPagination,
          pagesNumber: this.pagesNumber,
          isFirstPage: this.isFirstPage,
          isLastPage: this.isLastPage,
          prevPage: this.prevPage,
          nextPage: this.nextPage,

          inFullscreen: this.inFullscreen,
          toggleFullscreen: this.toggleFullscreen
        }
      }
    },

    methods: {
      getTop: function getTop (h) {
        var
          top = this.$scopedSlots.top,
          topLeft = this.$scopedSlots['top-left'],
          topRight = this.$scopedSlots['top-right'],
          topSelection = this.$scopedSlots['top-selection'],
          hasSelection = this.hasSelectionMode === true &&
            topSelection !== void 0 &&
            this.rowsSelectedNumber > 0,
          staticClass = 'q-table__top relative-position row items-center',
          child = [];

        if (top !== void 0) {
          return h('div', { staticClass: staticClass }, [ top(this.marginalsProps) ])
        }

        if (hasSelection === true) {
          child.push(topSelection(this.marginalsProps));
        }
        else {
          if (topLeft !== void 0) {
            child.push(
              h('div', { staticClass: 'q-table-control' }, [
                topLeft(this.marginalsProps)
              ])
            );
          }
          else if (this.title) {
            child.push(
              h('div', { staticClass: 'q-table__control' }, [
                h('div', { staticClass: 'q-table__title' }, this.title)
              ])
            );
          }
        }

        if (topRight !== void 0) {
          child.push(h('div', { staticClass: 'q-table__separator col' }));
          child.push(
            h('div', { staticClass: 'q-table__control' }, [
              topRight(this.marginalsProps)
            ])
          );
        }

        if (child.length === 0) {
          return
        }

        return h('div', { staticClass: staticClass }, child)
      }
    }
  };

  var QTh = Vue.extend({
    name: 'QTh',

    props: {
      props: Object,
      autoWidth: Boolean
    },

    render: function render (h) {
      var this$1 = this;

      if (this.props === void 0) {
        return h('td', {
          class: this.autoWidth === true ? 'q-table--col-auto-width' : null
        }, slot(this, 'default'))
      }

      var col;
      var
        name = this.$vnode.key,
        child = [].concat(slot(this, 'default'));

      if (name) {
        col = this.props.colsMap[name];
        if (col === void 0) { return }
      }
      else {
        col = this.props.col;
      }

      if (col.sortable === true) {
        var action = col.align === 'right'
          ? 'unshift'
          : 'push';

        child[action](
          h(QIcon, {
            props: { name: this.$q.iconSet.table.arrowUp },
            staticClass: col.__iconClass
          })
        );
      }

      return h('th', {
        class: col.__thClass +
          (this.autoWidth === true ? ' q-table--col-auto-width' : ''),
        on: col.sortable === true
          ? { click: function () { this$1.props.sort(col); } }
          : null
      }, child)
    }
  });

  var TableHeader = {
    methods: {
      getTableHeader: function getTableHeader (h) {
        var child = [ this.getTableHeaderRow(h) ];

        this.loading === true && child.push(
          h('tr', { staticClass: 'q-table__progress' }, [
            h('td', { staticClass: 'relative-position', attrs: { colspan: '100%' } }, [
              h(QLinearProgress, {
                staticClass: 'q-table__linear-progress',
                props: {
                  color: this.color,
                  dark: this.dark,
                  indeterminate: true
                }
              })
            ])
          ])
        );

        return h('thead', child)
      },

      getTableHeaderRow: function getTableHeaderRow (h) {
        var this$1 = this;

        var
          header = this.$scopedSlots.header,
          headerCell = this.$scopedSlots['header-cell'];

        if (header !== void 0) {
          return header(this.addTableHeaderRowMeta({
            header: true, cols: this.computedCols, sort: this.sort, colsMap: this.computedColsMap
          }))
        }

        var mapFn;

        if (headerCell !== void 0) {
          mapFn = function (col) { return headerCell({
            col: col, cols: this$1.computedCols, sort: this$1.sort, colsMap: this$1.computedColsMap
          }); };
        }
        else {
          mapFn = function (col) { return h(QTh, {
            key: col.name,
            props: {
              props: {
                col: col,
                cols: this$1.computedCols,
                sort: this$1.sort,
                colsMap: this$1.computedColsMap
              }
            },
            style: col.style,
            class: col.classes
          }, col.label); };
        }
        var child = this.computedCols.map(mapFn);

        if (this.singleSelection === true && this.grid !== true) {
          child.unshift(h('th', { staticClass: 'q-table--col-auto-width' }, [' ']));
        }
        else if (this.multipleSelection === true) {
          child.unshift(h('th', { staticClass: 'q-table--col-auto-width' }, [
            h(QCheckbox, {
              props: {
                color: this.color,
                value: this.someRowsSelected ? null : this.allRowsSelected,
                dark: this.dark,
                dense: this.dense
              },
              on: {
                input: function (val) {
                  if (this$1.someRowsSelected) {
                    val = false;
                  }
                  this$1.__updateSelection(
                    this$1.computedRows.map(function (row) { return row[this$1.rowKey]; }),
                    this$1.computedRows,
                    val
                  );
                }
              }
            })
          ]));
        }

        return h('tr', {
          style: this.tableHeaderStyle,
          class: this.tableHeaderClass
        }, child)
      },

      addTableHeaderRowMeta: function addTableHeaderRowMeta (data) {
        var this$1 = this;

        if (this.multipleSelection === true) {
          Object.defineProperty(data, 'selected', {
            get: function () { return this$1.someRowsSelected ? 'some' : this$1.allRowsSelected; },
            set: function (val) {
              if (this$1.someRowsSelected) {
                val = false;
              }
              this$1.__updateSelection(
                this$1.computedRows.map(function (row) { return row[this$1.rowKey]; }),
                this$1.computedRows,
                val
              );
            },
            configurable: true,
            enumerable: true
          });
          data.partialSelected = this.someRowsSelected;
          data.multipleSelect = true;
        }

        return data
      }
    }
  };

  var TableBody = {
    methods: {
      getTableBody: function getTableBody (h) {
        var this$1 = this;

        var
          body = this.$scopedSlots.body,
          bodyCell = this.$scopedSlots['body-cell'],
          topRow = this.$scopedSlots['top-row'],
          bottomRow = this.$scopedSlots['bottom-row'];
        var
          child = [];

        if (body !== void 0) {
          child = this.computedRows.map(function (row) {
            var
              key = row[this$1.rowKey],
              selected = this$1.isRowSelected(key);

            return body(this$1.addBodyRowMeta({
              key: key,
              row: row,
              cols: this$1.computedCols,
              colsMap: this$1.computedColsMap,
              __trClass: selected ? 'selected' : ''
            }))
          });
        }
        else {
          child = this.computedRows.map(function (row) {
            var
              key = row[this$1.rowKey],
              selected = this$1.isRowSelected(key),
              child = bodyCell
                ? this$1.computedCols.map(function (col) { return bodyCell(this$1.addBodyCellMetaData({ row: row, col: col })); })
                : this$1.computedCols.map(function (col) {
                  var slot = this$1.$scopedSlots[("body-cell-" + (col.name))];
                  return slot !== void 0
                    ? slot(this$1.addBodyCellMetaData({ row: row, col: col }))
                    : h('td', {
                      staticClass: col.__tdClass,
                      style: col.style,
                      class: col.classes
                    }, this$1.getCellValue(col, row))
                });

            this$1.hasSelectionMode === true && child.unshift(
              h('td', { staticClass: 'q-table--col-auto-width' }, [
                h(QCheckbox, {
                  props: {
                    value: selected,
                    color: this$1.color,
                    dark: this$1.dark,
                    dense: this$1.dense
                  },
                  on: {
                    input: function (adding) {
                      this$1.__updateSelection([key], [row], adding);
                    }
                  }
                })
              ])
            );

            return h('tr', { key: key, class: { selected: selected } }, child)
          });
        }

        if (topRow !== void 0) {
          child.unshift(topRow({ cols: this.computedCols }));
        }
        if (bottomRow !== void 0) {
          child.push(bottomRow({ cols: this.computedCols }));
        }

        return h('tbody', child)
      },

      addBodyRowMeta: function addBodyRowMeta (data) {
        var this$1 = this;

        this.hasSelectionMode === true && Object.defineProperty(data, 'selected', {
          get: function () { return this$1.isRowSelected(data.key); },
          set: function (adding) {
            this$1.__updateSelection([data.key], [data.row], adding);
          },
          configurable: true,
          enumerable: true
        });

        Object.defineProperty(data, 'expand', {
          get: function () { return this$1.rowsExpanded[data.key] === true; },
          set: function (val) {
            this$1.$set(this$1.rowsExpanded, data.key, val);
          },
          configurable: true,
          enumerable: true
        });

        data.cols = data.cols.map(function (col) {
          var c = Object.assign({}, col);
          Object.defineProperty(c, 'value', {
            get: function () { return this$1.getCellValue(col, data.row); },
            configurable: true,
            enumerable: true
          });
          return c
        });

        return data
      },

      addBodyCellMetaData: function addBodyCellMetaData (data) {
        var this$1 = this;

        Object.defineProperty(data, 'value', {
          get: function () { return this$1.getCellValue(data.col, data.row); },
          configurable: true,
          enumerable: true
        });
        return data
      },

      getCellValue: function getCellValue (col, row) {
        var val = typeof col.field === 'function' ? col.field(row) : row[col.field];
        return col.format !== void 0 ? col.format(val, row) : val
      }
    }
  };

  var Bottom = {
    computed: {
      navIcon: function navIcon () {
        var ico = [ this.$q.iconSet.table.prevPage, this.$q.iconSet.table.nextPage ];
        return this.$q.lang.rtl === true ? ico.reverse() : ico
      }
    },

    methods: {
      getBottom: function getBottom (h) {
        if (this.hideBottom === true) {
          return
        }

        if (this.nothingToDisplay === true) {
          var message = this.filter
            ? this.noResultsLabel || this.$q.lang.table.noResults
            : (this.loading === true ? this.loadingLabel || this.$q.lang.table.loading : this.noDataLabel || this.$q.lang.table.noData);

          return h('div', { staticClass: 'q-table__bottom row items-center q-table__bottom--nodata' }, [
            h(QIcon, { props: { name: this.$q.iconSet.table.warning } }),
            message
          ])
        }

        var bottom = this.$scopedSlots.bottom;

        return h('div', {
          staticClass: 'q-table__bottom row items-center',
          class: bottom !== void 0 ? null : 'justify-end'
        }, bottom !== void 0 ? [ bottom(this.marginalsProps) ] : this.getPaginationRow(h))
      },

      getPaginationRow: function getPaginationRow (h) {
        var this$1 = this;

        var ref = this.computedPagination;
        var rowsPerPage = ref.rowsPerPage;
        var paginationLabel = this.paginationLabel || this.$q.lang.table.pagination,
          paginationSlot = this.$scopedSlots.pagination;

        return [
          h('div', { staticClass: 'q-table__control' }, [
            h('div', [
              this.hasSelectionMode === true && this.rowsSelectedNumber > 0
                ? (this.selectedRowsLabel || this.$q.lang.table.selectedRecords)(this.rowsSelectedNumber)
                : ''
            ])
          ]),

          h('div', { staticClass: 'q-table__separator col' }),

          this.rowsPerPageOptions.length > 1
            ? h('div', { staticClass: 'q-table__control' }, [
              h('span', { staticClass: 'q-table__bottom-item' }, [
                this.rowsPerPageLabel || this.$q.lang.table.recordsPerPage
              ]),
              h(QSelect, {
                staticClass: 'inline q-table__bottom-item',
                props: {
                  color: this.color,
                  value: rowsPerPage,
                  options: this.computedRowsPerPageOptions,
                  displayValue: rowsPerPage === 0
                    ? this.$q.lang.table.allRows
                    : rowsPerPage,
                  dark: this.dark,
                  borderless: true,
                  dense: true,
                  optionsDense: true
                },
                on: {
                  input: function (pag) {
                    this$1.setPagination({
                      page: 1,
                      rowsPerPage: pag.value
                    });
                  }
                }
              })
            ])
            : null,

          h('div', { staticClass: 'q-table__control' }, [
            paginationSlot !== void 0
              ? paginationSlot(this.marginalsProps)
              : [
                h('span', { staticClass: 'q-table__bottom-item' }, [
                  rowsPerPage
                    ? paginationLabel(this.firstRowIndex + 1, Math.min(this.lastRowIndex, this.computedRowsNumber), this.computedRowsNumber)
                    : paginationLabel(1, this.computedRowsNumber, this.computedRowsNumber)
                ]),
                h(QBtn, {
                  props: {
                    color: this.color,
                    round: true,
                    icon: this.navIcon[0],
                    dense: true,
                    flat: true,
                    disable: this.isFirstPage
                  },
                  on: { click: this.prevPage }
                }),
                h(QBtn, {
                  props: {
                    color: this.color,
                    round: true,
                    icon: this.navIcon[1],
                    dense: true,
                    flat: true,
                    disable: this.isLastPage
                  },
                  on: { click: this.nextPage }
                })
              ]
          ])
        ]
      }
    }
  };

  var TableGrid = {
    methods: {
      getTableGrid: function getTableGrid (h) {
        var this$1 = this;

        var item = this.$scopedSlots.item !== void 0
          ? this.$scopedSlots.item
          : function (scope) {
            var child = scope.cols.map(
              function (col) { return h('div', { staticClass: 'q-table__grid-item-row' }, [
                h('div', { staticClass: 'q-table__grid-item-title' }, [ col.label ]),
                h('div', { staticClass: 'q-table__grid-item-value' }, [ col.value ])
              ]); }
            );

            this$1.hasSelectionMode === true && child.unshift(
              h('div', { staticClass: 'q-table__grid-item-row' }, [
                h(QCheckbox, {
                  props: {
                    value: scope.selected,
                    color: this$1.color,
                    dark: this$1.dark,
                    dense: true
                  },
                  on: {
                    input: function (val) {
                      scope.selected = val;
                    }
                  }
                })
              ]),

              h(QSeparator, { props: { dark: this$1.dark } })
            );

            return h('div', {
              staticClass: 'q-table__grid-item col-xs-12 col-sm-6 col-md-4 col-lg-3',
              class: scope.selected === true ? 'q-table__grid-item--selected' : null
            }, [
              h('div', {
                staticClass: 'q-table__grid-item-card' + this$1.cardDefaultClass,
                class: this$1.cardClass,
                style: this$1.cardStyle
              }, child)
            ])
          };

        return [
          this.hideHeader === false
            ? h('div', { staticClass: 'q-table__middle' }, [
              this.loading === true
                ? h(QLinearProgress, {
                  staticClass: 'q-table__linear-progress',
                  props: {
                    color: this.color,
                    dark: this.dark,
                    indeterminate: true
                  }
                })
                : null
            ])
            : null,

          h('div', { staticClass: 'row' }, this.computedRows.map(function (row) {
            var
              key = row[this$1.rowKey],
              selected = this$1.isRowSelected(key);

            return item(this$1.addBodyRowMeta({
              key: key,
              row: row,
              cols: this$1.computedCols,
              colsMap: this$1.computedColsMap,
              __trClass: selected ? 'selected' : ''
            }))
          }))
        ]
      }
    }
  };

  function sortDate (a, b) {
    return (new Date(a)) - (new Date(b))
  }

  var Sort = {
    props: {
      sortMethod: {
        type: Function,
        default: function default$1 (data, sortBy, descending) {
          var col = this.columns.find(function (def) { return def.name === sortBy; });
          if (col === null || col.field === void 0) {
            return data
          }

          var
            dir = descending === true ? -1 : 1,
            val = typeof col.field === 'function'
              ? function (v) { return col.field(v); }
              : function (v) { return v[col.field]; };

          return data.sort(function (a, b) {
            var assign;

            var
              A = val(a),
              B = val(b);

            if (A === null || A === void 0) {
              return -1 * dir
            }
            if (B === null || B === void 0) {
              return 1 * dir
            }
            if (col.sort !== void 0) {
              return col.sort(A, B, a, b) * dir
            }
            if (isNumber(A) === true && isNumber(B) === true) {
              return (A - B) * dir
            }
            if (isDate(A) === true && isDate(B) === true) {
              return sortDate(A, B) * dir
            }
            if (typeof A === 'boolean' && typeof B === 'boolean') {
              return (A - B) * dir
            }

            (assign = [A, B].map(function (s) { return (s + '').toLocaleString().toLowerCase(); }), A = assign[0], B = assign[1]);

            return A < B
              ? -1 * dir
              : (A === B ? 0 : dir)
          })
        }
      }
    },

    computed: {
      columnToSort: function columnToSort () {
        var ref = this.computedPagination;
        var sortBy = ref.sortBy;

        if (sortBy) {
          return this.columns.find(function (def) { return def.name === sortBy; }) || null
        }
      }
    },

    methods: {
      sort: function sort (col /* String(col name) or Object(col definition) */) {
        if (col === Object(col)) {
          col = col.name;
        }

        var ref = this.computedPagination;
        var sortBy = ref.sortBy;
        var descending = ref.descending;

        if (sortBy !== col) {
          sortBy = col;
          descending = false;
        }
        else {
          if (this.binaryStateSort === true) {
            descending = !descending;
          }
          else {
            if (descending === true) {
              sortBy = null;
            }
            else {
              descending = true;
            }
          }
        }

        this.setPagination({ sortBy: sortBy, descending: descending, page: 1 });
      }
    }
  };

  var Filter = {
    props: {
      filter: [String, Object],
      filterMethod: {
        type: Function,
        default: function default$1 (rows, terms, cols, cellValue) {
          if ( cols === void 0 ) cols = this.computedCols;
          if ( cellValue === void 0 ) cellValue = this.getCellValue;

          var lowerTerms = terms ? terms.toLowerCase() : '';
          return rows.filter(
            function (row) { return cols.some(function (col) { return (cellValue(col, row) + '').toLowerCase().indexOf(lowerTerms) !== -1; }); }
          )
        }
      }
    },

    watch: {
      filter: function filter () {
        var this$1 = this;

        this.$nextTick(function () {
          this$1.setPagination({ page: 1 }, true);
        });
      }
    }
  };

  function samePagination (oldPag, newPag) {
    for (var prop in newPag) {
      if (newPag[prop] !== oldPag[prop]) {
        return false
      }
    }
    return true
  }

  function fixPagination (p) {
    if (p.page < 1) {
      p.page = 1;
    }
    if (p.rowsPerPage !== void 0 && p.rowsPerPage < 1) {
      p.rowsPerPage = 0;
    }
    return p
  }

  var Pagination = {
    props: {
      pagination: Object,
      rowsPerPageOptions: {
        type: Array,
        default: function () { return [3, 5, 7, 10, 15, 20, 25, 50, 0]; }
      }
    },

    computed: {
      computedPagination: function computedPagination () {
        return fixPagination(Object.assign({}, this.innerPagination,
          this.pagination))
      },

      firstRowIndex: function firstRowIndex () {
        var ref = this.computedPagination;
        var page = ref.page;
        var rowsPerPage = ref.rowsPerPage;
        return (page - 1) * rowsPerPage
      },

      lastRowIndex: function lastRowIndex () {
        var ref = this.computedPagination;
        var page = ref.page;
        var rowsPerPage = ref.rowsPerPage;
        return page * rowsPerPage
      },

      isFirstPage: function isFirstPage () {
        return this.computedPagination.page === 1
      },

      pagesNumber: function pagesNumber () {
        return Math.max(
          1,
          Math.ceil(this.computedRowsNumber / this.computedPagination.rowsPerPage)
        )
      },

      isLastPage: function isLastPage () {
        return this.lastRowIndex === 0
          ? true
          : this.computedPagination.page >= this.pagesNumber
      },

      computedRowsPerPageOptions: function computedRowsPerPageOptions () {
        var this$1 = this;

        return this.rowsPerPageOptions.map(function (count) { return ({
          label: count === 0 ? this$1.$q.lang.table.allRows : '' + count,
          value: count
        }); })
      }
    },

    watch: {
      pagesNumber: function pagesNumber (lastPage, oldLastPage) {
        if (lastPage === oldLastPage) {
          return
        }

        var currentPage = this.computedPagination.page;
        if (lastPage && !currentPage) {
          this.setPagination({ page: 1 });
        }
        else if (lastPage < currentPage) {
          this.setPagination({ page: lastPage });
        }
      }
    },

    methods: {
      __sendServerRequest: function __sendServerRequest (pagination) {
        this.requestServerInteraction({
          pagination: pagination,
          filter: this.filter
        });
      },

      setPagination: function setPagination (val, forceServerRequest) {
        var newPagination = fixPagination(Object.assign({}, this.computedPagination,
          val));

        if (samePagination(this.computedPagination, newPagination)) {
          if (this.isServerSide && forceServerRequest) {
            this.__sendServerRequest(newPagination);
          }
          return
        }

        if (this.isServerSide) {
          this.__sendServerRequest(newPagination);
          return
        }

        if (this.pagination) {
          this.$emit('update:pagination', newPagination);
        }
        else {
          this.innerPagination = newPagination;
        }
      },

      prevPage: function prevPage () {
        var ref = this.computedPagination;
        var page = ref.page;
        if (page > 1) {
          this.setPagination({ page: page - 1 });
        }
      },

      nextPage: function nextPage () {
        var ref = this.computedPagination;
        var page = ref.page;
        var rowsPerPage = ref.rowsPerPage;
        if (this.lastRowIndex > 0 && page * rowsPerPage < this.computedRowsNumber) {
          this.setPagination({ page: page + 1 });
        }
      }
    },

    created: function created () {
      this.$emit('update:pagination', Object.assign({}, this.computedPagination));
    }
  };

  var RowSelection = {
    props: {
      selection: {
        type: String,
        default: 'none',
        validator: function (v) { return ['single', 'multiple', 'none'].includes(v); }
      },
      selected: {
        type: Array,
        default: function () { return []; }
      }
    },

    computed: {
      selectedKeys: function selectedKeys () {
        var this$1 = this;

        var keys = {};
        this.selected.map(function (row) { return row[this$1.rowKey]; }).forEach(function (key) {
          keys[key] = true;
        });
        return keys
      },

      hasSelectionMode: function hasSelectionMode () {
        return this.selection !== 'none'
      },

      singleSelection: function singleSelection () {
        return this.selection === 'single'
      },

      multipleSelection: function multipleSelection () {
        return this.selection === 'multiple'
      },

      allRowsSelected: function allRowsSelected () {
        var this$1 = this;

        if (this.multipleSelection === true) {
          return this.computedRows.length > 0 && this.computedRows.every(function (row) { return this$1.selectedKeys[row[this$1.rowKey]] === true; })
        }
      },

      someRowsSelected: function someRowsSelected () {
        var this$1 = this;

        if (this.multipleSelection === true) {
          return !this.allRowsSelected && this.computedRows.some(function (row) { return this$1.selectedKeys[row[this$1.rowKey]] === true; })
        }
      },

      rowsSelectedNumber: function rowsSelectedNumber () {
        return this.selected.length
      }
    },

    methods: {
      isRowSelected: function isRowSelected (key) {
        return this.selectedKeys[key] === true
      },

      clearSelection: function clearSelection () {
        this.$emit('update:selected', []);
      },

      __updateSelection: function __updateSelection (keys, rows, added) {
        var this$1 = this;

        this.$emit('selection', { rows: rows, added: added, keys: keys });

        var payload = this.singleSelection === true
          ? (added === true ? rows : [])
          : (
            added === true
              ? this.selected.concat(rows)
              : this.selected.filter(
                function (row) { return keys.includes(row[this$1.rowKey]) === false; }
              )
          );

        this.$emit('update:selected', payload);
      }
    }
  };

  var ColumnSelection = {
    props: {
      visibleColumns: Array
    },

    computed: {
      computedCols: function computedCols () {
        var this$1 = this;

        var ref = this.computedPagination;
        var sortBy = ref.sortBy;
        var descending = ref.descending;

        var cols = this.visibleColumns !== void 0
          ? this.columns.filter(function (col) { return col.required === true || this$1.visibleColumns.includes(col.name) === true; })
          : this.columns;

        return cols.map(function (col) {
          col.align = col.align || 'right';
          col.__iconClass = "q-table__sort-icon q-table__sort-icon--" + (col.align);
          col.__thClass = "text-" + (col.align) + (col.sortable ? ' sortable' : '') + (col.name === sortBy ? (" sorted " + (descending ? 'sort-desc' : '')) : '');
          col.__tdClass = "text-" + (col.align);
          return col
        })
      },

      computedColsMap: function computedColsMap () {
        var names = {};
        this.computedCols.forEach(function (col) {
          names[col.name] = col;
        });
        return names
      }
    }
  };

  var QTable = Vue.extend({
    name: 'QTable',

    mixins: [
      FullscreenMixin,
      Top,
      TableHeader,
      TableBody,
      Bottom,
      TableGrid,
      Sort,
      Filter,
      Pagination,
      RowSelection,
      ColumnSelection
    ],

    props: {
      data: {
        type: Array,
        default: function () { return []; }
      },
      rowKey: {
        type: String,
        default: 'id'
      },

      columns: Array,
      loading: Boolean,
      binaryStateSort: Boolean,

      title: String,

      hideHeader: Boolean,
      hideBottom: Boolean,

      grid: Boolean,
      dense: Boolean,
      flat: Boolean,
      bordered: Boolean,
      square: Boolean,
      separator: {
        type: String,
        default: 'horizontal',
        validator: function (v) { return ['horizontal', 'vertical', 'cell', 'none'].includes(v); }
      },
      wrapCells: Boolean,

      noDataLabel: String,
      noResultsLabel: String,
      loadingLabel: String,
      selectedRowsLabel: Function,
      rowsPerPageLabel: String,
      paginationLabel: Function,

      color: {
        type: String,
        default: 'grey-8'
      },

      tableStyle: [String, Array, Object],
      tableClass: [String, Array, Object],
      tableHeaderStyle: [String, Array, Object],
      tableHeaderClass: [String, Array, Object],
      cardStyle: [String, Array, Object],
      cardClass: [String, Array, Object],

      dark: Boolean
    },

    data: function data () {
      return {
        rowsExpanded: {},
        innerPagination: {
          sortBy: null,
          descending: false,
          page: 1,
          rowsPerPage: 5
        }
      }
    },

    computed: {
      computedData: function computedData () {
        var rows = this.data.slice().map(function (row, i) {
          row.__index = i;
          return row
        });

        if (rows.length === 0) {
          return {
            rowsNumber: 0,
            rows: []
          }
        }

        if (this.isServerSide === true) {
          return { rows: rows }
        }

        var ref = this.computedPagination;
        var sortBy = ref.sortBy;
        var descending = ref.descending;
        var rowsPerPage = ref.rowsPerPage;

        if (this.filter) {
          rows = this.filterMethod(rows, this.filter, this.computedCols, this.getCellValue);
        }

        if (this.columnToSort) {
          rows = this.sortMethod(rows, sortBy, descending);
        }

        var rowsNumber = rows.length;

        if (rowsPerPage) {
          rows = rows.slice(this.firstRowIndex, this.lastRowIndex);
        }

        return { rowsNumber: rowsNumber, rows: rows }
      },

      computedRows: function computedRows () {
        return this.computedData.rows
      },

      computedRowsNumber: function computedRowsNumber () {
        return this.isServerSide === true
          ? this.computedPagination.rowsNumber || 0
          : this.computedData.rowsNumber
      },

      nothingToDisplay: function nothingToDisplay () {
        return this.computedRows.length === 0
      },

      isServerSide: function isServerSide () {
        return this.computedPagination.rowsNumber !== void 0
      },

      cardDefaultClass: function cardDefaultClass () {
        return " q-table__card" +
          (this.dark === true ? ' q-table__card--dark' : '') +
          (this.square === true ? " q-table--square" : '') +
          (this.flat === true ? " q-table--flat" : '') +
          (this.bordered === true ? " q-table--bordered" : '')
      },

      containerClass: function containerClass () {
        return "q-table__container q-table--" + (this.separator) + "-separator" +
          (this.grid === true ? ' q-table--grid' : this.cardDefaultClass) +
          (this.dark === true ? " q-table--dark" : '') +
          (this.dense === true ? " q-table--dense" : '') +
          (this.wrapCells === false ? " q-table--no-wrap" : '') +
          (this.inFullscreen === true ? " fullscreen scroll" : '')
      }
    },

    render: function render (h) {
      var data = { staticClass: this.containerClass };

      if (this.grid === false) {
        data.class = this.cardClass;
        data.style = this.cardStyle;
      }

      return h('div', data, [
        this.getTop(h),
        this.getBody(h),
        this.getBottom(h)
      ])
    },

    methods: {
      requestServerInteraction: function requestServerInteraction (prop) {
        var this$1 = this;
        if ( prop === void 0 ) prop = {};

        this.$nextTick(function () {
          this$1.$emit('request', {
            pagination: prop.pagination || this$1.computedPagination,
            filter: prop.filter || this$1.filter,
            getCellValue: this$1.getCellValue
          });
        });
      },

      getBody: function getBody (h) {
        if (this.grid === true) {
          return this.getTableGrid(h)
        }

        return h('div', {
          staticClass: 'q-table__middle scroll',
          class: this.tableClass,
          style: this.tableStyle
        }, [
          h('table', { staticClass: 'q-table' }, [
            this.hideHeader !== true ? this.getTableHeader(h) : null,
            this.getTableBody(h)
          ])
        ])
      }
    }
  });

  var QTr = Vue.extend({
    name: 'QTr',

    props: {
      props: Object
    },

    render: function render (h) {
      return h(
        'tr',
        this.props === void 0 || this.props.header === true
          ? {}
          : { class: this.props.__trClass },
        slot(this, 'default')
      )
    }
  });

  var QTd = Vue.extend({
    name: 'QTd',

    props: {
      props: Object,
      autoWidth: Boolean
    },

    render: function render (h) {
      if (this.props === void 0) {
        return h('td', {
          class: { 'q-table--col-auto-width': this.autoWidth }
        }, slot(this, 'default'))
      }

      var col;
      var name = this.$vnode.key;

      if (name) {
        col = this.props.colsMap[name];
        if (col === void 0) { return }
      }
      else {
        col = this.props.col;
      }

      return h('td', {
        class: col.__tdClass +
          (this.autoWidth === true ? ' q-table--col-auto-width' : '')
      }, slot(this, 'default'))
    }
  });

  var QMarkupTable = Vue.extend({
    name: 'QMarkupTable',

    props: {
      dense: Boolean,
      dark: Boolean,
      flat: Boolean,
      bordered: Boolean,
      square: Boolean,
      separator: {
        type: String,
        default: 'horizontal',
        validator: function (v) { return ['horizontal', 'vertical', 'cell', 'none'].includes(v); }
      },
      wrapCells: Boolean
    },

    computed: {
      classes: function classes () {
        return "q-table--" + (this.separator) + "-separator" +
          (this.dark === true ? " q-table--dark q-table__card--dark" : '') +
          (this.dense === true ? " q-table--dense" : '') +
          (this.flat === true ? " q-table--flat" : '') +
          (this.bordered === true ? " q-table--bordered" : '') +
          (this.square === true ? " q-table--square" : '') +
          (this.wrapCells === false ? " q-table--no-wrap" : '')
      }
    },

    render: function render (h) {
      return h('div', {
        staticClass: 'q-markup-table q-table__container q-table__card',
        class: this.classes,
        on: this.$listeners
      }, [
        h('table', { staticClass: 'q-table' }, slot(this, 'default'))
      ])
    }
  });

  var trailingSlashRE = /\/?$/;

  function queryIncludes (current, target) {
    for (var key in target) {
      if (!(key in current)) {
        return false
      }
    }
    return true
  }

  function isSameRoute (current, target) {
    if (!target) {
      return false
    }
    if (current.path && target.path) {
      return (
        current.path.replace(trailingSlashRE, '') === target.path.replace(trailingSlashRE, '') &&
        current.hash === target.hash &&
        isDeepEqual(current.query, target.query)
      )
    }
    if (current.name && target.name) {
      return (
        current.name === target.name &&
        current.hash === target.hash &&
        isDeepEqual(current.query, target.query) &&
        isDeepEqual(current.params, target.params)
      )
    }
    return false
  }

  function isIncludedRoute (current, target) {
    return (
      current.path.replace(trailingSlashRE, '/').indexOf(target.path.replace(trailingSlashRE, '/')) === 0 &&
      (!target.hash || current.hash === target.hash) &&
      queryIncludes(current.query, target.query)
    )
  }

  var QRouteTab = Vue.extend({
    name: 'QRouteTab',

    mixins: [ QTab, RouterLinkMixin ],

    props: {
      to: { required: true }
    },

    inject: {
      __activateRoute: {}
    },

    watch: {
      $route: function $route () {
        this.__checkActivation();
      }
    },

    methods: {
      activate: function activate (e, keyboard) {
        if (this.disable !== true) {
          this.__checkActivation(true);
        }

        if (keyboard === true) {
          this.$el.focus();
        }
        else {
          this.$refs.blurTarget !== void 0 && this.$refs.blurTarget.focus();
        }
      },

      __checkActivation: function __checkActivation (selected) {
        if ( selected === void 0 ) selected = false;

        var
          current = this.$route;
        var ref = this.$router.resolve(this.to, current, this.append);
        var href = ref.href;
        var location = ref.location;
        var route = ref.route;
        var redirected = route.redirectedFrom !== void 0,
          checkFunction = this.exact === true ? isSameRoute : isIncludedRoute,
          params = {
            name: this.name,
            selected: selected,
            exact: this.exact,
            priorityMatched: route.matched.length,
            priorityHref: href.length
          };

        checkFunction(current, route) && this.__activateRoute(Object.assign({}, params, {redirected: redirected}));
        redirected === true && checkFunction(current, Object.assign({}, {path: route.redirectedFrom},
          location)) && this.__activateRoute(params);
        this.isActive && this.__activateRoute();
      }
    },

    mounted: function mounted () {
      this.$router !== void 0 && this.__checkActivation();
    },

    beforeDestroy: function beforeDestroy () {
      this.__activateRoute({ remove: true, name: this.name });
    },

    render: function render (h) {
      return this.__render(h, 'router-link', this.routerLinkProps)
    }
  });

  var QTimeline = Vue.extend({
    name: 'QTimeline',

    provide: function provide () {
      return {
        __timeline: this
      }
    },

    props: {
      color: {
        type: String,
        default: 'primary'
      },
      side: {
        type: String,
        default: 'right',
        validator: function (v) { return ['left', 'right'].includes(v); }
      },
      layout: {
        type: String,
        default: 'dense',
        validator: function (v) { return ['dense', 'comfortable', 'loose'].includes(v); }
      },
      dark: Boolean
    },

    computed: {
      classes: function classes () {
        var obj;

        return ( obj = {
          'q-timeline--dark': this.dark
        }, obj[("q-timeline--" + (this.layout))] = true, obj[("q-timeline--" + (this.layout) + "--" + (this.side))] = true, obj )
      }
    },

    render: function render (h) {
      return h('ul', {
        staticClass: 'q-timeline',
        class: this.classes,
        on: this.$listeners
      }, slot(this, 'default'))
    }
  });

  var QTimelineEntry = Vue.extend({
    name: 'QTimelineEntry',

    inject: {
      __timeline: {
        default: function default$1 () {
          console.error('QTimelineEntry needs to be child of QTimeline');
        }
      }
    },

    props: {
      heading: Boolean,
      tag: {
        type: String,
        default: 'h3'
      },
      side: {
        type: String,
        default: 'right',
        validator: function (v) { return ['left', 'right'].includes(v); }
      },

      icon: String,
      avatar: String,

      color: String,

      title: String,
      subtitle: String,
      body: String
    },

    computed: {
      colorClass: function colorClass () {
        return ("text-" + (this.color || this.__timeline.color))
      },

      classes: function classes () {
        return "q-timeline__entry--" + (this.side) +
          (this.icon !== void 0 || this.avatar !== void 0 ? ' q-timeline__entry--icon' : '')
      },

      reverse: function reverse () {
        return this.__timeline.layout === 'comfortable' && this.__timeline.side === 'left'
      }
    },

    render: function render (h) {
      var defSlot = this.$scopedSlots.default !== void 0
        ? this.$scopedSlots.default()
        : [];

      if (this.body !== void 0) {
        defSlot.unshift(this.body);
      }

      if (this.heading === true) {
        var content$1 = [
          h('div'),
          h('div'),
          h(
            this.tag,
            { staticClass: 'q-timeline__heading-title' },
            defSlot
          )
        ];

        return h('div', {
          staticClass: 'q-timeline__heading',
          on: this.$listeners
        }, this.reverse === true ? content$1.reverse() : content$1)
      }

      var dot;

      if (this.icon !== void 0) {
        dot = [
          h(QIcon, {
            staticClass: 'row items-center justify-center',
            props: { name: this.icon }
          })
        ];
      }
      else if (this.avatar !== void 0) {
        dot = [
          h('img', {
            staticClass: 'q-timeline__dot-img',
            domProps: { src: this.avatar }
          })
        ];
      }

      var content = [
        h('div', { staticClass: 'q-timeline__subtitle' }, [
          h(
            'span',
            this.$scopedSlots.subtitle !== void 0
              ? this.$scopedSlots.subtitle()
              : [ this.subtitle ]
          )
        ]),

        h('div', {
          staticClass: 'q-timeline__dot',
          class: this.colorClass
        }, dot),

        h('div', { staticClass: 'q-timeline__content' }, [
          h(
            'h6',
            { staticClass: 'q-timeline__title' },
            this.$scopedSlots.title !== void 0
              ? this.$scopedSlots.title()
              : [ this.title ]
          )
        ].concat(defSlot))
      ];

      return h('li', {
        staticClass: 'q-timeline__entry',
        class: this.classes,
        on: this.$listeners
      }, this.reverse === true ? content.reverse() : content)
    }
  });

  var QToolbar = Vue.extend({
    name: 'QToolbar',

    props: {
      inset: Boolean
    },

    render: function render (h) {
      return h('div', {
        staticClass: 'q-toolbar row no-wrap items-center',
        class: this.inset ? 'q-toolbar--inset' : null,
        on: this.$listeners
      }, slot(this, 'default'))
    }
  });

  var QToolbarTitle = Vue.extend({
    name: 'QToolbarTitle',

    props: {
      shrink: Boolean
    },

    render: function render (h) {
      return h('div', {
        staticClass: 'q-toolbar__title ellipsis',
        class: this.shrink === true ? 'col-shrink' : null,
        on: this.$listeners
      }, slot(this, 'default'))
    }
  });

  var QTree = Vue.extend({
    name: 'QTree',

    props: {
      nodes: {
        type: Array,
        required: true
      },
      nodeKey: {
        type: String,
        required: true
      },
      labelKey: {
        type: String,
        default: 'label'
      },

      color: String,
      controlColor: String,
      textColor: String,
      selectedColor: String,
      dark: Boolean,

      icon: String,

      tickStrategy: {
        type: String,
        default: 'none',
        validator: function (v) { return ['none', 'strict', 'leaf', 'leaf-filtered'].includes(v); }
      },
      ticked: Array, // sync
      expanded: Array, // sync
      selected: {}, // sync

      defaultExpandAll: Boolean,
      accordion: Boolean,

      filter: String,
      filterMethod: {
        type: Function,
        default: function default$1 (node, filter) {
          var filt = filter.toLowerCase();
          return node[this.labelKey] &&
            node[this.labelKey].toLowerCase().indexOf(filt) > -1
        }
      },

      duration: Number,

      noNodesLabel: String,
      noResultsLabel: String
    },

    computed: {
      classes: function classes () {
        var obj;

        return ( obj = {}, obj[("text-" + (this.color))] = this.color, obj['q-tree--dark'] = this.dark, obj )
      },

      hasSelection: function hasSelection () {
        return this.selected !== void 0
      },

      computedIcon: function computedIcon () {
        return this.icon || this.$q.iconSet.tree.icon
      },

      computedControlColor: function computedControlColor () {
        return this.controlColor || this.color
      },

      textColorClass: function textColorClass () {
        if (this.textColor !== void 0) {
          return ("text-" + (this.textColor))
        }
      },

      selectedColorClass: function selectedColorClass () {
        var color = this.selectedColor || this.color;
        if (color) {
          return ("text-" + color)
        }
      },

      meta: function meta () {
        var this$1 = this;

        var meta = {};

        var travel = function (node, parent) {
          var tickStrategy = node.tickStrategy || (parent ? parent.tickStrategy : this$1.tickStrategy);
          var
            key = node[this$1.nodeKey],
            isParent = node.children && node.children.length > 0,
            isLeaf = !isParent,
            selectable = !node.disabled && this$1.hasSelection && node.selectable !== false,
            expandable = !node.disabled && node.expandable !== false,
            hasTicking = tickStrategy !== 'none',
            strictTicking = tickStrategy === 'strict',
            leafFilteredTicking = tickStrategy === 'leaf-filtered',
            leafTicking = tickStrategy === 'leaf' || tickStrategy === 'leaf-filtered';

          var tickable = !node.disabled && node.tickable !== false;
          if (leafTicking && tickable && parent && !parent.tickable) {
            tickable = false;
          }

          var lazy = node.lazy;
          if (lazy && this$1.lazy[key]) {
            lazy = this$1.lazy[key];
          }

          var m = {
            key: key,
            parent: parent,
            isParent: isParent,
            isLeaf: isLeaf,
            lazy: lazy,
            disabled: node.disabled,
            link: !node.disabled && (selectable || (expandable && (isParent || lazy === true))),
            children: [],
            matchesFilter: this$1.filter ? this$1.filterMethod(node, this$1.filter) : true,

            selected: key === this$1.selected && selectable,
            selectable: selectable,
            expanded: isParent ? this$1.innerExpanded.includes(key) : false,
            expandable: expandable,
            noTick: node.noTick || (!strictTicking && lazy && lazy !== 'loaded'),
            tickable: tickable,
            tickStrategy: tickStrategy,
            hasTicking: hasTicking,
            strictTicking: strictTicking,
            leafFilteredTicking: leafFilteredTicking,
            leafTicking: leafTicking,
            ticked: strictTicking
              ? this$1.innerTicked.includes(key)
              : (isLeaf ? this$1.innerTicked.includes(key) : false)
          };

          meta[key] = m;

          if (isParent) {
            m.children = node.children.map(function (n) { return travel(n, m); });

            if (this$1.filter) {
              if (!m.matchesFilter) {
                m.matchesFilter = m.children.some(function (n) { return n.matchesFilter; });
              }
              if (
                m.matchesFilter &&
                !m.noTick &&
                !m.disabled &&
                m.tickable &&
                leafFilteredTicking &&
                m.children.every(function (n) { return !n.matchesFilter || n.noTick || !n.tickable; })
              ) {
                m.tickable = false;
              }
            }

            if (m.matchesFilter) {
              if (!m.noTick && !strictTicking && m.children.every(function (n) { return n.noTick; })) {
                m.noTick = true;
              }

              if (leafTicking) {
                m.ticked = false;
                m.indeterminate = m.children.some(function (node) { return node.indeterminate; });

                if (!m.indeterminate) {
                  var sel = m.children
                    .reduce(function (acc, meta) { return meta.ticked ? acc + 1 : acc; }, 0);

                  if (sel === m.children.length) {
                    m.ticked = true;
                  }
                  else if (sel > 0) {
                    m.indeterminate = true;
                  }
                }
              }
            }
          }

          return m
        };

        this.nodes.forEach(function (node) { return travel(node, null); });
        return meta
      }
    },

    data: function data () {
      return {
        lazy: {},
        innerTicked: this.ticked || [],
        innerExpanded: this.expanded || []
      }
    },

    watch: {
      ticked: function ticked (val) {
        this.innerTicked = val;
      },

      expanded: function expanded (val) {
        this.innerExpanded = val;
      }
    },

    methods: {
      getNodeByKey: function getNodeByKey (key) {
        var this$1 = this;

        var reduce = [].reduce;

        var find = function (result, node) {
          if (result || !node) {
            return result
          }
          if (Array.isArray(node)) {
            return reduce.call(Object(node), find, result)
          }
          if (node[this$1.nodeKey] === key) {
            return node
          }
          if (node.children) {
            return find(null, node.children)
          }
        };

        return find(null, this.nodes)
      },

      getTickedNodes: function getTickedNodes () {
        var this$1 = this;

        return this.innerTicked.map(function (key) { return this$1.getNodeByKey(key); })
      },

      getExpandedNodes: function getExpandedNodes () {
        var this$1 = this;

        return this.innerExpanded.map(function (key) { return this$1.getNodeByKey(key); })
      },

      isExpanded: function isExpanded (key) {
        return key && this.meta[key]
          ? this.meta[key].expanded
          : false
      },

      collapseAll: function collapseAll () {
        if (this.expanded !== void 0) {
          this.$emit('update:expanded', []);
        }
        else {
          this.innerExpanded = [];
        }
      },

      expandAll: function expandAll () {
        var this$1 = this;

        var
          expanded = this.innerExpanded,
          travel = function (node) {
            if (node.children && node.children.length > 0) {
              if (node.expandable !== false && node.disabled !== true) {
                expanded.push(node[this$1.nodeKey]);
                node.children.forEach(travel);
              }
            }
          };

        this.nodes.forEach(travel);

        if (this.expanded !== void 0) {
          this.$emit('update:expanded', expanded);
        }
        else {
          this.innerExpanded = expanded;
        }
      },

      setExpanded: function setExpanded (key, state, node, meta) {
        var this$1 = this;
        if ( node === void 0 ) node = this.getNodeByKey(key);
        if ( meta === void 0 ) meta = this.meta[key];

        if (meta.lazy && meta.lazy !== 'loaded') {
          if (meta.lazy === 'loading') {
            return
          }

          this.$set(this.lazy, key, 'loading');
          this.$emit('lazy-load', {
            node: node,
            key: key,
            done: function (children) {
              this$1.lazy[key] = 'loaded';
              if (children) {
                this$1.$set(node, 'children', children);
              }
              this$1.$nextTick(function () {
                var m = this$1.meta[key];
                if (m && m.isParent) {
                  this$1.__setExpanded(key, true);
                }
              });
            },
            fail: function () {
              this$1.$delete(this$1.lazy, key);
            }
          });
        }
        else if (meta.isParent && meta.expandable) {
          this.__setExpanded(key, state);
        }
      },

      __setExpanded: function __setExpanded (key, state) {
        var this$1 = this;

        var target = this.innerExpanded;
        var emit = this.expanded !== void 0;

        if (emit === true) {
          target = target.slice();
        }

        if (state) {
          if (this.accordion) {
            if (this.meta[key]) {
              var collapse = [];
              if (this.meta[key].parent) {
                this.meta[key].parent.children.forEach(function (m) {
                  if (m.key !== key && m.expandable) {
                    collapse.push(m.key);
                  }
                });
              }
              else {
                this.nodes.forEach(function (node) {
                  var k = node[this$1.nodeKey];
                  if (k !== key) {
                    collapse.push(k);
                  }
                });
              }
              if (collapse.length > 0) {
                target = target.filter(function (k) { return !collapse.includes(k); });
              }
            }
          }

          target = target.concat([ key ])
            .filter(function (key, index, self) { return self.indexOf(key) === index; });
        }
        else {
          target = target.filter(function (k) { return k !== key; });
        }

        if (emit === true) {
          this.$emit("update:expanded", target);
        }
        else {
          this.innerExpanded = target;
        }
      },

      isTicked: function isTicked (key) {
        return key && this.meta[key]
          ? this.meta[key].ticked
          : false
      },

      setTicked: function setTicked (keys, state) {
        var target = this.innerTicked;
        var emit = this.ticked !== void 0;

        if (emit === true) {
          target = target.slice();
        }

        if (state) {
          target = target.concat(keys)
            .filter(function (key, index, self) { return self.indexOf(key) === index; });
        }
        else {
          target = target.filter(function (k) { return !keys.includes(k); });
        }

        if (emit === true) {
          this.$emit("update:ticked", target);
        }
      },

      __getSlotScope: function __getSlotScope (node, meta, key) {
        var this$1 = this;

        var scope = { tree: this, node: node, key: key, color: this.color, dark: this.dark };

        Object.defineProperty(scope, 'expanded', {
          get: function () { return meta.expanded },
          set: function (val) { val !== meta.expanded && this$1.setExpanded(key, val); },
          configurable: true,
          enumerable: true
        });
        Object.defineProperty(scope, 'ticked', {
          get: function () { return meta.ticked },
          set: function (val) { val !== meta.ticked && this$1.setTicked([ key ], val); },
          configurable: true,
          enumerable: true
        });

        return scope
      },

      __getChildren: function __getChildren (h, nodes) {
        var this$1 = this;

        return (
          this.filter
            ? nodes.filter(function (n) { return this$1.meta[n[this$1.nodeKey]].matchesFilter; })
            : nodes
        ).map(function (child) { return this$1.__getNode(h, child); })
      },

      __getNodeMedia: function __getNodeMedia (h, node) {
        if (node.icon !== void 0) {
          return h(QIcon, {
            staticClass: "q-tree__icon q-mr-sm",
            props: { name: node.icon, color: node.iconColor }
          })
        }
        var src = node.img || node.avatar;
        if (src) {
          return h('img', {
            staticClass: ("q-tree__" + (node.img ? 'img' : 'avatar') + " q-mr-sm"),
            attrs: { src: src }
          })
        }
      },

      __getNode: function __getNode (h, node) {
        var this$1 = this;

        var
          key = node[this.nodeKey],
          meta = this.meta[key],
          header = node.header
            ? this.$scopedSlots[("header-" + (node.header))] || this.$scopedSlots['default-header']
            : this.$scopedSlots['default-header'];

        var children = meta.isParent
          ? this.__getChildren(h, node.children)
          : [];

        var isParent = children.length > 0 || (meta.lazy && meta.lazy !== 'loaded');

        var
          body = node.body
            ? this.$scopedSlots[("body-" + (node.body))] || this.$scopedSlots['default-body']
            : this.$scopedSlots['default-body'],
          slotScope = header || body
            ? this.__getSlotScope(node, meta, key)
            : null;

        if (body !== void 0) {
          body = h('div', { staticClass: 'q-tree__node-body relative-position' }, [
            h('div', { class: this.textColorClass }, [
              body(slotScope)
            ])
          ]);
        }

        return h('div', {
          key: key,
          staticClass: 'q-tree__node relative-position',
          class: { 'q-tree__node--parent': isParent, 'q-tree__node--child': !isParent }
        }, [
          h('div', {
            staticClass: 'q-tree__node-header relative-position row no-wrap items-center',
            class: {
              'q-tree__node--link q-hoverable q-focusable': meta.link,
              'q-tree__node--selected': meta.selected,
              disabled: meta.disabled
            },
            attrs: { tabindex: meta.link ? 0 : -1 },
            on: {
              click: function (e) {
                this$1.__onClick(node, meta, e);
              },
              keypress: function (e) {
                if (e.keyCode === 13) { this$1.__onClick(node, meta, e, true); }
                else if (e.keyCode === 32) { this$1.__onExpandClick(node, meta, e, true); }
              }
            }
          }, [
            h('div', { staticClass: 'q-focus-helper', attrs: { tabindex: -1 }, ref: ("blurTarget_" + (meta.key)) }),

            meta.lazy === 'loading'
              ? h(QSpinner, {
                staticClass: 'q-tree__spinner q-mr-xs',
                props: { color: this.computedControlColor }
              })
              : (
                isParent === true
                  ? h(QIcon, {
                    staticClass: 'q-tree__arrow q-mr-xs',
                    class: { 'q-tree__arrow--rotate': meta.expanded },
                    props: { name: this.computedIcon },
                    nativeOn: {
                      click: function (e) {
                        this$1.__onExpandClick(node, meta, e);
                      }
                    }
                  })
                  : null
              ),

            meta.hasTicking && !meta.noTick
              ? h(QCheckbox, {
                staticClass: 'q-mr-xs',
                props: {
                  value: meta.indeterminate ? null : meta.ticked,
                  color: this.computedControlColor,
                  dark: this.dark,
                  dense: true,
                  keepColor: true,
                  disable: !meta.tickable
                },
                on: {
                  keydown: stopAndPrevent,
                  input: function (v) {
                    this$1.__onTickedClick(meta, v);
                  }
                }
              })
              : null,

            h('div', {
              'staticClass': 'q-tree__node-header-content col row no-wrap items-center',
              class: meta.selected ? this.selectedColorClass : this.textColorClass
            }, [
              header
                ? header(slotScope)
                : [
                  this.__getNodeMedia(h, node),
                  h('div', node[this.labelKey])
                ]
            ])
          ]),

          isParent === true
            ? h(QSlideTransition, {
              props: { duration: this.duration }
            }, [
              h('div', {
                staticClass: 'q-tree__node-collapsible',
                class: this.textColorClass,
                directives: [{ name: 'show', value: meta.expanded }]
              }, [
                body,

                h('div', {
                  staticClass: 'q-tree__children',
                  class: { disabled: meta.disabled }
                }, children)
              ])
            ])
            : body
        ])
      },

      __blur: function __blur (key) {
        var blurTarget = this.$refs[("blurTarget_" + key)];
        blurTarget !== void 0 && blurTarget.focus();
      },

      __onClick: function __onClick (node, meta, e, keyboard) {
        keyboard !== true && this.__blur(meta.key);

        if (this.hasSelection) {
          if (meta.selectable) {
            this.$emit('update:selected', meta.key !== this.selected ? meta.key : null);
          }
        }
        else {
          this.__onExpandClick(node, meta, e, keyboard);
        }

        if (typeof node.handler === 'function') {
          node.handler(node);
        }
      },

      __onExpandClick: function __onExpandClick (node, meta, e, keyboard) {
        if (e !== void 0) {
          stopAndPrevent(e);
        }
        keyboard !== true && this.__blur(meta.key);
        this.setExpanded(meta.key, !meta.expanded, node, meta);
      },

      __onTickedClick: function __onTickedClick (meta, state) {
        if (meta.indeterminate && state) {
          state = false;
        }
        if (meta.strictTicking) {
          this.setTicked([ meta.key ], state);
        }
        else if (meta.leafTicking) {
          var keys = [];
          var travel = function (meta) {
            if (meta.isParent) {
              if (!state && !meta.noTick && meta.tickable) {
                keys.push(meta.key);
              }
              if (meta.leafTicking) {
                meta.children.forEach(travel);
              }
            }
            else if (!meta.noTick && meta.tickable && (!meta.leafFilteredTicking || meta.matchesFilter)) {
              keys.push(meta.key);
            }
          };
          travel(meta);
          this.setTicked(keys, state);
        }
      }
    },

    render: function render (h) {
      var children = this.__getChildren(h, this.nodes);

      return h(
        'div', {
          staticClass: 'q-tree',
          class: this.classes
        },
        children.length === 0
          ? (
            this.filter
              ? this.noResultsLabel || this.$q.lang.tree.noResults
              : this.noNodesLabel || this.$q.lang.tree.noNodes
          )
          : children
      )
    },

    created: function created () {
      this.defaultExpandAll === true && this.expandAll();
    }
  });

  var QUploaderBase = {
    props: {
      label: String,

      color: String,
      textColor: String,

      dark: Boolean,

      square: Boolean,
      flat: Boolean,
      bordered: Boolean,

      multiple: Boolean,
      accept: String,
      maxFileSize: Number,
      maxTotalSize: Number,
      filter: Function,
      noThumbnails: Boolean,
      autoUpload: Boolean,
      hideUploadBtn: Boolean,

      disable: Boolean,
      readonly: Boolean
    },

    provide: function provide () {
      return {
        __qUploaderGetInput: this.__getInputControl
      }
    },

    data: function data () {
      return {
        files: [],
        queuedFiles: [],
        uploadedFiles: [],
        dnd: false,
        expanded: false,

        uploadSize: 0,
        uploadedSize: 0
      }
    },

    watch: {
      isUploading: function isUploading (newVal, oldVal) {
        if (oldVal === false && newVal === true) {
          this.$emit('start');
        }
        else if (oldVal === true && newVal === false) {
          this.$emit('finish');
        }
      }
    },

    computed: {
      /*
       * When extending:
       *   Required : isUploading
       *   Optional: isBusy
       */

      canUpload: function canUpload () {
        return this.editable === true &&
          this.isBusy !== true &&
          this.isUploading !== true &&
          this.queuedFiles.length > 0
      },

      canAddFiles: function canAddFiles () {
        return this.editable &&
          this.isUploading !== true &&
          (this.multiple === true || this.queuedFiles.length === 0)
      },

      extensions: function extensions () {
        if (this.accept !== void 0) {
          return this.accept.split(',').map(function (ext) {
            ext = ext.trim();
            // support "image/*"
            if (ext.endsWith('/*')) {
              ext = ext.slice(0, ext.length - 1);
            }
            return ext
          })
        }
      },

      uploadProgress: function uploadProgress () {
        return this.uploadSize === 0
          ? 0
          : this.uploadedSize / this.uploadSize
      },

      uploadProgressLabel: function uploadProgressLabel () {
        return this.__getProgressLabel(this.uploadProgress)
      },

      uploadedSizeLabel: function uploadedSizeLabel () {
        return humanStorageSize(this.uploadedSize)
      },

      uploadSizeLabel: function uploadSizeLabel () {
        return humanStorageSize(this.uploadSize)
      },

      colorClass: function colorClass () {
        var cls = [];
        this.color !== void 0 && cls.push(("bg-" + (this.color)));
        this.textColor !== void 0 && cls.push(("text-" + (this.textColor)));
        return cls.join(' ')
      },

      editable: function editable () {
        return this.disable !== true && this.readonly !== true
      }
    },

    methods: {
      pickFiles: function pickFiles (e) {
        if (this.editable) {
          var input = this.__getFileInput();
          input && input.click(e);
        }
      },

      addFiles: function addFiles (files) {
        if (this.editable && files) {
          this.__addFiles(null, files);
        }
      },

      reset: function reset () {
        if (!this.disable) {
          this.abort();
          this.uploadedSize = 0;
          this.uploadSize = 0;
          this.files = [];
          this.queuedFiles = [];
          this.uploadedFiles = [];
        }
      },

      removeUploadedFiles: function removeUploadedFiles () {
        if (!this.disable) {
          this.files = this.files.filter(function (f) { return f.__status !== 'uploaded'; });
          this.uploadedFiles = [];
        }
      },

      removeQueuedFiles: function removeQueuedFiles () {
        var this$1 = this;

        if (!this.disable) {
          var removedFiles = [];

          this.files.forEach(function (file) {
            if (file.__status === 'idle' || file.__status === 'failed') {
              this$1.uploadSize -= file.size;
              removedFiles.push(file);
            }
          });

          if (removedFiles.length > 0) {
            this.files = this.files.filter(function (f) { return f.__status !== 'idle' && f.__status !== 'failed'; });
            this.queuedFiles = [];
            this.$emit('removed', removedFiles);
          }
        }
      },

      removeFile: function removeFile (file) {
        if (this.disable) { return }

        if (file.__status === 'uploaded') {
          this.uploadedFiles = this.uploadedFiles.filter(function (f) { return f.name !== file.name; });
        }
        else if (file.__status === 'uploading') {
          file.__abort();
        }
        else {
          this.uploadSize -= file.size;
        }

        this.files = this.files.filter(function (f) { return f.name !== file.name; });
        this.queuedFiles = this.queuedFiles.filter(function (f) { return f.name !== file.name; });
        this.$emit('removed', [ file ]);
      },

      __getFileInput: function __getFileInput () {
        return this.$refs.input ||
          this.$el.getElementsByClassName('q-uploader__input')[0]
      },

      __getProgressLabel: function __getProgressLabel (p) {
        return (p * 100).toFixed(2) + '%'
      },

      __updateFile: function __updateFile (file, status, uploadedSize) {
        file.__status = status;

        if (status === 'idle') {
          file.__uploaded = 0;
          file.__progress = 0;
          file.__sizeLabel = humanStorageSize(file.size);
          file.__progressLabel = '0.00%';
          return
        }
        if (status === 'failed') {
          this.$forceUpdate();
          return
        }

        file.__uploaded = status === 'uploaded'
          ? file.size
          : uploadedSize;

        file.__progress = status === 'uploaded'
          ? 1
          : Math.min(0.9999, file.__uploaded / file.size);

        file.__progressLabel = this.__getProgressLabel(file.__progress);
        this.$forceUpdate();
      },

      __addFiles: function __addFiles (e, files) {
        var this$1 = this;

        files = Array.prototype.slice.call(files || e.target.files);
        this.__getFileInput().value = '';

        // make sure we don't duplicate files
        files = files.filter(function (file) { return !this$1.files.some(function (f) { return file.name === f.name; }); });
        if (files.length === 0) { return }

        // filter file types
        if (this.accept !== void 0) {
          files = Array.prototype.filter.call(files, function (file) {
            return this$1.extensions.some(function (ext) { return (
              file.type.toUpperCase().startsWith(ext.toUpperCase()) ||
              file.name.toUpperCase().endsWith(ext.toUpperCase())
            ); })
          });
          if (files.length === 0) { return }
        }

        // filter max file size
        if (this.maxFileSize !== void 0) {
          files = Array.prototype.filter.call(files, function (file) { return file.size <= this$1.maxFileSize; });
          if (files.length === 0) { return }
        }

        // Cordova/iOS allows selecting multiple files even when the
        // multiple attribute is not specified. We also normalize drag'n'dropped
        // files here:
        if (this.multiple !== true) {
          files = [ files[0] ];
        }

        if (this.maxTotalSize !== void 0) {
          var size = 0;
          for (var i = 0; i < files.length; i++) {
            size += files[i].size;
            if (size > this.maxTotalSize) {
              if (i > 0) {
                files = files.slice(0, i - 1);
                break
              }
              else {
                return
              }
            }
          }
          if (files.length === 0) { return }
        }

        // do we have custom filter function?
        if (typeof this.filter === 'function') {
          files = this.filter(files);
        }

        if (files.length === 0) { return }

        var filesReady = []; // List of image load promises

        files.forEach(function (file) {
          this$1.__updateFile(file, 'idle');
          this$1.uploadSize += file.size;

          if (this$1.noThumbnails !== true && file.type.toUpperCase().startsWith('IMAGE')) {
            var reader = new FileReader();
            var p = new Promise(function (resolve, reject) {
              reader.onload = function (e) {
                var img = new Image();
                img.src = e.target.result;
                file.__img = img;
                resolve(true);
              };
              reader.onerror = function (e) { reject(e); };
            });

            reader.readAsDataURL(file);
            filesReady.push(p);
          }
        });

        Promise.all(filesReady).then(function () {
          this$1.files = this$1.files.concat(files);
          this$1.queuedFiles = this$1.queuedFiles.concat(files);
          this$1.$emit('added', files);
          this$1.autoUpload === true && this$1.upload();
        });
      },

      __onDragOver: function __onDragOver (e) {
        stopAndPrevent(e);
        this.dnd = true;
      },

      __onDragLeave: function __onDragLeave (e) {
        stopAndPrevent(e);
        this.dnd = false;
      },

      __onDrop: function __onDrop (e) {
        stopAndPrevent(e);
        var files = e.dataTransfer.files;

        if (files.length > 0) {
          this.__addFiles(null, files);
        }

        this.dnd = false;
      },

      __getBtn: function __getBtn (h, show, icon, fn) {
        if (show === true) {
          return h(QBtn, {
            props: {
              type: 'a',
              icon: this.$q.iconSet.uploader[icon],
              flat: true,
              dense: true
            },
            on: icon === 'add' ? null : { click: fn }
          }, icon === 'add' ? this.__getInputControl(h) : null)
        }
      },

      __getInputControl: function __getInputControl (h) {
        return [
          h('input', {
            ref: 'input',
            staticClass: 'q-uploader__input overflow-hidden absolute-full',
            attrs: Object.assign({}, {type: 'file',
              title: '', // try to remove default tooltip
              accept: this.accept},
              (this.multiple === true ? { multiple: true } : {})),
            on: {
              change: this.__addFiles
            }
          })
        ]
      },

      __getHeader: function __getHeader (h) {
        if (this.$scopedSlots.header !== void 0) {
          return this.$scopedSlots.header(this)
        }

        return h('div', {
          staticClass: 'q-uploader__header-content flex flex-center no-wrap q-gutter-xs'
        }, [
          this.__getBtn(h, this.queuedFiles.length > 0, 'removeQueue', this.removeQueuedFiles),
          this.__getBtn(h, this.uploadedFiles.length > 0, 'removeUploaded', this.removeUploadedFiles),

          this.isUploading === true
            ? h(QSpinner, { staticClass: 'q-uploader__spinner' })
            : null,

          h('div', { staticClass: 'col column justify-center' }, [
            this.label !== void 0
              ? h('div', { staticClass: 'q-uploader__title' }, [ this.label ])
              : null,

            h('div', { staticClass: 'q-uploader__subtitle' }, [
              this.uploadSizeLabel + ' / ' + this.uploadProgressLabel
            ])
          ]),

          this.__getBtn(h, this.canAddFiles, 'add', this.pickFiles),
          this.__getBtn(h, this.hideUploadBtn === false && this.canUpload === true, 'upload', this.upload),
          this.__getBtn(h, this.isUploading, 'clear', this.abort)
        ])
      },

      __getList: function __getList (h) {
        var this$1 = this;

        if (this.$scopedSlots.list !== void 0) {
          return this.$scopedSlots.list(this)
        }

        return this.files.map(function (file) { return h('div', {
          key: file.name,
          staticClass: 'q-uploader__file relative-position',
          class: {
            'q-uploader__file--img': file.__img !== void 0,
            'q-uploader__file--failed': file.__status === 'failed',
            'q-uploader__file--uploaded': file.__status === 'uploaded'
          },
          style: file.__img !== void 0 ? {
            backgroundImage: 'url(' + file.__img.src + ')'
          } : null
        }, [
          h('div', {
            staticClass: 'q-uploader__file-header row flex-center no-wrap'
          }, [
            file.__status === 'failed'
              ? h(QIcon, {
                staticClass: 'q-uploader__file-status',
                props: {
                  name: this$1.$q.iconSet.type.negative,
                  color: 'negative'
                }
              })
              : null,

            h('div', { staticClass: 'q-uploader__file-header-content col' }, [
              h('div', { staticClass: 'q-uploader__title' }, [ file.name ]),
              h('div', {
                staticClass: 'q-uploader__subtitle row items-center no-wrap'
              }, [
                file.__sizeLabel + ' / ' + file.__progressLabel
              ])
            ]),

            file.__status === 'uploading'
              ? h(QCircularProgress, {
                props: {
                  value: file.__progress,
                  min: 0,
                  max: 1,
                  indeterminate: file.__progress === 0
                }
              })
              : h(QBtn, {
                props: {
                  round: true,
                  dense: true,
                  flat: true,
                  icon: this$1.$q.iconSet.uploader[file.__status === 'uploaded' ? 'done' : 'clear']
                },
                on: {
                  click: function () { this$1.removeFile(file); }
                }
              })
          ])
        ]); })
      }
    },

    beforeDestroy: function beforeDestroy () {
      this.isUploading === true && this.abort();
    },

    render: function render (h) {
      return h('div', {
        staticClass: 'q-uploader column no-wrap',
        class: {
          'q-uploader--dark': this.dark,
          'q-uploader--bordered': this.bordered,
          'q-uploader--square no-border-radius': this.square,
          'q-uploader--flat no-shadow': this.flat,
          'disabled q-uploader--disable': this.disable
        },
        on: this.editable === true && this.isUploading !== true
          ? { dragover: this.__onDragOver }
          : null
      }, [
        h('div', {
          staticClass: 'q-uploader__header',
          class: this.colorClass
        }, [
          this.__getHeader(h)
        ]),

        h('div', {
          staticClass: 'q-uploader__list scroll'
        }, this.__getList(h)),

        this.dnd === true ? h('div', {
          staticClass: 'q-uploader__dnd absolute-full',
          on: {
            dragenter: stopAndPrevent,
            dragover: stopAndPrevent,
            dragleave: this.__onDragLeave,
            drop: this.__onDrop
          }
        }) : null,

        this.isBusy === true ? h('div', {
          staticClass: 'q-uploader__overlay absolute-full flex flex-center'
        }, [
          h(QSpinner)
        ]) : null
      ])
    }
  };

  function getFn (prop) {
    return typeof prop === 'function'
      ? prop
      : function () { return prop; }
  }

  var UploaderXHRMixin = {
    props: {
      url: [Function, String],
      method: {
        type: [Function, String],
        default: 'POST'
      },
      fieldName: {
        type: [Function, String],
        default: function (file) { return file.name; }
      },
      headers: [Function, Array],
      formFields: [Function, Array],
      withCredentials: [Function, Boolean],
      sendRaw: [Function, Boolean],

      batch: [Function, Boolean],
      factory: Function
    },

    data: function data () {
      return {
        xhrs: [],
        promises: [],
        workingThreads: 0
      }
    },

    computed: {
      xhrProps: function xhrProps () {
        return {
          url: getFn(this.url),
          method: getFn(this.method),
          headers: getFn(this.headers),
          formFields: getFn(this.formFields),
          fieldName: getFn(this.fieldName),
          withCredentials: getFn(this.withCredentials),
          sendRaw: getFn(this.sendRaw),
          batch: getFn(this.batch)
        }
      },

      isUploading: function isUploading () {
        return this.workingThreads > 0
      },

      isBusy: function isBusy () {
        return this.promises.length > 0
      }
    },

    methods: {
      abort: function abort () {
        this.xhrs.forEach(function (x) { x.abort(); });

        if (this.promises.length > 0) {
          this.abortPromises = true;
        }
      },

      upload: function upload () {
        var this$1 = this;

        if (this.canUpload === false) {
          return
        }

        var queue = this.queuedFiles.slice(0);
        this.queuedFiles = [];

        if (this.xhrProps.batch(queue)) {
          this.__runFactory(queue);
        }
        else {
          queue.forEach(function (file) {
            this$1.__runFactory([ file ]);
          });
        }
      },

      __runFactory: function __runFactory (files) {
        var this$1 = this;

        this.workingThreads++;

        if (typeof this.factory !== 'function') {
          this.__uploadFiles(files, {});
          return
        }

        var res = this.factory(files);

        if (!res) {
          this.$emit(
            'factory-failed',
            new Error('QUploader: factory() does not return properly'),
            files
          );
          this.workingThreads--;
        }
        else if (typeof res.catch === 'function' && typeof res.then === 'function') {
          this.promises.push(res);

          var failed = function (err) {
            if (this$1._isBeingDestroyed !== true && this$1._isDestroyed !== true) {
              this$1.promises = this$1.promises.filter(function (p) { return p !== res; });

              if (this$1.promises.length === 0) {
                this$1.abortPromises = false;
              }

              this$1.queuedFiles = this$1.queuedFiles.concat(files);
              files.forEach(function (f) { this$1.__updateFile(f, 'failed'); });

              this$1.$emit('factory-failed', err, files);
              this$1.workingThreads--;
            }
          };

          res.then(function (factory) {
            if (this$1.abortPromises === true) {
              failed(new Error('Aborted'));
            }
            else if (this$1._isBeingDestroyed !== true && this$1._isDestroyed !== true) {
              this$1.promises = this$1.promises.filter(function (p) { return p !== res; });
              this$1.__uploadFiles(files, factory);
            }
          }).catch(failed);
        }
        else {
          this.__uploadFiles(files, res || {});
        }
      },

      __uploadFiles: function __uploadFiles (files, factory) {
        var this$1 = this;

        var
          form = new FormData(),
          xhr = new XMLHttpRequest();

        var getProp = function (name, arg) {
          return factory[name] !== void 0
            ? getFn(factory[name])(arg)
            : this$1.xhrProps[name](arg)
        };

        var url = getProp('url', files);

        if (!url) {
          console.error('q-uploader: invalid or no URL specified');
          this.workingThreads--;
          return
        }

        var fields = getProp('formFields', files);
        fields !== void 0 && fields.forEach(function (field) {
          form.append(field.name, field.value);
        });

        var
          uploadIndex = 0,
          uploadIndexSize = 0,
          uploadedSize = 0,
          maxUploadSize = 0,
          aborted;

        xhr.upload.addEventListener('progress', function (e) {
          if (aborted === true) { return }

          var loaded = Math.min(maxUploadSize, e.loaded);

          this$1.uploadedSize += loaded - uploadedSize;
          uploadedSize = loaded;

          var size = uploadedSize - uploadIndexSize;
          for (var i = uploadIndex; size > 0 && i < files.length; i++) {
            var
              file = files[i],
              uploaded = size > file.size;

            if (uploaded) {
              size -= file.size;
              uploadIndex++;
              uploadIndexSize += file.size;
              this$1.__updateFile(file, 'uploading', file.size);
            }
            else {
              this$1.__updateFile(file, 'uploading', size);
              return
            }
          }
        }, false);

        xhr.onreadystatechange = function () {
          if (xhr.readyState < 4) {
            return
          }

          if (xhr.status && xhr.status < 400) {
            this$1.uploadedFiles = this$1.uploadedFiles.concat(files);
            files.forEach(function (f) { this$1.__updateFile(f, 'uploaded'); });
            this$1.$emit('uploaded', { files: files, xhr: xhr });
          }
          else {
            aborted = true;
            this$1.uploadedSize -= uploadedSize;
            this$1.queuedFiles = this$1.queuedFiles.concat(files);
            files.forEach(function (f) { this$1.__updateFile(f, 'failed'); });
            this$1.$emit('failed', { files: files, xhr: xhr });
          }

          this$1.workingThreads--;
          this$1.xhrs = this$1.xhrs.filter(function (x) { return x !== xhr; });
        };

        xhr.open(
          getProp('method', files),
          url
        );

        if (getProp('withCredentials', files) === true) {
          xhr.withCredentials = true;
        }

        var headers = getProp('headers', files);
        headers !== void 0 && headers.forEach(function (head) {
          xhr.setRequestHeader(head.name, head.value);
        });

        var sendRaw = getProp('sendRaw', files);

        files.forEach(function (file) {
          this$1.__updateFile(file, 'uploading', 0);
          if (sendRaw !== true) {
            form.append(getProp('fieldName', file), file);
          }
          file.xhr = xhr;
          file.__abort = xhr.abort;
          maxUploadSize += file.size;
        });

        this.$emit('uploading', { files: files, xhr: xhr });
        this.xhrs.push(xhr);

        if (sendRaw === true) {
          xhr.send(new Blob(files));
        }
        else {
          xhr.send(form);
        }
      }
    }
  };

  var QUploader = Vue.extend({
    name: 'QUploader',
    mixins: [ QUploaderBase, UploaderXHRMixin ]
  });

  var QUploaderAddTrigger = Vue.extend({
    name: 'QUploaderAddTrigger',

    inject: {
      __qUploaderGetInput: {
        default: function default$1 () {
          console.error('QUploaderAddTrigger needs to be child of QUploader');
        }
      }
    },

    render: function render (h) {
      return this.__qUploaderGetInput(h)
    }
  });

  var QVideo = Vue.extend({
    name: 'QVideo',

    props: {
      src: {
        type: String,
        required: true
      }
    },

    computed: {
      iframeData: function iframeData () {
        return {
          attrs: {
            src: this.src,
            frameborder: '0',
            allowfullscreen: true
          }
        }
      }
    },

    render: function render (h) {
      return h('div', {
        staticClass: 'q-video',
        on: this.$listeners
      }, [
        h('iframe', this.iframeData)
      ])
    }
  });



  var components$1 = /*#__PURE__*/Object.freeze({
    QResizeObserver: QResizeObserver,
    QScrollObserver: QScrollObserver,
    QAjaxBar: QAjaxBar,
    QBadge: QBadge,
    QBanner: QBanner,
    QBar: QBar,
    QBreadcrumbs: QBreadcrumbs,
    QBreadcrumbsEl: QBreadcrumbsEl,
    QBtn: QBtn,
    QBtnGroup: QBtnGroup,
    QBtnDropdown: QBtnDropdown,
    QBtnToggle: QBtnToggle,
    QCard: QCard,
    QCardSection: QCardSection,
    QCardActions: QCardActions,
    QCarousel: QCarousel,
    QCarouselSlide: QCarouselSlide,
    QCarouselControl: QCarouselControl,
    QChatMessage: QChatMessage,
    QCheckbox: QCheckbox,
    QChip: QChip,
    QCircularProgress: QCircularProgress,
    QColor: QColor,
    QDate: QDate,
    QTime: QTime,
    QDialog: QDialog,
    QEditor: QEditor,
    QFab: QFab,
    QFabAction: QFabAction,
    QField: QField,
    QForm: QForm,
    QIcon: QIcon,
    QImg: QImg,
    QInfiniteScroll: QInfiniteScroll,
    QInnerLoading: QInnerLoading,
    QInput: QInput,
    QKnob: QKnob,
    QLayout: QLayout,
    QDrawer: QDrawer,
    QFooter: QFooter,
    QHeader: QHeader,
    QPage: QPage,
    QPageContainer: QPageContainer,
    QPageSticky: QPageSticky,
    QList: QList,
    QItem: QItem,
    QItemSection: QItemSection,
    QItemLabel: QItemLabel,
    QExpansionItem: QExpansionItem,
    QSlideItem: QSlideItem,
    QMenu: QMenu,
    QNoSsr: QNoSsr,
    QAvatar: QAvatar,
    QOptionGroup: QOptionGroup,
    QPageScroller: QPageScroller,
    QPagination: QPagination,
    QParallax: QParallax,
    QPopupEdit: QPopupEdit,
    QPopupProxy: QPopupProxy,
    QLinearProgress: QLinearProgress,
    QPullToRefresh: QPullToRefresh,
    QRadio: QRadio,
    QRange: QRange,
    QRating: QRating,
    QScrollArea: QScrollArea,
    QSelect: QSelect,
    QSeparator: QSeparator,
    QSlideTransition: QSlideTransition,
    QSlider: QSlider,
    QSpace: QSpace,
    QSpinner: QSpinner,
    QSpinnerAudio: QSpinnerAudio,
    QSpinnerBall: QSpinnerBall,
    QSpinnerBars: QSpinnerBars,
    QSpinnerComment: QSpinnerComment,
    QSpinnerCube: QSpinnerCube,
    QSpinnerDots: QSpinnerDots,
    QSpinnerFacebook: QSpinnerFacebook,
    QSpinnerGears: QSpinnerGears,
    QSpinnerGrid: QSpinnerGrid,
    QSpinnerHearts: QSpinnerHearts,
    QSpinnerHourglass: QSpinnerHourglass,
    QSpinnerInfinity: QSpinnerInfinity,
    QSpinnerIos: QSpinnerIos,
    QSpinnerOval: QSpinnerOval,
    QSpinnerPie: QSpinnerPie,
    QSpinnerPuff: QSpinnerPuff,
    QSpinnerRadio: QSpinnerRadio,
    QSpinnerRings: QSpinnerRings,
    QSpinnerTail: QSpinnerTail,
    QSplitter: QSplitter,
    QStep: QStep,
    QStepper: QStepper,
    QStepperNavigation: QStepperNavigation,
    QTabPanels: QTabPanels,
    QTabPanel: QTabPanel,
    QTable: QTable,
    QTh: QTh,
    QTr: QTr,
    QTd: QTd,
    QMarkupTable: QMarkupTable,
    QTabs: QTabs,
    QTab: QTab,
    QRouteTab: QRouteTab,
    QTimeline: QTimeline,
    QTimelineEntry: QTimelineEntry,
    QToggle: QToggle,
    QToolbar: QToolbar,
    QToolbarTitle: QToolbarTitle,
    QTooltip: QTooltip,
    QTree: QTree,
    QUploader: QUploader,
    QUploaderBase: QUploaderBase,
    QUploaderAddTrigger: QUploaderAddTrigger,
    QVideo: QVideo
  });

  var ClosePopup = {
    name: 'close-popup',

    bind: function bind (el, ref, vnode) {
      var value = ref.value;

      var ctx = {
        enabled: value !== false,

        handler: function handler (evt) {
          // allow @click to be emitted
          ctx.enabled !== false && setTimeout(function () {
            var vm = (vnode.componentInstance || vnode.context).$root;
            vm.__qClosePopup !== void 0 && vm.__qClosePopup(evt);
          });
        },

        handlerKey: function handlerKey (evt) {
          evt.keyCode === 13 && ctx.handler(evt);
        }
      };

      if (el.__qclosepopup !== void 0) {
        el.__qclosepopup_old = el.__qclosepopup;
      }

      el.__qclosepopup = ctx;
      el.addEventListener('click', ctx.handler);
      el.addEventListener('keyup', ctx.handlerKey);
    },

    update: function update (el, ref) {
      var value = ref.value;

      if (el.__qclosepopup !== void 0) {
        el.__qclosepopup.enabled = value !== false;
      }
    },

    unbind: function unbind (el) {
      var ctx = el.__qclosepopup_old || el.__qclosepopup;
      if (ctx !== void 0) {
        el.removeEventListener('click', ctx.handler);
        el.removeEventListener('keyup', ctx.handlerKey);
        delete el[el.__qclosepopup_old ? '__qclosepopup_old' : '__qclosepopup'];
      }
    }
  };

  var GoBack = {
    name: 'go-back',

    bind: function bind (el, ref, vnode) {
      var value = ref.value;
      var modifiers = ref.modifiers;

      var ctx = {
        value: value,

        position: window.history.length - 1,
        single: modifiers.single,

        goBack: function goBack () {
          var router = vnode.context.$router;

          if (ctx.single) {
            router.go(-1);
          }
          else if (Platform.is.cordova === true) {
            router.go(ctx.position - window.history.length);
          }
          else {
            router.replace(ctx.value);
          }
        },

        goBackKey: function goBackKey (e) {
          // ENTER
          e.keyCode === 13 && ctx.goBack();
        }
      };

      if (el.__qgoback) {
        el.__qgoback_old = el.__qgoback;
      }

      el.__qgoback = ctx;
      el.addEventListener('click', ctx.goBack);
      el.addEventListener('keyup', ctx.goBackKey);
    },

    update: function update (el, ref) {
      var value = ref.value;
      var oldValue = ref.oldValue;

      if (value !== oldValue) {
        el.__qgoback.value = value;
      }
    },

    unbind: function unbind (el) {
      var ctx = el.__qgoback_old || el.__qgoback;
      if (ctx !== void 0) {
        el.removeEventListener('click', ctx.goBack);
        el.removeEventListener('keyup', ctx.goBackKey);
        delete el[el.__qgoback_old ? '__qgoback_old' : '__qgoback'];
      }
    }
  };

  function updateBinding (el, ref) {
    var value = ref.value;
    var oldValue = ref.oldValue;

    var ctx = el.__qscrollfire;

    if (typeof value !== 'function') {
      ctx.scrollTarget.removeEventListener('scroll', ctx.scroll);
      console.error('v-scroll-fire requires a function as parameter', el);
      return
    }

    ctx.handler = value;
    if (typeof oldValue !== 'function') {
      ctx.scrollTarget.addEventListener('scroll', ctx.scroll, listenOpts.passive);
      ctx.scroll();
    }
  }

  var ScrollFire = {
    name: 'scroll-fire',

    bind: function bind (el) {
      var ctx = {
        scroll: debounce(function () {
          var containerBottom, elBottom;

          if (ctx.scrollTarget === window) {
            elBottom = el.getBoundingClientRect().bottom;
            containerBottom = window.innerHeight;
          }
          else {
            elBottom = offset(el).top + height(el);
            containerBottom = offset(ctx.scrollTarget).top + height(ctx.scrollTarget);
          }

          if (elBottom > 0 && elBottom < containerBottom) {
            ctx.scrollTarget.removeEventListener('scroll', ctx.scroll, listenOpts.passive);
            ctx.handler(el);
          }
        }, 25)
      };

      if (el.__qscrollfire) {
        el.__qscrollfire_old = el.__qscrollfire;
      }

      el.__qscrollfire = ctx;
    },

    inserted: function inserted (el, binding) {
      var ctx = el.__qscrollfire;
      ctx.scrollTarget = getScrollTarget(el);
      updateBinding(el, binding);
    },

    update: function update (el, binding) {
      if (binding.value !== binding.oldValue) {
        updateBinding(el, binding);
      }
    },

    unbind: function unbind (el) {
      var ctx = el.__qscrollfire_old || el.__qscrollfire;
      if (ctx !== void 0) {
        ctx.scrollTarget.removeEventListener('scroll', ctx.scroll, listenOpts.passive);
        delete el[el.__qscrollfire_old ? '__qscrollfire_old' : '__qscrollfire'];
      }
    }
  };

  function updateBinding$1 (el, ref) {
    var value = ref.value;
    var oldValue = ref.oldValue;

    var ctx = el.__qscroll;

    if (typeof value !== 'function') {
      ctx.scrollTarget.removeEventListener('scroll', ctx.scroll, listenOpts.passive);
      console.error('v-scroll requires a function as parameter', el);
      return
    }

    ctx.handler = value;
    if (typeof oldValue !== 'function') {
      ctx.scrollTarget.addEventListener('scroll', ctx.scroll, listenOpts.passive);
    }
  }

  var Scroll = {
    name: 'scroll',

    bind: function bind (el) {
      var ctx = {
        scroll: function scroll () {
          ctx.handler(
            getScrollPosition(ctx.scrollTarget),
            getHorizontalScrollPosition(ctx.scrollTarget)
          );
        }
      };

      if (el.__qscroll) {
        el.__qscroll_old = el.__qscroll;
      }

      el.__qscroll = ctx;
    },

    inserted: function inserted (el, binding) {
      var ctx = el.__qscroll;
      ctx.scrollTarget = getScrollTarget(el);
      updateBinding$1(el, binding);
    },

    update: function update (el, binding) {
      if (binding.oldValue !== binding.value) {
        updateBinding$1(el, binding);
      }
    },

    unbind: function unbind (el) {
      var ctx = el.__qscroll_old || el.__qscroll;
      if (ctx !== void 0) {
        ctx.scrollTarget.removeEventListener('scroll', ctx.scroll, listenOpts.passive);
        delete el[el.__qscroll_old ? '__qscroll_old' : '__qscroll'];
      }
    }
  };

  function objectWithoutProperties (obj, exclude) { var target = {}; for (var k in obj) if (Object.prototype.hasOwnProperty.call(obj, k) && exclude.indexOf(k) === -1) target[k] = obj[k]; return target; }

  function update (el, binding) {
    var assign;

    var ctx = el.__qtouchhold;

    if (ctx !== void 0) {
      if (binding.oldValue !== binding.value) {
        ctx.handler = binding.value;
      }

      // duration in ms, touch in pixels, mouse in pixels
      var data = [600, 5, 7];

      if (typeof binding.arg === 'string' && binding.arg.length) {
        binding.arg.split(':').forEach(function (val, index) {
          var v = parseInt(val, 10);
          v && (data[index] = v);
        });
      }

      (assign = data, ctx.duration = assign[0], ctx.touchSensitivity = assign[1], ctx.mouseSensitivity = assign[2]);
    }
  }

  var TouchHold = {
    name: 'touch-hold',

    bind: function bind (el, ref) {
      var modifiers = ref.modifiers;
      var rest$1 = objectWithoutProperties( ref, ["modifiers"] );
      var rest = rest$1;

      if (el.__qtouchhold) {
        el.__qtouchhold_old = el.__qtouchhold;
      }

      // early return, we don't need to do anything
      if (modifiers.mouse !== true && Platform.has.touch !== true) {
        return
      }

      var ctx = {
        mouseStart: function mouseStart (evt) {
          if (leftClick(evt)) {
            document.addEventListener('mousemove', ctx.mouseMove, true);
            document.addEventListener('click', ctx.mouseEnd, true);
            ctx.start(evt, true);
          }
        },

        mouseMove: function mouseMove (evt) {
          var ref = position(evt);
          var top = ref.top;
          var left = ref.left;
          if (
            Math.abs(left - ctx.origin.left) >= ctx.mouseSensitivity ||
            Math.abs(top - ctx.origin.top) >= ctx.mouseSensitivity
          ) {
            ctx.mouseEnd(evt);
          }
        },

        mouseEnd: function mouseEnd (evt) {
          document.removeEventListener('mousemove', ctx.mouseMove, true);
          document.removeEventListener('click', ctx.mouseEnd, true);
          ctx.end(evt);
        },

        start: function start (evt, mouseEvent) {
          removeObserver(ctx);
          mouseEvent !== true && setObserver(el, evt, ctx);

          ctx.origin = position(evt);

          var startTime = new Date().getTime();

          if (Platform.is.mobile === true) {
            document.body.classList.add('non-selectable');
            clearSelection();
          }

          ctx.triggered = false;

          ctx.timer = setTimeout(function () {
            if (Platform.is.mobile !== true) {
              document.body.classList.add('non-selectable');
              clearSelection();
            }
            ctx.triggered = true;

            ctx.handler({
              evt: evt,
              touch: mouseEvent !== true,
              mouse: mouseEvent === true,
              position: ctx.origin,
              duration: new Date().getTime() - startTime
            });
          }, ctx.duration);
        },

        move: function move (evt) {
          var ref = position(evt);
          var top = ref.top;
          var left = ref.left;
          if (
            Math.abs(left - ctx.origin.left) >= ctx.touchSensitivity ||
            Math.abs(top - ctx.origin.top) >= ctx.touchSensitivity
          ) {
            ctx.end(evt);
          }
        },

        end: function end (evt) {
          removeObserver(ctx);
          document.body.classList.remove('non-selectable');

          if (ctx.triggered === true) {
            stopAndPrevent(evt);
          }
          else {
            clearTimeout(ctx.timer);
          }
        }
      };

      el.__qtouchhold = ctx;
      update(el, rest);

      if (modifiers.mouse === true) {
        el.addEventListener('mousedown', ctx.mouseStart, modifiers.mouseCapture);
      }

      if (Platform.has.touch === true) {
        var opts = listenOpts['notPassive' + (modifiers.capture === true ? 'Capture' : '')];

        el.addEventListener('touchstart', ctx.start, opts);
        el.addEventListener('touchmove', ctx.move, opts);
        el.addEventListener('touchcancel', ctx.end, opts);
        el.addEventListener('touchend', ctx.end, opts);
      }
    },

    update: update,

    unbind: function unbind (el, ref) {
      var modifiers = ref.modifiers;

      var ctx = el.__qtouchhold_old || el.__qtouchhold;
      if (ctx !== void 0) {
        removeObserver(ctx);
        clearTimeout(ctx.timer);
        document.body.classList.remove('non-selectable');

        if (modifiers.mouse === true) {
          el.removeEventListener('mousedown', ctx.mouseStart, modifiers.mouseCapture);
          document.removeEventListener('mousemove', ctx.mouseMove, true);
          document.removeEventListener('click', ctx.mouseEnd, true);
        }

        if (Platform.has.touch === true) {
          var opts = listenOpts['notPassive' + (modifiers.capture === true ? 'Capture' : '')];

          el.removeEventListener('touchstart', ctx.start, opts);
          el.removeEventListener('touchmove', ctx.move, opts);
          el.removeEventListener('touchcancel', ctx.end, opts);
          el.removeEventListener('touchend', ctx.end, opts);
        }

        delete el[el.__qtouchhold_old ? '__qtouchhold_old' : '__qtouchhold'];
      }
    }
  };

  var
    keyCodes$2 = {
      esc: 27,
      tab: 9,
      enter: 13,
      space: 32,
      up: 38,
      left: 37,
      right: 39,
      down: 40,
      'delete': [8, 46]
    },
    keyRegex = new RegExp(("^([\\d+]+|" + (Object.keys(keyCodes$2).join('|')) + ")$"), 'i');

  function shouldEnd (evt, origin) {
    var ref = position(evt);
    var top = ref.top;
    var left = ref.left;

    return Math.abs(left - origin.left) >= 7 ||
      Math.abs(top - origin.top) >= 7
  }

  var docEvtOpts = listenOpts.notPassiveCapture;

  var TouchRepeat = {
    name: 'touch-repeat',

    bind: function bind (el, ref) {
      var modifiers = ref.modifiers;
      var value = ref.value;
      var arg = ref.arg;

      if (el.__qtouchrepeat) {
        el.__qtouchrepeat_old = el.__qtouchrepeat;
      }

      var keyboard = Object.keys(modifiers).reduce(function (acc, key) {
        if (keyRegex.test(key)) {
          var keyCode = parseInt(key, 10);
          acc.push(keyCode || keyCodes$2[key.toLowerCase()]);
        }

        return acc
      }, []);

      // early return, we don't need to do anything
      if (
        modifiers.mouse !== true &&
        Platform.has.touch !== true &&
        keyboard.length === 0
      ) {
        return
      }

      var durations = typeof arg === 'string' && arg.length
        ? arg.split(':').map(function (val) { return parseInt(val, 10); })
        : [0, 600, 300];

      var durationsLast = durations.length - 1;

      var ctx = {
        keyboard: keyboard,
        handler: value,

        mouseStart: function mouseStart (evt) {
          if (leftClick(evt)) {
            document.addEventListener('mousemove', ctx.mouseMove, docEvtOpts);
            document.addEventListener('mouseup', ctx.mouseEnd, docEvtOpts);
            document.addEventListener('click', ctx.mouseEnd, docEvtOpts);

            ctx.start(evt, true);
          }
        },

        mouseMove: function mouseMove (evt) {
          ctx.event !== void 0 && shouldEnd(evt, ctx.origin) === true && ctx.mouseEnd(evt);
        },

        mouseEnd: function mouseEnd (evt) {
          document.removeEventListener('mousemove', ctx.mouseMove, docEvtOpts);
          document.removeEventListener('mouseup', ctx.mouseEnd, docEvtOpts);
          document.removeEventListener('click', ctx.mouseEnd, docEvtOpts);

          ctx.end(evt);
        },

        keyboardStart: function keyboardStart (evt) {
          if (keyboard.includes(evt.keyCode)) {
            if (durations[0] === 0 || ctx.event !== void 0) {
              stopAndPrevent(evt);
              el.focus();
              if (ctx.event !== void 0) {
                return
              }
            }

            document.addEventListener('keyup', ctx.keyboardEnd, docEvtOpts);
            ctx.start(evt, false, true);
          }
        },

        keyboardEnd: function keyboardEnd (evt) {
          document.removeEventListener('keyup', ctx.keyboardEnd, docEvtOpts);
          ctx.end(evt);
        },

        start: function start (evt, mouseEvent, keyboardEvent) {
          removeObserver(ctx);
          if (mouseEvent !== true && keyboardEvent !== true) {
            setObserver(el, evt, ctx);
          }

          if (keyboardEvent !== true) {
            ctx.origin = position(evt);
          }

          if (Platform.is.mobile === true) {
            document.body.classList.add('non-selectable');
            clearSelection();
          }

          ctx.event = {
            touch: mouseEvent !== true && keyboardEvent !== true,
            mouse: mouseEvent === true,
            keyboard: keyboardEvent === true,
            startTime: new Date().getTime(),
            repeatCount: 0
          };

          var fn = function () {
            if (ctx.event === void 0) {
              return
            }

            if (ctx.event.repeatCount === 0) {
              ctx.event.evt = evt;

              if (keyboardEvent === true) {
                ctx.event.keyCode = evt.keyCode;
              }
              else {
                ctx.event.position = position(evt);
              }

              if (Platform.is.mobile !== true) {
                document.documentElement.style.cursor = 'pointer';
                document.body.classList.add('non-selectable');
                clearSelection();
              }
            }

            ctx.event.duration = new Date().getTime() - ctx.event.startTime;
            ctx.event.repeatCount += 1;

            ctx.handler(ctx.event);

            var index = durationsLast < ctx.event.repeatCount
              ? durationsLast
              : ctx.event.repeatCount;

            ctx.timer = setTimeout(fn, durations[index]);
          };

          if (durations[0] === 0) {
            fn();
          }
          else {
            ctx.timer = setTimeout(fn, durations[0]);
          }
        },

        move: function move (evt) {
          ctx.event !== void 0 && shouldEnd(evt, ctx.origin) === true && ctx.end(evt);
        },

        end: function end (evt) {
          if (ctx.event === void 0) {
            return
          }

          removeObserver(ctx);

          var triggered = ctx.event.repeatCount > 0;

          triggered === true && prevent(evt);

          if (Platform.is.mobile === true || triggered === true) {
            document.documentElement.style.cursor = '';
            document.body.classList.remove('non-selectable');
          }

          clearTimeout(ctx.timer);
          ctx.timer = void 0;
          ctx.event = void 0;
        }
      };

      el.__qtouchrepeat = ctx;

      if (modifiers.mouse === true) {
        el.addEventListener('mousedown', ctx.mouseStart, modifiers.mouseCapture);
      }

      if (keyboard.length > 0) {
        el.addEventListener('keydown', ctx.keyboardStart, modifiers.keyCapture);
      }

      if (Platform.has.touch === true) {
        var opts = listenOpts['notPassive' + (modifiers.capture === true ? 'Capture' : '')];

        el.addEventListener('touchstart', ctx.start, opts);
        el.addEventListener('touchmove', ctx.move, opts);
        el.addEventListener('touchcancel', ctx.end, opts);
        el.addEventListener('touchend', ctx.end, opts);
      }
    },

    update: function update (el, binding) {
      var ctx = el.__qtouchrepeat;

      if (ctx !== void 0 && binding.oldValue !== binding.value) {
        ctx.handler = binding.value;
      }
    },

    unbind: function unbind (el, ref) {
      var modifiers = ref.modifiers;

      var ctx = el.__qtouchrepeat_old || el.__qtouchrepeat;

      if (ctx !== void 0) {
        removeObserver(ctx);
        clearTimeout(ctx.timer);

        if (Platform.is.mobile === true || (ctx.event !== void 0 && ctx.event.repeatCount > 0)) {
          document.documentElement.style.cursor = '';
          document.body.classList.remove('non-selectable');
        }

        ctx.timer = void 0;
        ctx.event = void 0;

        if (modifiers.mouse === true) {
          el.removeEventListener('mousedown', ctx.mouseStart, modifiers.mouseCapture);
          document.removeEventListener('mousemove', ctx.mouseMove, docEvtOpts);
          document.removeEventListener('mouseup', ctx.mouseEnd, docEvtOpts);
          document.removeEventListener('click', ctx.mouseEnd, docEvtOpts);
        }

        if (ctx.keyboard.length > 0) {
          el.removeEventListener('keydown', ctx.keyboardStart, modifiers.keyCapture);
          document.removeEventListener('keyup', ctx.keyboardEnd, docEvtOpts);
        }

        if (Platform.has.touch === true) {
          var opts = listenOpts['notPassive' + (modifiers.capture === true ? 'Capture' : '')];

          el.removeEventListener('touchstart', ctx.start, opts);
          el.removeEventListener('touchmove', ctx.move, opts);
          el.removeEventListener('touchcancel', ctx.end, opts);
          el.removeEventListener('touchend', ctx.end, opts);
        }

        delete el[el.__qtouchrepeat_old ? '__qtouchrepeat_old' : '__qtouchrepeat'];
      }
    }
  };



  var directives = /*#__PURE__*/Object.freeze({
    ClosePopup: ClosePopup,
    GoBack: GoBack,
    Ripple: Ripple,
    ScrollFire: ScrollFire,
    Scroll: Scroll,
    TouchHold: TouchHold,
    TouchPan: TouchPan,
    TouchRepeat: TouchRepeat,
    TouchSwipe: TouchSwipe
  });

  var metaValue;

  function getProp () {
    if (Platform.is.winphone) {
      return 'msapplication-navbutton-color'
    }
    if (Platform.is.safari) {
      return 'apple-mobile-web-app-status-bar-style'
    }
    // Chrome, Firefox OS, Opera, Vivaldi
    return 'theme-color'
  }

  function getMetaTag (v) {
    var els = document.getElementsByTagName('META');
    for (var i in els) {
      if (els[i].name === v) {
        return els[i]
      }
    }
  }

  function setColor (hexColor) {
    if (metaValue === void 0) {
      // cache it
      metaValue = getProp();
    }

    var metaTag = getMetaTag(metaValue);
    var newTag = metaTag === void 0;

    if (newTag) {
      metaTag = document.createElement('meta');
      metaTag.setAttribute('name', metaValue);
    }

    metaTag.setAttribute('content', hexColor);

    if (newTag) {
      document.head.appendChild(metaTag);
    }
  }

  var AddressbarColor = {
    install: function install (ref) {
      var $q = ref.$q;
      var cfg = ref.cfg;

      this.set = isSSR === false && Platform.is.mobile === true && (
        Platform.is.cordova === true ||
        Platform.is.winphone === true || Platform.is.safari === true ||
        Platform.is.webkit === true || Platform.is.vivaldi === true
      )
        ? function (hexColor) {
          var val = hexColor || getBrand('primary');

          if (Platform.is.cordova === true && window.StatusBar) {
            window.StatusBar.backgroundColorByHexString(val);
          }
          else {
            setColor(val);
          }
        }
        : function () {};

      $q.addressbarColor = this;

      cfg.addressbarColor && this.set(cfg.addressbarColor);
    }
  };

  var prefixes = {};

  var AppFullscreen = {
    isCapable: false,
    isActive: false,

    request: function request (target) {
      if (this.isCapable && !this.isActive) {
        target = target || document.documentElement;
        target[prefixes.request]();
      }
    },
    exit: function exit () {
      if (this.isCapable && this.isActive) {
        document[prefixes.exit]();
      }
    },
    toggle: function toggle (target) {
      if (this.isActive) {
        this.exit();
      }
      else {
        this.request(target);
      }
    },

    install: function install (ref) {
      var this$1 = this;
      var $q = ref.$q;

      $q.fullscreen = this;

      if (isSSR === true) { return }

      prefixes.request = [
        'requestFullscreen',
        'msRequestFullscreen', 'mozRequestFullScreen', 'webkitRequestFullscreen'
      ].find(function (request) { return document.documentElement[request]; });

      this.isCapable = prefixes.request !== undefined;
      if (!this.isCapable) {
        // it means the browser does NOT support it
        return
      }

      prefixes.exit = [
        'exitFullscreen',
        'msExitFullscreen', 'mozCancelFullScreen', 'webkitExitFullscreen'
      ].find(function (exit) { return document[exit]; });

      this.isActive = !!(document.fullscreenElement ||
        document.mozFullScreenElement ||
        document.webkitFullscreenElement ||
        document.msFullscreenElement)

      ;[
        'onfullscreenchange', 'onmsfullscreenchange', 'onwebkitfullscreenchange'
      ].forEach(function (evt) {
        document[evt] = function () {
          this$1.isActive = !this$1.isActive;
        };
      });

      Vue.util.defineReactive(this, 'isActive', this.isActive);
    }
  };

  var AppVisibility = {
    appVisible: false,

    install: function install (ref) {
      var this$1 = this;
      var $q = ref.$q;

      if (isSSR === true) {
        this.appVisible = $q.appVisible = true;
        return
      }

      var prop, evt;

      if (typeof document.hidden !== 'undefined') { // Opera 12.10 and Firefox 18 and later support
        prop = 'hidden';
        evt = 'visibilitychange';
      }
      else if (typeof document.msHidden !== 'undefined') {
        prop = 'msHidden';
        evt = 'msvisibilitychange';
      }
      else if (typeof document.webkitHidden !== 'undefined') {
        prop = 'webkitHidden';
        evt = 'webkitvisibilitychange';
      }

      var update = function () {
        this$1.appVisible = $q.appVisible = !document[prop];
      };

      update();

      if (evt && typeof document[prop] !== 'undefined') {
        Vue.util.defineReactive($q, 'appVisible', this.appVisible);
        document.addEventListener(evt, update, false);
      }
    }
  };

  var BottomSheet = Vue.extend({
    name: 'BottomSheetPlugin',

    inheritAttrs: false,

    props: {
      title: String,
      message: String,
      actions: Array,

      grid: Boolean,

      cardClass: [String, Array, Object],
      cardStyle: [String, Array, Object],

      dark: Boolean
    },

    methods: {
      show: function show () {
        this.$refs.dialog.show();
      },

      hide: function hide () {
        this.$refs.dialog.hide();
      },

      onOk: function onOk (action) {
        this.$emit('ok', action);
        this.hide();
      },

      __getGrid: function __getGrid (h) {
        var this$1 = this;

        return this.actions.map(function (action) {
          var img = action.avatar || action.img;

          return action.label === void 0
            ? h(QSeparator, {
              staticClass: 'col-all',
              props: { dark: this$1.dark }
            })
            : h('div', {
              staticClass: 'q-bottom-sheet__item q-hoverable q-focusable cursor-pointer relative-position',
              class: action.classes,
              attrs: { tabindex: 0 },
              on: {
                click: function () { return this$1.onOk(action); },
                keyup: function (e) {
                  e.keyCode === 13 && this$1.onOk(action);
                }
              }
            }, [
              h('div', { staticClass: 'q-focus-helper' }),

              action.icon
                ? h(QIcon, { props: { name: action.icon, color: action.color } })
                : (
                  img
                    ? h('img', {
                      attrs: { src: img },
                      staticClass: action.avatar ? 'q-bottom-sheet__avatar' : null
                    })
                    : h('div', { staticClass: 'q-bottom-sheet__empty-icon' })
                ),

              h('div', [ action.label ])
            ])
        })
      },

      __getList: function __getList (h) {
        var this$1 = this;

        return this.actions.map(function (action) {
          var img = action.avatar || action.img;

          return action.label === void 0
            ? h(QSeparator, { props: { spaced: true, dark: this$1.dark } })
            : h(QItem, {
              staticClass: 'q-bottom-sheet__item',
              class: action.classes,
              props: {
                tabindex: 0,
                clickable: true,
                dark: this$1.dark
              },
              on: {
                click: function () { return this$1.onOk(action); },
                keyup: function (e) {
                  e.keyCode === 13 && this$1.onOk(action);
                }
              }
            }, [
              h(QItemSection, { props: { avatar: true } }, [
                action.icon
                  ? h(QIcon, { props: { name: action.icon, color: action.color } })
                  : (
                    img
                      ? h('img', {
                        attrs: { src: img },
                        staticClass: action.avatar ? 'q-bottom-sheet__avatar' : null
                      })
                      : null
                  )
              ]),
              h(QItemSection, [ action.label ])
            ])
        })
      }
    },

    render: function render (h) {
      var this$1 = this;

      var child = [];

      if (this.title) {
        child.push(
          h(QCardSection, {
            staticClass: 'q-dialog__title'
          }, [ this.title ])
        );
      }

      if (this.message) {
        child.push(
          h(QCardSection, {
            staticClass: 'q-dialog__message scroll'
          }, [ this.message ])
        );
      }

      child.push(
        this.grid === true
          ? h('div', {
            staticClass: 'scroll row items-stretch justify-start'
          }, this.__getGrid(h))
          : h('div', { staticClass: 'scroll' }, this.__getList(h))
      );

      return h(QDialog, {
        ref: 'dialog',

        props: Object.assign({}, this.$attrs,
          {position: 'bottom'}),

        on: {
          hide: function () {
            this$1.$emit('hide');
          }
        }
      }, [
        h(QCard, {
          staticClass: "q-bottom-sheet q-bottom-sheet--" + (this.grid === true ? 'grid' : 'list') +
            (this.dark === true ? ' q-bottom-sheet--dark' : ''),
          style: this.cardStyle,
          class: this.cardClass
        }, child)
      ])
    }
  });

  function objectWithoutProperties$1 (obj, exclude) { var target = {}; for (var k in obj) if (Object.prototype.hasOwnProperty.call(obj, k) && exclude.indexOf(k) === -1) target[k] = obj[k]; return target; }

  var ssrAPI = {
    onOk: function () { return ssrAPI; },
    okCancel: function () { return ssrAPI; },
    hide: function () { return ssrAPI; }
  };

  function globalDialog (DefaultComponent) {
    return function (ref) {
      var className = ref.className;
      var klass = ref.class;
      var style = ref.style;
      var component = ref.component;
      var root = ref.root;
      var rest = objectWithoutProperties$1( ref, ["className", "class", "style", "component", "root"] );
      var props = rest;

      if (isSSR === true) { return ssrAPI }

      klass !== void 0 && (props.cardClass = klass);
      style !== void 0 && (props.cardStyle = style);

      var
        okFns = [],
        cancelFns = [],
        API = {
          onOk: function onOk (fn) {
            okFns.push(fn);
            return API
          },
          onCancel: function onCancel (fn) {
            cancelFns.push(fn);
            return API
          },
          onDismiss: function onDismiss (fn) {
            okFns.push(fn);
            cancelFns.push(fn);
            return API
          },
          hide: function hide () {
            vm.$refs.dialog.hide();
            return API
          }
        };

      var node = document.createElement('div');
      document.body.appendChild(node);

      var emittedOK = false;

      var on = {
        ok: function (data) {
          emittedOK = true;
          okFns.forEach(function (fn) { fn(data); });
        },

        hide: function () {
          vm.$destroy();
          vm.$el.remove();
          vm = null;

          if (emittedOK !== true) {
            cancelFns.forEach(function (fn) { fn(); });
          }
        }
      };

      Vue.observable(props);

      var DialogComponent = component !== void 0
        ? component
        : DefaultComponent;

      var attrs = component === void 0
        ? props
        : void 0;

      var vm = getVm(root, {
        el: node,

        render: function render (h) {
          return h(DialogComponent, {
            ref: 'dialog',
            props: props,
            attrs: attrs,
            on: on
          })
        },

        mounted: function mounted () {
          this.$refs.dialog.show();
        }
      });

      return API
    }
  }

  var BottomSheet$1 = {
    install: function install (ref) {
      var $q = ref.$q;

      this.create = $q.bottomSheet = globalDialog(BottomSheet);
    }
  };

  function encode (string) {
    return encodeURIComponent(string)
  }

  function decode (string) {
    return decodeURIComponent(string)
  }

  function stringifyCookieValue (value) {
    return encode(value === Object(value) ? JSON.stringify(value) : '' + value)
  }

  function read (string) {
    if (string === '') {
      return string
    }

    if (string.indexOf('"') === 0) {
      // This is a quoted cookie as according to RFC2068, unescape...
      string = string.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, '\\');
    }

    // Replace server-side written pluses with spaces.
    // If we can't decode the cookie, ignore it, it's unusable.
    // If we can't parse the cookie, ignore it, it's unusable.
    string = decode(string.replace(/\+/g, ' '));

    try {
      string = JSON.parse(string);
    }
    catch (e) {}

    return string
  }

  function set (key, val, opts, ssr) {
    if ( opts === void 0 ) opts = {};

    var expire, expireValue;

    if (opts.expires !== void 0) {
      expireValue = parseFloat(opts.expires);

      if (isNaN(expireValue)) {
        expire = opts.expires;
      }
      else {
        expire = new Date();
        expire.setMilliseconds(expire.getMilliseconds() + expireValue * 864e+5);
        expire = expire.toUTCString();
      }
    }

    var keyValue = (encode(key)) + "=" + (stringifyCookieValue(val));

    var cookie = [
      keyValue,
      expire !== void 0 ? '; Expires=' + expire : '', // use expires attribute, max-age is not supported by IE
      opts.path ? '; Path=' + opts.path : '',
      opts.domain ? '; Domain=' + opts.domain : '',
      opts.httpOnly ? '; HttpOnly' : '',
      opts.secure ? '; Secure' : ''
    ].join('');

    if (ssr) {
      if (ssr.req.qCookies) {
        ssr.req.qCookies.push(cookie);
      }
      else {
        ssr.req.qCookies = [ cookie ];
      }

      ssr.res.setHeader('Set-Cookie', ssr.req.qCookies);

      // make temporary update so future get()
      // within same SSR timeframe would return the set value

      var all = ssr.req.headers.cookie || '';

      if (expire !== void 0 && expireValue < 0) {
        var val$1 = get(key, ssr);
        if (val$1 !== undefined) {
          all = all
            .replace((key + "=" + val$1 + "; "), '')
            .replace(("; " + key + "=" + val$1), '')
            .replace((key + "=" + val$1), '');
        }
      }
      else {
        all = all
          ? (keyValue + "; " + all)
          : cookie;
      }

      ssr.req.headers.cookie = all;
    }
    else {
      document.cookie = cookie;
    }
  }

  function get (key, ssr) {
    var
      result = key ? undefined : {},
      cookieSource = ssr ? ssr.req.headers : document,
      cookies = cookieSource.cookie ? cookieSource.cookie.split('; ') : [],
      i = 0,
      l = cookies.length,
      parts,
      name,
      cookie;

    for (; i < l; i++) {
      parts = cookies[i].split('=');
      name = decode(parts.shift());
      cookie = parts.join('=');

      if (!key) {
        result[name] = cookie;
      }
      else if (key === name) {
        result = read(cookie);
        break
      }
    }

    return result
  }

  function remove (key, options, ssr) {
    set(
      key,
      '',
      Object.assign({}, options, {expires: -1}),
      ssr
    );
  }

  function has (key, ssr) {
    return get(key, ssr) !== undefined
  }

  function getObject (ctx) {
    if ( ctx === void 0 ) ctx = {};

    var ssr = ctx.ssr;

    return {
      get: function (key) { return get(key, ssr); },
      set: function (key, val, opts) { return set(key, val, opts, ssr); },
      has: function (key) { return has(key, ssr); },
      remove: function (key, options) { return remove(key, options, ssr); },
      getAll: function () { return get(null, ssr); }
    }
  }

  var Cookies = {
    parseSSR: function parseSSR (/* ssrContext */ ssr) {
      return ssr ? getObject({ ssr: ssr }) : this
    },

    install: function install (ref) {
      var $q = ref.$q;
      var queues = ref.queues;

      if (isSSR === true) {
        queues.server.push(function (q, ctx) {
          q.cookies = getObject(ctx);
        });
      }
      else {
        Object.assign(this, getObject());
        $q.cookies = this;
      }
    }
  };

  var DialogPlugin = Vue.extend({
    name: 'DialogPlugin',

    inheritAttrs: false,

    props: {
      title: String,
      message: String,
      prompt: Object,
      options: Object,

      html: Boolean,

      ok: {
        type: [String, Object, Boolean],
        default: true
      },
      cancel: [String, Object, Boolean],

      stackButtons: Boolean,
      color: String,

      cardClass: [String, Array, Object],
      cardStyle: [String, Array, Object],

      dark: Boolean
    },

    computed: {
      hasForm: function hasForm () {
        return this.prompt || this.options
      },

      okLabel: function okLabel () {
        return this.ok === true
          ? this.$q.lang.label.ok
          : this.ok
      },

      cancelLabel: function cancelLabel () {
        return this.cancel === true
          ? this.$q.lang.label.cancel
          : this.cancel
      },

      vmColor: function vmColor () {
        return this.color || (this.dark === true ? 'amber' : 'primary')
      },

      okProps: function okProps () {
        return Object(this.ok) === this.ok
          ? Object.assign({}, {color: this.vmColor,
            label: this.$q.lang.label.ok,
            ripple: false},
            this.ok)
          : {
            color: this.vmColor,
            flat: true,
            label: this.okLabel,
            ripple: false
          }
      },

      cancelProps: function cancelProps () {
        return Object(this.cancel) === this.cancel
          ? Object.assign({}, {color: this.vmColor,
            label: this.$q.lang.label.cancel,
            ripple: false},
            this.cancel)
          : {
            color: this.vmColor,
            flat: true,
            label: this.cancelLabel,
            ripple: false
          }
      }
    },

    methods: {
      show: function show () {
        this.$refs.dialog.show();
      },

      hide: function hide () {
        this.$refs.dialog.hide();
      },

      getPrompt: function getPrompt (h) {
        var this$1 = this;

        return [
          h(QInput, {
            props: {
              value: this.prompt.model,
              type: this.prompt.type || 'text',
              color: this.vmColor,
              dense: true,
              autofocus: true,
              dark: this.dark
            },
            on: {
              input: function (v) { this$1.prompt.model = v; },
              keyup: function (evt) {
                // if ENTER key
                if (this$1.prompt.type !== 'textarea' && evt.keyCode === 13) {
                  this$1.onOk();
                }
              }
            }
          })
        ]
      },

      getOptions: function getOptions (h) {
        var this$1 = this;

        return [
          h(QOptionGroup, {
            props: {
              value: this.options.model,
              type: this.options.type,
              color: this.vmColor,
              inline: this.options.inline,
              options: this.options.items,
              dark: this.dark
            },
            on: {
              input: function (v) { this$1.options.model = v; }
            }
          })
        ]
      },

      getButtons: function getButtons (h) {
        var child = [];

        if (this.cancel) {
          child.push(h(QBtn, {
            props: this.cancelProps,
            attrs: { autofocus: !this.prompt && !this.ok },
            on: { click: this.onCancel }
          }));
        }
        if (this.ok) {
          child.push(h(QBtn, {
            props: this.okProps,
            attrs: { autofocus: !this.prompt },
            on: { click: this.onOk }
          }));
        }

        if (child.length > 0) {
          return h(QCardActions, {
            staticClass: this.stackButtons === true ? 'items-end' : null,
            props: {
              vertical: this.stackButtons,
              align: 'right'
            }
          }, child)
        }
      },

      onOk: function onOk () {
        this.$emit('ok', clone$1(this.getData()));
        this.hide();
      },

      onCancel: function onCancel () {
        this.hide();
      },

      getData: function getData () {
        if (this.prompt) {
          return this.prompt.model
        }
        if (this.options) {
          return this.options.model
        }
      },

      getSection: function getSection (h, staticClass, text) {
        return this.html === true
          ? h(QCardSection, {
            staticClass: staticClass,
            domProps: {
              innerHTML: text
            }
          })
          : h(QCardSection, { staticClass: staticClass }, [ text ])
      }
    },

    render: function render (h) {
      var this$1 = this;

      var child = [];

      if (this.title) {
        child.push(
          this.getSection(h, 'q-dialog__title', this.title)
        );
      }

      if (this.message) {
        child.push(
          this.getSection(h, 'q-dialog__message scroll', this.message)
        );
      }

      if (this.hasForm) {
        child.push(
          h(
            QCardSection,
            { staticClass: 'scroll' },
            this.prompt ? this.getPrompt(h) : this.getOptions(h)
          )
        );
      }

      if (this.ok || this.cancel) {
        child.push(this.getButtons(h));
      }

      return h(QDialog, {
        ref: 'dialog',

        props: Object.assign({}, this.$attrs,
          {value: this.value}),

        on: {
          hide: function () {
            this$1.$emit('hide');
          }
        }
      }, [
        h(QCard, {
          staticClass: 'q-dialog-plugin' +
            (this.dark === true ? ' q-dialog-plugin--dark' : ''),
          style: this.cardStyle,
          class: this.cardClass,
          props: {
            dark: this.dark
          }
        }, child)
      ])
    }
  });

  var Dialog = {
    install: function install (ref) {
      var $q = ref.$q;

      this.create = $q.dialog = globalDialog(DialogPlugin);
    }
  };

  var LoadingBar = {
    isActive: false,
    start: function start () {},
    stop: function stop () {},
    increment: function increment () {},
    setDefaults: function setDefaults () {},

    install: function install (ref) {
      var this$1 = this;
      var $q = ref.$q;
      var cfg = ref.cfg;

      if (isSSR === true) {
        $q.loadingBar = this;
        return
      }

      var props = cfg.loadingBar !== void 0
        ? Object.assign({}, cfg.loadingBar)
        : {};

      var bar = $q.loadingBar = new Vue({
        name: 'LoadingBar',
        render: function (h) { return h(QAjaxBar, {
          ref: 'bar',
          props: props
        }); }
      }).$mount().$refs.bar;

      Object.assign(this, {
        start: function (speed) {
          bar.start(speed);
          this$1.isActive = bar.isActive = bar.calls > 0;
        },
        stop: function () {
          bar.stop();
          this$1.isActive = bar.isActive = bar.calls > 0;
        },
        increment: bar.increment,
        setDefaults: function (def) {
          Object.assign(props, def || {});
          bar.$parent.$forceUpdate();
        }
      });

      Vue.util.defineReactive(this, 'isActive', this.isActive);
      Vue.util.defineReactive(bar, 'isActive', this.isActive);
      bar.setDefaults = this.setDefaults;

      document.body.appendChild(bar.$parent.$el);
    }
  };

  var
    vm = null,
    timeout,
    props = {},
    defaults = {
      delay: 0,
      message: false,
      spinnerSize: 80,
      spinnerColor: 'white',
      messageColor: 'white',
      backgroundColor: 'black',
      spinner: QSpinner,
      customClass: ''
    };

  var Loading = {
    isActive: false,

    show: function show (opts) {
      if (isSSR === true) { return }

      props = Object.assign({}, defaults, opts);
      props.customClass += " text-" + (props.backgroundColor);

      if (this.isActive) {
        if (vm) {
          if (!vm.isActive) {
            vm.isActive = true;
          }
          vm.$forceUpdate();
        }
        return
      }

      timeout = setTimeout(function () {
        timeout = null;

        var node = document.createElement('div');
        document.body.appendChild(node);
        document.body.classList.add('q-body--loading');

        vm = new Vue({
          name: 'QLoading',
          el: node,
          data: function data () {
            return {
              isActive: true
            }
          },
          render: function render (h) {
            var this$1 = this;
            var obj;

            return h('transition', {
              props: {
                name: 'q-transition--fade',
                appear: true
              },
              on: {
                'after-leave': function () {
                  this$1.$emit('destroy');
                }
              }
            }, [
              this.isActive ? h('div', {
                staticClass: 'q-loading fullscreen column flex-center z-max',
                key: uid(),
                class: props.customClass.trim()
              }, [
                h(props.spinner, {
                  props: {
                    color: props.spinnerColor,
                    size: props.spinnerSize
                  }
                }),
                (props.message && h('div', {
                  class: ("text-" + (props.messageColor)),
                  domProps: ( obj = {}, obj[props.sanitize === true ? 'textContent' : 'innerHTML'] = props.message, obj )
                })) || void 0
              ]) : null
            ])
          }
        });
      }, props.delay);

      this.isActive = true;
    },

    hide: function hide () {
      var this$1 = this;

      if (!this.isActive) {
        return
      }

      if (timeout) {
        clearTimeout(timeout);
        timeout = null;
        this.isActive = false;
      }
      else {
        vm.isActive = false;
        vm.$on('destroy', function () {
          if (vm !== null) {
            vm.$destroy();
            document.body.classList.remove('q-body--loading');
            vm.$el.remove();
            vm = null;
          }
          this$1.isActive = false;
        });
      }
    },

    setDefaults: function setDefaults (opts) {
      Object.assign(defaults, opts);
    },

    install: function install (ref) {
      var $q = ref.$q;
      var loading = ref.cfg.loading;

      loading !== void 0 && this.setDefaults(loading);

      $q.loading = this;
      Vue.util.defineReactive(this, 'isActive', this.isActive);
    }
  };

  var updateId, ssrTakeover;

  function normalize (meta) {
    if (meta.title) {
      meta.title = meta.titleTemplate
        ? meta.titleTemplate(meta.title)
        : meta.title;
      delete meta.titleTemplate;
    }
  [['meta', 'content'], ['link', 'href']].forEach(function (type) {
      var
        metaType = meta[type[0]],
        metaProp = type[1];

      for (var name in metaType) {
        var metaLink = metaType[name];

        if (metaLink.template) {
          if (Object.keys(metaLink).length === 1) {
            delete metaType[name];
          }
          else {
            metaLink[metaProp] = metaLink.template(metaLink[metaProp] || '');
            delete metaLink.template;
          }
        }
      }
    });
  }

  function changed (old, def) {
    if (Object.keys(old).length !== Object.keys(def).length) {
      return true
    }
    for (var key in old) {
      if (old[key] !== def[key]) {
        return true
      }
    }
  }

  function bodyFilter (name) {
    return ['class', 'style'].includes(name) === false
  }

  function htmlFilter (name) {
    return ['lang', 'dir'].includes(name) === false
  }

  function diff (meta, other) {
    var add = {}, remove = {};

    if (meta === void 0) {
      return { add: other, remove: remove }
    }

    if (meta.title !== other.title) {
      add.title = other.title;
    }
  ['meta', 'link', 'script', 'htmlAttr', 'bodyAttr'].forEach(function (type) {
      var old = meta[type], cur = other[type];
      remove[type] = [];

      if (old === void 0 || old === null) {
        add[type] = cur;
        return
      }

      add[type] = {};

      for (var key in old) {
        if (cur.hasOwnProperty(key) === false) {
          remove[type].push(key);
        }
      }
      for (var key$1 in cur) {
        if (old.hasOwnProperty(key$1) === false) {
          add[type][key$1] = cur[key$1];
        }
        else if (changed(old[key$1], cur[key$1]) === true) {
          remove[type].push(key$1);
          add[type][key$1] = cur[key$1];
        }
      }
    });

    return { add: add, remove: remove }
  }

  function apply (ref) {
    var add = ref.add;
    var remove = ref.remove;

    if (add.title) {
      document.title = add.title;
    }

    if (Object.keys(remove).length > 0) {
      ['meta', 'link', 'script'].forEach(function (type) {
        remove[type].forEach(function (name) {
          document.head.querySelector((type + "[data-qmeta=\"" + name + "\"]")).remove();
        });
      });
      remove.htmlAttr.filter(htmlFilter).forEach(function (name) {
        document.documentElement.removeAttribute(name);
      });
      remove.bodyAttr.filter(bodyFilter).forEach(function (name) {
        document.body.removeAttribute(name);
      });
    }
  ['meta', 'link', 'script'].forEach(function (type) {
      var metaType = add[type];

      for (var name in metaType) {
        var tag = document.createElement(type);
        for (var att in metaType[name]) {
          if (att !== 'innerHTML') {
            tag.setAttribute(att, metaType[name][att]);
          }
        }
        tag.setAttribute('data-qmeta', name);
        if (type === 'script') {
          tag.innerHTML = metaType[name].innerHTML || '';
        }
        document.head.appendChild(tag);
      }
    });
    Object.keys(add.htmlAttr).filter(htmlFilter).forEach(function (name) {
      document.documentElement.setAttribute(name, add.htmlAttr[name] || '');
    });
    Object.keys(add.bodyAttr).filter(bodyFilter).forEach(function (name) {
      document.body.setAttribute(name, add.bodyAttr[name] || '');
    });
  }

  function parseMeta (component, meta) {
    if (component._inactive === true) { return }

    // if it has meta
    if (component.__qMeta !== void 0) {
      extend(true, meta, component.__qMeta);
      if (component.$options.meta.stopPropagation === true) {
        return
      }
    }

    component.$children.forEach(function (child) {
      parseMeta(child, meta);
    });
  }

  function updateClient () {
    if (ssrTakeover === true) {
      ssrTakeover = false;
      this.$root.__currentMeta = window.__Q_META__;
      document.body.querySelector('script[data-qmeta-init]').remove();
      return
    }

    var meta = {
      title: '',
      titleTemplate: null,
      meta: {},
      link: {},
      script: {},
      htmlAttr: {},
      bodyAttr: {}
    };
    parseMeta(this.$root, meta);
    normalize(meta);

    apply(diff(this.$root.__currentMeta, meta));
    this.$root.__currentMeta = meta;
  }

  function getAttr (seed) {
    return function (att) {
      var val = seed[att];
      return att + (val !== void 0 ? ("=\"" + val + "\"") : '')
    }
  }

  function getHead (meta) {
    var output = '';
    if (meta.title) {
      output += "<title>" + (meta.title) + "</title>";
    }
  ['meta', 'link', 'script'].forEach(function (type) {
      var metaType = meta[type];

      for (var att in metaType) {
        var attrs = Object.keys(metaType[att])
          .filter(function (att) { return att !== 'innerHTML'; })
          .map(getAttr(metaType[att]));

        output += "<" + type + " " + (attrs.join(' ')) + " data-qmeta=\"" + att + "\">";
        if (type === 'script') {
          output += (metaType[att].innerHTML || '') + "</script>";
        }
      }
    });
    return output
  }

  function getServerMeta (app, html) {
    var meta = {
      title: '',
      titleTemplate: null,
      meta: {},
      link: {},
      htmlAttr: {},
      bodyAttr: {},
      noscript: {}
    };

    parseMeta(app, meta);
    normalize(meta);

    var tokens = {
      '%%Q_HTML_ATTRS%%': Object.keys(meta.htmlAttr)
        .filter(htmlFilter)
        .map(getAttr(meta.htmlAttr))
        .join(' '),
      '%%Q_HEAD_TAGS%%': getHead(meta),
      '%%Q_BODY_ATTRS%%': Object.keys(meta.bodyAttr)
        .filter(bodyFilter)
        .map(getAttr(meta.bodyAttr))
        .join(' '),
      '%%Q_BODY_TAGS%%': Object.keys(meta.noscript)
        .map(function (name) { return ("<noscript data-qmeta=\"" + name + "\">" + (meta.noscript[name]) + "</noscript>"); })
        .join('') +
        "<script data-qmeta-init>window.__Q_META__=" + (delete meta.noscript && JSON.stringify(meta)) + "</script>"
    };

    Object.keys(tokens).forEach(function (key) {
      html = html.replace(key, tokens[key]);
    });

    return html
  }

  function beforeCreate () {
    if (typeof this.$options.meta === 'function') {
      if (this.$options.computed === void 0) {
        this.$options.computed = {};
      }
      this.$options.computed.__qMeta = this.$options.meta;
    }
    else if (hasMeta(this) === true) {
      this.__qMeta = this.$options.meta;
    }
  }

  // needs to be really fast
  function hasMeta (vm) {
    return vm.$options.meta !== void 0 &&
      vm.$options.meta !== null
  }

  function triggerMeta () {
    hasMeta(this) === true && this.__qMetaUpdate();
  }

  var Meta = {
    install: function install (ref) {
      var queues = ref.queues;

      if (isSSR === true) {
        Vue.prototype.$getMetaHTML = function (app) { return function (html) { return getServerMeta(app, html); }; };
        Vue.mixin({ beforeCreate: beforeCreate });

        queues.server.push(function (_, ctx) {
          ctx.ssr.Q_HTML_ATTRS += ' %%Q_HTML_ATTRS%%';
          Object.assign(ctx.ssr, {
            Q_HEAD_TAGS: '%%Q_HEAD_TAGS%%',
            Q_BODY_ATTRS: '%%Q_BODY_ATTRS%%',
            Q_BODY_TAGS: '%%Q_BODY_TAGS%%'
          });
        });
      }
      else {
        ssrTakeover = fromSSR;

        Vue.mixin({
          beforeCreate: beforeCreate,
          created: function created () {
            if (hasMeta(this) === true) {
              this.__qMetaUnwatch = this.$watch('__qMeta', this.__qMetaUpdate);
            }
          },
          activated: triggerMeta,
          deactivated: triggerMeta,
          beforeMount: triggerMeta,
          destroyed: function destroyed () {
            if (hasMeta(this) === true) {
              this.__qMetaUnwatch();
              this.__qMetaUpdate();
            }
          },
          methods: {
            __qMetaUpdate: function __qMetaUpdate () {
              clearTimeout(updateId);
              updateId = setTimeout(updateClient.bind(this), 50);
            }
          }
        });
      }
    }
  };

  var defaults$1 = {};

  var positionList = [
    'top-left', 'top-right',
    'bottom-left', 'bottom-right',
    'top', 'bottom', 'left', 'right', 'center'
  ];

  var Notifications = {
    name: 'QNotifications',

    data: {
      notifs: {
        center: [],
        left: [],
        right: [],
        top: [],
        'top-left': [],
        'top-right': [],
        bottom: [],
        'bottom-left': [],
        'bottom-right': []
      }
    },

    methods: {
      add: function add (config) {
        var this$1 = this;

        if (!config) {
          console.error('Notify: parameter required');
          return false
        }

        var notif = Object.assign(
          { textColor: 'white' },
          defaults$1,
          typeof config === 'string'
            ? { message: config }
            : clone$1(config)
        );

        if (notif.position) {
          if (!positionList.includes(notif.position)) {
            console.error(("Notify: wrong position: " + (notif.position)));
            return false
          }
        }
        else {
          notif.position = 'bottom';
        }

        notif.__uid = uid();

        if (notif.timeout === void 0) {
          notif.timeout = 5000;
        }
        else {
          var t = parseInt(notif.timeout, 10);
          if (isNaN(t) || t < 0) {
            console.error(("Notify: wrong timeout: " + (notif.timeout)));
            return false
          }
          notif.timeout = t;
        }

        var close = function () {
          this$1.remove(notif);
        };

        var actions =
          (config.actions || []).concat(defaults$1.actions || []);

        if (actions.length > 0) {
          notif.actions = actions.map(function (item) {
            var
              handler = item.handler,
              action = clone$1(item);

            action.handler = typeof handler === 'function'
              ? function () {
                handler();
                !item.noDismiss && close();
              }
              : function () { return close(); };

            return action
          });
        }

        if (typeof config.onDismiss === 'function') {
          notif.onDismiss = config.onDismiss;
        }

        if (notif.closeBtn) {
          var btn = [{
            closeBtn: true,
            label: notif.closeBtn,
            handler: close
          }];
          notif.actions = notif.actions
            ? notif.actions.concat(btn)
            : btn;
        }

        if (notif.timeout > 0) {
          notif.__timeout = setTimeout(function () {
            close();
          }, notif.timeout + /* show duration */ 1000);
        }

        if (notif.multiLine === void 0 && notif.actions) {
          notif.multiLine = notif.actions.length > 1;
        }

        notif.staticClass = [
          "q-notification row items-center",
          notif.color && ("bg-" + (notif.color)),
          notif.textColor && ("text-" + (notif.textColor)),
          ("q-notification--" + (notif.multiLine ? 'multi-line' : 'standard')),
          notif.classes
        ].filter(function (n) { return n; }).join(' ');

        var action = notif.position.indexOf('top') > -1 ? 'unshift' : 'push';
        this.notifs[notif.position][action](notif);

        return close
      },

      remove: function remove (notif) {
        if (notif.__timeout) { clearTimeout(notif.__timeout); }

        var index = this.notifs[notif.position].indexOf(notif);
        if (index !== -1) {
          var el = this.$refs[("notif_" + (notif.__uid))];

          if (el) {
            var ref = getComputedStyle(el);
            var width = ref.width;
            var height = ref.height;

            el.style.left = (el.offsetLeft) + "px";
            el.style.width = width;
            el.style.height = height;
          }

          this.notifs[notif.position].splice(index, 1);
          if (typeof notif.onDismiss === 'function') {
            notif.onDismiss();
          }
        }
      }
    },

    render: function render (h) {
      var this$1 = this;

      return h('div', { staticClass: 'q-notifications' }, positionList.map(function (pos) {
        var
          vert = ['left', 'center', 'right'].includes(pos) ? 'center' : (pos.indexOf('top') > -1 ? 'top' : 'bottom'),
          align = pos.indexOf('left') > -1 ? 'start' : (pos.indexOf('right') > -1 ? 'end' : 'center'),
          classes = ['left', 'right'].includes(pos) ? ("items-" + (pos === 'left' ? 'start' : 'end') + " justify-center") : (pos === 'center' ? 'flex-center' : ("items-" + align));

        return h('transition-group', {
          key: pos,
          staticClass: ("q-notifications__list q-notifications__list--" + vert + " fixed column " + classes),
          tag: 'div',
          props: {
            name: ("q-notification--" + pos),
            mode: 'out-in'
          }
        }, this$1.notifs[pos].map(function (notif) {
          var msg = notif.html === true
            ? h('div', { staticClass: 'q-notification__message col', domProps: { innerHTML: notif.message } })
            : h('div', { staticClass: 'q-notification__message col' }, [ notif.message ]);

          return h('div', {
            ref: ("notif_" + (notif.__uid)),
            key: notif.__uid,
            staticClass: notif.staticClass
          }, [

            h('div', { staticClass: 'row items-center ' + (notif.multiLine ? 'col-all' : 'col') }, [
              notif.icon ? h(QIcon, {
                staticClass: 'q-notification__icon col-auto',
                props: { name: notif.icon }
              }) : null,

              notif.avatar ? h(QAvatar, { staticClass: 'q-notification__avatar col-auto' }, [
                h('img', { attrs: { src: notif.avatar } })
              ]) : null,

              msg
            ]),

            notif.actions ? h('div', {
              staticClass: 'q-notification__actions row items-center ' + (notif.multiLine ? 'col-all justify-end' : 'col-auto')
            }, notif.actions.map(function (action) { return h(QBtn, {
              props: Object.assign({}, {flat: true}, action),
              on: { click: action.handler }
            }); })) : null

          ])
        }))
      }))
    }
  };

  function init () {
    var node = document.createElement('div');
    document.body.appendChild(node);

    this.__vm = new Vue(Notifications);
    this.__vm.$mount(node);
  }

  var Notify = {
    create: function create (opts) {
      if (isSSR === true) { return function () {} }
      return this.__vm.add(opts)
    },
    setDefaults: function setDefaults (opts) {
      Object.assign(defaults$1, opts);
    },

    install: function install (args) {
      if (isSSR === true) {
        args.$q.notify = function () {};
        args.$q.notify.setDefaults = function () {};
        return
      }

      init.call(this, args);

      args.cfg.notify && this.setDefaults(args.cfg.notify);

      args.$q.notify = this.create.bind(this);
      args.$q.notify.setDefaults = this.setDefaults;
    }
  };

  function encode$1 (value) {
    if (Object.prototype.toString.call(value) === '[object Date]') {
      return '__q_date|' + value.toUTCString()
    }
    if (Object.prototype.toString.call(value) === '[object RegExp]') {
      return '__q_expr|' + value.source
    }
    if (typeof value === 'number') {
      return '__q_numb|' + value
    }
    if (typeof value === 'boolean') {
      return '__q_bool|' + (value ? '1' : '0')
    }
    if (typeof value === 'string') {
      return '__q_strn|' + value
    }
    if (typeof value === 'function') {
      return '__q_strn|' + value.toString()
    }
    if (value === Object(value)) {
      return '__q_objt|' + JSON.stringify(value)
    }

    // hmm, we don't know what to do with it,
    // so just return it as is
    return value
  }

  function decode$1 (value) {
    var type, length, source;

    length = value.length;
    if (length < 9) {
      // then it wasn't encoded by us
      return value
    }

    type = value.substr(0, 8);
    source = value.substring(9);

    switch (type) {
      case '__q_date':
        return new Date(source)

      case '__q_expr':
        return new RegExp(source)

      case '__q_numb':
        return Number(source)

      case '__q_bool':
        return Boolean(source === '1')

      case '__q_strn':
        return '' + source

      case '__q_objt':
        return JSON.parse(source)

      default:
        // hmm, we reached here, we don't know the type,
        // then it means it wasn't encoded by us, so just
        // return whatever value it is
        return value
    }
  }

  function getEmptyStorage () {
    var fn = function () {};

    return {
      has: fn,
      get: {
        length: fn,
        item: fn,
        index: fn,
        all: fn
      },
      set: fn,
      remove: fn,
      clear: fn,
      isEmpty: fn
    }
  }

  function getStorage (type) {
    var
      webStorage = window[type + 'Storage'],
      get = function (key) {
        var item = webStorage.getItem(key);
        return item
          ? decode$1(item)
          : null
      };

    return {
      has: function (key) { return webStorage.getItem(key) !== null; },
      getLength: function () { return webStorage.length; },
      getItem: get,
      getIndex: function (index) {
        if (index < webStorage.length) {
          return get(webStorage.key(index))
        }
      },
      getAll: function () {
        var result = {}, key, len = webStorage.length;

        for (var i = 0; i < len; i++) {
          key = webStorage.key(i);
          result[key] = get(key);
        }

        return result
      },
      set: function (key, value) { webStorage.setItem(key, encode$1(value)); },
      remove: function (key) { webStorage.removeItem(key); },
      clear: function () { webStorage.clear(); },
      isEmpty: function () { return webStorage.length === 0; }
    }
  }

  var LocalStorage = {
    install: function install (ref) {
      var $q = ref.$q;

      var storage = isSSR === true || hasWebStorage() === false
        ? getEmptyStorage()
        : getStorage('local');

      $q.localStorage = storage;
      Object.assign(this, storage);
    }
  };

  var SessionStorage = {
    install: function install (ref) {
      var $q = ref.$q;

      var storage = isSSR === true || hasWebStorage() === false
        ? getEmptyStorage()
        : getStorage('session');

      $q.sessionStorage = storage;
      Object.assign(this, storage);
    }
  };



  var plugins = /*#__PURE__*/Object.freeze({
    AddressbarColor: AddressbarColor,
    AppFullscreen: AppFullscreen,
    AppVisibility: AppVisibility,
    BottomSheet: BottomSheet$1,
    Cookies: Cookies,
    Dialog: Dialog,
    LoadingBar: LoadingBar,
    Loading: Loading,
    Meta: Meta,
    Notify: Notify,
    Platform: Platform,
    Screen: Screen,
    LocalStorage: LocalStorage,
    SessionStorage: SessionStorage
  });

  function openUrl (url, reject) {
    var open = window.open;

    if (Platform.is.cordova === true) {
      if (cordova !== void 0 && cordova.InAppBrowser !== void 0 && cordova.InAppBrowser.open !== void 0) {
        open = cordova.InAppBrowser.open;
      }
      else if (navigator !== void 0 && navigator.app !== void 0) {
        return navigator.app.loadUrl(url, {
          openExternal: true
        })
      }
    }
    else if (Vue.prototype.$q.electron !== void 0) {
      return Vue.prototype.$q.electron.shell.openExternal(url)
    }

    var win = open(url, '_blank');

    if (win) {
      win.focus();
      return win
    }
    else {
      reject && reject();
    }
  }

  function noop () {}

  var utils = /*#__PURE__*/Object.freeze({
    clone: clone$1,
    colors: colors,
    date: date,
    debounce: debounce,
    dom: dom,
    event: event,
    extend: extend,
    format: format,
    frameDebounce: frameDebounce,
    noop: noop,
    openURL: openUrl,
    patterns: patterns,
    scroll: scroll,
    throttle: throttle,
    uid: uid
  });

  Vue.use({ install: install }, {
    components: components$1,
    directives: directives,
    plugins: plugins,
    config: window.quasarConfig || {}
  });

  var index_umd = {
    version: version,
    lang: lang,
    iconSet: iconSet,
    components: components$1,
    directives: directives,
    plugins: plugins,
    utils: utils
  };

  return index_umd;

}));
