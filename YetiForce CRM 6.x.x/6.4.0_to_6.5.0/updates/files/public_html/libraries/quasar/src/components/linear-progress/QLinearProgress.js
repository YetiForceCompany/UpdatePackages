import Vue from 'vue'

import DarkMixin from '../../mixins/dark.js'
import { getSizeMixin } from '../../mixins/size.js'
import ListenersMixin from '../../mixins/listeners.js'

import { mergeSlot } from '../../utils/private/slot.js'

function width (val, reverse, $q) {
  return {
    transform: reverse === true
      ? `translateX(${$q.lang.rtl === true ? '-' : ''}100%) scale3d(${-val},1,1)`
      : `scale3d(${val},1,1)`
  }
}

export default Vue.extend({
  name: 'QLinearProgress',

  mixins: [
    ListenersMixin,
    DarkMixin,
    getSizeMixin({
      xs: 2,
      sm: 4,
      md: 6,
      lg: 10,
      xl: 14
    })
  ],

  props: {
    value: {
      type: Number,
      default: 0
    },
    buffer: Number,

    color: String,
    trackColor: String,

    reverse: Boolean,
    stripe: Boolean,
    indeterminate: Boolean,
    query: Boolean,
    rounded: Boolean,

    instantFeedback: Boolean
  },

  computed: {
    motion () {
      return this.indeterminate === true || this.query === true
    },

    widthReverse () {
      return this.reverse !== this.query
    },

    classes () {
      return 'q-linear-progress' +
        (this.color !== void 0 ? ` text-${this.color}` : '') +
        (this.reverse === true || this.query === true ? ' q-linear-progress--reverse' : '') +
        (this.rounded === true ? ' rounded-borders' : '')
    },

    trackStyle () {
      return width(this.buffer !== void 0 ? this.buffer : 1, this.widthReverse, this.$q)
    },

    transitionSuffix () {
      return `with${this.instantFeedback === true ? 'out' : ''}-transition`
    },

    trackClass () {
      return `q-linear-progress__track absolute-full q-linear-progress__track--${this.transitionSuffix}` +
        ` q-linear-progress__track--${this.isDark === true ? 'dark' : 'light'}` +
        (this.trackColor !== void 0 ? ` bg-${this.trackColor}` : '')
    },

    modelStyle () {
      return width(this.motion === true ? 1 : this.value, this.widthReverse, this.$q)
    },

    modelClasses () {
      return `q-linear-progress__model absolute-full q-linear-progress__model--${this.transitionSuffix}` +
        ` q-linear-progress__model--${this.motion === true ? 'in' : ''}determinate`
    },

    stripeStyle () {
      return { width: (this.value * 100) + '%' }
    },

    stripeClass () {
      return `q-linear-progress__stripe q-linear-progress__stripe--${this.transitionSuffix}`
        + ` absolute-${this.reverse === true ? 'right' : 'left'}`
    },

    attrs () {
      return {
        role: 'progressbar',
        'aria-valuemin': 0,
        'aria-valuemax': 1,
        'aria-valuenow': this.indeterminate === true ? void 0 : this.value
      }
    }
  },

  render (h) {
    const child = [
      h('div', {
        style: this.trackStyle,
        class: this.trackClass
      }),

      h('div', {
        style: this.modelStyle,
        class: this.modelClasses
      })
    ]

    this.stripe === true && this.motion === false && child.push(
      h('div', {
        style: this.stripeStyle,
        class: this.stripeClass
      })
    )

    return h('div', {
      style: this.sizeStyle,
      class: this.classes,
      attrs: this.attrs,
      on: { ...this.qListeners }
    }, mergeSlot(child, this, 'default'))
  }
})
