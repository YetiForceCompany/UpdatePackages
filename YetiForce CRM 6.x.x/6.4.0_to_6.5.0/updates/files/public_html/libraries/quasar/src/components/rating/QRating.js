import Vue from 'vue'

import { stopAndPrevent } from '../../utils/event.js'
import { between } from '../../utils/format.js'
import QIcon from '../icon/QIcon.js'

import SizeMixin from '../../mixins/size.js'
import FormMixin from '../../mixins/form.js'
import ListenersMixin from '../../mixins/listeners.js'

import cache from '../../utils/private/cache.js'
import { mergeSlot } from '../../utils/private/slot.js'

export default Vue.extend({
  name: 'QRating',

  mixins: [ SizeMixin, FormMixin, ListenersMixin ],

  props: {
    value: {
      type: Number,
      required: true
    },

    max: {
      type: [String, Number],
      default: 5
    },

    icon: [String, Array],
    iconHalf: [String, Array],
    iconSelected: [String, Array],

    iconAriaLabel: [String, Array],

    color: [String, Array],
    colorHalf: [String, Array],
    colorSelected: [String, Array],

    noReset: Boolean,
    noDimming: Boolean,

    readonly: Boolean,
    disable: Boolean
  },

  data () {
    return {
      mouseModel: 0
    }
  },

  computed: {
    editable () {
      return this.readonly !== true && this.disable !== true
    },

    classes () {
      return `q-rating--${this.editable === true ? '' : 'non-'}editable` +
        (this.noDimming === true ? ' q-rating--no-dimming' : '') +
        (this.disable === true ? ' disabled' : '') +
        (this.color !== void 0 && Array.isArray(this.color) === false ? ` text-${this.color}` : '')
    },

    iconData () {
      const
        iconLen = Array.isArray(this.icon) === true ? this.icon.length : 0,
        selIconLen = Array.isArray(this.iconSelected) === true ? this.iconSelected.length : 0,
        halfIconLen = Array.isArray(this.iconHalf) === true ? this.iconHalf.length : 0,
        colorLen = Array.isArray(this.color) === true ? this.color.length : 0,
        selColorLen = Array.isArray(this.colorSelected) === true ? this.colorSelected.length : 0,
        halfColorLen = Array.isArray(this.colorHalf) === true ? this.colorHalf.length : 0

      return {
        iconLen,
        icon: iconLen > 0 ? this.icon[iconLen - 1] : this.icon,
        selIconLen,
        selIcon: selIconLen > 0 ? this.iconSelected[selIconLen - 1] : this.iconSelected,
        halfIconLen,
        halfIcon: halfIconLen > 0 ? this.iconHalf[selIconLen - 1] : this.iconHalf,
        colorLen,
        color: colorLen > 0 ? this.color[colorLen - 1] : this.color,
        selColorLen,
        selColor: selColorLen > 0 ? this.colorSelected[selColorLen - 1] : this.colorSelected,
        halfColorLen,
        halfColor: halfColorLen > 0 ? this.colorHalf[halfColorLen - 1] : this.colorHalf
      }
    },

    iconLabel () {
      if (typeof this.iconAriaLabel === 'string') {
        const label = this.iconAriaLabel.length > 0 ? `${this.iconAriaLabel} ` : ''

        return i => `${label}${i}`
      }

      if (Array.isArray(this.iconAriaLabel) === true) {
        const iMax = this.iconAriaLabel.length

        if (iMax > 0) {
          return i => this.iconAriaLabel[Math.min(i, iMax) - 1]
        }
      }

      return (i, label) => `${label} ${i}`
    },

    stars () {
      const
        acc = [],
        icons = this.iconData,
        ceil = Math.ceil(this.value),
        tabindex = this.editable === true ? 0 : null

      const halfIndex = this.iconHalf === void 0 || ceil === this.value
        ? -1
        : ceil

      for (let i = 1; i <= this.max; i++) {
        const
          active = (this.mouseModel === 0 && this.value >= i) || (this.mouseModel > 0 && this.mouseModel >= i),
          half = halfIndex === i && this.mouseModel < i,
          exSelected = this.mouseModel > 0 && (half === true ? ceil : this.value) >= i && this.mouseModel < i,
          color = half === true
            ? (i <= icons.halfColorLen ? this.colorHalf[i - 1] : icons.halfColor)
            : (
              icons.selColor !== void 0 && active === true
                ? (i <= icons.selColorLen ? this.colorSelected[i - 1] : icons.selColor)
                : (i <= icons.colorLen ? this.color[i - 1] : icons.color)
            ),
          name = (
            half === true
              ? (i <= icons.halfIconLen ? this.iconHalf[i - 1] : icons.halfIcon)
              : (
                icons.selIcon !== void 0 && (active === true || exSelected === true)
                  ? (i <= icons.selIconLen ? this.iconSelected[i - 1] : icons.selIcon)
                  : (i <= icons.iconLen ? this.icon[i - 1] : icons.icon)
              )
          ) || this.$q.iconSet.rating.icon

        acc.push({
          name,

          attrs: {
            tabindex,
            role: 'radio',
            'aria-checked': this.value === i ? 'true' : 'false',
            'aria-label': this.iconLabel(i, name)
          },

          classes: 'q-rating__icon' +
            (active === true || half === true ? ' q-rating__icon--active' : '') +
            (exSelected === true ? ' q-rating__icon--exselected' : '') +
            (this.mouseModel === i ? ' q-rating__icon--hovered' : '') +
            (color !== void 0 ? ` text-${color}` : '')
        })
      }

      return acc
    },

    attrs () {
      const attrs = {
        role: 'radiogroup'
      }
      if (this.disable === true) {
        attrs[ 'aria-disabled' ] = 'true'
      }
      if (this.readonly === true) {
        attrs[ 'aria-readonly' ] = 'true'
      }
      return attrs
    }
  },

  methods: {
    __set (value) {
      if (this.editable === true) {
        const
          model = between(parseInt(value, 10), 1, parseInt(this.max, 10)),
          newVal = this.noReset !== true && this.value === model ? 0 : model

        newVal !== this.value && this.$emit('input', newVal)
        this.mouseModel = 0
      }
    },

    __setHoverValue (value) {
      if (this.editable === true) {
        this.mouseModel = value
      }
    },

    __keyup (e, i) {
      switch (e.keyCode) {
        case 13:
        case 32:
          this.__set(i)
          return stopAndPrevent(e)
        case 37: // LEFT ARROW
        case 40: // DOWN ARROW
          if (this.$refs[`rt${i - 1}`]) {
            this.$refs[`rt${i - 1}`].focus()
          }
          return stopAndPrevent(e)
        case 39: // RIGHT ARROW
        case 38: // UP ARROW
          if (this.$refs[`rt${i + 1}`]) {
            this.$refs[`rt${i + 1}`].focus()
          }
          return stopAndPrevent(e)
      }
    }
  },

  render (h) {
    const child = []

    this.stars.forEach(({ classes, name, attrs }, index) => {
      const i = index + 1

      child.push(
        h('div', {
          key: i,
          ref: `rt${i}`,
          class: 'q-rating__icon-container flex flex-center',
          attrs,
          on: cache(this, 'i#' + i, {
            click: () => { this.__set(i) },
            mouseover: () => { this.__setHoverValue(i) },
            mouseout: () => { this.mouseModel = 0 },
            focus: () => { this.__setHoverValue(i) },
            blur: () => { this.mouseModel = 0 },
            keyup: e => { this.__keyup(e, i) }
          })
        }, mergeSlot(
          [h(QIcon, { class: classes, props: { name } })],
          this, `tip-${i}`
        ))
      )
    })

    if (this.name !== void 0 && this.disable !== true) {
      this.__injectFormInput(child, 'push')
    }

    return h('div', {
      staticClass: 'q-rating row inline items-center',
      class: this.classes,
      style: this.sizeStyle,
      attrs: this.attrs,
      on: { ...this.qListeners }
    }, child)
  }
})
