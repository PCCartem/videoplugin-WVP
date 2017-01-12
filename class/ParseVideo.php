<?php

class ParseVideo {
    private $video;
    private $isMobile = false;
    private $width = '640';
    private $height = '400';
    private $logo = false;


    public function __construct($attr, $mobile)
    {
        if($mobile) { $this->isMobile = true; }
        $this->width = !empty($attr['width']) ? $attr['width'] : $this->width;
        $this->height = !empty($attr['height']) ? $attr['height'] : $this->height;
        $videoId = $attr['id'];
        $video = new Video();
        $this->video = $video->getVideoById($videoId);
    }

    public function getVideoJsEmbed()
    {
        $autoplay = false;
        $loop = false;
        //if mobile and show_gif == TRUE
        if($this->isMobile && $this->video['show_gif'] == 1) {
            return '<img src="' . $this->video['gif_url'] . '">';
        }

        if($this->video['autoplay'] == '1') {
            $autoplay = true;
        }
        if($this->video['video_loop'] == '1') {
            $loop = true;
        }
        if(get_option( 'show_media_selector_attachment_id_'.$this->video['id'] ) == 1 ){
            $url = wp_get_attachment_url( get_option( 'media_selector_attachment_id_'.$this->video['id'] ) );;
            $varScript = "<style>.video-js-".$this->video['id']." {background-image: url('".$url ."')}</style>";
        }else {
            $varScript = "";
        }


        //youtube player
        if($this->video['source'] == SRC_YOUTUBE) {
            return $varScript.'<video playsinline webkit-playsinline
                        id="video-js-' . $this->video['id'] . '"
                        class="video-js vjs-fluid vjs-default-skin"
                        controls
                        ' . ($loop?"loop ":" ") . ' 
                        ' . ($autoplay?"autoplay ":" ") . '
                        width="' . $this->width . '" 
                        height="' . $this->height . '"
                        data-setup=\'{/*"inactivityTimeout":0*/,"fluid": true, "techOrder": ["youtube"], "sources": [{ "type": "video/youtube", "src": "' . $this->video['video_url'] . '"}] }\'>
                     </video>';
        }
        //vimeo player
        elseif ($this->video['source'] == SRC_VIMEO) {
			$data = array(
				'techOrder' => array(
					'vimeo'
				),
				'sources' => array(
					array(
						'type' => 'video/vimeo',
						'src' => $this->video['video_url']
					)
				)
			);
            return $varScript.'<video playsinline webkit-playsinline
                        id="video-js-' . $this->video['id'] . '"
                        class="video-js vjs-fluid vjs-default-skin"
                        controls
                        ' . ($loop?"loop ":" ") . '
                        ' . ($autoplay?"autoplay ":" ") . '
                        width="' . $this->width . '"
                        height="' . $this->height . '"
//							data-setup=\'{"/*inactivityTimeout":0, */"fluid": true, "techOrder": ["vimeo"], "sources": [{ "type": "video/vimeo", "src": "' . $this->video['video_url'] . '"}] }\'>
                    </video>';
        }
        //mp4 player
        else {
            return $varScript.'<video playsinline webkit-playsinline id="video-js-' . $this->video['id'] . '" class="video-js vjs-fluid vjs-default-skin" ' . ($autoplay?"autoplay ":" ") . ($loop?"loop ":" ") . '  controls preload="auto" controls="true" width="' . $this->width . '" height="' . $this->height . '"
                        data-setup=\'{/*"inactivityTimeout":0*/}\'>
                        <source src="' . $this->video['video_url'] . '" type=\'video/mp4\'>
                    </video>';
        }
    }
}