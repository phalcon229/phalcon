var defaultOptions = {
    min: 0,
    max: 2.0,
    start: 0,
    render: function(rate) {

    },
    formula: function(rate) {
        var m = 1948 + rate * 20;
        return m;
    }
};

var RangeSlider = function(ele, options) {
    options = options || {};

    this.ele = ele;
    this.options = {};

    this.mergeOptions = function(options) {
        for (var i in defaultOptions) {
            if (options.hasOwnProperty(i)) {
                if (i == 'min' || i == 'max' || i == 'start')
                    defaultOptions[i] = parseFloat(options[i]);
                else
                    defaultOptions[i] = options[i];
            }
        }
        this.options = defaultOptions;
    }.bind(this);

    this.mergeOptions(options);

    this.rangeChange = function() {
        var init = new Powerange(this.ele, {
            decimal: true,
            max: this.options.max,
            min: this.options.min,
            start: this.options.start,
            hideRange: true
        });
        this.ele.onchange = function() {
            var rate = parseFloat(ele.value).toFixed(1);
            $('.js-display-decimal').text(rate);
            this.options.render(rate);
            var m = this.options.formula(rate);
            if (document.querySelector('.bouns')) {
                document.querySelector('.bouns').innerHTML = m;
                document.querySelector('.rate-money').innerHTML = m;
            }
        }.bind(this);
    }.bind(this);

    this.init = function() {
        var isDown = false;
        this.rangeChange();
        var s = $('.range-handle');
        var dis = $('.range-bar').width() - 22;
        var m = s.css('left');
        var pl = m.substr(0, m.length - 2);
        var _add = $('.range-change .add');
        var _reduce = $('.range-change .reduce');
        var _val = $('.js-display-decimal');
        var n = parseFloat(_val.text());
        var _bouns = $('.bouns');
        var bouns_val = parseFloat(_bouns.text());

        _val.text(this.options.start);

        _add.bind('touchstart', function(event) {
            isDown = true;
            m = s.css('left');
            pl = m.substr(0, m.length - 2);
            n = parseFloat(_val.text());
            bouns_val = parseFloat(_bouns.text());
            event.preventDefault();
        });
        _reduce.bind('touchstart', function(event) {
            isDown = true;
            m = s.css('left');
            pl = m.substr(0, m.length - 2);
            n = parseFloat(_val.text());
            bouns_val = parseFloat(_bouns.text());
            event.preventDefault();
        });

        _add.bind('touchend', this, function(event) {
            if (isDown) {
                var obj = event.data;
                n = parseFloat(obj.accAdd(n, 0.1)).toFixed(1);
                pl = parseFloat(obj.accAdd(pl, dis / (obj.options.max * 10))).toFixed(2);
                if (n >= obj.options.max) {
                    n = parseFloat(obj.options.max).toFixed(1);
                }
                if (pl >= dis) {
                    pl = dis;
                }
                bouns_val = obj.options.formula(n);
                _bouns.text(bouns_val);
                _val.text(n);
                s.css('left', pl + "px");
                isDown = true;

                obj.options.render(n)
            }
            event.preventDefault();
        });
        _reduce.bind('touchend', this, function(event) {
            if (isDown) {
                var obj = event.data;
                n = parseFloat(obj.accSub(n, 0.1)).toFixed(1);
                pl = parseFloat(obj.accSub(pl, dis / (obj.options.max * 10))).toFixed(2);
                if (n <= obj.options.min) {
                    n = parseFloat(obj.options.min).toFixed(1);
                }
                if (pl <= 0) {
                    pl = 0;
                }
                bouns_val = obj.options.formula(n);
                _bouns.text(bouns_val);
                _val.text(n);
                s.css('left', pl + "px");
                isDown = true;
            }
            event.preventDefault();
            obj.options.render(n);
        })

    }.bind(this);

    this.accSub = function(arg1, arg2) {
        return this.accAdd(arg1, -arg2);
    }.bind(this)

    this.accAdd = function(arg1, arg2) {
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
        return (arg1 * m + arg2 * m) / m
    }

    // this.restart = function() {
    //     this.options = defaultOptions;
    //     this.init();
    // }.bind(this)

    this.init();
};