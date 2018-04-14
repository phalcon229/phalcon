$(document).ready(function() {
    setPoint.init({
        max: parseFloat($('.js-decimal').attr('data-max')),
        start: parseFloat($('.js-decimal').attr('data-start')),
        bouns: parseFloat($('.bouns').text()),
    });
});
var setPoint = {
    max: 2.00,
    start: 1.00,
    bouns: 1948,
    init: function(opts) {
        this.max = opts.max;
        this.start = opts.start;
        this.bouns = opts.bouns;
        if ($('.js-decimal')) {
            this.rangeChange();
            this.load1();
        }
    },
    load1: function() {
        var that = this;
        var s = $('.range-handle');
        var dis = $('.range-bar').width() - 22;
        var m = s.css('left');
        var _add = $('.range-change .add');
        var _reduce = $('.range-change .reduce');
        var _val = $('.js-display-decimal');
        var n = parseFloat(_val.text());
        var _bouns = $('.bouns');
        _add.bind('mousedown', function(event) {
            m = s.css('left');
            n = parseFloat(_val.text());
            pl = m.substr(0, m.length - 2);
            bouns_val = parseFloat(_bouns.text());
            n = parseFloat(that.accAdd(n, 0.01));
            pl = parseFloat(that.accAdd(pl, dis / (that.max * 100)));

            if (pl >= dis) {
                pl = dis;
            }
            if (n >= that.max) {
                n = parseFloat(that.max);
            }
            bouns_val = that.countVal(n);
            _bouns.text(bouns_val);
            _val.text(n + "%");
            $('.js-decimal').attr('value', n);
            s.css('left', pl + "px");
            event.preventDefault();
        });
        _reduce.bind('mousedown', function(event) {
            m = s.css('left');
            n = parseFloat(_val.text());
            pl = m.substr(0, m.length - 2);
            bouns_val = parseFloat(_bouns.text());
            n = parseFloat(that.accSub(n, 0.01));
            pl = parseFloat(that.accSub(pl, dis / (that.max * 100)));

            if (pl <= 0) {
                pl = 0;
            }
            if (n <= 0) {
                n = parseFloat(0.00);
            }
            bouns_val = that.countVal(n);
            _bouns.text(bouns_val);
            _val.text(n + "%");
            $('.js-decimal').attr('value', n + '%');
            s.css('left', pl + "px");
            console.log(pl);
            event.preventDefault();
        });
    },
    accSub: function(arg1, arg2) {
        return accAdd(arg1, -arg2);
    },
    accAdd: function(arg1, arg2) {
        var r1, r2, m;
        try {
            r1 = arg1.toString().split(".")[1].length
        } catch (e) {
            r1 = 0
        }
        try {
            r2 = arg2.toString().split(".")[1].length
        } catch (e) {
            r2 = 0
        }
        m = Math.pow(10, Math.max(r1, r2))
        var s = parseFloat((arg1 * m + arg2 * m) / m).toFixed(Math.max(r1, r2))
        return s
    },
    countVal: function(rate) {
        return 1948 + rate * 20;
    },
    rangeChange: function() {
        var that = this;
        if (document.querySelector('.js-decimal')) {
            var ele = document.querySelector('.js-decimal');
            var init = new Powerange(ele, {
                decimal: true,
                max: that.max,
                start: that.start,
                hideRange: true
            });
            ele.onchange = function() {
                var str = parseFloat(ele.value);
                $('.js-display-decimal').text(str + "%");
                $('.js-decimal').attr('value', str);
                var m = parseFloat(that.countVal(str));
                if (document.querySelector('.bouns')) {
                    document.querySelector('.bouns').innerHTML = m;
                }
            };
        }

    },
}
