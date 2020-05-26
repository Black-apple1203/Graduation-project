+ function($) {
	"use strict";

	var a = function(element, options) {
		this.$el = $(element)
		this.options = $.extend(!0, {}, this.defaults, options)
		this.isVisible = !1
		this.$hoverElem = this.$el.find(this.options.hoverElem)
		this.transitionProp = "all " + this.options.speed + "ms " + this.options.easing
		this.support = this._supportsTransitions()
		this._loadEvents()
	}
	a.prototype = {
		defaults: {
			speed: 300,
			easing: "ease",
			hoverDelay: 0,
			inverse: !1,
			hoverElem: "div"
		},
		constructor: a,
		_supportsTransitions: function() {
			if ("undefined" != typeof Modernizr) return Modernizr.csstransitions;
			var h = document.body || document.documentElement,
				s = h.style,
				p = "transition";
			if ("string" == typeof s[p]) return !0;
			var a = ["Moz", "webkit", "Webkit", "Khtml", "O", "ms"];
			p = p.charAt(0).toUpperCase() + p.substr(1);
			for (var i = 0; i < a.length; i++)
				if ("string" == typeof s[a[i] + p]) return !0;
			return !1
		},
		_loadEvents: function() {
			this.$el.on("mouseenter.hoverdir mouseleave.hoverdir", $.proxy(function(h) {
				this.direction = this._getDir({
					x: h.pageX,
					y: h.pageY
				}), "mouseenter" === h.type ? this._showHover() : this._hideHover()
			}, this))
		},
		_showHover: function() {
			var a = this._getStyle(this.direction);
			this.support && this.$hoverElem.css("transition", ""), this.$hoverElem.hide().css(a.from), clearTimeout(this.tmhover), this.tmhover = setTimeout($.proxy(function() {
				this.$hoverElem.show(0, $.proxy(function() {
					this.support && this.$hoverElem.css("transition", this.transitionProp), this._applyAnimation(a.to)
				}, this))
			}, this), this.options.hoverDelay), this.isVisible = !0
		},
		_hideHover: function() {
			var h = this._getStyle(this.direction);
			this.support && this.$hoverElem.css("transition", this.transitionProp), clearTimeout(this.tmhover), this._applyAnimation(h.from), this.isVisible = !1
		},
		_getDir: function(h) {
			var a = this.$el.width(),
				c = this.$el.height(),
				x = (h.x - this.$el.offset().left - a / 2) * (a > c ? c / a : 1),
				v = (h.y - this.$el.offset().top - c / 2) * (c > a ? a / c : 1),
				y = Math.round((Math.atan2(v, x) * (180 / Math.PI) + 180) / 90 + 3) % 4;
			return y
		},
		_getStyle: function(h) {
			var a, c, v = {
					left: "0",
					top: "-100%"
				},
				y = {
					left: "0",
					top: "100%"
				},
				_ = {
					left: "-100%",
					top: "0"
				},
				b = {
					left: "100%",
					top: "0"
				},
				$ = {
					top: "0"
				},
				g = {
					left: "0"
				};
			switch (h) {
				case 0:
				case "top":
					a = this.options.inverse ? y : v, c = $;
					break;
				case 1:
				case "right":
					a = this.options.inverse ? _ : b, c = g;
					break;
				case 2:
				case "bottom":
					a = this.options.inverse ? v : y, c = $;
					break;
				case 3:
				case "left":
					a = this.options.inverse ? b : _, c = g
			}
			return {
				from: a,
				to: c
			}
		},
		_applyAnimation: function(a) {
			$.fn.applyStyle = this.support ? $.fn.css : $.fn.animate, this.$hoverElem.stop().applyStyle(a, $.extend(!0, [], {
				duration: this.options.speed
			}))
		},
		show: function(h) {
			this.$el.off("mouseenter.hoverdir mouseleave.hoverdir"), this.isVisible || (this.direction = h || "top", this._showHover())
		},
		hide: function(h) {
			this.rebuild(), this.isVisible && (this.direction = h || "bottom", this._hideHover())
		},
		setOptions: function(a) {
			this.options = h.extend(!0, {}, this.defaults, this.options, a)
		},
		destroy: function() {
			this.$el.off("mouseenter.hoverdir mouseleave.hoverdir"), this.$el.data("hoverdir", null)
		},
		rebuild: function(h) {
			"object" == typeof h && this.setOptions(h), this._loadEvents()
		}
	}, $.fn.hoverdir = function(c, v) {
		return this.each(function() {
			var y = $(this).data("hoverdir"),
				_ = "object" == typeof c && c;
			y || (y = new a(this, _), $(this).data("hoverdir", y)), "string" == typeof c && (y[c](v), "destroy" === c && $(this).data("hoverdir", !1))
		})
	}, $.fn.hoverdir.Constructor = a
}(jQuery);