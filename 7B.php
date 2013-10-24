<?php
/*
 * Plugin Name: 7B
 * Plugin URI: http://core.trac.wordpress.org/ticket/25639
 * Description: Add a JSON feed to your WordPress site
 * Author: pento
 * Version: 0.3
 * License: GPL2+
 */

// Don't allow the plugin to be loaded directly
if ( ! function_exists( 'add_action' ) ) {
	echo "Please enable this plugin from your wp-admin.";
	exit;
}

/*
 * JSON Feed management class.
 *
 * This class controls the /feeds/json/ endpoint, and helps with adding new sub-endpoints, or changing the default handler for /feeds/json/

 * @package WordPress
 * @subpackage Feed
 * @since 3.8.0
 */
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

		/*
		 * The array of JSON feeds available
		 *
		 * @since 3.8.0
		 *
		 * @param array $feeds The JSON feeds array
		 */
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
		/*
		 * The default feed type to use for the /feeds/json/ endpoint
		 *
		 * @since 3.8.0
		 *
		 * @param string $feed The default feed type
		 */
		$default = apply_filters( 'json_feed_default', 'as1' );

		switch( $default ) {
			case 'as1':
			default:
				$rel = 'alternate activities';
				$type = 'application/activitystream+json';
		}

		/*
		 * The 'rel' data to be added to the JSON link in all page headers
		 *
		 * @since 3.8.0
		 *
		 * @param string $rel The default 'rel'
		 * @param string $default The feed type
		 */
		$rel = apply_filters( 'json_feed_link_rel', $rel, $default );
		/*
		 * The 'type' data to be added to the JSON link in all page headers
		 *
		 * @since 3.8.0
		 *
		 * @param string $type The default 'type'
		 * @param string $default The feed type
		 */
		$type = apply_filters( 'json_feed_link_type', $type, $default );
		$url = get_feed_link( 'json' );

		echo "<link rel='$rel' type='$type' href='$url' />\n";
	}

	function doJSONFeed() {
		$feed = apply_filters( 'json_feed_default', 'as1' );

		switch( $feed ) {
			case 'as1':
				$this->doAS1Feed();
				break;
			default:
				/*
				 * Load the template for a given feed type
				 *
				 * @since 3.8.0
				 *
				 * @param string $feed The feed type being requested
				 */
				do_action( 'json_feed_load_template', $feed );
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
		load_template( dirname( __FILE__ ) . '/feed-as1.php' );
	}
}

add_action( 'init', array( 'JSONFeed', 'init' ) );

register_activation_hook( __FILE__, array( 'JSONFeed', 'flushRewriteRules' ) );
register_deactivation_hook( __FILE__, array( 'JSONFeed', 'flushRewriteRules' ) );
