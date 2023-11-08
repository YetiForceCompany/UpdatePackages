import { ResizeObserver as ResizeObserver$1 } from 'vue-resize';
import { ObserveVisibility } from 'vue-observe-visibility';
import ScrollParent from 'scrollparent';
import Vue from 'vue';

var config = {
  itemsLimit: 1000
};

const props = {
  items: {
    type: Array,
    required: true
  },
  keyField: {
    type: String,
    default: 'id'
  },
  direction: {
    type: String,
    default: 'vertical',
    validator: value => ['vertical', 'horizontal'].includes(value)
  },
  listTag: {
    type: String,
    default: 'div'
  },
  itemTag: {
    type: String,
    default: 'div'
  }
};
function simpleArray() {
  return this.items.length && typeof this.items[0] !== 'object';
}

let supportsPassive = false;
if (typeof window !== 'undefined') {
  supportsPassive = false;
  try {
    var opts = Object.defineProperty({}, 'passive', {
      get() {
        supportsPassive = true;
      }
    });
    window.addEventListener('test', null, opts);
  } catch (e) {}
}

//
let uid = 0;
var script$2 = {
  name: 'RecycleScroller',
  components: {
    ResizeObserver: ResizeObserver$1
  },
  directives: {
    ObserveVisibility
  },
  props: {
    ...props,
    itemSize: {
      type: Number,
      default: null
    },
    gridItems: {
      type: Number,
      default: undefined
    },
    itemSecondarySize: {
      type: Number,
      default: undefined
    },
    minItemSize: {
      type: [Number, String],
      default: null
    },
    sizeField: {
      type: String,
      default: 'size'
    },
    typeField: {
      type: String,
      default: 'type'
    },
    buffer: {
      type: Number,
      default: 200
    },
    pageMode: {
      type: Boolean,
      default: false
    },
    prerender: {
      type: Number,
      default: 0
    },
    emitUpdate: {
      type: Boolean,
      default: false
    },
    skipHover: {
      type: Boolean,
      default: false
    },
    listTag: {
      type: String,
      default: 'div'
    },
    itemTag: {
      type: String,
      default: 'div'
    },
    listClass: {
      type: [String, Object, Array],
      default: ''
    },
    itemClass: {
      type: [String, Object, Array],
      default: ''
    }
  },
  data() {
    return {
      pool: [],
      totalSize: 0,
      ready: false,
      hoverKey: null
    };
  },
  computed: {
    sizes() {
      if (this.itemSize === null) {
        const sizes = {
          '-1': {
            accumulator: 0
          }
        };
        const items = this.items;
        const field = this.sizeField;
        const minItemSize = this.minItemSize;
        let computedMinSize = 10000;
        let accumulator = 0;
        let current;
        for (let i = 0, l = items.length; i < l; i++) {
          current = items[i][field] || minItemSize;
          if (current < computedMinSize) {
            computedMinSize = current;
          }
          accumulator += current;
          sizes[i] = {
            accumulator,
            size: current
          };
        }
        // eslint-disable-next-line
        this.$_computedMinItemSize = computedMinSize;
        return sizes;
      }
      return [];
    },
    simpleArray
  },
  watch: {
    items() {
      this.updateVisibleItems(true);
    },
    pageMode() {
      this.applyPageMode();
      this.updateVisibleItems(false);
    },
    sizes: {
      handler() {
        this.updateVisibleItems(false);
      },
      deep: true
    },
    gridItems() {
      this.updateVisibleItems(true);
    },
    itemSecondarySize() {
      this.updateVisibleItems(true);
    }
  },
  created() {
    this.$_startIndex = 0;
    this.$_endIndex = 0;
    this.$_views = new Map();
    this.$_unusedViews = new Map();
    this.$_scrollDirty = false;
    this.$_lastUpdateScrollPosition = 0;

    // In SSR mode, we also prerender the same number of item for the first render
    // to avoir mismatch between server and client templates
    if (this.prerender) {
      this.$_prerender = true;
      this.updateVisibleItems(false);
    }
    if (this.gridItems && !this.itemSize) {
      console.error('[vue-recycle-scroller] You must provide an itemSize when using gridItems');
    }
  },
  mounted() {
    this.applyPageMode();
    this.$nextTick(() => {
      // In SSR mode, render the real number of visible items
      this.$_prerender = false;
      this.updateVisibleItems(true);
      this.ready = true;
    });
  },
  activated() {
    const lastPosition = this.$_lastUpdateScrollPosition;
    if (typeof lastPosition === 'number') {
      this.$nextTick(() => {
        this.scrollToPosition(lastPosition);
      });
    }
  },
  beforeDestroy() {
    this.removeListeners();
  },
  methods: {
    addView(pool, index, item, key, type) {
      const view = {
        item,
        position: 0
      };
      const nonReactive = {
        id: uid++,
        index,
        used: true,
        key,
        type
      };
      Object.defineProperty(view, 'nr', {
        configurable: false,
        value: nonReactive
      });
      pool.push(view);
      return view;
    },
    unuseView(view, fake = false) {
      const unusedViews = this.$_unusedViews;
      const type = view.nr.type;
      let unusedPool = unusedViews.get(type);
      if (!unusedPool) {
        unusedPool = [];
        unusedViews.set(type, unusedPool);
      }
      unusedPool.push(view);
      if (!fake) {
        view.nr.used = false;
        view.position = -9999;
        this.$_views.delete(view.nr.key);
      }
    },
    handleResize() {
      this.$emit('resize');
      if (this.ready) this.updateVisibleItems(false);
    },
    handleScroll(event) {
      if (!this.$_scrollDirty) {
        this.$_scrollDirty = true;
        requestAnimationFrame(() => {
          this.$_scrollDirty = false;
          const {
            continuous
          } = this.updateVisibleItems(false, true);

          // It seems sometimes chrome doesn't fire scroll event :/
          // When non continous scrolling is ending, we force a refresh
          if (!continuous) {
            clearTimeout(this.$_refreshTimout);
            this.$_refreshTimout = setTimeout(this.handleScroll, 100);
          }
        });
      }
    },
    handleVisibilityChange(isVisible, entry) {
      if (this.ready) {
        if (isVisible || entry.boundingClientRect.width !== 0 || entry.boundingClientRect.height !== 0) {
          this.$emit('visible');
          requestAnimationFrame(() => {
            this.updateVisibleItems(false);
          });
        } else {
          this.$emit('hidden');
        }
      }
    },
    updateVisibleItems(checkItem, checkPositionDiff = false) {
      const itemSize = this.itemSize;
      const gridItems = this.gridItems || 1;
      const itemSecondarySize = this.itemSecondarySize || itemSize;
      const minItemSize = this.$_computedMinItemSize;
      const typeField = this.typeField;
      const keyField = this.simpleArray ? null : this.keyField;
      const items = this.items;
      const count = items.length;
      const sizes = this.sizes;
      const views = this.$_views;
      const unusedViews = this.$_unusedViews;
      const pool = this.pool;
      let startIndex, endIndex;
      let totalSize;
      let visibleStartIndex, visibleEndIndex;
      if (!count) {
        startIndex = endIndex = visibleStartIndex = visibleEndIndex = totalSize = 0;
      } else if (this.$_prerender) {
        startIndex = visibleStartIndex = 0;
        endIndex = visibleEndIndex = Math.min(this.prerender, items.length);
        totalSize = null;
      } else {
        const scroll = this.getScroll();

        // Skip update if use hasn't scrolled enough
        if (checkPositionDiff) {
          let positionDiff = scroll.start - this.$_lastUpdateScrollPosition;
          if (positionDiff < 0) positionDiff = -positionDiff;
          if (itemSize === null && positionDiff < minItemSize || positionDiff < itemSize) {
            return {
              continuous: true
            };
          }
        }
        this.$_lastUpdateScrollPosition = scroll.start;
        const buffer = this.buffer;
        scroll.start -= buffer;
        scroll.end += buffer;

        // account for leading slot
        let beforeSize = 0;
        if (this.$refs.before) {
          beforeSize = this.$refs.before.scrollHeight;
          scroll.start -= beforeSize;
        }

        // account for trailing slot
        if (this.$refs.after) {
          const afterSize = this.$refs.after.scrollHeight;
          scroll.end += afterSize;
        }

        // Variable size mode
        if (itemSize === null) {
          let h;
          let a = 0;
          let b = count - 1;
          let i = ~~(count / 2);
          let oldI;

          // Searching for startIndex
          do {
            oldI = i;
            h = sizes[i].accumulator;
            if (h < scroll.start) {
              a = i;
            } else if (i < count - 1 && sizes[i + 1].accumulator > scroll.start) {
              b = i;
            }
            i = ~~((a + b) / 2);
          } while (i !== oldI);
          i < 0 && (i = 0);
          startIndex = i;

          // For container style
          totalSize = sizes[count - 1].accumulator;

          // Searching for endIndex
          for (endIndex = i; endIndex < count && sizes[endIndex].accumulator < scroll.end; endIndex++);
          if (endIndex === -1) {
            endIndex = items.length - 1;
          } else {
            endIndex++;
            // Bounds
            endIndex > count && (endIndex = count);
          }

          // search visible startIndex
          for (visibleStartIndex = startIndex; visibleStartIndex < count && beforeSize + sizes[visibleStartIndex].accumulator < scroll.start; visibleStartIndex++);

          // search visible endIndex
          for (visibleEndIndex = visibleStartIndex; visibleEndIndex < count && beforeSize + sizes[visibleEndIndex].accumulator < scroll.end; visibleEndIndex++);
        } else {
          // Fixed size mode
          startIndex = ~~(scroll.start / itemSize * gridItems);
          const remainer = startIndex % gridItems;
          startIndex -= remainer;
          endIndex = Math.ceil(scroll.end / itemSize * gridItems);
          visibleStartIndex = Math.max(0, Math.floor((scroll.start - beforeSize) / itemSize * gridItems));
          visibleEndIndex = Math.floor((scroll.end - beforeSize) / itemSize * gridItems);

          // Bounds
          startIndex < 0 && (startIndex = 0);
          endIndex > count && (endIndex = count);
          visibleStartIndex < 0 && (visibleStartIndex = 0);
          visibleEndIndex > count && (visibleEndIndex = count);
          totalSize = Math.ceil(count / gridItems) * itemSize;
        }
      }
      if (endIndex - startIndex > config.itemsLimit) {
        this.itemsLimitError();
      }
      this.totalSize = totalSize;
      let view;
      const continuous = startIndex <= this.$_endIndex && endIndex >= this.$_startIndex;
      if (this.$_continuous !== continuous) {
        if (continuous) {
          views.clear();
          unusedViews.clear();
          for (let i = 0, l = pool.length; i < l; i++) {
            view = pool[i];
            this.unuseView(view);
          }
        }
        this.$_continuous = continuous;
      } else if (continuous) {
        for (let i = 0, l = pool.length; i < l; i++) {
          view = pool[i];
          if (view.nr.used) {
            // Update view item index
            if (checkItem) {
              view.nr.index = items.indexOf(view.item);
            }

            // Check if index is still in visible range
            if (view.nr.index === -1 || view.nr.index < startIndex || view.nr.index >= endIndex) {
              this.unuseView(view);
            }
          }
        }
      }
      const unusedIndex = continuous ? null : new Map();
      let item, type, unusedPool;
      let v;
      for (let i = startIndex; i < endIndex; i++) {
        item = items[i];
        const key = keyField ? item[keyField] : item;
        if (key == null) {
          throw new Error(`Key is ${key} on item (keyField is '${keyField}')`);
        }
        view = views.get(key);
        if (!itemSize && !sizes[i].size) {
          if (view) this.unuseView(view);
          continue;
        }

        // No view assigned to item
        if (!view) {
          if (i === items.length - 1) this.$emit('scroll-end');
          if (i === 0) this.$emit('scroll-start');
          type = item[typeField];
          unusedPool = unusedViews.get(type);
          if (continuous) {
            // Reuse existing view
            if (unusedPool && unusedPool.length) {
              view = unusedPool.pop();
              view.item = item;
              view.nr.used = true;
              view.nr.index = i;
              view.nr.key = key;
              view.nr.type = type;
            } else {
              view = this.addView(pool, i, item, key, type);
            }
          } else {
            // Use existing view
            // We don't care if they are already used
            // because we are not in continous scrolling
            v = unusedIndex.get(type) || 0;
            if (!unusedPool || v >= unusedPool.length) {
              view = this.addView(pool, i, item, key, type);
              this.unuseView(view, true);
              unusedPool = unusedViews.get(type);
            }
            view = unusedPool[v];
            view.item = item;
            view.nr.used = true;
            view.nr.index = i;
            view.nr.key = key;
            view.nr.type = type;
            unusedIndex.set(type, v + 1);
            v++;
          }
          views.set(key, view);
        } else {
          view.nr.used = true;
          view.item = item;
        }

        // Update position
        if (itemSize === null) {
          view.position = sizes[i - 1].accumulator;
          view.offset = 0;
        } else {
          view.position = Math.floor(i / gridItems) * itemSize;
          view.offset = i % gridItems * itemSecondarySize;
        }
      }
      this.$_startIndex = startIndex;
      this.$_endIndex = endIndex;
      if (this.emitUpdate) this.$emit('update', startIndex, endIndex, visibleStartIndex, visibleEndIndex);

      // After the user has finished scrolling
      // Sort views so text selection is correct
      clearTimeout(this.$_sortTimer);
      this.$_sortTimer = setTimeout(this.sortViews, 300);
      return {
        continuous
      };
    },
    getListenerTarget() {
      let target = ScrollParent(this.$el);
      // Fix global scroll target for Chrome and Safari
      if (window.document && (target === window.document.documentElement || target === window.document.body)) {
        target = window;
      }
      return target;
    },
    getScroll() {
      const {
        $el: el,
        direction
      } = this;
      const isVertical = direction === 'vertical';
      let scrollState;
      if (this.pageMode) {
        const bounds = el.getBoundingClientRect();
        const boundsSize = isVertical ? bounds.height : bounds.width;
        let start = -(isVertical ? bounds.top : bounds.left);
        let size = isVertical ? window.innerHeight : window.innerWidth;
        if (start < 0) {
          size += start;
          start = 0;
        }
        if (start + size > boundsSize) {
          size = boundsSize - start;
        }
        scrollState = {
          start,
          end: start + size
        };
      } else if (isVertical) {
        scrollState = {
          start: el.scrollTop,
          end: el.scrollTop + el.clientHeight
        };
      } else {
        scrollState = {
          start: el.scrollLeft,
          end: el.scrollLeft + el.clientWidth
        };
      }
      return scrollState;
    },
    applyPageMode() {
      if (this.pageMode) {
        this.addListeners();
      } else {
        this.removeListeners();
      }
    },
    addListeners() {
      this.listenerTarget = this.getListenerTarget();
      this.listenerTarget.addEventListener('scroll', this.handleScroll, supportsPassive ? {
        passive: true
      } : false);
      this.listenerTarget.addEventListener('resize', this.handleResize);
    },
    removeListeners() {
      if (!this.listenerTarget) {
        return;
      }
      this.listenerTarget.removeEventListener('scroll', this.handleScroll);
      this.listenerTarget.removeEventListener('resize', this.handleResize);
      this.listenerTarget = null;
    },
    scrollToItem(index) {
      let scroll;
      if (this.itemSize === null) {
        scroll = index > 0 ? this.sizes[index - 1].accumulator : 0;
      } else {
        scroll = Math.floor(index / this.gridItems) * this.itemSize;
      }
      this.scrollToPosition(scroll);
    },
    scrollToPosition(position) {
      const direction = this.direction === 'vertical' ? {
        scroll: 'scrollTop',
        start: 'top'
      } : {
        scroll: 'scrollLeft',
        start: 'left'
      };
      let viewport;
      let scrollDirection;
      let scrollDistance;
      if (this.pageMode) {
        const viewportEl = ScrollParent(this.$el);
        // HTML doesn't overflow like other elements
        const scrollTop = viewportEl.tagName === 'HTML' ? 0 : viewportEl[direction.scroll];
        const bounds = viewportEl.getBoundingClientRect();
        const scroller = this.$el.getBoundingClientRect();
        const scrollerPosition = scroller[direction.start] - bounds[direction.start];
        viewport = viewportEl;
        scrollDirection = direction.scroll;
        scrollDistance = position + scrollTop + scrollerPosition;
      } else {
        viewport = this.$el;
        scrollDirection = direction.scroll;
        scrollDistance = position;
      }
      viewport[scrollDirection] = scrollDistance;
    },
    itemsLimitError() {
      setTimeout(() => {
        console.log('It seems the scroller element isn\'t scrolling, so it tries to render all the items at once.', 'Scroller:', this.$el);
        console.log('Make sure the scroller has a fixed height (or width) and \'overflow-y\' (or \'overflow-x\') set to \'auto\' so it can scroll correctly and only render the items visible in the scroll viewport.');
      });
      throw new Error('Rendered items limit reached');
    },
    sortViews() {
      this.pool.sort((viewA, viewB) => viewA.nr.index - viewB.nr.index);
    }
  }
};

function normalizeComponent(template, style, script, scopeId, isFunctionalTemplate, moduleIdentifier /* server only */, shadowMode, createInjector, createInjectorSSR, createInjectorShadow) {
  if (typeof shadowMode !== 'boolean') {
    createInjectorSSR = createInjector;
    createInjector = shadowMode;
    shadowMode = false;
  }
  // Vue.extend constructor export interop.
  const options = typeof script === 'function' ? script.options : script;
  // render functions
  if (template && template.render) {
    options.render = template.render;
    options.staticRenderFns = template.staticRenderFns;
    options._compiled = true;
    // functional template
    if (isFunctionalTemplate) {
      options.functional = true;
    }
  }
  // scopedId
  if (scopeId) {
    options._scopeId = scopeId;
  }
  let hook;
  if (moduleIdentifier) {
    // server build
    hook = function (context) {
      // 2.3 injection
      context = context ||
      // cached call
      this.$vnode && this.$vnode.ssrContext ||
      // stateful
      this.parent && this.parent.$vnode && this.parent.$vnode.ssrContext; // functional
      // 2.2 with runInNewContext: true
      if (!context && typeof __VUE_SSR_CONTEXT__ !== 'undefined') {
        context = __VUE_SSR_CONTEXT__;
      }
      // inject component styles
      if (style) {
        style.call(this, createInjectorSSR(context));
      }
      // register component module identifier for async chunk inference
      if (context && context._registeredComponents) {
        context._registeredComponents.add(moduleIdentifier);
      }
    };
    // used by ssr in case component is cached and beforeCreate
    // never gets called
    options._ssrRegister = hook;
  } else if (style) {
    hook = shadowMode ? function (context) {
      style.call(this, createInjectorShadow(context, this.$root.$options.shadowRoot));
    } : function (context) {
      style.call(this, createInjector(context));
    };
  }
  if (hook) {
    if (options.functional) {
      // register for functional component in vue file
      const originalRender = options.render;
      options.render = function renderWithStyleInjection(h, context) {
        hook.call(context);
        return originalRender(h, context);
      };
    } else {
      // inject component registration as beforeCreate hook
      const existing = options.beforeCreate;
      options.beforeCreate = existing ? [].concat(existing, hook) : [hook];
    }
  }
  return script;
}

/* script */
const __vue_script__$2 = script$2;
/* template */
var __vue_render__$1 = function () {
  var _obj, _obj$1;
  var _vm = this;
  var _h = _vm.$createElement;
  var _c = _vm._self._c || _h;
  return _c(
    "div",
    {
      directives: [
        {
          name: "observe-visibility",
          rawName: "v-observe-visibility",
          value: _vm.handleVisibilityChange,
          expression: "handleVisibilityChange",
        },
      ],
      staticClass: "vue-recycle-scroller",
      class:
        ((_obj = {
          ready: _vm.ready,
          "page-mode": _vm.pageMode,
        }),
        (_obj["direction-" + _vm.direction] = true),
        _obj),
      on: {
        "&scroll": function ($event) {
          return _vm.handleScroll.apply(null, arguments)
        },
      },
    },
    [
      _vm.$slots.before
        ? _c(
            "div",
            { ref: "before", staticClass: "vue-recycle-scroller__slot" },
            [_vm._t("before")],
            2
          )
        : _vm._e(),
      _vm._v(" "),
      _c(
        _vm.listTag,
        {
          ref: "wrapper",
          tag: "component",
          staticClass: "vue-recycle-scroller__item-wrapper",
          class: _vm.listClass,
          style:
            ((_obj$1 = {}),
            (_obj$1[_vm.direction === "vertical" ? "minHeight" : "minWidth"] =
              _vm.totalSize + "px"),
            _obj$1),
        },
        [
          _vm._l(_vm.pool, function (view) {
            return _c(
              _vm.itemTag,
              _vm._g(
                {
                  key: view.nr.id,
                  tag: "component",
                  staticClass: "vue-recycle-scroller__item-view",
                  class: [
                    _vm.itemClass,
                    {
                      hover: !_vm.skipHover && _vm.hoverKey === view.nr.key,
                    },
                  ],
                  style: _vm.ready
                    ? {
                        transform:
                          "translate" +
                          (_vm.direction === "vertical" ? "Y" : "X") +
                          "(" +
                          view.position +
                          "px) translate" +
                          (_vm.direction === "vertical" ? "X" : "Y") +
                          "(" +
                          view.offset +
                          "px)",
                        width: _vm.gridItems
                          ? (_vm.direction === "vertical"
                              ? _vm.itemSecondarySize || _vm.itemSize
                              : _vm.itemSize) + "px"
                          : undefined,
                        height: _vm.gridItems
                          ? (_vm.direction === "horizontal"
                              ? _vm.itemSecondarySize || _vm.itemSize
                              : _vm.itemSize) + "px"
                          : undefined,
                      }
                    : null,
                },
                _vm.skipHover
                  ? {}
                  : {
                      mouseenter: function () {
                        _vm.hoverKey = view.nr.key;
                      },
                      mouseleave: function () {
                        _vm.hoverKey = null;
                      },
                    }
              ),
              [
                _vm._t("default", null, {
                  item: view.item,
                  index: view.nr.index,
                  active: view.nr.used,
                }),
              ],
              2
            )
          }),
          _vm._v(" "),
          _vm._t("empty"),
        ],
        2
      ),
      _vm._v(" "),
      _vm.$slots.after
        ? _c(
            "div",
            { ref: "after", staticClass: "vue-recycle-scroller__slot" },
            [_vm._t("after")],
            2
          )
        : _vm._e(),
      _vm._v(" "),
      _c("ResizeObserver", { on: { notify: _vm.handleResize } }),
    ],
    1
  )
};
var __vue_staticRenderFns__$1 = [];
__vue_render__$1._withStripped = true;

  /* style */
  const __vue_inject_styles__$2 = undefined;
  /* scoped */
  const __vue_scope_id__$2 = undefined;
  /* module identifier */
  const __vue_module_identifier__$2 = undefined;
  /* functional template */
  const __vue_is_functional_template__$2 = false;
  /* style inject */
  
  /* style inject SSR */
  
  /* style inject shadow dom */
  

  
  const __vue_component__$2 = /*#__PURE__*/normalizeComponent(
    { render: __vue_render__$1, staticRenderFns: __vue_staticRenderFns__$1 },
    __vue_inject_styles__$2,
    __vue_script__$2,
    __vue_scope_id__$2,
    __vue_is_functional_template__$2,
    __vue_module_identifier__$2,
    false,
    undefined,
    undefined,
    undefined
  );

//
var script$1 = {
  name: 'DynamicScroller',
  components: {
    RecycleScroller: __vue_component__$2
  },
  provide() {
    if (typeof ResizeObserver !== 'undefined') {
      this.$_resizeObserver = new ResizeObserver(entries => {
        requestAnimationFrame(() => {
          if (!Array.isArray(entries)) {
            return;
          }
          for (const entry of entries) {
            if (entry.target) {
              const event = new CustomEvent('resize', {
                detail: {
                  contentRect: entry.contentRect
                }
              });
              entry.target.dispatchEvent(event);
            }
          }
        });
      });
    }
    return {
      vscrollData: this.vscrollData,
      vscrollParent: this,
      vscrollResizeObserver: this.$_resizeObserver
    };
  },
  inheritAttrs: false,
  props: {
    ...props,
    minItemSize: {
      type: [Number, String],
      required: true
    }
  },
  data() {
    return {
      vscrollData: {
        active: true,
        sizes: {},
        validSizes: {},
        keyField: this.keyField,
        simpleArray: false
      }
    };
  },
  computed: {
    simpleArray,
    itemsWithSize() {
      const result = [];
      const {
        items,
        keyField,
        simpleArray
      } = this;
      const sizes = this.vscrollData.sizes;
      const l = items.length;
      for (let i = 0; i < l; i++) {
        const item = items[i];
        const id = simpleArray ? i : item[keyField];
        let size = sizes[id];
        if (typeof size === 'undefined' && !this.$_undefinedMap[id]) {
          size = 0;
        }
        result.push({
          item,
          id,
          size
        });
      }
      return result;
    },
    listeners() {
      const listeners = {};
      for (const key in this.$listeners) {
        if (key !== 'resize' && key !== 'visible') {
          listeners[key] = this.$listeners[key];
        }
      }
      return listeners;
    }
  },
  watch: {
    items() {
      this.forceUpdate(false);
    },
    simpleArray: {
      handler(value) {
        this.vscrollData.simpleArray = value;
      },
      immediate: true
    },
    direction(value) {
      this.forceUpdate(true);
    },
    itemsWithSize(next, prev) {
      const scrollTop = this.$el.scrollTop;

      // Calculate total diff between prev and next sizes
      // over current scroll top. Then add it to scrollTop to
      // avoid jumping the contents that the user is seeing.
      let prevActiveTop = 0;
      let activeTop = 0;
      const length = Math.min(next.length, prev.length);
      for (let i = 0; i < length; i++) {
        if (prevActiveTop >= scrollTop) {
          break;
        }
        prevActiveTop += prev[i].size || this.minItemSize;
        activeTop += next[i].size || this.minItemSize;
      }
      const offset = activeTop - prevActiveTop;
      if (offset === 0) {
        return;
      }
      this.$el.scrollTop += offset;
    }
  },
  beforeCreate() {
    this.$_updates = [];
    this.$_undefinedSizes = 0;
    this.$_undefinedMap = {};
  },
  activated() {
    this.vscrollData.active = true;
  },
  deactivated() {
    this.vscrollData.active = false;
  },
  methods: {
    onScrollerResize() {
      const scroller = this.$refs.scroller;
      if (scroller) {
        this.forceUpdate();
      }
      this.$emit('resize');
    },
    onScrollerVisible() {
      this.$emit('vscroll:update', {
        force: false
      });
      this.$emit('visible');
    },
    forceUpdate(clear = true) {
      if (clear || this.simpleArray) {
        this.vscrollData.validSizes = {};
      }
      this.$emit('vscroll:update', {
        force: true
      });
    },
    scrollToItem(index) {
      const scroller = this.$refs.scroller;
      if (scroller) scroller.scrollToItem(index);
    },
    getItemSize(item, index = undefined) {
      const id = this.simpleArray ? index != null ? index : this.items.indexOf(item) : item[this.keyField];
      return this.vscrollData.sizes[id] || 0;
    },
    scrollToBottom() {
      if (this.$_scrollingToBottom) return;
      this.$_scrollingToBottom = true;
      const el = this.$el;
      // Item is inserted to the DOM
      this.$nextTick(() => {
        el.scrollTop = el.scrollHeight + 5000;
        // Item sizes are computed
        const cb = () => {
          el.scrollTop = el.scrollHeight + 5000;
          requestAnimationFrame(() => {
            el.scrollTop = el.scrollHeight + 5000;
            if (this.$_undefinedSizes === 0) {
              this.$_scrollingToBottom = false;
            } else {
              requestAnimationFrame(cb);
            }
          });
        };
        requestAnimationFrame(cb);
      });
    }
  }
};

/* script */
const __vue_script__$1 = script$1;

/* template */
var __vue_render__ = function () {
  var _vm = this;
  var _h = _vm.$createElement;
  var _c = _vm._self._c || _h;
  return _c(
    "RecycleScroller",
    _vm._g(
      _vm._b(
        {
          ref: "scroller",
          attrs: {
            items: _vm.itemsWithSize,
            "min-item-size": _vm.minItemSize,
            direction: _vm.direction,
            "key-field": "id",
            "list-tag": _vm.listTag,
            "item-tag": _vm.itemTag,
          },
          on: { resize: _vm.onScrollerResize, visible: _vm.onScrollerVisible },
          scopedSlots: _vm._u(
            [
              {
                key: "default",
                fn: function (ref) {
                  var itemWithSize = ref.item;
                  var index = ref.index;
                  var active = ref.active;
                  return [
                    _vm._t("default", null, null, {
                      item: itemWithSize.item,
                      index: index,
                      active: active,
                      itemWithSize: itemWithSize,
                    }),
                  ]
                },
              },
            ],
            null,
            true
          ),
        },
        "RecycleScroller",
        _vm.$attrs,
        false
      ),
      _vm.listeners
    ),
    [
      _vm._v(" "),
      _c("template", { slot: "before" }, [_vm._t("before")], 2),
      _vm._v(" "),
      _c("template", { slot: "after" }, [_vm._t("after")], 2),
      _vm._v(" "),
      _c("template", { slot: "empty" }, [_vm._t("empty")], 2),
    ],
    2
  )
};
var __vue_staticRenderFns__ = [];
__vue_render__._withStripped = true;

  /* style */
  const __vue_inject_styles__$1 = undefined;
  /* scoped */
  const __vue_scope_id__$1 = undefined;
  /* module identifier */
  const __vue_module_identifier__$1 = undefined;
  /* functional template */
  const __vue_is_functional_template__$1 = false;
  /* style inject */
  
  /* style inject SSR */
  
  /* style inject shadow dom */
  

  
  const __vue_component__$1 = /*#__PURE__*/normalizeComponent(
    { render: __vue_render__, staticRenderFns: __vue_staticRenderFns__ },
    __vue_inject_styles__$1,
    __vue_script__$1,
    __vue_scope_id__$1,
    __vue_is_functional_template__$1,
    __vue_module_identifier__$1,
    false,
    undefined,
    undefined,
    undefined
  );

var script = {
  name: 'DynamicScrollerItem',
  inject: ['vscrollData', 'vscrollParent', 'vscrollResizeObserver'],
  props: {
    // eslint-disable-next-line vue/require-prop-types
    item: {
      required: true
    },
    watchData: {
      type: Boolean,
      default: false
    },
    /**
     * Indicates if the view is actively used to display an item.
     */
    active: {
      type: Boolean,
      required: true
    },
    index: {
      type: Number,
      default: undefined
    },
    sizeDependencies: {
      type: [Array, Object],
      default: null
    },
    emitResize: {
      type: Boolean,
      default: false
    },
    tag: {
      type: String,
      default: 'div'
    }
  },
  computed: {
    id() {
      if (this.vscrollData.simpleArray) return this.index;
      // eslint-disable-next-line no-prototype-builtins
      if (this.item.hasOwnProperty(this.vscrollData.keyField)) return this.item[this.vscrollData.keyField];
      throw new Error(`keyField '${this.vscrollData.keyField}' not found in your item. You should set a valid keyField prop on your Scroller`);
    },
    size() {
      return this.vscrollData.validSizes[this.id] && this.vscrollData.sizes[this.id] || 0;
    },
    finalActive() {
      return this.active && this.vscrollData.active;
    }
  },
  watch: {
    watchData: 'updateWatchData',
    id() {
      if (!this.size) {
        this.onDataUpdate();
      }
    },
    finalActive(value) {
      if (!this.size) {
        if (value) {
          if (!this.vscrollParent.$_undefinedMap[this.id]) {
            this.vscrollParent.$_undefinedSizes++;
            this.vscrollParent.$_undefinedMap[this.id] = true;
          }
        } else {
          if (this.vscrollParent.$_undefinedMap[this.id]) {
            this.vscrollParent.$_undefinedSizes--;
            this.vscrollParent.$_undefinedMap[this.id] = false;
          }
        }
      }
      if (this.vscrollResizeObserver) {
        if (value) {
          this.observeSize();
        } else {
          this.unobserveSize();
        }
      } else if (value && this.$_pendingVScrollUpdate === this.id) {
        this.updateSize();
      }
    }
  },
  created() {
    if (this.$isServer) return;
    this.$_forceNextVScrollUpdate = null;
    this.updateWatchData();
    if (!this.vscrollResizeObserver) {
      for (const k in this.sizeDependencies) {
        this.$watch(() => this.sizeDependencies[k], this.onDataUpdate);
      }
      this.vscrollParent.$on('vscroll:update', this.onVscrollUpdate);
      this.vscrollParent.$on('vscroll:update-size', this.onVscrollUpdateSize);
    }
  },
  mounted() {
    if (this.vscrollData.active) {
      this.updateSize();
      this.observeSize();
    }
  },
  beforeDestroy() {
    this.vscrollParent.$off('vscroll:update', this.onVscrollUpdate);
    this.vscrollParent.$off('vscroll:update-size', this.onVscrollUpdateSize);
    this.unobserveSize();
  },
  methods: {
    updateSize() {
      if (this.finalActive) {
        if (this.$_pendingSizeUpdate !== this.id) {
          this.$_pendingSizeUpdate = this.id;
          this.$_forceNextVScrollUpdate = null;
          this.$_pendingVScrollUpdate = null;
          this.computeSize(this.id);
        }
      } else {
        this.$_forceNextVScrollUpdate = this.id;
      }
    },
    updateWatchData() {
      if (this.watchData && !this.vscrollResizeObserver) {
        this.$_watchData = this.$watch('item', () => {
          this.onDataUpdate();
        }, {
          deep: true
        });
      } else if (this.$_watchData) {
        this.$_watchData();
        this.$_watchData = null;
      }
    },
    onVscrollUpdate({
      force
    }) {
      // If not active, sechedule a size update when it becomes active
      if (!this.finalActive && force) {
        this.$_pendingVScrollUpdate = this.id;
      }
      if (this.$_forceNextVScrollUpdate === this.id || force || !this.size) {
        this.updateSize();
      }
    },
    onDataUpdate() {
      this.updateSize();
    },
    computeSize(id) {
      this.$nextTick(() => {
        if (this.id === id) {
          const width = this.$el.offsetWidth;
          const height = this.$el.offsetHeight;
          this.applySize(width, height);
        }
        this.$_pendingSizeUpdate = null;
      });
    },
    applySize(width, height) {
      const size = ~~(this.vscrollParent.direction === 'vertical' ? height : width);
      if (size && this.size !== size) {
        if (this.vscrollParent.$_undefinedMap[this.id]) {
          this.vscrollParent.$_undefinedSizes--;
          this.vscrollParent.$_undefinedMap[this.id] = undefined;
        }
        this.$set(this.vscrollData.sizes, this.id, size);
        this.$set(this.vscrollData.validSizes, this.id, true);
        if (this.emitResize) this.$emit('resize', this.id);
      }
    },
    observeSize() {
      if (!this.vscrollResizeObserver || !this.$el.parentNode) return;
      this.vscrollResizeObserver.observe(this.$el.parentNode);
      this.$el.parentNode.addEventListener('resize', this.onResize);
    },
    unobserveSize() {
      if (!this.vscrollResizeObserver) return;
      this.vscrollResizeObserver.unobserve(this.$el.parentNode);
      this.$el.parentNode.removeEventListener('resize', this.onResize);
    },
    onResize(event) {
      const {
        width,
        height
      } = event.detail.contentRect;
      this.applySize(width, height);
    }
  },
  render(h) {
    return h(this.tag, this.$slots.default);
  }
};

/* script */
const __vue_script__ = script;

/* template */

  /* style */
  const __vue_inject_styles__ = undefined;
  /* scoped */
  const __vue_scope_id__ = undefined;
  /* module identifier */
  const __vue_module_identifier__ = undefined;
  /* functional template */
  const __vue_is_functional_template__ = undefined;
  /* style inject */
  
  /* style inject SSR */
  
  /* style inject shadow dom */
  

  
  const __vue_component__ = /*#__PURE__*/normalizeComponent(
    {},
    __vue_inject_styles__,
    __vue_script__,
    __vue_scope_id__,
    __vue_is_functional_template__,
    __vue_module_identifier__,
    false,
    undefined,
    undefined,
    undefined
  );

function IdState ({
  idProp = vm => vm.item.id
} = {}) {
  const store = {};
  const vm = new Vue({
    data() {
      return {
        store
      };
    }
  });

  // @vue/component
  return {
    data() {
      return {
        idState: null
      };
    },
    created() {
      this.$_id = null;
      if (typeof idProp === 'function') {
        this.$_getId = () => idProp.call(this, this);
      } else {
        this.$_getId = () => this[idProp];
      }
      this.$watch(this.$_getId, {
        handler(value) {
          this.$nextTick(() => {
            this.$_id = value;
          });
        },
        immediate: true
      });
      this.$_updateIdState();
    },
    beforeUpdate() {
      this.$_updateIdState();
    },
    methods: {
      /**
       * Initialize an idState
       * @param {number|string} id Unique id for the data
       */
      $_idStateInit(id) {
        const factory = this.$options.idState;
        if (typeof factory === 'function') {
          const data = factory.call(this, this);
          vm.$set(store, id, data);
          this.$_id = id;
          return data;
        } else {
          throw new Error('[mixin IdState] Missing `idState` function on component definition.');
        }
      },
      /**
       * Ensure idState is created and up-to-date
       */
      $_updateIdState() {
        const id = this.$_getId();
        if (id == null) {
          console.warn(`No id found for IdState with idProp: '${idProp}'.`);
        }
        if (id !== this.$_id) {
          if (!store[id]) {
            this.$_idStateInit(id);
          }
          this.idState = store[id];
        }
      }
    }
  };
}

function registerComponents(Vue, prefix) {
  Vue.component(`${prefix}recycle-scroller`, __vue_component__$2);
  Vue.component(`${prefix}RecycleScroller`, __vue_component__$2);
  Vue.component(`${prefix}dynamic-scroller`, __vue_component__$1);
  Vue.component(`${prefix}DynamicScroller`, __vue_component__$1);
  Vue.component(`${prefix}dynamic-scroller-item`, __vue_component__);
  Vue.component(`${prefix}DynamicScrollerItem`, __vue_component__);
}
const plugin = {
  // eslint-disable-next-line no-undef
  version: "1.1.2",
  install(Vue, options) {
    const finalOptions = Object.assign({}, {
      installComponents: true,
      componentsPrefix: ''
    }, options);
    for (const key in finalOptions) {
      if (typeof finalOptions[key] !== 'undefined') {
        config[key] = finalOptions[key];
      }
    }
    if (finalOptions.installComponents) {
      registerComponents(Vue, finalOptions.componentsPrefix);
    }
  }
};

// Auto-install
let GlobalVue = null;
if (typeof window !== 'undefined') {
  GlobalVue = window.Vue;
} else if (typeof global !== 'undefined') {
  GlobalVue = global.Vue;
}
if (GlobalVue) {
  GlobalVue.use(plugin);
}

export { __vue_component__$1 as DynamicScroller, __vue_component__ as DynamicScrollerItem, IdState, __vue_component__$2 as RecycleScroller, plugin as default };
//# sourceMappingURL=vue-virtual-scroller.esm.js.map
