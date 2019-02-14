export default (function (o, c, d) {
  c.prototype.isBetween = function (a, b, u) {
    var dA = d(a);
    var dB = d(b);
    return this.isAfter(dA, u) && this.isBefore(dB, u) || this.isBefore(dA, u) && this.isAfter(dB, u);
  };
});