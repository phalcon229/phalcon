    function accSub(arg1, arg2) {
        return accAdd(arg1, -arg2);
    };

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
        var s = parseFloat((arg1 * m + arg2 * m) / m).toFixed(Math.max(r1, r2))
        return s
    };


    var handleEvnt = {
        init: function() {
            if ($('.reduce-s')) {
                this.touchEvent();
            }
        },
        touchEvent: function() {
            var that = this;
            var $reduce = $('.reduce-s');
            var $add = $('.add-s');
            var point;
            $('.main-table').on('mousedown', '.reduce-s', function(event) {
                point = parseFloat($(this).siblings('.point-va').find('.rate').text());
                point = parseFloat(accSub(point, 0.01));
                if (point <= 0) {
                    point = parseFloat(0);
                }
                $(this).siblings('.point-va').find('.rate').text(point);
                event.preventDefault();
            });
            $('.main-table').on('mousedown', '.add-s', function(event) {
                point = parseFloat($(this).siblings('.point-va').find('.rate').text());
                point = parseFloat(accAdd(point, 0.01));
                $(this).siblings('.point-va').find('.rate').text(point);
                event.preventDefault();
            });
        },
        setDownList: function(obj, obj2) {
            $(obj).change(function() {
                $(this).siblings(obj2).text($(this).find('option:checked').text());
                $(this).siblings(obj2).addClass('on');
            });
        },
        setTableDownList: function(obj, obj2) {
            $(obj).click(function() {
                $(this).siblings('.i-arrow').addClass('on');
            });
            $(obj).change(function() {
                $(this).siblings(obj2).text($(this).find('option:checked').text());
                $(this).siblings('.i-arrow').removeClass('on');
            });
        },
        deleteBtn: function(obj, val) {
            $(obj).click(function() {
                var v = $(this).parent().parent().prev('tr').find('.val').attr('data-val');
                $(this).parent().parent().prev('tr').find('.val .changeval').val(v);
                $(this).parent().parent().detach();
                location.reload();
            });
        },
        addVal: function(obj) {
            var that = this;
            $(obj).click(function() {
                var id = parseInt($(this).attr('data-id'));
                var oldVal = parseFloat($(this).parent('tr').find('.val .changeval').val());
                var newVal = 0.00;
                var hasCls = $(this).parent().next('tr').hasClass('pop-btn');
                var html = "<tr class='pop-btn'><td colspan='6'><a class='a-cancel'>取消</a><a class='a-save'>保存</a></td></tr>"
                switch (id) {
                    case 1:
                        newVal = accAdd(oldVal, 0.0001);
                        break;
                    case 2:
                        newVal = accSub(oldVal, 0.0001);
                        break;
                }
                if (newVal < 0) {
                    newVal = 0;
                    return;
                }
                $(this).siblings('.val').find('.changeval').val(newVal).trigger("change");
                if (!hasCls) {
                    $(this).parent().after(html);
                    that.deleteBtn('.pop-btn td .a-cancel', oldVal);
                }
            });
            $('.bett-ball').on('focus','.changeval',function(){
                var oldVal = parseFloat($(this).val());
                if($(this).parent().parent().next().hasClass('pop-btn')) return;
                else{
                    var html = "<tr class='pop-btn'><td colspan='6'><a class='a-cancel'>取消</a><a class='a-save'>保存</a></td></tr>";
                    $(this).parent().parent().after(html);
                }
                that.deleteBtn('.pop-btn td .a-cancel', oldVal);
            });
        },
        setLight: function(obj, cls) {
            $(obj).click(function() {
                $(obj).removeClass(cls);
                $(this).addClass(cls);
            });
        },
        showImg: function() {
            var $imgp = $('.up');
            if ($imgp.hasClass('up')) {
                $(".up").uploadPreview({
                    Img: "ImgPr"
                });
            }
        },
        showBox: function(obj, obj2) {
            $(obj).click(function() {
                var id = Number($(this).attr('data-id'));
                $(obj).removeClass('active');
                $(this).addClass('active');
                $(obj2).addClass('unsee');
                $(obj2).eq(id).removeClass('unsee');
            });
        },

        // hidePanel: function(obj) {
        //     $(obj).click(function() {
        //         $(this).parent().parent().addClass('unsee');
        //     });
        // }
    }
    $(document).ready(function() {
        handleEvnt.init();
        handleEvnt.setDownList('.select-box .list', '.txt');
        handleEvnt.setTableDownList('.td-down .list', '.txt');
        handleEvnt.addVal('.bett-ball tr .td-add');
        handleEvnt.addVal('.bett-ball tr .td-reduce');
        handleEvnt.setLight('.home-bar .item-tit', 'on');
        //handleEvnt.hidePanel('.pop-panel .close');
        handleEvnt.showBox('.group-box .btn-group', '.act-set .banner-set');
        handleEvnt.showImg();
    });
