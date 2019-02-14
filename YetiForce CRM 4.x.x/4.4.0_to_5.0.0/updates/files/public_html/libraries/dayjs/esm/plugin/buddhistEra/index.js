import { FORMAT_DEFAULT } from '../../constant';
export default (function (o, c) {
  // locale needed later
  var proto = c.prototype;
  var oldFormat = proto.format; // extend en locale here

  proto.format = function (formatStr) {
    var _this = this;

    var yearBias = 543;

    var _this$$utils = this.$utils(),
        padStart = _this$$utils.padStart;

    var str = formatStr || FORMAT_DEFAULT;
    var result = str.replace(/BBBB|BB/g, function (match) {
      var year = String(_this.$y + yearBias);
      var args = match === 'BB' ? [year.slice(-2), 2] : [year, 4];
      return padStart.apply(void 0, args.concat(['0']));
    });
    return oldFormat.bind(this)(result);
  };
});