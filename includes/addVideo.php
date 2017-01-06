<?php

global $wpdb;
$error_message = '';
if(isset($_POST["video_name"]) && empty($_POST["video_name"])) {
    $error_message .= 'Video Name is empty!</br>';
}

if(isset($_POST["video_url"]) && empty($_POST["video_url"])) {
    $error_message .= 'Video URL is empty!</br>';
}

if(isset($_POST["show_gif"]) && $_POST["show_gif"] == 'show_gif_true' && empty($_POST["gif_url"])) {
    $error_message .= 'Gif URL is empty!</br>';
}

if($error_message == '' && isset($_POST["video_name"])) {
    $videoName = $_POST["video_name"];
    $videoUrl = $_POST["video_url"];
    $gifUrl = $_POST["gif_url"];
    if($_POST["video_autoplay"] == 'autoplay_true') {
        $videoAutoplay = 1;
    } else {
        $videoAutoplay = 0;
    }
    if($_POST["video_loop"] == 'video_loop_true') {
        $videoLoop = 1;
    } else {
        $videoLoop = 0;
    }
    if($_POST["show_gif"] == 'show_gif_true') {
        $showGif = 1;
    } else {
        $showGif = 0;
    }

    $video = new Video();
    $video->setName($videoName);
    $video->setVideoUrl($videoUrl);
    $video->setShowGif($showGif);
    $video->setGifUrl($gifUrl);
    $video->setAutoplay($videoAutoplay);
    $video->setVideoLoop($videoLoop);
    if($video->getError() != '') {
        $error_message .= $video->getError();
    } else {
        if($video->save()) {
            $added = true;
        }
    }
}

if ($added == true) {
    $success_message = "Your video has been added successfully!";
    wp_redirect('?page=WebVideoPlayer-plugin');
}
?>
<div class='wrap'>
    <?php if($error_message != '') { ?>
        <div class="error notice"><p><?=$error_message?></p></div>
    <?php } ?>
    <?php if(!empty($success_message)) { ?>
        <div class="updated notice">
            <p><?=$success_message?></p>
            <p>The shorcode for this video is: <b><?=Video::getShortCode($wpdb->insert_id)?></b></p>
            <?
            $insert_id = $wpdb->insert_id;
            if ( isset( $_POST['image_attachment_id'] ) ) :
                update_option( 'media_selector_attachment_id_'.$insert_id, absint( $_POST['image_attachment_id'] ) );
            endif;
            if ( isset( $_POST['image_attachment_id'] ) && isset( $_POST['show_image_attachment_id'] ) ) {
                update_option( 'show_media_selector_attachment_id_'.$insert_id, 1 );
            } else {
                update_option( 'show_media_selector_attachment_id_'.$insert_id, 0 );
            }
            ?>
        </div>
    <?php } ?>

    <h1>Add New Video</h1>
    <p>Add a new video from Youtube, Vimeo or URL (mp4)</p>
    <form method="post" action="admin.php?page=add-WebVideoPlayer&video_added">
        <table class="form-table">
            <tbody>
            <tr class="form-field form-required">
                <th scope="row"><label for="video_name">Video Name: <span class="description">(required)</span></label></th>
                <td><input type="text" name="video_name" id="video_name" value="<?=(isset($_POST["video_name"])) ? $_POST["video_name"] : ''?>"></td>
            </tr>
            <tr class="form-field form-required">
                <th scope="row"><label for="video_url">Video Url: <span class="description">(required)</span></label></th>
                <td><input type="text" name="video_url" id="video_url" value="<?=(isset($_POST["video_url"])) ? $_POST["video_url"] : ''?>"></td>
            </tr>
            <tr class="form-field">
                <th scope="row"><label for="gif_url">Gif Url: </label></th>
                <td><input type="text" name="gif_url" id="gif_url" value="<?=(isset($_POST["gif_url"])) ? $_POST["gif_url"] : ''?>"></td>
            </tr>
            <tr class="form-field">
                <th scope="row"><label for="video_autoplay">AutoPlay: </label></th>
                <td>
                    <input type="radio" name="video_autoplay" value="autoplay_true" <?=(isset($_POST["video_autoplay"]) && $_POST["video_autoplay"] == 'autoplay_true') ? 'checked' : ''?><?=(!isset($_POST["video_autoplay"])) ? 'checked' : ''?>> Yes
                    <input type="radio" name="video_autoplay" value="autoplay_false" <?=(isset($_POST["video_autoplay"]) && $_POST["video_autoplay"] == 'autoplay_false') ? 'checked' : ''?>> No<br>
                </td>
            </tr>
            <tr class="form-field">
                <th scope="row"><label for="video_loop">Video Loop: </label></th>
                <td>
                    <input type="radio" name="video_loop" value="video_loop_true" <?=(isset($_POST["video_loop"]) && $_POST["video_loop"] == 'video_loop_true') ? 'checked' : ''?><?=(!isset($_POST["video_loop"])) ? 'checked' : ''?>> Yes
                    <input type="radio" name="video_loop" value="video_loop_false" <?=(isset($_POST["video_loop"]) && $_POST["video_loop"] == 'video_loop_false') ? 'checked' : ''?>> No<br>
                </td>
            </tr>
            <tr class="form-field">
                <th scope="row"><label for="show_gif">Show gif on mobile: </label></th>
                <td>
                    <input type="radio" name="show_gif" value="show_gif_true" <?=(isset($_POST["show_gif"]) && $_POST["show_gif"] == 'show_gif_true') ? 'checked' : ''?>> Yes
                    <input type="radio" name="show_gif" value="show_gif_false" <?=(isset($_POST["show_gif"]) && $_POST["show_gif"] == 'show_gif_false') ? 'checked' : ''?><?=(!isset($_POST["show_gif"])) ? 'checked' : ''?>> No<br>
                </td>
            </tr>
            <tr>
                <? wp_enqueue_media(); ?>
                <td colspan="2">
                    <span><h2>Change logo</h2></span>
                    <div class='image-preview-wrapper'>
                        <img id='image-preview' src='<?php echo wp_get_attachment_url( get_option( 'media_selector_attachment_id_'.$wpdb->insert_id ) ); ?>' height='100'>
                    </div>
                    <input id="upload_image_button" type="button" class="button" value="<?php _e( 'Upload image' ); ?>" />
                    Show logo: <input type='checkbox' name='show_image_attachment_id' id='show_image_attachment_id' <?php echo get_option( 'show_media_selector_attachment_id_'.$wpdb->insert_id ) == 1 ? "checked" : ""; ?>>
                    <input type='hidden' name='image_attachment_id' id='image_attachment_id' value='<?php echo get_option( 'media_selector_attachment_id_'.$wpdb->insert_id ); ?>'>
                </td>
               </tr>

            </tbody>
            </table>
        <input type="submit" class="button button-primary" value="Add New Video">
    </form>
</div>
<?
if ($added == true) {
$_POST = array();
}
?>