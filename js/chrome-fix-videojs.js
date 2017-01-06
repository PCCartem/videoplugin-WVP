(function($){
    $( document ).ready(function() {
        $('.video-js').each(function() {
            var id = $(this).attr('id');
            if($(this)[0].hasAttribute("autoplay")) {
                videojs(this).ready(function(){
                    var myPlayer = this;
                    // Start playing the video.
                    myPlayer.play();
                });
            }
        });
    });
})(jQuery);
