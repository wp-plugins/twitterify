<?php 
/*
Plugin Name: Twitterify
Plugin URI: http://shailan.com/wordpress/plugins/twitterify
Description: Enables use of <strong>autolink</strong>, <strong>#hashtags</strong> and <strong>@author</strong> links on your posts. <strong>Links are not directed to twitter. They provide this functionality on your site.</strong>
Version: 1.0
Author: Matt Say
Author URI: http://shailan.com
*/

function twitterify_content ( $text ){

	// Make content clickable
	$text = make_clickable( $text );
	
	// Remove http://
	$text = preg_replace( '/(">http:\/\/)(.*?)<\/a>/i', "\">$2</a>", $text ); 
	
	 // Remove www.
	$text = preg_replace( '/(">www.)(.*?)<\/a>/i', "\">$2</a>", $text );
	
	// Check permalink structure
	if ( get_option('permalink_structure') != '' ) { 
	
		// permalinks enabled
		$permalink = get_option( 'permalink_structure', '' );
		
		$prefix = '';
		if( strpos( $permalink, '/index.php/') !== false ){
			$prefix = '/index.php';
		}		
		
		$tag_base = get_option( 'tag_base', 'tag' );
		if($tag_base == ''){ $tag_base = 'tag'; }
		$tag_base = $prefix . "/" . $tag_base . "/";
		$author_base = $prefix . "/author/";
		
	} else {
	
		// permalinks not enabled
		$tag_base = "?tag=";
		$author_base = "?author=";
		
	}
	
    // Author links
    $twitter = "/ @([A-Za-z0-9_]+)/is";
    $text = preg_replace ($twitter, " <a href='" . home_url($author_base) . "$1'>@$1</a>", $text);

    // Hashtags
    $hashtag = "/ #([A-Aa-z0-9_-]+)/is";
    $text = preg_replace ($hashtag, " <a href='" . home_url($tag_base) . "$1'>#$1</a>", $text);
	
	// Return post content
    return $text;
	
} 

add_filter( 'the_content', 'twitterify_content', 99, 1 );
add_filter( 'the_excerpt', 'twitterify_content', 99, 1 );