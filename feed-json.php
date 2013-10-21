<?php
$json = array();

$json['items'] = array();

header( 'Content-Type: ' . feed_content_type( 'json' ) . '; charset=' . get_option( 'blog_charset' ), true );

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

	$object_type = apply_filters( 'json_object_type', $object_type, $post_type );

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
	
	$item = apply_filters( 'json_feed_item', $item );

	$json['items'][] = $item;
}

$json = apply_filters( 'json_feed', $json );

$options = 0;
if ( ! get_query_var( 'array' ) )
	$options |= JSON_FORCE_OBJECT;
if ( get_query_var( 'pretty' ) )
	$options |= JSON_PRETTY_PRINT;

$options = apply_filters( 'json_feed_options', $options );

$callback = apply_filters( 'json_feed_callback', get_query_var( 'callback' ) );

if ( ! empty( $callback ) )
	echo $callback . '( ' . json_encode( $json, $options ) . ' );';
else
	echo json_encode( $json, $options );

do_action( 'json_feed_post' );
