<?php

if(isset($_GET['edit'])) {
    $videoId = $_GET['edit'];
    $videoEdit = new Video();
    $videoEdit = $videoEdit->getVideoById($videoId);

    $error_message = '';
    if(isset($_POST["video_name"]) && empty($_POST["video_name"])) {
        $error_message .= 'Video Name is empty</br>';
    }

    if(isset($_POST["video_url"]) && empty($_POST["video_url"])) {
        $error_message .= 'Video URL is empty</br>';
    }

    if(isset($_POST["show_gif"]) && $_POST["show_gif"] == 'show_gif_true' && empty($_POST["gif_url"])) {
        $error_message .= 'Gif URL is empty</br>';
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

        $video = new Video($videoId);
        $video->setName($videoName);
        $video->setVideoUrl($videoUrl);
        $video->setGifUrl($gifUrl);
        $video->setAutoplay($videoAutoplay);
        $video->setVideoLoop($videoLoop);
        $video->setShowGif($showGif);
        if($video->getError() != '') {
            $error_message .= $video->getError();
        } else {
            $video->save();
            wp_redirect('?page=WebVideoPlayer-plugin&modified');
            if ( isset( $_POST['image_attachment_id'] ) ) :
                update_option( 'media_selector_attachment_id_'.$videoId, absint( $_POST['image_attachment_id'] ) );
            endif;
            if (  isset( $_POST['image_attachment_id'] ) && isset( $_POST['show_image_attachment_id'] ) ) {
                update_option( 'show_media_selector_attachment_id_'.$videoId, 1 );
            } else {
                update_option( 'show_media_selector_attachment_id_'.$videoId, 0 );
            }
        }
    } ?>


<div class="wrap">
    <h2>Edit Video</h2>

    <?php if($error_message != '') { ?>
        <div class="error notice"><p><?=$error_message?></p></div>
    <?php } ?>

    <form method="post">
        <table class="form-table">
            <tbody>
            <tr class="form-field form-required">
                <th scope="row"><label for="video_name">Video Name: <span class="description">(required)</span></label></th>
                <td><input type="text" name="video_name" id="video_name" value="<?=$videoEdit["name"]?>"></td>
            </tr>
            <tr class="form-field form-required">
                <th scope="row"><label for="video_url">Video Url: <span class="description">(required)</span></label></th>
                <td><input type="text" id="video_url" name="video_url" value="<?=$videoEdit["video_url"]?>"></td>
            </tr>
            <tr class="form-field">
                <th scope="row"><label for="gif_url">Gif Url: </label></th>
                <td><input type="text" name="gif_url" id="gif_url" value="<?=$videoEdit["gif_url"]?>"></td>
            </tr>
            <tr class="form-field">
                <th scope="row"><label for="video_autoplay">AutoPlay: </label></th>
                <td>
                    <input type="radio" name="video_autoplay" value="autoplay_true" <?=($videoEdit["autoplay"] == '1') ? 'checked' : ''?>> Yes
                    <input type="radio" name="video_autoplay" value="autoplay_false" <?=($videoEdit["autoplay"] == '0') ? 'checked' : ''?> > No
                </td>
            </tr>
            <tr class="form-field">
                <th scope="row"><label for="video_loop">Video Loop: </label></th>
                <td>
                    <input type="radio" name="video_loop" value="video_loop_true" <?=($videoEdit["video_loop"] == '1') ? 'checked' : ''?>> Yes
                    <input type="radio" name="video_loop" value="video_loop_false" <?=($videoEdit["video_loop"] == '0') ? 'checked' : ''?>> No
                </td>
            </tr>
            <tr class="form-field">
                <th scope="row"><label for="show_gif">Show gif on mobile: </label></th>
                <td>
                    <input type="radio" name="show_gif" value="show_gif_true" <?=($videoEdit["show_gif"] == '1') ? 'checked' : ''?>> Yes
                    <input type="radio" name="show_gif" value="show_gif_false" <?=($videoEdit["show_gif"] == '0') ? 'checked' : ''?>> No
                </td>
            </tr>
            <tr>
                <? wp_enqueue_media(); ?>
                <td colspan="2">
                    <span><h2>Change logo</h2></span>
                    <div class='image-preview-wrapper'>
                        <img id='image-preview' src='<?php echo wp_get_attachment_url( get_option( 'media_selector_attachment_id_'.$videoId ) ); ?>' height='100'>
                    </div>
                    <input id="upload_image_button" type="button" class="button" value="<?php _e( 'Upload image' ); ?>" />
                    Show logo: <input type='checkbox' name='show_image_attachment_id' id='show_image_attachment_id' <?php echo get_option( 'show_media_selector_attachment_id_'.$videoId ) == 1 ? "checked" : ""; ?>>
                    <input type='hidden' name='image_attachment_id' id='image_attachment_id' value='<?php echo get_option( 'media_selector_attachment_id_'.$videoId ); ?>'>
                </td>
            </tr>
            </tbody>
        </table>

        <input type="submit" class="button button-primary" value="Save Changes">
    </form>
<?php } ?>