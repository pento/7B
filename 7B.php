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
		if ( 'json' === $feed )
			return 'application/json';

		return $type;
	}

	function headLink() {
		echo '<link rel="alternate activities" type="application/activitystream+json" href="' . get_feed_link( 'json' ) . '" />';
	}

	function doJSONFeed() {
		load_template( dirname( __FILE__ ) . '/feed-json.php' );
	}
}

add_action( 'init', array( 'JSONFeed', 'init' ) );

register_activation_hook( __FILE__, array( 'JSONFeed', 'flushRewriteRules' ) );
register_deactivation_hook( __FILE__, array( 'JSONFeed', 'flushRewriteRules' ) );
