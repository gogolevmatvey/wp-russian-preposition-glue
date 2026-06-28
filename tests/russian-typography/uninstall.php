<?php
/**
 * Standalone tests for Russian Typography uninstall cleanup.
 *
 * @package RussianTypography
 */

declare(strict_types=1);

$russian_typography_root_dir        = dirname( __DIR__, 2 );
$russian_typography_plugin_dir      = is_file( $russian_typography_root_dir . '/uninstall.php' )
	? $russian_typography_root_dir
	: $russian_typography_root_dir . '/www/wordpress/wp-content/plugins/russian-typography';
$russian_typography_uninstall_file  = $russian_typography_plugin_dir . '/uninstall.php';
$russian_typography_deleted_options = array();
$russian_typography_failures        = array();

define( 'WP_UNINSTALL_PLUGIN', true );

if ( ! function_exists( 'delete_option' ) ) {
	/**
	 * Minimal WordPress delete_option stub for standalone tests.
	 *
	 * @param string $option Option name.
	 */
	function delete_option( string $option ): bool {
		global $russian_typography_deleted_options;

		$russian_typography_deleted_options[] = $option;

		return true;
	}
}

if ( ! is_file( $russian_typography_uninstall_file ) ) {
	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
	fwrite( STDERR, sprintf( "Missing uninstall.php: %s\n", $russian_typography_uninstall_file ) );
	exit( 1 );
}

require $russian_typography_uninstall_file;

$russian_typography_expected_options = array(
	'russian_typography_scope',
	'russian_typography_skip_heading_short_words',
	'russian_typography_disabled_headings',
	'russian_typography_disable_title_typography',
	'russian_typography_short_word_mode',
	'russian_typography_soft_max_next_word_length',
	'russian_typography_full_max_next_word_length',
	'wp_russian_typography_scope',
	'wp_russian_typography_skip_heading_short_words',
	'wp_russian_typography_short_word_mode',
	'wp_russian_typography_soft_max_next_word_length',
	'wp_russian_typography_full_max_next_word_length',
);

// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
$russian_typography_uninstall_text = (string) file_get_contents( $russian_typography_uninstall_file );

if ( false !== strpos( $russian_typography_uninstall_text, 'wp_russian_preposition_glue_scope' ) ) {
	$russian_typography_expected_options = array_merge(
		$russian_typography_expected_options,
		array(
			'wp_russian_preposition_glue_scope',
			'wp_russian_preposition_glue_skip_heading_short_words',
			'wp_russian_preposition_glue_short_word_mode',
			'wp_russian_preposition_glue_soft_max_next_word_length',
		)
	);
}

$russian_typography_missing_options   = array_values(
	array_diff( $russian_typography_expected_options, $russian_typography_deleted_options )
);
$russian_typography_duplicate_options = array_keys(
	array_filter(
		array_count_values( $russian_typography_deleted_options ),
		static function ( int $count ): bool {
			return $count > 1;
		}
	)
);

if ( array() !== $russian_typography_missing_options ) {
	$russian_typography_failures[] = 'Missing deleted options: ' . implode( ', ', $russian_typography_missing_options );
}

if ( array() !== $russian_typography_duplicate_options ) {
	$russian_typography_failures[] = 'Duplicate deleted options: ' . implode( ', ', $russian_typography_duplicate_options );
}

if ( array() !== $russian_typography_failures ) {
	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
	fwrite( STDERR, implode( "\n", $russian_typography_failures ) . "\n" );
	exit( 1 );
}

echo "Russian Typography uninstall tests passed.\n";
