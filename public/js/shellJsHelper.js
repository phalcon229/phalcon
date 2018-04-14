        function loginWx() {
            window.WebViewJavascriptBridge.callHandler(
                'JsInterfaceBridge'
                ,'loginWx'
                , function(responseData) {
                }
            );

        }

        function checkIsAppMode() {
                    var d = new Date();
                    console.log('start:1,time:'+d.getMilliseconds());
                    window.WebViewJavascriptBridge.callHandler(
                        'JsInterfaceBridge'
                        ,'checkIsAppMode'
                        , function(responseData) {
                            console.log('callback:2,time:'+d.getMilliseconds());
                            if(responseData ==1)
                                $('#wx').removeClass('unsee');
                            console.log('end:3,time:'+d.getMilliseconds());
                 }
             );
         }

        function setupWebViewJavascriptBridge(callback) {
            if (window.WebViewJavascriptBridge) {
                callback(WebViewJavascriptBridge);
            }else{
                document.addEventListener(
                    'WebViewJavascriptBridgeReady'
                    , function() {
                        callback(WebViewJavascriptBridge)
                    },
                    false
                );
             }
            if (window.WVJBCallbacks) {
                window.WVJBCallbacks.push(callback);
              }

            window.WVJBCallbacks = [callback];
            var WVJBIframe = document.createElement('iframe');
            WVJBIframe.style.display = 'none';
            WVJBIframe.src = 'wvjbscheme://__BRIDGE_LOADED__';
            document.documentElement.appendChild(WVJBIframe);
            setTimeout(function() { document.documentElement.removeChild(WVJBIframe) }, 0)
        }

        setupWebViewJavascriptBridge(function(bridge) {
            try {
                bridge.init(function(message, responseCallback) {
                    console.log('JS got a message', message);
                    var data = {
                        'Javascript Responds': '测试中文!'
                    };
                    console.log('JS responding with', data);
                    responseCallback(data);
                });
            } catch (e) {
                console.log(e.message);
                console.log(e.description);
                console.log(e.number);
                console.log(e.name);
            }
            bridge.registerHandler("thirdPlatLoginCallBack", function(data, responseCallback) {
                var obj = JSON.parse(data);
                 $.ajax({
                    type: 'post',
                    url: '/auth/wxlogin',
                    data: {accessToken:obj.access_token, openId:obj.openid},
                    dataType: 'json',
                    success: function(res) {
                        if(res.code == 500)
                            alert(res.msg);
                        window.location.href = res.data.url;
                    },
                    error: function() {
                        alert('system error');
                    },
                });
            });

        })