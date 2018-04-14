var colorArray=['#1acc16','purple','#d91d36','#426dbe','orange'];
function drawLine(colorIndex){
        var dataTable = document.getElementsByClassName('dataTable')[0];
        var containerList=document.getElementsByClassName('table-container')[0];        
        var canvas = document.getElementById('canvas');
        canvas.width = dataTable.getBoundingClientRect().width;
        canvas.height = dataTable.getBoundingClientRect().height;       
        

        //判断浏览器是否支持 canvas
        if (!canvas.getContext("2d")) {
            document.body.innerHTML = "当当前浏览器不支持 canvas 的时候,请更换浏览器尝试";
            return; //终止后面代码的执行
        }

        var bodyIsscrollTop = document.documentElement.scrollTop;
        var bodyIsscrollLeft = document.documentElement.scrollLeft;

        var context = canvas.getContext("2d"); //初始化 canvas画布

        ////////////// 获取所有目标 开始进行拼接 坐标json数据
        var objXyJson = {
            "p": []
        };
        var objI = dataTable.getElementsByTagName("i");
        
        var color='';

        switch(parseInt(colorIndex)){
            case 0:color='c-green';break;
            case 1:color='c-purple';break;
            case 2:color='c-theme';break;
            case 3:color='c-blue';break;
            case 4:color='c-orange';break;
        }
        
        for (var i = 0; i < objI.length; i++) {                    
            var thisObjI = objI[i];
            if (thisObjI.className == color) {
                var dataTableX = dataTable.getBoundingClientRect().left + bodyIsscrollLeft;
                var dataTableY = dataTable.getBoundingClientRect().top + bodyIsscrollTop;

                //////// 注意了 这里因为 tabel是 自适应居中布局 所以还要减掉 tabel 的 x坐标值
                var X =  thisObjI.getBoundingClientRect().left + bodyIsscrollLeft - dataTableX;
                var Y =  thisObjI.getBoundingClientRect().top + bodyIsscrollTop - dataTableY;
                var W = thisObjI.getBoundingClientRect().width;
                var H = thisObjI.getBoundingClientRect().height;

                var objX = X + W / 2;
                var objY = Y + H / 2;

                var objXyJsonPSum = JSONLength(objXyJson["p"]);

                objXyJson["p"][objXyJsonPSum] = {
                    x: objX,
                    y: objY
                };


            }

        }

        var tangram = [];
        var objXyJsonP = objXyJson["p"];
        
        for (var i = 0; i < objXyJsonP.length - 1; i++) {
            var thisObjXyJsonP = objXyJsonP[i];
            var tangramNum = JSONLength(tangram);
            tangram[tangramNum] = {
                "p": [
                    objXyJsonP[i],
                    objXyJsonP[i + 1]
                ],
                "color":colorArray[colorIndex]
            };
            
        }

        for (var i = 0; i < tangram.length; i++) {        
            draw(tangram[i], context);
            
        }

}
function draw(piece, cxt) {
    cxt.beginPath();
    cxt.moveTo(piece.p[0].x, piece.p[0].y);
    for (var i = 1; i < piece.p.length; i++) {
        cxt.lineTo(piece.p[i].x, piece.p[i].y);
    }
    cxt.closePath();
    cxt.lineWidth = 2;
    cxt.strokeStyle = piece.color;
    cxt.stroke();  
}

function JSONLength(obj) {
    var size = 0,
        key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};
window.onload = function() {
    document.addEventListener('touchstart', function(event) {
        if (event.touches.length > 1) {
            event.preventDefault();
        }
    })
    var lastTouchEnd = 0;
    document.addEventListener('touchend', function(event) {
        var now = (new Date()).getTime();
        if (now - lastTouchEnd <= 300) {
            event.preventDefault();
        }
        lastTouchEnd = now;
    }, false)
    FastClick.attach(document.body);
    
}
var dataList = {
    getdata: function() {
        if($('.prize-detail .item').length>0){
            var t=$('.prize-detail .item:first-of-type')[0];
            var pt =t.getBoundingClientRect().height +1+'px';
            return pt;
        }        
    }
}

var handleEvnt = {
    init: function() {
        this.setdata();
        this.navSilder();
        this.maskClick();
        this.addNewProject();
        this.setAct();
        this.setDates();
        this.setDownList();
        this.experiod();
        this.showjunior();
        this.showNewbie();
        this.expand();
    },
    expand: function() {
        $('.main-table').on('click', 'i.right', function() {
            if ($(this).hasClass('on')) {
                $(this).removeClass('on');
                $(this).parent().parent().next('tr').addClass('unsee');
            } else {
                $(this).addClass('on');
                $(this).parent().parent().next('tr').removeClass('unsee');
            }
        })
    },
    setdata: function() {
        var pt = dataList.getdata();
        $('.periods .t-periods ul').css({ 'height': pt });
    },
    hideMoney: function(pa, pa2) {
        $(pa).click(function() {
            if ($(this).hasClass('unsee')) {} else {
                $(this).addClass('unsee');
                $(pa2).removeClass('unsee');
            }
        });
    },
    navSilder: function() {
        $('.top-nav .nav-btn').click(function() {
            if ($(this).hasClass('on')) {
                $(this).removeClass('on');
                $('.mask').addClass('unsee');
                $('.side-nav').removeClass('active');
                $('html,body').removeClass('no-scroll');
            } else {
                $(this).addClass('on');
                $('.mask').removeClass('unsee');
                $('.side-nav').addClass('active');
                $('html,body').addClass('no-scroll');

            }
        })
    },
    maskClick: function() {
        $('.mask').click(function(e) {
            if ($('.side-nav').hasClass('active')) {
                $('.top-nav .nav-btn').removeClass('on');
                $('.side-nav').removeClass('active');
                $(this).addClass('unsee');
                $('html,body').removeClass('no-scroll');
            }
        });
    },
    addNewProject: function() {
        $(".project-h .item.add").click(function() {
            var id = $(this).attr('data-id');
            $('.junior-ban').eq(id).removeClass('unsee');
            $('.mask').removeClass('unsee');
            $('html,body').addClass('no-scroll');
        });
    },
    closeP: function(obj) {
        $(obj).on('click', '.junior-ban .close', function() {
            $(".mask").addClass('unsee');
            $('.junior-ban').addClass('unsee');
            $('html,body').removeClass('no-scroll');
        })
    },
    setAct: function(obj, cls) {
        $(obj).click(function() {
            $(obj).removeClass(cls);
            $(this).addClass(cls);
        });
    },
    setDates: function() {
        $('.search-box .date .box input').trigger('focus');
        $('.search-box .date .box input').on('change', function() {
            $(this).next('em').text($(this).val());
            $(this).next('em').addClass('on');
        });
    },
    setDownList: function() {
        $('.variety-box .down-list,.input-panel .item .flex1,.select-title-black .select-p,.play-black .play-d,.dnlist .select-p').change(function() {
            var s = $(this).find('option:selected').text();
            var id = parseInt($(this).find('option:selected').val()) - 1;
            $(this).siblings('em').text(s);
            $(this).siblings('em').addClass('on');
            if ($('.newbie-context')) {
                $('.newbie-context').addClass('unsee');
                $('.newbie-context').eq(id).removeClass('unsee');
            }
        });
    },
    experiod: function() {
        $('.periods .expand-t').click(function() {
            var pt = dataList.getdata();
            if ($(this).hasClass('on')) {
                $(this).removeClass('on');
                $('.periods .t-periods ul').css({ 'height': pt, 'overflow': 'hidden' });

            } else {
                $('.periods .t-periods ul').css({ 'height': 'auto', 'overflow': 'auto' });
                $(this).addClass('on');
            }
        });
    },
    extap: function(obj, cls, _pre) {
        $(obj).click(function() {
            var id = $(this).attr('data-id');
            var _n = _pre + '-' + id;
            $(this).siblings().removeClass(cls);
            $(this).addClass(cls);
            $(_pre).addClass('unsee');
            $(_n).removeClass('unsee');
        });
    },
    showNewbie: function() {
        $('.newbie-nav .item').on('click', function() {
            var index = $(this).index();
            $(this).siblings('.item').removeClass('on');
            $(this).addClass('on');
            $('.newbie-main').addClass('unsee');
            $('.newbie-main').eq(index).removeClass('unsee');
        });
    },
    showMb: function(obj1) {
        $(obj1).click(function() {
            $('.mask').removeClass('unsee');
            $('html,body').addClass('no-scroll');
        });
    },

    showjunior: function() {
        $('.pro-list .item .divide .md').click(function() {
            var id = $(this).parent().parent().attr('data-id');
            $('.junior-ban').eq(id).removeClass('unsee');
            $('.mask').removeClass('unsee');
            $('html,body').addClass('no-scroll');
        });
    },
    showjunior2: function(obj1, obj2, cls) {
        $('.game-body').on('click', obj1, function() {
            var id = $(this).attr('data-id');
            $(this).siblings().removeClass(cls);
            $(this).addClass(cls);
            $(this).parent().parent().find(obj2).not('.game-new').addClass('unsee');
            $(this).parent().parent().find(obj2).not('.game-new').eq(id).removeClass('unsee');
            $('.mask').removeClass('unsee');
            $('html,body').addClass('no-scroll');          
        });
    },
    showjunior3: function(obj1, obj2, cls) {        
        $('.game-body').on('click', obj1, function() {
            id= $(this).attr('data-id');
            $(this).siblings().removeClass(cls);
            $(this).addClass(cls);
            $(this).parent().parent().parent().find(obj2).addClass('unsee');
            $(this).parent().parent().parent().find(obj2).eq(id).removeClass('unsee');
            $('.mask').removeClass('unsee');
            $('html,body').addClass('no-scroll');
        });
    },
    closejunior: function(obj) {
        $(obj).on('click', '.junior-ban .earn-btn .eclose,.junior-ban .close', function() {
            $(this).parent().parent().addClass('unsee');
            $('.mask').addClass('unsee');
            $('html,body').removeClass('no-scroll');
        });
    },
    clickchk: function(obj, obj2, cls) { 
        $(obj).on('click', obj2, function(e) {
            if ($(this).hasClass(cls)) {
                $(this).removeClass(cls);
            } else {
                $(this).addClass(cls);
            } 
            e.preventDefault();
        });        
    },
    showTab:function(){
        $('.game-body').on('click','.cz-tab',function(){
            if($('.game-new').hasClass('unsee')){
                $('.mask').removeClass('unsee');
                $('html,body').addClass('no-scroll');
                $('.game-new').removeClass('unsee');
            }     

        });

    },
    clickSiglechk:function(obj, obj2, obj3,obj4,cls) { 
        $(obj).on('click', obj2, function(e) {
            if ($(this).hasClass(cls)) {
            } else {                
                $(obj2).removeClass(cls);
                $(this).addClass(cls);
                $(obj3).html($(this).find('.tit').text());
                var n = $(this).find('.srate').text();
                $('#b').html('<em></em>')
                var h = '赔率 : ';
                $("#b").prepend(h);
                $(obj4).html($(this).find('.srate').text());
                if ($(this).find('.srate').text() == '')
                    $('#b').html('');
                $('.game-new').addClass('unsee');
                $('.mask').addClass('unsee');
                $('html,body').removeClass('no-scroll');
            } 
            e.preventDefault();
        });        
    },
}
$(function(){
    FastClick.attach(document.body);
    handleEvnt.init();
    handleEvnt.hideMoney('.tip .money', '.tip .h-money');
    handleEvnt.hideMoney('.tip .h-money', '.tip .money');
    handleEvnt.hideMoney('.top-nav .coin', '.top-nav .coin-2');
    handleEvnt.hideMoney('.top-nav .coin-2', '.top-nav .coin');
    handleEvnt.hideMoney('.side-nav .showm', '.side-nav .hidem');
    handleEvnt.hideMoney('.side-nav .hidem', '.side-nav .showm');
    handleEvnt.closeP('.account-centre-body');
    handleEvnt.closeP('.order-manage-body');
    handleEvnt.closeP('.home-body');
    handleEvnt.closeP('.user-list-body');
    handleEvnt.closejunior('.earn-money-body');
    handleEvnt.closejunior('.game-body');
    handleEvnt.extap('.earn-tap .item', 'on', '.pro');
    handleEvnt.setAct('.game-body .context .radio', 'on');
    handleEvnt.setAct('.head-bar .item', 'on');
    handleEvnt.showMb('.db-bar a');
    handleEvnt.showjunior2('.game-bar .item', '.move', 'on');
    handleEvnt.showjunior2('.move-bar .item', '.size', 'on');
    handleEvnt.showjunior2('.game-bar .item', '.main-table', 'on');
    handleEvnt.clickchk('.game-body', '.context .rel', 'on');
    handleEvnt.clickchk('.game-body', '.data-show li .cms', 'on');
    handleEvnt.clickSiglechk('.game-body', '.play-way li .cube','.cz-tab em','.cz-rate em','on');
    handleEvnt.showTab();
    handleEvnt.showjunior3('.scroll-bar .item', '.trend', 'on');      
});

