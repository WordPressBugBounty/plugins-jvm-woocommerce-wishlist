<?php
namespace CIXW_WISHLIST;

class Helper {

	/**
	 * Helper constructor.
	 */
	private function __construct() {
	}
	/**
	 * Add WP ajax action with ease.
	 *
	 * @param string   $action    Ajax action name
	 * @param callable $callback  Ajax callback function
	 * @param bool     $is_nopriv Whether privileged request or not
	 */
	public static function add_ajax( $action, $callback, $is_nopriv = true, $priority = 10 ) {
		if ( empty( $action ) || ! is_callable( $callback ) ) {
			return;
		}

		// Use prefix in case we want to namespace all the ajax requests.
		$prefix = '_';

		add_action(
			'wp_ajax' . $prefix . $action,
			$callback,
			$priority
		);

		if ( $is_nopriv ) {
			add_action(
				'wp_ajax_nopriv' . $prefix . $action,
				$callback,
				$priority
			);
		}
	}
	/**
	 * Replaces text in a given string with specified values.
	 *
	 * @param string $text The original string to be modified.
	 * @param array  $replace An associative array where the keys represent the text to be replaced and the values represent the replacement text.
	 * @param int    $post_id The post ID.
	 * @return string The modified string after replacing the specified text.
	 */
	public static function replace_text( $text, $replace, $post_id = null ) {

		$param_list = array();

		$replace = apply_filters( 'cix_replace_text_list', $param_list, $post_id );

		return str_replace( array_keys( $replace ), array_values( $replace ), $text );
	}

	/**
	 * Retrieves the expiration time of a transient option and echoes the human-readable time difference
	 *  is returned in a human readable format such as "1 hour", "5 mins", "2 days".
	 *
	 * @param string $option_name The name of the transient option.
	 * @return void
	 */
	public static function get_transient_expiration( $option_name ) {
		$expires = (int) get_option( '_transient_timeout_' . $option_name, 0 );

		return human_time_diff( time(), $expires );
		// write function for The difference is returned in a human readable format such as "1 hour", "5 mins", "2 days"
	}

	/**
	 * Creates a new WordPress page with the given title and content.
	 *
	 * @param string $title   The title of the page.
	 * @param string $content The content of the page.
	 *
	 * @return int The ID of the newly created page, or the ID of an existing page with the same title.
	 */
	public static function create_page( $title, $content ) {
		$args = array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'title'          => $title,
			'fields'         => 'ids',
			'posts_per_page' => -1,
		);

		$query = new \WP_Query( $args );

		if ( ! $query->have_posts() ) {
			$page = array(
				'post_title'   => wp_kses_post( $title ),
				'post_content' => wp_kses_post( $content ),
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_author'  => get_current_user_id(),
			);

			$page_id = wp_insert_post( $page );

			return $page_id;
		}

		wp_reset_postdata();

		return $query->posts[0];
	}
}
