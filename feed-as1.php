<?php
/**
 * Activity Streams 1 Feed Template for displaying AS1 Posts feed.
 *
 * @package WordPress
 * @subpackage Feed
 * @since 3.8.0
 */

$json = array();

$json['items'] = array();

header( 'Content-Type: ' . feed_content_type( 'json' ) . '; charset=' . get_option( 'blog_charset' ), true );

/*
 * The JSONP callback function to add to the JSON feed
 *
 * @since 3.8.0
 *
 * @param string $callback The JSONP callback function name
 */
$callback = apply_filters( 'json_feed_callback', get_query_var( 'callback' ) );

if ( ! empty( $callback ) && ! apply_filters( 'json_jsonp_enabled', true ) ) {
	status_header( 400 );
	echo json_encode( array(
						'code'    => 'json_callback_disabled',
						'message' => 'JSONP support is disabled on this site.'
					) );
	exit;
}

if ( preg_match( '/\W/', $callback ) ) {
	status_header( 400 );
	echo json_encode( array(
						'code'    => 'json_callback_invalid',
						'message' => 'The JSONP callback function is invalid.'
					) );
	exit;
}

/*
 * Action triggerd prior to the JSON feed being created and sent to the client
 *
 * @since 3.8.0
 */
do_action( 'json_feed_pre' );

while( have_posts() ) {
	the_post();

	$post_type = get_post_type();
	switch ( $post_type ) {
		case "aside":
		case "status":
		case "quote":
		case "note":
    		$object_type = "note";
    		break;
		default:
    		$object_type = "article";
    		break;
	}

	/*
	 * The object type of the current post in the Activity Streams 1 feed
	 *
	 * @since 3.8.0
	 *
	 * @param string $object_type The current object type
	 * @param string $post_type The current post type
	 */
	$object_type = apply_filters( 'as1_object_type', $object_type, $post_type );

	$item = array(
			'published' => get_post_modified_time( 'Y-m-d\TH:i:s\Z', true ),
			'verb' => 'post',
			'target' => array(
						'id'          => get_feed_link( 'json' ),
						'url'         => get_feed_link( 'json' ),
						'objectType'  => 'blog',
						'displayName' => get_bloginfo( 'name' )
					),
			'object' => array(
						'id'          => get_the_guid(),
						'displayName' => get_the_title(),
						'objectType'  => $object_type,
						'summary'     => get_the_excerpt(),
						'url'         => get_permalink(),
						'content'     => get_the_content()
					),
			'actor' => array(
						'id'          => get_author_posts_url( get_the_author_meta( 'ID' ), get_the_author_meta( 'nicename' ) ),
						'displayName' => get_the_author(),
						'objectType'  => 'person',
						'url'         => get_author_posts_url( get_the_author_meta( 'ID' ), get_the_author_meta( 'nicename' ) ),
						'image'       => array(
											'width'  => 96,
											'height' => 96,
											// TODO: get_avatar_url()
											'url'    => 'http://www.gravatar.com/avatar/' . md5( get_the_author_meta( 'email' ) ) . '.png?s=96'
										)
					)
			);

	/*
	 * The item to be added to the Activity Streams 1 feed
	 *
	 * @since 3.8.0
	 *
	 * @param array $item The Activity Streams 1 item
	 */
	$item = apply_filters( 'as1_feed_item', $item );

	$json['items'][] = $item;
}

/*
 * The array of data to be sent to the user as JSON
 *
 * @since 3.8.0
 *
 * @param array $json The JSON data array
 */
$json = apply_filters( 'as1_feed', $json );

if ( version_compare( phpversion(), '5.3.0', '<' ) ) {
	// json_encode() options added in PHP 5.3
	if ( get_query_var( 'array' ) )
		$json_str = json_encode( $json );
	else
		$json_str = json_encode( (object)$json );
} else {
	$options = 0;
	if ( ! get_query_var( 'array' ) )
		$options |= JSON_FORCE_OBJECT;
	// JSON_PRETTY_PRINT added in PHP 5.4
	if ( get_query_var( 'pretty' ) && version_compare( phpversion(), '5.4.0', '>=' ) )
		$options |= JSON_PRETTY_PRINT;

	/*
	 * Options to be passed to json_encode()
	 *
	 * @since 3.8.0
	 *
	 * @param int $options The current options flags
	 */
	$options = apply_filters( 'json_feed_options', $options );

	$json_str = json_encode( $json, $options );
}

if ( ! empty( $callback ) )
	echo "$callback( $json_str );";
else
	echo $json_str;

/*
 * Action triggerd after the JSON feed has been created and sent to the client
 *
 * @since 3.8.0
 */
do_action( 'json_feed_post' );
