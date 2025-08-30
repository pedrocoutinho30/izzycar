//jquery-click-scroll
//by syamsul'isul' Arifin (corrigido)

var sectionArray = [1, 2, 3, 4, 5];

$.each(sectionArray, function(index, value){
    
    // Scroll event
    $(document).scroll(function(){
        var $section = $('#section_' + value);
        if ($section.length) { // só corre se existir
            var offsetSection = $section.offset().top - 75;
            var docScroll = $(document).scrollTop();
            var docScroll1 = docScroll + 1;

            if (docScroll1 >= offsetSection) {
                $('.navbar-nav .nav-item .nav-link').removeClass('active').addClass('inactive');
                $('.navbar-nav .nav-item .nav-link').eq(index).addClass('active').removeClass('inactive');
            }
        }
    });

    // Click event
    $('.click-scroll').eq(index).click(function(e){
        var $section = $('#section_' + value);
        if ($section.length) { // só corre se existir
            var offsetClick = $section.offset().top - 75;
            e.preventDefault();
            $('html, body').animate({
                'scrollTop': offsetClick
            }, 300);
        }
    });
});

$(document).ready(function(){
    $('.navbar-nav .nav-item .nav-link:link').addClass('inactive');    
    $('.navbar-nav .nav-item .nav-link').eq(0).addClass('active').removeClass('inactive');
});
