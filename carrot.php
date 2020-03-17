<?php
/**
 * Modify the core 'post' post type to take on the guise of a carrot
 *
 * @package Carrot
 * @author  Barry Ceelen barry.ceelen@10up.com
 * @license GPL2+
 */

namespace Carrot;

// Re-register default 'post' post type.
add_action( 'init', __NAMESPACE__ . '\re_register_post_type' );

// Filter post updated messages.
add_filter( 'post_updated_messages', __NAMESPACE__ . '\post_updated_messages' );

// We don't want carrots to have tags.
add_action( 'init', __NAMESPACE__ . '\remove_taxonomy_support', 999 );

// Remove post related help tabs.
add_action( 'admin_head-post.php', __NAMESPACE__ . '\remove_help_tabs', 10, 3 );
add_action( 'admin_head-edit.php', __NAMESPACE__ . '\remove_help_tabs', 10, 3 );

// Replace the post date with the post name in the TinyMCE link dialog.
add_filter( 'wp_link_query', __NAMESPACE__ . '\filter_link_query_results', 10, 2 );

// Filter the 'At a Glance' widget post type labels by adding a filter to ngettext on the Dashboard.
add_action( 'admin_head-index.php', __NAMESPACE__ . '\add_ngettext_filter_on_dashboard' );

// Change the icon in the 'At a Glance' dashboard widget.
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\at_a_glance_icon_style', 20 );

/**
 * Re-register default 'post' post type.
 *
 * @global $wp_post_types
 * @global $_wp_post_type_features
 * @return void
 */
function re_register_post_type() {

	global $wp_post_types, $_wp_post_type_features;

	// Unregister core 'post' post type.
	if ( isset( $wp_post_types['post'] ) ) {
		unset( $wp_post_types['post'] );
	}

	// Remove 'post' support features, we'll re-add them later on.
	if ( isset( $_wp_post_type_features['post'] ) ) {
		unset( $_wp_post_type_features['post'] );
	}

	$labels = array(
		'name'                     => _x( 'Carrots', 'post type general name', 'carrot' ),
		'singular_name'            => _x( 'Carrot', 'post type singular name', 'carrot' ),
		'add_new'                  => __( 'Add New', 'carrot' ),
		'add_new_item'             => __( 'Add New Carrot', 'carrot' ),
		'edit_item'                => __( 'Edit Carrot', 'carrot' ),
		'new_item'                 => __( 'New Carrot', 'carrot' ),
		'view_item'                => __( 'View Carrot', 'carrot' ),
		'view_items'               => __( 'View Carrots', 'carrot' ),
		'search_items'             => __( 'Search Carrots', 'carrot' ),
		'not_found'                => __( 'No carrots found', 'carrot' ),
		'not_found_in_trash'       => __( 'No carrots found in Trash', 'carrot' ),
		'parent_item_colon'        => null, // If this post were hierarchical, use __( 'Parent Carrot:' ).
		'all_items'                => __( 'All Carrots', 'carrot' ),
		'archives'                 => __( 'Carrot Archives', 'carrot' ),
		'attributes'               => __( 'Carrot Attributes', 'carrot' ),
		'insert_into_item'         => __( 'Insert into carrot', 'carrot' ),
		'uploaded_to_this_item'    => __( 'Uploaded to this carrot', 'carrot' ),
		'featured_image'           => __( 'Carrot Image', 'carrot' ),
		'set_featured_image'       => __( 'Set carrot image', 'carrot' ),
		'remove_featured_image'    => __( 'Remove carrot image', 'carrot' ),
		'use_featured_image'       => __( 'Use as carrot image', 'carrot' ),
		'filter_items_list'        => __( 'Filter carrots list', 'carrot' ),
		'items_list_navigation'    => __( 'Carrots list navigation', 'carrot' ),
		'items_list'               => __( 'Carrots list', 'carrot' ),
		'name_admin_bar'           => _x( 'Carrot', 'add new on admin bar', 'carrot' ),
		'menu_name'                => __( 'Carrots', 'carrot' ),
		'item_published'           => __( 'Carrot published.,', 'carrot' ),
		'item_published_privately' => __( 'Carrot published privately.', 'carrot' ),
		'item_reverted_to_draft'   => __( 'Carrot reverted to draft.', 'carrot' ),
		'item_scheduled'           => __( 'Carrot scheduled.', 'carrot' ),
		'item_updated'             => __( 'Carrot updated.', 'carrot' ),
	);

	$args = array(
		'labels'           => $labels,
		'public'           => true,
		'_builtin'         => true,
		'_edit_link'       => 'post.php?post=%d',
		'capability_type'  => 'post',
		'map_meta_cap'     => true,
		'menu_icon'        => 'dashicons-carrot',
		'menu_position'    => 5,
		'hierarchical'     => false,
		'has_archive'      => true,
		'rewrite'          => array( 'slug' => 'post' ),
		'query_var'        => 'post',
		'delete_with_user' => false,
		'show_in_rest'     => true,
		'supports'         => array(
			'editor',
			'excerpt',
			'revisions',
			'thumbnail',
			'title',
		),
		'taxonomies'       => array(
			'category',
		),
	);

	register_post_type( 'post', $args ); // phpcs:ignore WordPress.NamingConventions.ValidPostTypeSlug.Reserved
}

/**
 * Filter post updated messages.
 *
 * @param array $messages Post updated messages. For defaults @see wp-admin/edit-form-advanced.php.
 * @return array $messages Modified post updated messages.
 */
function post_updated_messages( $messages ) {

	global $post_type_object, $post;

	if ( 'post' !== $post->post_type ) {
		return $messages;
	}

	$permalink                = get_permalink( $post );
	$preview_url              = get_preview_post_link( $post );
	$preview_post_link_html   = '';
	$scheduled_post_link_html = '';
	$view_post_link_html      = '';

	$viewable = is_post_type_viewable( $post_type_object );

	if ( $viewable ) {

		// Preview post link.
		$preview_post_link_html = sprintf(
			' <a target="_blank" href="%1$s">%2$s</a>',
			esc_url( $preview_url ),
			__( 'Preview carrot', 'carrot' )
		);

		// Scheduled post preview link.
		$scheduled_post_link_html = sprintf(
			' <a target="_blank" href="%1$s">%2$s</a>',
			esc_url( $permalink ),
			__( 'Preview carrot', 'carrot' )
		);

		// View post link.
		$view_post_link_html = sprintf(
			' <a href="%1$s">%2$s</a>',
			esc_url( $permalink ),
			__( 'View carrot', 'carrot' )
		);
	}

	$scheduled_date = sprintf(
		/* translators: Publish box date string. 1: Date, 2: Time. */
		__( '%1$s at %2$s' ),
		/* translators: Publish box date format, see https://secure.php.net/date */
		date_i18n( _x( 'M j, Y', 'publish box date format' ), strtotime( $post->post_date ) ),
		/* translators: Publish box time format, see https://secure.php.net/date */
		date_i18n( _x( 'H:i', 'publish box time format' ), strtotime( $post->post_date ) )
	);

	$messages['post'] = array(
		0  => '', // Unused. Messages start at index 1.
		1  => __( 'Carrot updated.' ) . $view_post_link_html,
		2  => __( 'Custom field updated.', 'carrot' ),
		3  => __( 'Custom field deleted.', 'carrot' ),
		4  => __( 'Carrot updated.', 'carrot' ),
		/* phpcs:disable WordPress.Security.NonceVerification.Recommended */
		/* translators: %s: date and time of the revision */
		5  => isset( $_GET['revision'] ) ? sprintf( __( 'Carrot restored to revision from %s', 'carrot' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		/* phpcs:enable WordPress.Security.NonceVerification.Recommended */
		6  => __( 'Carrot published.', 'carrot' ) . $view_post_link_html,
		7  => __( 'Carrot saved.', 'carrot' ),
		8  => __( 'Carrot submitted.', 'carrot' ) . $preview_post_link_html,
		/* translators: %s: Scheduled date for the post. */
		9  => sprintf( __( 'Carrot scheduled for: %s', 'carrot' ), '<strong>' . $scheduled_date . '</strong>' . $scheduled_post_link_html ),
		10 => __( 'Carrot draft updated.', 'carrot' ) . $preview_post_link_html,
	);

	return $messages;
}

/**
 * We don't want carrots to have tags.
 */
function remove_taxonomy_support() {
	unregister_taxonomy_for_object_type( 'post_tag', 'post' );
}

/**
 * Remove default help tabs when viewing edit.php or post.php.
 *
 * The help tabs use references to the 'post' post type so lets
 * remove them to avoid confusion.
 */
function remove_help_tabs() {

	$current_screen = get_current_screen();
	$ids            = array();

	switch ( $current_screen->id ) {
		case 'post':
			$ids = array(
				'customize-display',
				'inserting-media',
				'publish-box',
				'discussion-settings',
				'title-post-editor',
			);
			break;
		case 'edit-post':
			$ids = array(
				'overview',
				'screen-content',
				'action-links',
				'bulk-actions',
			);
			break;
	}

	foreach ( $ids as $id ) {
		if ( $current_screen->get_help_tab( $id ) ) {
			$current_screen->remove_help_tab( $id );
		}
	}
}

/**
 * Replace the post date with the post name in the TinyMCE link dialog.
 *
 * @see 'wp_link_query_args' filter
 *
 * @param array $results {
 *     An associative array of query results.
 *
 *     @type array {
 *         @type int    $ID        Post ID.
 *         @type string $title     The trimmed, escaped post title.
 *         @type string $permalink Post permalink.
 *         @type string $info      A 'Y/m/d'-formatted date for 'post' post type,
 *                                 the 'singular_name' post type label otherwise.
 *     }
 * }
 * @param array $query An array of WP_Query arguments.
 */
function filter_link_query_results( $results, $query ) {

	if ( empty( $results ) ) {
		return $results;
	}

	$get_posts = new \WP_Query();
	$posts     = $get_posts->query( $query );

	if ( empty( $posts ) ) {
		return $posts;
	}

	$posts         = wp_list_pluck( $posts, 'post_type', 'ID' );
	$post_type_obj = get_post_type_object( 'post' );
	$info          = esc_html( $post_type_obj->labels->singular_name );
	$count         = 0;

	foreach ( $results as $result ) {
		if ( 'post' === $posts[ $result['ID'] ] ) {
			$results[ $count ]['info'] = $info;
		}
		$count++;
	}

	return $results;
}

/**
 * Filter the 'At a Glance' post type labels by adding a filter to ngettext on the Dashboard.
 *
 * This hook is added to 'admin_head-index.php' with the intention to not run the
 * filter more than needed.
 */
function add_ngettext_filter_on_dashboard() {
	add_filter( 'ngettext', __NAMESPACE__ . '\filter_ngettext', 10, 5 );
}

/**
 * Filter the '%s Post' and '%s Posts' labels used in the 'At a Glance' dashboard widget.
 *
 * The labels are hardcoded into the widget and unfortunately do not use the values defined in the post type object.
 *
 * @see https://core.trac.wordpress.org/ticket/26066
 *
 * @param string $translation Translated text.
 * @param string $single      The text to be used if the number is singular.
 * @param string $plural      The text to be used if the number is plural.
 * @param string $number      The number to compare against to use either the singular or plural form.
 * @param string $domain      Text domain. Unique identifier for retrieving translated strings.
 * @return string Original or modified translation.
 */
function filter_ngettext( $translation, $single, $plural, $number, $domain ) {

	if ( ! is_admin() || 'default' !== $domain || '%s Post' !== $single || '%s Posts' !== $plural ) {
		return $translation;
	}

	/* translators: number of carrots */
	return _n( '%s Carrot', '%s Carrots', $number, 'carrot' );
}

/**
 * Change the icon in the 'At a Glance' dashboard widget
 */
function at_a_glance_icon_style() {

	$current_screen = get_current_screen();

	if ( 'dashboard' === $current_screen->id ) {

		wp_add_inline_style(
			'wp-admin',
			'#dashboard_right_now .post-count a:before, #dashboard_right_now .post-count span:before {content: "\f511";}'
		);
	}
}
