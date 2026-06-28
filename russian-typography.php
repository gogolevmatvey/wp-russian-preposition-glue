<?php
/**
 * Plugin Name: Russian Typography
 * Description: Adds Russian typography spacing rules without changing saved post content.
 * Version: 0.1.0
 * Requires at least: 7.0
 * Requires PHP: 8.0
 * Author: gogolevmatvey
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: russian-typography
 *
 * @package RussianTypography
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'RUSSIAN_TYPOGRAPHY_PLUGIN_FILE' ) ) {
	define( 'RUSSIAN_TYPOGRAPHY_PLUGIN_FILE', __FILE__ );
}

const RUSSIAN_TYPOGRAPHY_SCOPE_OPTION                      = 'russian_typography_scope';
const RUSSIAN_TYPOGRAPHY_SCOPE_ALL                         = 'all';
const RUSSIAN_TYPOGRAPHY_SCOPE_SINGLE                      = 'singular';
const RUSSIAN_TYPOGRAPHY_SKIP_HEADING_SHORT_WORDS_OPTION   = 'russian_typography_skip_heading_short_words';
const RUSSIAN_TYPOGRAPHY_DISABLED_HEADINGS_OPTION          = 'russian_typography_disabled_headings';
const RUSSIAN_TYPOGRAPHY_DISABLE_TITLE_TYPOGRAPHY_OPTION   = 'russian_typography_disable_title_typography';
const RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_OPTION            = 'russian_typography_short_word_mode';
const RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_SOFT              = 'soft';
const RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_FULL              = 'full';
const RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_STANDART          = 'standart';
const RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_OFF               = 'off';
const RUSSIAN_TYPOGRAPHY_SOFT_MAX_NEXT_WORD_LENGTH_OPTION  = 'russian_typography_soft_max_next_word_length';
const RUSSIAN_TYPOGRAPHY_SOFT_MAX_NEXT_WORD_LENGTH_DEFAULT = 10;
const RUSSIAN_TYPOGRAPHY_FULL_MAX_NEXT_WORD_LENGTH_OPTION  = 'russian_typography_full_max_next_word_length';
const RUSSIAN_TYPOGRAPHY_FULL_MAX_NEXT_WORD_LENGTH_DEFAULT = 14;

require_once __DIR__ . '/includes/settings.php';
require_once __DIR__ . '/includes/typography.php';
require_once __DIR__ . '/includes/hooks.php';

register_activation_hook( __FILE__, 'russian_typography_delete_legacy_options' );

russian_typography_register_hooks();
