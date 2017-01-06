<?php
global $wpdb;

if(isset($_GET['pagenum']) && isset($_GET['modified'])) {
    $page_num = $_GET['pagenum'];
    wp_redirect('?page=WebVideoPlayer-plugin&pagenum=' . $page_num);
}

if(isset($_GET['del'])) {
    $id = $_GET['del'];
    if(Video::deleteById($id)){ ?>
        <div class="updated notice">
            <p>The video [id=<?=$id?>] was deleted!</p>
        </div>
    <?php }
}

if(isset($_GET['modified'])){ ?>
    <div class="updated notice">
        <p>The video was updated!</p>
    </div>
<?php }

if(isset($_GET['edit'])) {
    include_once plugin_dir_path( __FILE__ ).'/editVideo.php';

} else {

    $type = !empty($_GET['type']) ? $_GET['type'] : '';
    $pagenum = isset($_GET['pagenum']) ? absint($_GET['pagenum']) : 1;
    $limit = 10; // number of rows in page
    $offset = ($pagenum - 1) * $limit;
    $videos = new Video();

    $where = !empty($type) ? 'WHERE `source` = ' . $type : '';

    $videos = $videos->getAll($limit, $offset, $where);

    $total = array(
        SRC_VIMEO => $wpdb->get_var("SELECT COUNT(`id`) FROM " . Video::tableName() . " WHERE `source` = '" . SRC_VIMEO . "'"),
        SRC_YOUTUBE => $wpdb->get_var("SELECT COUNT(`id`) FROM " . Video::tableName() . " WHERE `source` = '" . SRC_YOUTUBE . "'"),
        SRC_MP4 => $wpdb->get_var("SELECT COUNT(`id`) FROM " . Video::tableName() . " WHERE `source` = '" . SRC_MP4 . "'")
    );

    $total[0] = array_sum($total);

    $num_of_pages = ceil($total[(int)$type] / $limit);
    $page_links = paginate_links(array(
        'base' => add_query_arg('pagenum', '%#%'),
        'format' => '',
        'prev_text' => __('&laquo;', 'text-domain'),
        'next_text' => __('&raquo;', 'text-domain'),
        'total' => $num_of_pages,
        'current' => $pagenum
    ));

    echo "<div class='wrap'>";
    $outputHtml = "<h1>Web Video Player / All Videos <a href='?page=add-WebVideoPlayer' class='page-title-action'>Add New Video</a></h1>";
    $outputHtml .= '<ul class="subsubsub">
	<li class="all"><a href="?page=WebVideoPlayer-plugin" class="' . (empty($type) ? 'current' : '') . '">All <span class="count">(' . $total[0] . ')</span></a> |</li>
	<li class="vimeo"><a href="?page=WebVideoPlayer-plugin&type=' . SRC_VIMEO . '" class="' . ($type == SRC_VIMEO ? 'current' : '') . '">Vimeo<span class="count">(' . $total[SRC_VIMEO] . ')</span></a> | </li>
	<li class="youtube"><a href="?page=WebVideoPlayer-plugin&type=' . SRC_YOUTUBE . '" class="' . ($type == SRC_YOUTUBE ? 'current' : '') . '">Youtube<span class="count">(' . $total[SRC_YOUTUBE] . ')</span></a> | </li>
	<li class="mp4"><a href="?page=WebVideoPlayer-plugin&type=' . SRC_MP4 . '" class="' . ($type == SRC_MP4 ? 'current' : '') . '">MP4<span class="count">(' . $total[SRC_MP4] . ')</span></a></li>
</ul>';
    $outputHtml .= '<table class="wp-list-table widefat fixed striped videos">';
    $outputHtml .= '<thead>
	                <tr>
		                <th scope="col" id="title" class="manage-column column-id"><span>ID</span></th>
		                <th scope="col" id="author" class="manage-column column-name">Name</th>
		                <th scope="col" id="author" class="manage-column column-video-url">Video Url</th>
		                <th scope="col" id="author" class="manage-column column-gif-url">Gif Url</th>
		                <th scope="col" id="author" class="manage-column column-autoplay">AutoPlay</th>
		                <th scope="col" id="author" class="manage-column column-loop">Loop</th>
		                <th scope="col" id="author" class="manage-column column-gif-mobile">Gif on mobile</th>
		                <th scope="col" id="author" class="manage-column column-video-source">Source</th>
		                <th scope="col" id="author" class="manage-column column-shortcode">Shortcode</th>
		                <th scope="col" id="author" class="manage-column column-action">Action</th>
		            </tr>
		         </thead>';

    foreach ($videos as $video) {
        if ($video['source'] == 1) {
            $source = "Youtube";
        } elseif ($video['source'] == 2) {
            $source = "Vimeo";
        } else {
            $source = "MP4";
        }
        $outputHtml .= '<tr>
		                <th><span>' . $video['id'] . '</span></th>
		                <th><span>' . $video['name'] . '</span></th>
		                <th><span>' . $video['video_url'] . '</span></th>
		                <th><span>' . $video['gif_url'] . '</span></th>
		                <th><span>' . ($video['autoplay'] == 0 ? "No" : "Yes") . '</span></th>
		                <th><span>' . ($video['video_loop'] == 0 ? "No" : "Yes") . '</span></th>
		                <th><span>' . ($video['show_gif'] == 0 ? "No" : "Yes") . '</span></th>
		                <th><span>' . $source . '</span></th>
		                <th><span>' . Video::getShortCode($video['id']) . '</span></th>
		                <th><p><a href="?page=WebVideoPlayer-plugin&edit=' . $video['id'] . '" class="button button-primary">Edit</a></p><p><a href="?page=WebVideoPlayer-plugin&del=' . $video['id'] . '" class="button button-secondary">Delete</a></p></th>
		            </tr>';
    }
    $outputHtml .= '</tbody></table>';


    echo $outputHtml;

    if ($page_links) {
        echo '<div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">' . $page_links . '</div></div>';
    }

    echo "</div>";
}
?>

