$(document).ready(function(){
    
    $('.parallax').parallax();
    
    $(".my-bar").on('click',function(){
        $('.ui.sidebar').sidebar('toggle');
    });
    $(document).on('scroll',function(){
        //alert(document.documentElement.clientHeight);
        //alert($(document).scrollTop())
        if($(document).scrollTop()>300){
            $('.scrollButton').css({opacity:1,visibility:'visible'});
        }
        else{
            $('.scrollButton').css({opacity:0,visibility:'hidden'});
        }
    });
    $('.scrollButton').click(function(){
        $("html, body").animate({ scrollTop: 0 }, 600);
        return false;
    });
    
});