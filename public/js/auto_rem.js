! function(win) {
    function resize() {
        var $base = 750;
        var $size = 18.75;
        var domWidth = domEle.getBoundingClientRect().width;
        if (domWidth / v > $base) {
            domWidth = $base * v;
        }
        win.rem = domWidth / $size;
        domEle.style.fontSize = win.rem + "px"; //320/20px(min),640/40px(max)
    }
    var v, initial_scale, timeCode, dom = win.document,
        domEle = dom.documentElement,
        viewport = dom.querySelector('meta[name="viewport"]'),
        flexible = dom.querySelector('meta[name="flexible"]');
    if (viewport) {
        //viewport：<meta name="viewport"content="initial-scale=0.5, minimum-scale=0.5, maximum-scale=0.5,user-scalable=no,minimal-ui"/>
        var o = viewport.getAttribute("content").match(/initial\-scale=(["']?)([\d\.]+)\1?/);
        if (o) {
            initial_scale = parseFloat(o[2]);
            v = parseInt(1 / initial_scale);
        }
    } else {
        if (flexible) {
            var o = flexible.getAttribute("content").match(/initial\-dpr=(["']?)([\d\.]+)\1?/);
            if (o) {
                v = parseFloat(o[2]);
                initial_scale = parseFloat((1 / v).toFixed(2))
            }
        }
    }
    if (!v && !initial_scale) {
        var n = (win.navigator.appVersion.match(/android/gi), win.navigator.appVersion.match(/iphone/gi));
        v = win.devicePixelRatio;
        v = n ? v >= 3 ? 3 : v >= 2 ? 2 : 1 : 1, initial_scale = 1 / v
    }
    //没有viewport标签的情况下
    if (domEle.setAttribute("data-dpr", v), !viewport) {
        if (viewport = dom.createElement("meta"), viewport.setAttribute("name", "viewport"), viewport.setAttribute("content", "initial-scale=" + initial_scale + ", maximum-scale=" + initial_scale + ", minimum-scale=" + initial_scale + ", user-scalable=no"), domEle.firstElementChild) {
            domEle.firstElementChild.appendChild(viewport)
        } else {
            var m = dom.createElement("div");
            m.appendChild(viewport), dom.write(m.innerHTML)
        }
    }
    win.dpr = v;
    win.addEventListener("resize", function() {
        clearTimeout(timeCode), timeCode = setTimeout(resize, 300)
    }, false);
    win.addEventListener("pageshow", function(b) {
        b.persisted && (clearTimeout(timeCode), timeCode = setTimeout(resize, 300))
    }, false);
    resize();
}(window);




var adaptUILayout = (function() {

    //根据校正appVersion或userAgent校正屏幕分辨率宽度值
    var regulateScreen = (function() {
        var cache = {};

        //默认尺寸
        var defSize = {
            width: window.screen.width,
            height: window.screen.height
        };

        var ver = window.navigator.appVersion;

        var _ = null;

        var check = function(key) {
            return key.constructor == String ? ver.indexOf(key) > -1 : ver.test(key);
        };

        var add = function(name, key, size) {
            if (name && key)
                cache[name] = {
                    key: key,
                    size: size
                };
        };

        var del = function(name) {
            if (cache[name])
                delete cache[name];
        };

        var cal = function() {
            if (_ != null)
                return _;

            for (var name in cache) {
                if (check(cache[name].key)) {
                    _ = cache[name].size;
                    break;
                }
            }

            if (_ == null)
                _ = defSize;

            return _;
        };

        return {
            add: add,
            del: del,
            cal: cal
        };
    })();


    //实现缩放
    var adapt = function(uiWidth) {
        var
            deviceWidth,
            devicePixelRatio,
            targetDensitydpi,
            //meta,
            initialContent,
            head,
            viewport,
            ua;

        ua = navigator.userAgent.toLowerCase();
        //whether it is the iPhone or iPad
        isiOS = ua.indexOf('ipad') > -1 || ua.indexOf('iphone') > -1;

        //获取设备信息,并矫正参数值
        devicePixelRatio = window.devicePixelRatio;
        deviceWidth = regulateScreen.cal().width;

        //获取最终dpi
        targetDensitydpi = uiWidth / deviceWidth * devicePixelRatio * 160;

        //use viewport width attribute on the iPhone or iPad device
        //use viewport target-densitydpi attribute on the Android device
        initialContent = isiOS ? 'width=' + uiWidth + ', user-scalable=no' : 'target-densitydpi=' + targetDensitydpi + ', width=' + uiWidth + ', user-scalable=no';

        //add a new meta node of viewport in head node
        head = document.getElementsByTagName('head');
        viewport = document.createElement('meta');
        viewport.name = 'viewport';
        viewport.content = initialContent;
        head.length > 0 && head[head.length - 1].appendChild(viewport);
    };
    return {
        regulateScreen: regulateScreen,
        adapt: adapt
    };
})();
/*
 *640为当期页面指定的统一分辨率，其他分辨率下均为此分辨率的放缩变化
 */
/*adaptUILayout.adapt(640); */
window.onload = function() {
        var body = document.getElementsByTagName('body')[0];
        console.log(body)
        var script = document.createElement('script');
        var ip = window.location.hostname;
        script.setAttribute('src', 'css.bundle.js');
        body.appendChild(script);
    }
//     (function(e) { e.setAttribute("src", "http://0.0.0.0:8000/target/target-script-min.js#anonymous");
//         document.getElementsByTagName("body")[0].appendChild(e); })(document.createElement("script"));
// void(0);
