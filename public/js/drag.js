var w=document.documentElement.clientWidth;
var h=document.documentElement.clientHeight;
var halfw=w/2;
var homeIcon=document.getElementById('back-home');
var isDraging=false;

var drag=function(){
    this.touchOffsetX;
    this.touchOffsetY;
}
drag.prototype.init=function(){
    this.touchOffsetX=0;
    this.touchOffsetY=0;
}
function init(){
    var top=h-108-homeIcon.offsetHeight;
    homeIcon.style.left=5+'px';
    homeIcon.style.top=top+'px';
}
var ele=new drag();
ele.init();
init();


homeIcon.addEventListener('touchstart',touchstart,false);
homeIcon.addEventListener('touchmove',touchmove,false);
homeIcon.addEventListener('touchend',touchend,false);

function touchstart(e){
    var touch=e.touches[0];
    isDraging=true;    
    ele.touchOffsetX=touch.clientX-homeIcon.offsetLeft;
    ele.touchOffsetY=touch.clientY-homeIcon.offsetTop;
}
function touchmove(e){
    var touch=e.touches[0];
    var touchX=touch.clientX;
    var touchY=touch.clientY;

    var moveX=0;
    var moveY=0;
    if(isDraging==true){
        moveX=touchX-ele.touchOffsetX;
        moveY=touchY-ele.touchOffsetY;

        homeIcon.style.left=moveX+'px';
        homeIcon.style.top=moveY+'px';
    }
}
function touchend(e){
    var touch=e.changedTouches[0];
    isDraging=false;
    var endX=touch.clientX-ele.touchOffsetX;
    var endY=homeIcon.offsetTop;
    var deltaX=endX;
    if(endX>halfw){
        var mySetInterval=setInterval(function(){            
            homeIcon.style.left=deltaX+'px';
            deltaX+=5;
            if(deltaX>=Math.floor(w-5-homeIcon.offsetWidth)){
                homeIcon.style.left=Math.floor(w-5-homeIcon.offsetWidth)+'px';
                clearInterval(mySetInterval);
            }
        },10);
    }else{
        var mySetInterval=setInterval(function(){            
            homeIcon.style.left=deltaX+'px';
            deltaX-=5;
            if(deltaX<=Math.floor(5)){
                homeIcon.style.left=5+'px';
                clearInterval(mySetInterval);
            }
        },10);
    } 
    if(endY<0){
        homeIcon.style.top='5px';
    }else if(endY+homeIcon.offsetHeight>h){
        homeIcon.style.top=Math.floor(h-5-homeIcon.offsetHeight)+'px';
    }  
}