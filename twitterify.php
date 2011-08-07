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
	
		$this->version = "1.2";
		$this->settings_key = "stf_twitterify";
		$this->options_page = "twitterify";
	
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
		
		// Register filters
		add_filter( 'the_content', array( &$this, 'twitterify_content' ), 99, 1 );
		add_filter( 'the_excerpt', array( &$this, 'twitterify_content' ), 99, 1 );
		
		// Include options
		require_once("twitterify-options.php");
		$this->options = $options;
		$this->settings = $this->get_plugin_settings();
		
		add_action('admin_menu', array( &$this, 'admin_menu') );

	}
	
	function get_plugin_settings(){
		$settings = get_option( $this->settings_key );		
		
		if(FALSE === $settings){ // Options doesn't exist, install standard settings
			// Create settings array
			$settings = array();
			// Set default values
			foreach($this->options as $option){
				if( array_key_exists( 'id', $option ) )
					$settings[ $option['id'] ] = $option['std'];
			}
			
			$settings['version'] = $this->version;
			// Save the settings
			update_option( $this->settings_key, $settings );
		} else { // Options exist, update if necessary
			
			if( !empty( $settings['version'] ) ){ $ver = $settings['version']; } 
			else { $ver = ''; }
			
			if($ver != $this->version){ // Update needed
			
				// Add missing keys
				foreach($this->options as $option){
					if( array_key_exists ( 'id' , $option ) && !array_key_exists ( $option['id'] ,$settings ) ){
						$settings[ $option['id'] ] = $option['std'];
					}
				}
				
				update_option( $this->settings_key, $settings );
				
				return $settings; 
			} else { 
			
				// Everythings gonna be alright. Return.
				return $settings;
			} 
		}		
	}
	
	function update_plugin_setting( $key, $value ){
		$settings = $this->get_plugin_settings();
		$settings[$key] = $value;
		update_option( $this->settings_key, $settings );
	}
	
	function get_plugin_setting( $key, $default = '' ) {
		$settings = $this->get_plugin_settings();
		if( array_key_exists($key, $settings) ){
			return $settings[$key];
		} else {
			return $default;
		}
		
		return FALSE;
	}
	
	function admin_menu(){

		if ( @$_GET['page'] == $this->options_page ) {		
			
			if ( @$_REQUEST['action'] && 'save' == $_REQUEST['action'] ) {
			
				// Save settings
				// Get settings array
				$settings = $this->get_plugin_settings();
				
				// Set updated values
				foreach($this->options as $option){					
					if( $option['type'] == 'checkbox' && empty( $_REQUEST[ $option['id'] ] ) ) {
						$settings[ $option['id'] ] = 'off';
					} else {
						$settings[ $option['id'] ] = $_REQUEST[ $option['id'] ]; 
					}
				}
				
				// Save the settings
				update_option( $this->settings_key, $settings );
				header("Location: admin.php?page=" . $this->options_page . "&saved=true&message=1");
				die;
			} else if( @$_REQUEST['action'] && 'reset' == $_REQUEST['action'] ) {
				// Remove settings key
				delete_option( $this->settings_key );
				header("Location: admin.php?page=" . $this->options_page . "&reset=true&message=2");
				die;
			}
			
			// Enqueue scripts & styles
			wp_enqueue_script( "jquery" );
			wp_enqueue_script( "tweetable", plugins_url( '/scripts/jquery.tweetable.js' , __FILE__ ), 'jquery' );
			wp_enqueue_style( "twitterify-admin", plugins_url( '/twitterify.css' , __FILE__ ), false, "1.0", "all");	
			wp_enqueue_style( "google-droid-sans", "http://fonts.googleapis.com/css?family=Droid+Sans:regular,bold&v1", false, "1.0", "all");
			
		}

		$page = add_options_page( __('Twitterify Options', 'twitterify') , __('Twitterify', 'twitterify'), 'edit_themes', $this->options_page, array( &$this, 'options_page') );
	}

	function options_page(){
		global $options, $current;

		$title = "Twitterify Options";
		
		$options = $this->options;	
		$current = $this->get_plugin_settings();
		
		$messages = array( 
			"1" => __("Twitterify settings saved.", "twitterify"),
			"2" => __("Twitterify settings reset.", "twitterify")
		);
		
		$navigation = '<div id="stf_nav"><a href="http://shailan.com/wordpress/plugins/twitterify/">Plugin page</a> | <a href="http://shailan.com/wordpress/plugins/twitterify/help/">Usage</a> | <a href="http://shailan.com/donate/">Donate</a> | <a href="http://shailan.com/wordpress/">Get more widgets..</a></div>
		
	<div class="stf_share">
		<div class="share-label">
			Like this plugin? 
		</div>
		<div class="share-button tweet">
			<a href="http://twitter.com/share" class="twitter-share-button" data-url="http://shailan.com/wordpress/plugins/adsense-widget/" data-text="I am using #twitterify to easily create tags & links on my #wordpress #blog, Check this out!" data-count="horizontal" data-via="shailancom">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
		</div>
		<div class="share-button facebook">
			<script src="http://connect.facebook.net/en_US/all.js#xfbml=1"></script>
			<fb:like href="http://shailan.com/wordpress/plugins/twitterify/" ref="plugin_options" show_faces="false" width="300" font="segoe ui"></fb:like>
		</div>
	</div>
		
		';
		
		$footer_text = '<em><a href="http://shailan.com/wordpress/plugins/twitterify/">Twitterify</a> by <a href="http://shailan.com/">SHAILAN </a></em>';
		
		include_once( dirname(__FILE__) . "/inc/stf-page-options.php" );

	}

	function twitterify_content ( $content ){
		
		$content = preg_replace_callback ( "#(.*?)(\<([a-z]+)[^\>]*\>([^\<]*?)\<\/(\\3)[^\>]*\>)(.*?)#is", array( &$this, 'twitterify_filter_codes' ), $content );
		return $content;
	}
	
	function twitterify_filter_codes( $matches = array() ){
		
		//return print_r($matches, false);
		
		if( $matches[3] != 'code' && $matches[3] != 'pre' ){
			return $this->twitterify_text( $matches[0] );
		} else {
			return $matches[0];
		}
		
	}
	
	function twitterify_text( $text ){
		$ret = ' ' . $text;
		$ret = preg_replace("#(^|[\n> ])([\w]+?://[\w]+[^ \"\n\r\t<]*)#ise", "'\\1<a target=\"_blank\" rel=\"nofollow\" href=\"\\2\" >\\2</a>'", $ret);
		$ret = preg_replace("#(^|[\n> ])((www|ftp)\.[^ \"\t\n\r<]*)#ise", "'\\1<a target=\"_blank\" rel=\"nofollow\" href=\"http://\\2\" >\\2</a>'", $ret);
		
		// .com/test
		$ret = preg_replace("#(^|[\n> ])([a-zA-Z0-9-]+\.[a-zA-Z.]{2,5})(\/[a-zA-Z0-9-]+)*#ise", "'\\1<a target=\"_blank\" rel=\"nofollow\" href=\"http://\\2\\3\" >\\2\\3</a>'", $ret);
		
		// Remove http://
		$ret = preg_replace( '/(>http:\/\/)(.*?)<\/a>/i', ">$2</a>", $ret ); 
		
		 // Remove www.
		$ret = preg_replace( '/(>www.)(.*?)<\/a>/i', ">$2</a>", $ret );
		
		// Author links
		$author_pattern = "/([\n> ])@([A-Za-z0-9_]+)/is";
		$ret = preg_replace_callback ( $author_pattern, array( &$this, 'twitterify_author_callback' ), $ret );

		// Hashtags
		$hashtag_pattern = "{([^&//])#([A-Za-z0-9_-]+)}is";
		$ret = preg_replace_callback ( $hashtag_pattern, array( &$this, 'twitterify_tag_callback' ), $ret );
		
		// Return post content
		return substr( $ret, 1 );
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
		
		$hash = '#';
		if( 'on' == $this->get_plugin_setting('hide_hash') )
			$hash = '';
			
		$hashtags_link_to = $this->get_plugin_setting( 'hashtags_link_to' );
		
		if( 'twitter' == $hashtags_link_to ){
			$hash_base = "http://twitter.com/search?q=%23";
		} elseif ( 'search' == $hashtags_link_to ){
			$hash_base = home_url( '?s=' );
		} else {
			$hash_base = home_url( $this->tag_base );
		}
		
		return $matches[1] . " <a href='" . $hash_base . "". $matches[2] ."'>" . $hash . $matches[2] . "</a>";
	}

} } // stf_twitterify

$twitterify = new stf_twitterify();
