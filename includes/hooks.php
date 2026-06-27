<?php
/**
 * WordPress hooks for Russian Typography.
 *
 * @package RussianTypography
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers WordPress hooks used by the plugin.
 */
function russian_typography_register_hooks(): void {
	add_action( 'admin_init', 'russian_typography_register_settings' );
	add_action( 'admin_menu', 'russian_typography_add_settings_page' );

	add_filter(
		'plugin_action_links_' . plugin_basename( RUSSIAN_TYPOGRAPHY_PLUGIN_FILE ),
		'russian_typography_add_plugin_action_links'
	);

	add_filter( 'the_content', 'russian_typography_process_html', 99 );
	add_filter( 'the_excerpt', 'russian_typography_process_html', 99 );
	add_filter( 'comment_text', 'russian_typography_process_comment_html', 99, 2 );
	add_filter( 'the_title', 'russian_typography_process_plain_output', 99, 2 );
}

/**
 * Adds a settings shortcut to the plugin row on the Plugins page.
 *
 * @param array<int|string, string> $links Existing plugin action links.
 */
function russian_typography_add_plugin_action_links( array $links ): array {
	$settings_link = sprintf(
		'<a href="%s">%s</a>',
		esc_url( admin_url( 'options-general.php?page=russian-typography' ) ),
		esc_html__( 'Settings', 'russian-typography' )
	);

	array_unshift( $links, $settings_link );

	return $links;
}

/**
 * Returns true when front-end text output can be typographed.
 *
 * @param string $context   Output context: post, title, comment.
 * @param int    $object_id Related post/comment ID.
 */
function russian_typography_should_process( string $context = 'post', int $object_id = 0 ): bool {
	if ( is_admin() || is_feed() || wp_doing_ajax() ) {
		return false;
	}

	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return false;
	}

	if ( RUSSIAN_TYPOGRAPHY_SCOPE_ALL === russian_typography_get_scope() ) {
		return true;
	}

	return russian_typography_is_singular_post_page_context( $context, $object_id );
}

/**
 * Returns true when the current output belongs to the main single post/page.
 *
 * @param string $context   Output context: post, title, comment.
 * @param int    $object_id Related post/comment ID.
 */
function russian_typography_is_singular_post_page_context( string $context, int $object_id = 0 ): bool {
	if ( ! is_singular( array( 'post', 'page' ) ) ) {
		return false;
	}

	$queried_id = (int) get_queried_object_id();

	if ( $queried_id <= 0 ) {
		return false;
	}

	if ( 'comment' === $context ) {
		if ( $object_id <= 0 ) {
			return true;
		}

		$comment = get_comment( $object_id );

		return $comment instanceof WP_Comment && (int) $comment->comment_post_ID === $queried_id;
	}

	if ( $object_id <= 0 ) {
		$object_id = (int) get_the_ID();
	}

	if ( $object_id <= 0 || $object_id !== $queried_id ) {
		return false;
	}

	return in_array( get_post_type( $object_id ), array( 'post', 'page' ), true );
}

/**
 * Processes HTML output in post-like contexts.
 *
 * @param string $html Rendered HTML.
 */
function russian_typography_process_html( string $html ): string {
	if ( '' === $html || ! russian_typography_should_process( 'post', (int) get_the_ID() ) ) {
		return $html;
	}

	return russian_typography_process_html_nodes( $html );
}

/**
 * Processes plain title-like strings.
 *
 * @param string $text    Plain text.
 * @param int    $post_id Related post ID.
 */
function russian_typography_process_plain_output( string $text, int $post_id = 0 ): string {
	if ( '' === $text || ! russian_typography_should_process( 'title', $post_id ) ) {
		return $text;
	}

	return russian_typography_process_text( $text, ! russian_typography_skip_short_words_in_headings() );
}

/**
 * Processes rendered comment text.
 *
 * @param string $html    Rendered comment HTML.
 * @param mixed  $comment Optional comment object or ID.
 */
function russian_typography_process_comment_html( string $html, mixed $comment = null ): string {
	$comment_id = 0;

	if ( $comment instanceof WP_Comment ) {
		$comment_id = (int) $comment->comment_ID;
	} elseif ( is_numeric( $comment ) ) {
		$comment_id = (int) $comment;
	}

	if ( '' === $html || ! russian_typography_should_process( 'comment', $comment_id ) ) {
		return $html;
	}

	return russian_typography_process_html_nodes( $html );
}
