<?php
/**
 * Uninstall cleanup for Russian Typography.
 *
 * @package RussianTypography
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$russian_typography_options = array(
	'russian_typography_scope',
	'russian_typography_skip_heading_short_words',
	'russian_typography_short_word_mode',
	'russian_typography_soft_max_next_word_length',
	'russian_typography_full_max_next_word_length',
	'wp_russian_typography_scope',
	'wp_russian_typography_skip_heading_short_words',
	'wp_russian_typography_short_word_mode',
	'wp_russian_typography_soft_max_next_word_length',
	'wp_russian_typography_full_max_next_word_length',
	'wp_russian_preposition_glue_scope',
	'wp_russian_preposition_glue_skip_heading_short_words',
	'wp_russian_preposition_glue_short_word_mode',
	'wp_russian_preposition_glue_soft_max_next_word_length',
);

foreach ( $russian_typography_options as $russian_typography_option ) {
	delete_option( $russian_typography_option );
}
