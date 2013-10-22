<?php
/*
 * Plugin Name: 7B
 * Plugin URI: http://core.trac.wordpress.org/ticket/25639
 * Description: Add a JSON feed to your WordPress site
 * Author: pento
 * Version: 0.2
 * License: GPL2+
 */

// Don't allow the plugin to be loaded directly
if ( ! function_exists( 'add_action' ) ) {
	echo "Please enable this plugin from your wp-admin.";
	exit;
}

class JSONFeed {
	private $feeds = array( 'as1' );

	static function init() {
		static $instance;
		
		if ( empty( $instance ) )
			$instance = new JSONFeed();

		return $instance;
	}

	function __construct() {
		add_action( 'do_feed_json', array( $this, 'doJSONFeed' ) );
		add_action( 'wp_head',      array( $this, 'headLink' ) );

		add_filter( 'query_vars',   array( $this, 'queryVars' ) );
		add_filter( 'feed_content_type', array( $this, 'contentType' ), 10, 2 );

		add_feed( 'json', array( $this, 'doJSONFeed' ) );

		$this->feeds = apply_Filters( 'json_feeds', $this->feeds );
		foreach ( $this->feeds as $feed ) {
			add_action( "do_feed_json/$feed",  array( $this, 'doFeed' ) );
			add_feed( "json/$feed", array( $this, 'doFeed' ) );
		}
	}

	static function flushRewriteRules() {
		global $wp_rewrite;
		$wp_rewrite->flush_rules();
	}

	function queryVars( $vars ) {
		$vars[] = 'array';
		$vars[] = 'callback';
		$vars[] = 'pretty';

		return $vars;
	}

	function contentType( $type, $feed ) {
		$feeds = $this->feeds;
		$feeds[] = 'json';

		if ( in_array( $feed, $feeds ) )
			return 'application/json';

		return $type;
	}

	function headLink() {
		$default = apply_filters( 'json_feed_default', 'as1' );

		switch( $default ) {
			case 'as1':
			default:
				$rel = 'alternate activities';
				$type = 'application/activitystream+json';
		}

		$rel = apply_filters( 'json_feed_link_rel', $rel, $default );
		$type = apply_filters( 'json_feed_link_type', $type, $default );
		$url = get_feed_link( 'json' );

		echo "<link rel='$rel' type='$type' href='$url' />\n";
	}

	function doJSONFeed() {
		$default = apply_filters( 'json_feed_default', 'as1' );

		switch( $default ) {
			case 'as1':
				$this->doAS1Feed();
				break;
			default:
				do_action( 'json_feed_load_template', $default );
				break;
		}
	}

	function doFeed() {
		$filter = current_filter();
		$pieces = explode( '/', $filter );
		$feed = $pieces[1];

		switch( $feed ) {
			case 'as1':
				$this->doAS1Feed();
				break;
			default:
				do_action( 'json_feed_load_template', $default );
				break;
		}
	}

	function doAS1Feed() {
		load_template( dirname( __FILE__ ) . '/feed-json.php' );
	}
}

add_action( 'init', array( 'JSONFeed', 'init' ) );

register_activation_hook( __FILE__, array( 'JSONFeed', 'flushRewriteRules' ) );
register_deactivation_hook( __FILE__, array( 'JSONFeed', 'flushRewriteRules' ) );
