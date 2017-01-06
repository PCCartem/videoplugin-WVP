<?php
/*
Plugin Name: Web Video Player
Description: VideoJS plugin for wordpress
Version: 1.0
Author: Arman Arakelyan
*/

class VideoPlugin
{
    public function __construct()
    {
        ob_start();
        include_once plugin_dir_path( __FILE__ ).'includes/const.php';
        include_once plugin_dir_path( __FILE__ ).'class/Video.php';
        include_once plugin_dir_path( __FILE__ ).'class/ParseVideo.php';
        add_filter('wp_head', array($this, 'addCss'), 20);
        add_filter('wp_footer', array($this, 'addJS'), 20);
        add_action('admin_menu', array($this, 'videoJsMenu'));
        add_shortcode('WebVideoPlayer', array($this, 'getVideoJsEmbed'));
    }

    public function videoJsMenu(){
        add_menu_page( 'Web Video Player Plugin', 'Web Video Player', 'manage_options', 'WebVideoPlayer-plugin', array($this, 'adminPage') );
        add_submenu_page( 'WebVideoPlayer-plugin', 'Add New Video', 'Add Video', 'manage_options', 'add-WebVideoPlayer', array($this, 'adminSubPage'));
    }

    public function addCss()
    {
        //$cssUrl = plugins_url( 'css/video-js.css', __FILE__ );
		$css_bundle_Url = plugins_url( 'css/bundle.min.css', __FILE__ );
        $ieJsUrl = plugins_url( 'js/videojs-ie8.min.js', __FILE__ );

        echo "<!--[if lt IE 9]><script type='text/javascript' src='" . $ieJsUrl . "'></script><![endif]-->";
        //echo "<link rel='stylesheet' id='video-js-css'  href='" . $cssUrl . "' type='text/css' media='all' />";
		echo "<link rel='stylesheet' id='video-js-bundle-css'  href='" . $css_bundle_Url . "' type='text/css' media='all' />";
		echo " <style>.vjs-youtube .vjs-iframe-blocker { display: none; }.vjs-youtube.vjs-user-inactive .vjs-iframe-blocker { display: block; }.vjs-youtube .vjs-poster { background-size: cover; }.vjs-youtube-mobile .vjs-big-play-button { display: none; }
		.vjs-vimeo .vjs-iframe-blocker { display: none; }.vjs-vimeo.vjs-user-inactive .vjs-iframe-blocker { display: block; }.vjs-vimeo .vjs-poster { background-size: cover; }.vjs-vimeo { height:100%; }.vimeoplayer { width:100%; height:180%; position:absolute; left:0; top:-40%; }
		</style>";
   }

    public function addJS()
    {
        //$videoJs = plugins_url( 'js/video.js', __FILE__ );
        $videoJsYoutube = plugins_url( 'js/youtube.min.js', __FILE__ );
        $videoJsVimeo = plugins_url( 'js/vimeo.js', __FILE__ );
        $chromeFixJs = plugins_url( 'js/chrome-fix-videojs.js', __FILE__ );
		$Js_bundle_Url = plugins_url( 'js/bundle.min.js', __FILE__ );
		$Js_custom = plugins_url( 'js/custom.js', __FILE__ );
        //echo '<script src="' . $videoJs . '"></script>';
		echo '<script src="' . $Js_custom . '"></script>';
		echo '<script src="' . $Js_bundle_Url . '"></script>';		
        echo '<script src="' . $videoJsYoutube . '"></script>';
        echo '<script src="' . $videoJsVimeo . '"></script>';
        echo '<script src="' . $chromeFixJs . '"></script>';
		echo '<script type="text/javascript" id="www-widgetapi-script" src="https://s.ytimg.com/yts/jsbin/www-widgetapi-vflS50iB-/www-widgetapi.js" async=""></script>';
		echo '<script src="https://www.youtube.com/iframe_api" async=""></script>';
    }

    public function adminPage(){
        include_once plugin_dir_path( __FILE__ ).'includes/allVideos.php';

    }

    public function adminSubPage(){
        include_once plugin_dir_path( __FILE__ ).'includes/addVideo.php';

    }

    public static function install()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "CinemagraphPlayer";

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
                  id mediumint(9) NOT NULL AUTO_INCREMENT,
                  name tinytext NOT NULL,
                  video_url varchar(255) DEFAULT '' NOT NULL,
                  gif_url varchar(255) DEFAULT '' NOT NULL,
                  autoplay char(1) NOT NULL,
                  video_loop char(1) NOT NULL,
                  show_gif char(1) NOT NULL,
                  source char(1) NOT NULL,
                  PRIMARY KEY  (id)
                ) $charset_collate;";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    public function getVideoJsEmbed($atts)
    {
        $mobile = false;
        if(wp_is_mobile()) {$mobile = true;}
        $videoEmbed = new ParseVideo($atts, $mobile);
        $videoEmbed = $videoEmbed->getVideoJsEmbed();
        return $videoEmbed;
    }
}

register_activation_hook( __FILE__, array( 'VideoPlugin', 'install' ) );
new VideoPlugin();

function media_selector_settings_page_callback() {

}
add_action( 'admin_footer', 'media_selector_print_scripts' );
function media_selector_print_scripts() {
    $my_saved_attachment_post_id = get_option( 'media_selector_attachment_id', 0 );
    ?><script type='text/javascript'>
        jQuery( document ).ready( function( $ ) {
            // Uploading files
            var file_frame;
            var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id
            var set_to_post_id = <?php echo $my_saved_attachment_post_id; ?>; // Set this

            jQuery('#upload_image_button').on('click', function( event ){
                event.preventDefault();
                // If the media frame already exists, reopen it.
                if ( file_frame ) {
                    // Set the post ID to what we want
                    file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
                    // Open frame
                    file_frame.open();
                    return;
                } else {
                    // Set the wp.media post id so the uploader grabs the ID we want when initialised
                    wp.media.model.settings.post.id = set_to_post_id;
                }
                // Create the media frame.
                file_frame = wp.media.frames.file_frame = wp.media({
                    title: 'Select a image to upload',
                    button: {
                        text: 'Use this image',
                    },
                    multiple: false	// Set to true to allow multiple files to be selected
                });
                // When an image is selected, run a callback.
                file_frame.on( 'select', function() {
                    // We set multiple to false so only get one image from the uploader
                    attachment = file_frame.state().get('selection').first().toJSON();
                    // Do something with attachment.id and/or attachment.url here
                    $( '#image-preview' ).attr( 'src', attachment.url ).css( 'width', 'auto' );
                    $( '#image_attachment_id' ).val( attachment.id );
                    // Restore the main post ID
                    wp.media.model.settings.post.id = wp_media_post_id;
                });
                // Finally, open the modal
                file_frame.open();
            });
            // Restore the main ID when the add media button is pressed
            jQuery( 'a.add_media' ).on( 'click', function() {
                wp.media.model.settings.post.id = wp_media_post_id;
            });
        });
    </script><?php

}