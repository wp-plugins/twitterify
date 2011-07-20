<?php 
/*
Plugin Name: Twitterify
Plugin URI: http://shailan.com/wordpress/plugins/twitterify
Description: Enables use of <strong>autolink</strong>, <strong>#hashtags</strong> and <strong>@author</strong> links on your posts. <strong>Links are not directed to twitter. They provide this functionality on your site.</strong>
Version: 1.2
Author: Matt Say
Author URI: http://shailan.com
*/

global $twitterify;

if(!class_exists('stf_twitterify')){
class stf_twitterify {

	// Constructor
	function stf_twitterify(){
	
		// Get permalink structure
		if ( get_option('permalink_structure') != '' ) { 

			// Permalinks enabled
			$permalink = get_option( 'permalink_structure', '' );
			
			$prefix = '';
			if( strpos( $permalink, '/index.php/') !== false ){
				$prefix = '/index.php';
			}		
			
			$tag_base = get_option( 'tag_base', 'tag' );
			if($tag_base == ''){ $tag_base = 'tag'; }
			$this->tag_base = $prefix . "/" . $tag_base . "/";
			$this->author_base = $prefix . "/author/";
			
		} else {

			// Permalinks not enabled
			$this->tag_base = "?tag=";
			$this->author_base = "?author=";
			
		}
		
		add_filter( 'the_content', array( &$this, 'twitterify_content' ), 99, 1 );
		add_filter( 'the_excerpt', array( &$this, 'twitterify_content' ), 99, 1 );

	}

	function twitterify_content ( $text ){
		global $author_base;

		// Make content clickable
		//$text = make_clickable( $text );
		
		$ret = ' ' . $text;
		$ret = preg_replace("#(^|[\n> ])([\w]+?://[\w]+[^ \"\n\r\t<]*)#ise", "'\\1<a target=\"_blank\" rel=\"nofollow\" href=\"\\2\" >\\2</a>'", $ret);
		$ret = preg_replace("#(^|[\n> ])((www|ftp)\.[^ \"\t\n\r<]*)#ise", "'\\1<a target=\"_blank\" rel=\"nofollow\" href=\"http://\\2\" >\\2</a>'", $ret);
		
		// Remove http://
		$ret = preg_replace( '/(">http:\/\/)(.*?)<\/a>/i', "\">$2</a>", $ret ); 
		
		 // Remove www.
		$ret = preg_replace( '/(">www.)(.*?)<\/a>/i', "\">$2</a>", $ret );
		
		// Author links
		$author_pattern = "/([\n> ])@([A-Za-z0-9_]+)/is";
		$ret = preg_replace_callback ( $author_pattern, array( &$this, 'twitterify_author_callback' ), $ret );

		// Hashtags
		$hashtag_pattern = "{([^//])#([A-Za-z0-9_-]+)}is";
		$ret = preg_replace_callback ( $hashtag_pattern, array( &$this, 'twitterify_tag_callback' ), $ret );
		
		// Return post content
		return $ret;
		
	} 

	// Check if author exists
	function twitterify_author_callback( $matches ){
		global $author_base;
		
		if ( username_exists( $matches[2] ) ){
			return $matches[1] . "<a href='" . home_url( $this->author_base ) . $matches[2] . "'>@" . $matches[2] . "</a>";
		} else {
			return $matches[1] . "<a href='http://twitter.com/" . $matches[2] . "'>@" . $matches[2] . "</a>";
		}
	}

	// Check tags for color codes
	function twitterify_tag_callback( $matches ){
		global $tag_base;
		
		// Check fox hex color codes 
		if( strlen($matches[2]) == 3 || strlen($matches[2]) == 6 ){
			// Check for chars
			if( strlen( preg_replace("/[^0-9A-Fa-f]/", '', $matches[2])) == 6 || strlen( preg_replace("/[^0-9A-Fa-f]/", '', $matches[2])) == 3 ){
				// Surely, hexadecimal value
				return $matches[1] . "#" . $matches[2];
			}
		}
		
		return $matches[1] . " <a href='" . home_url( $this->tag_base ) . "". $matches[2] ."'>#".$matches[2]."</a>";
	}

} } // stf_twitterify

$twitterify = new stf_twitterify();
