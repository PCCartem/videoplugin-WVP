<?php

Class Video {
    private $id;
    private $name;
    private $video_url;
    private $gif_url;
    private $autoplay;
    private $video_loop;
    private $show_gif;
    private $source;
    private $error = '';

    public function __construct($id = NULL)
    {
        if(isset($id) && is_numeric($id)) {
            $this->id = $id;
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getVideoUrl()
    {
        return $this->video_url;
    }

    public function setVideoUrl($video_url)
    {
        if($this->parseVideoUrl($video_url) == FALSE) {
            $this->error .= "Video url is not valid" ;
        }
        $this->video_url = $video_url;
    }

    public function getGifUrl()
    {
        return $this->gif_url;
    }

    public function setGifUrl($gif_url)
    {
        if(empty($gif_url) || strtolower(end(explode(".",$gif_url))) == "gif") {
            $this->gif_url = $gif_url;
        } else {
            $this->error .= '<br>Invalid Gif Url.';
            return false;
        }
    }

    public function getAutoplay()
    {
        return $this->autoplay;
    }

    public function setAutoplay($autoplay)
    {
        $this->autoplay = $autoplay;
    }

    public function getVideoLoop()
    {
        return $this->video_loop;
    }

    public function setVideoLoop($video_loop)
    {
        $this->video_loop = $video_loop;
    }

    public function getShowGif()
    {
        return $this->show_gif;
    }

    public function setShowGif($show_gif)
    {
        $this->show_gif = $show_gif;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function setSource($source)
    {
        $this->source = $source;
    }

    public function getError()
    {
        return $this->error;
    }

    public function setError($error)
    {
        $this->error = $error;
    }

    public function getVideoById($id)
    {
        global $wpdb;
        $tableName = self::tableName();
        $video = $wpdb->get_row( 'SELECT * FROM ' . $tableName . ' WHERE id = "' . $id . '"', ARRAY_A );
        if($video) {
            $this->setName($video['name']);
            $this->setVideoUrl($video['video_url']);
            $this->setGifUrl($video['gif_url']);
            $this->setAutoplay($video['autoplay']);
            $this->setVideoLoop($video['video_loop']);
            $this->setShowGif($video['show_gif']);
            $this->setSource($video['source']);
            return $video;
        } else {
            $this->error .= 'no video found';
            return false;
        }
    }

    public function getAll($limit, $offset, $where = '')
    {
        global $wpdb;
        $tableName = self::tableName();
        $videos = $wpdb->get_results( "SELECT * FROM $tableName $where ORDER BY id DESC LIMIT $offset, $limit", ARRAY_A );

        return $videos;
    }

    public static function deleteById($id)
    {
        global $wpdb;
        $tableName = Video::tableName();
        if($wpdb->delete($tableName, array( 'id' => $id ))) {
            return true;
        }
    }

    public function save()
    {
        global  $wpdb;

        if($this->id == NULL && $this->error == '') {
            $addVideo = $wpdb->insert(
                self::tableName(),
                array(
                    'name' => $this->name,
                    'video_url' => $this->video_url,
                    'gif_url' => $this->gif_url,
                    'autoplay' => $this->autoplay,
                    'video_loop' => $this->video_loop,
                    'show_gif' => $this->show_gif,
                    'source' => $this->source
                )
            );

            if($addVideo === FALSE) {
                echo "error adding video";
            } else {
                return true;
            }
        } elseif ($this->error != '') {
            return $this->error;
        } else {
            $wpdb->update(
                self::tableName(),
                array(
                    'name' => $this->name,
                    'video_url' => $this->video_url,
                    'gif_url' => $this->gif_url,
                    'autoplay' => $this->autoplay,
                    'video_loop' => $this->video_loop,
                    'show_gif' => $this->show_gif,
                    'source' => $this->source
                ),
                array( 'id' => $this->id )
            );
        }
    }

    public function parseVideoUrl($videoUrl)
    {
        $urls = parse_url($videoUrl);

        if (!filter_var($videoUrl, FILTER_VALIDATE_URL) === false) {
            //Check if Youtube
            if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $videoUrl, $match)) {
                $this->setSource(SRC_YOUTUBE);
                return true;
            }

            //Check if Vimeo
            if($urls['host'] == 'vimeo.com'){
                $vimid = ltrim($urls['path'], '/');
                if (is_numeric($vimid)) {
                    $this->setSource(SRC_VIMEO);
                    return true;
                }
            }

            //Check if MP4
            if(strtolower(end(explode(".",$videoUrl))) =="mp4") {
                $this->setSource(SRC_MP4);
                return true;
            }
        }
        return false;
    }

    public static function getShortCode($id)
    {
        return '[WebVideoPlayer id="' . $id . '"]';
    }

    public static function tableName()
    {
        global  $wpdb;
        $table_name = $wpdb->prefix . "CinemagraphPlayer";
        return $table_name;
    }
}