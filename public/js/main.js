function countVal(rate) {
    var m = 1948 + rate * 20;
    return m;
}
(function($) {
    $.rateCount = function(opts) {
        var _self = this;
        opts = $.extend({
            max: 2,
            start: 1,
            bouns: 1958,
            rate: 1.00,
            speed: 0.01,
            Callback: function() {}
        }, opts || {});
        this.init = function() {
            load1();
        }



        function load1() {
            var s = $('.range-handle');
            var dis = $('.range-bar').width() - 22;
            var m = s.css('left');
            var _add = $('.range-change .add');
            var _reduce = $('.range-change .reduce');
            var _val = $('.js-display-decimal');
            var n = parseFloat(_val.text());
            var _bouns = $('.bouns');
            _add.bind('touchend', function(event) {
                m = s.css('left');
                n = parseFloat(_val.text());
                pl = m.substr(0, m.length - 2);
                bouns_val = parseFloat(_bouns.text());
                n = parseFloat(accAdd(n, 0.01));
                pl = parseFloat(accAdd(pl, dis / (opts.max * 100)));

                if (pl >= dis) {
                    pl = dis;
                }
                if (n >= opts.max) {
                    n = parseFloat(opts.max);
                }
                bouns_val = countVal(n);
                _bouns.text(bouns_val);
                _val.text(n + "%");
                $('.js-decimal').val(n);
                s.css('left', pl + "px");
                event.preventDefault();
            });
            _reduce.bind('touchend', function(event) {
                m = s.css('left');
                n = parseFloat(_val.text());
                pl = m.substr(0, m.length - 2);
                bouns_val = parseFloat(_bouns.text());
                n = parseFloat(accSub(n, 0.01));
                pl = parseFloat(accSub(pl, dis / (opts.max * 100)));

                if (pl <= 0) {
                    pl = 0;
                }
                if (n <= 0) {
                    n = parseFloat(0.00);
                }
                bouns_val = countVal(n);
                _bouns.text(bouns_val);
                _val.text(n + "%");
                $('.js-decimal').val(n);
                s.css('left', pl + "px");
                event.preventDefault();
            });
        }

        function accSub(arg1, arg2) {
            return accAdd(arg1, -arg2);
        }

        function accAdd(arg1, arg2) {
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
            var s = parseFloat((arg1 * m + arg2 * m) / m).toFixed(Math.max(r1, r2));
            return s
        }
    }
})(jQuery)



window.onload = function() {

    var ele = document.querySelector('.js-decimal');
    if (ele) {
        var init = new Powerange(ele, {
            decimal: true,
            max: 2.00,
            start: 1.00,
        });
        ele.onchange = function() {
            var str = parseFloat(ele.value);
            $('.js-display-decimal').text(str + "%");
            $('.js-decimal').val(str);
            console.log($('.js-decimal').val());
            var m = parseFloat(countVal(str));
            if (document.querySelector('.bouns')) {
                document.querySelector('.bouns').innerHTML = m;
            }
        };
    }
    var rc = new $.rateCount({
        max: 2.00,
        start: 1.00,
        bouns: 1958,
        rate: 1.00,
        speed: 0.01,
    });
    rc.init();

}
