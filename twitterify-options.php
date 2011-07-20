<?php 

 /* Twitterify options */
 
 $hashtags_link_to_options = array(
	'tags' => 'Tags',
	'search' => 'Search',
	'twitter' => 'Twitter'
 );
  
$options = array(

	array( "name" => __( "General Settings", 'twitterify' ),
		"type" => "section"),
		
	array( "type" => "open"),
	
		array(
			"type" => "checkbox",
			"name" => __( 'Hide hash symbols', 'twitterify' ),
			"id" => "hide_hash",
			"desc" => __( "Hide hashtags in display mode <a href='http://shailan.com/wordpress/plugins/twitterify/help/#hide-hash-symbols' class='helplink' target='_blank'>(?)</a> </span>", 'twitterify' ),
			"std" => "off"
		),
		
		array(
			"type" => "select",
			"name" => __( 'Hashtags link to', 'twitterify' ),
			"id" => "hashtags_link_to",
			"desc" => __( "Choose where to link your hashtags <a href='http://shailan.com/wordpress/plugins/twitterify/help/#hashtags-link-to' class='helplink' target='_blank'>(?)</a> </span>", 'twitterify' ),
			"std" => "",
			"options" => $hashtags_link_to_options
		),
	
	array( "type" => "close")

);