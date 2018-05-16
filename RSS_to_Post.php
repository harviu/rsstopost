<?php
/*
Plugin Name: RSS to post
Plugin URI:
Description: Automatically post from the RSS feed
Version: 1.0.0
Author: Harvey
Author URI: http://amoeba.website/
License: GPL
*/
require_once ABSPATH . '/wp-admin/includes/post.php';

register_activation_hook(__FILE__, 'my_activation');

register_deactivation_hook(__FILE__, 'my_deactivation');

function my_deactivation() {
	wp_clear_scheduled_hook('my_daily_event');
}

function my_activation() {
    if (! wp_next_scheduled ( 'my_daily_event' )) {
	     wp_schedule_event(time(), 'daily', 'my_daily_event');
    }
}

add_action('my_daily_event', 'rss_to_post');


function rss_to_post(){

  $url ="http://feeds.soundcloud.com/users/soundcloud:users:298588440/sounds.rss";

	$options = array();
	$options['user-agent'] = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36';
	$options['timeout'] = 10;
	$response = wp_safe_remote_get($url, $options);
	$m_content = wp_remote_retrieve_body( $response );

	$rss = fetch_feed($url);
  $maxitems = 1;

  if(! is_wp_error($rss)){
    $maxitems = $rss->get_item_quantity($maxitems);
    $rss_items = $rss->get_items( 0, $maxitems );
  }

  foreach ($rss_items as $item){
	$enclosure = $item->get_enclosure();
// 	$encstring = $enclosure->get_link();
	$postdate = date('Y-m-d H:i:s', strtotime($item->get_date()));
	$content = $item->get_content()."\n".$item->get_link();
	$title = $item->get_title();
	$para = array(
		'post_content' => $m_content,
		'post_title' => $title,
		'post_author' => 3,
		'post_date_gmt' => $postdate,
  		'post_status' => 'publish'
	);

	if(substr($para['post_title'],0,7)=="You Inc"){
		$para['post_category'] = array(7);
	}else{
		$para['post_category'] = array(8);
	}
 	if( 0==post_exists($title) ) {
		$post_id = wp_insert_post($para);
 	}
// 	$meta_id = add_post_meta($post_id,'enclosure',$encstring,true);
  }
}

?>
