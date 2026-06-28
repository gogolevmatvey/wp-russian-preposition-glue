<?php
/**
 * Standalone tests for Russian Typography text transformations.
 *
 * @package RussianTypography
 */

declare(strict_types=1);

$russian_typography_root_dir   = dirname( __DIR__, 2 );
$russian_typography_plugin_dir = is_file( $russian_typography_root_dir . '/russian-typography.php' )
	? $russian_typography_root_dir
	: $russian_typography_root_dir . '/www/wordpress/wp-content/plugins/russian-typography';
$russian_typography_wp_dir     = is_dir( $russian_typography_root_dir . '/www/wordpress' )
	? $russian_typography_root_dir . '/www/wordpress/'
	: $russian_typography_root_dir . '/';

define( 'ABSPATH', $russian_typography_wp_dir );

define( 'RUSSIAN_TYPOGRAPHY_SCOPE_OPTION', 'russian_typography_scope' );
define( 'RUSSIAN_TYPOGRAPHY_SCOPE_ALL', 'all' );
define( 'RUSSIAN_TYPOGRAPHY_SCOPE_SINGLE', 'singular' );
define( 'RUSSIAN_TYPOGRAPHY_SKIP_HEADING_SHORT_WORDS_OPTION', 'russian_typography_skip_heading_short_words' );
define( 'RUSSIAN_TYPOGRAPHY_DISABLED_HEADINGS_OPTION', 'russian_typography_disabled_headings' );
define( 'RUSSIAN_TYPOGRAPHY_DISABLE_TITLE_TYPOGRAPHY_OPTION', 'russian_typography_disable_title_typography' );
define( 'RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_OPTION', 'russian_typography_short_word_mode' );
define( 'RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_SOFT', 'soft' );
define( 'RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_FULL', 'full' );
define( 'RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_STANDART', 'standart' );
define( 'RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_OFF', 'off' );
define( 'RUSSIAN_TYPOGRAPHY_SOFT_MAX_NEXT_WORD_LENGTH_OPTION', 'russian_typography_soft_max_next_word_length' );
define( 'RUSSIAN_TYPOGRAPHY_SOFT_MAX_NEXT_WORD_LENGTH_DEFAULT', 10 );
define( 'RUSSIAN_TYPOGRAPHY_FULL_MAX_NEXT_WORD_LENGTH_OPTION', 'russian_typography_full_max_next_word_length' );
define( 'RUSSIAN_TYPOGRAPHY_FULL_MAX_NEXT_WORD_LENGTH_DEFAULT', 14 );

$russian_typography_test_options = array();

if ( ! function_exists( 'apply_filters' ) ) {
	/**
	 * Minimal WordPress apply_filters stub for standalone tests.
	 *
	 * @param string $hook Hook name.
	 * @param mixed  $value Filtered value.
	 * @param mixed  ...$args Additional arguments.
	 */
	function apply_filters( string $hook, mixed $value, mixed ...$args ): mixed {
		unset( $hook, $args );

		return $value;
	}
}

if ( ! function_exists( 'get_option' ) ) {
	/**
	 * Minimal WordPress get_option stub for standalone tests.
	 *
	 * @param string $option Option name.
	 * @param mixed  $fallback Fallback value.
	 */
	function get_option( string $option, mixed $fallback = false ): mixed {
		global $russian_typography_test_options;

		if ( array_key_exists( $option, $russian_typography_test_options ) ) {
			return $russian_typography_test_options[ $option ];
		}

		return $fallback;
	}
}

if ( ! function_exists( 'sanitize_key' ) ) {
	/**
	 * Minimal WordPress sanitize_key stub for standalone tests.
	 *
	 * @param mixed $key Raw key.
	 */
	function sanitize_key( mixed $key ): string {
		return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $key ) ) ?? '';
	}
}

if ( ! function_exists( 'absint' ) ) {
	/**
	 * Minimal WordPress absint stub for standalone tests.
	 *
	 * @param mixed $maybeint Raw integer-like value.
	 */
	function absint( mixed $maybeint ): int {
		return abs( (int) $maybeint );
	}
}

if ( ! function_exists( 'is_admin' ) ) {
	/**
	 * Minimal WordPress is_admin stub for standalone tests.
	 */
	function is_admin(): bool {
		return false;
	}
}

if ( ! function_exists( 'is_feed' ) ) {
	/**
	 * Minimal WordPress is_feed stub for standalone tests.
	 */
	function is_feed(): bool {
		return false;
	}
}

if ( ! function_exists( 'wp_doing_ajax' ) ) {
	/**
	 * Minimal WordPress wp_doing_ajax stub for standalone tests.
	 */
	function wp_doing_ajax(): bool {
		return false;
	}
}

require_once $russian_typography_plugin_dir . '/includes/settings.php';
require_once $russian_typography_plugin_dir . '/includes/typography.php';
require_once $russian_typography_plugin_dir . '/includes/hooks.php';

$russian_typography_failures = array();
$russian_typography_nbsp     = "\xC2\xA0";

/**
 * Formats string values for assertion failure output.
 *
 * @param string $value Value to format.
 */
function russian_typography_format_test_value( string $value ): string {
	return '"' . addcslashes( $value, "\0..\37\\\"" ) . '"';
}

/**
 * Records a failed assertion.
 *
 * @param string $label Expected behavior label.
 * @param string $expected Expected value.
 * @param string $actual Actual value.
 */
function russian_typography_assert_same( string $label, string $expected, string $actual ): void {
	global $russian_typography_failures;

	if ( $expected === $actual ) {
		return;
	}

	$russian_typography_failures[] = sprintf(
		"%s\nExpected: %s\nActual:   %s",
		$label,
		russian_typography_format_test_value( $expected ),
		russian_typography_format_test_value( $actual )
	);
}

/**
 * Sets option values returned by the standalone get_option stub.
 *
 * @param array<string, mixed> $options Option map.
 */
function russian_typography_set_test_options( array $options ): void {
	global $russian_typography_test_options;

	$russian_typography_test_options = $options;
}

russian_typography_set_test_options( array() );

russian_typography_assert_same(
	'Default settings must disable typography for h1-h3 headings.',
	'h1,h2,h3',
	implode( ',', russian_typography_get_disabled_headings() )
);

russian_typography_assert_same(
	'Default settings must disable typography for the_title output.',
	'1',
	russian_typography_disable_title_typography() ? '1' : '0'
);

russian_typography_set_test_options(
	array(
		RUSSIAN_TYPOGRAPHY_SKIP_HEADING_SHORT_WORDS_OPTION => '0',
	)
);

russian_typography_assert_same(
	'Legacy disabled heading setting off must keep all heading levels enabled until new settings are saved.',
	'',
	implode( ',', russian_typography_get_disabled_headings() )
);

russian_typography_assert_same(
	'Legacy disabled heading setting off must keep the_title typography enabled until new settings are saved.',
	'0',
	russian_typography_disable_title_typography() ? '1' : '0'
);

russian_typography_set_test_options(
	array(
		RUSSIAN_TYPOGRAPHY_DISABLED_HEADINGS_OPTION => array( 'h2', 'h2', 'unknown', '' ),
		RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_OPTION   => RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_STANDART,
	)
);

$russian_typography_processed_history_date = 'в' . $russian_typography_nbsp . '480 году до' . $russian_typography_nbsp . 'н.' . $russian_typography_nbsp . 'э.';

russian_typography_assert_same(
	'HTML processing must fully skip disabled heading levels and process enabled headings normally.',
	'<h2>в 480 году до н. э.</h2><h3>' . $russian_typography_processed_history_date . '</h3><p>' . $russian_typography_processed_history_date . '</p>',
	russian_typography_process_html_nodes( '<h2>в 480 году до н. э.</h2><h3>в 480 году до н. э.</h3><p>в 480 году до н. э.</p>' )
);

russian_typography_set_test_options(
	array(
		RUSSIAN_TYPOGRAPHY_SCOPE_OPTION                    => RUSSIAN_TYPOGRAPHY_SCOPE_ALL,
		RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_OPTION          => RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_STANDART,
		RUSSIAN_TYPOGRAPHY_DISABLE_TITLE_TYPOGRAPHY_OPTION => '1',
	)
);

russian_typography_assert_same(
	'Title processing must return the original text when title typography is disabled.',
	'в 480 году до н. э.',
	russian_typography_process_plain_output( 'в 480 году до н. э.', 1 )
);

russian_typography_set_test_options(
	array(
		RUSSIAN_TYPOGRAPHY_SCOPE_OPTION                    => RUSSIAN_TYPOGRAPHY_SCOPE_ALL,
		RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_OPTION          => RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_STANDART,
		RUSSIAN_TYPOGRAPHY_DISABLE_TITLE_TYPOGRAPHY_OPTION => '0',
	)
);

russian_typography_assert_same(
	'Title processing must apply the active typography mode when title typography is enabled.',
	$russian_typography_processed_history_date,
	russian_typography_process_plain_output( 'в 480 году до н. э.', 1 )
);

russian_typography_assert_same(
	'Full mode must glue semantic prepositions and conjunctions.',
	'для' . $russian_typography_nbsp . 'приготовления, что' . $russian_typography_nbsp . 'командование, от' . $russian_typography_nbsp . 'необходимости',
	russian_typography_process_text( 'для приготовления, что командование, от необходимости', RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_FULL )
);

russian_typography_assert_same(
	'Full mode must glue short pronouns when the next word is not too long.',
	'он' . $russian_typography_nbsp . 'ушёл, они' . $russian_typography_nbsp . 'видели, вы' . $russian_typography_nbsp . 'работаете',
	russian_typography_process_text( 'он ушёл, они видели, вы работаете', RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_FULL )
);

russian_typography_assert_same(
	'Full mode must leave long next words breakable.',
	'он преимущественно, и могущественными',
	russian_typography_process_text( 'он преимущественно, и могущественными', RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_FULL )
);

russian_typography_assert_same(
	'Full mode must not glue the short-word fragment after an ASCII hyphen.',
	'они' . $russian_typography_nbsp . 'какую-то симпатию',
	russian_typography_process_text( 'они какую-то симпатию', RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_FULL )
);

russian_typography_assert_same(
	'Full mode must not glue after a Unicode hyphen.',
	'кто‐то пришёл',
	russian_typography_process_text( 'кто‐то пришёл', RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_FULL )
);

russian_typography_assert_same(
	'Full mode must not glue after a non-breaking hyphen.',
	'что‑то важное',
	russian_typography_process_text( 'что‑то важное', RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_FULL )
);

russian_typography_assert_same(
	'Full mode must still glue after a colon.',
	'Автор сказал: то' . $russian_typography_nbsp . 'слово важно',
	russian_typography_process_text( 'Автор сказал: то слово важно', RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_FULL )
);

russian_typography_assert_same(
	'Full mode must still glue after an em dash.',
	'Автор сказал — то' . $russian_typography_nbsp . 'слово важно',
	russian_typography_process_text( 'Автор сказал — то слово важно', RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_FULL )
);

russian_typography_assert_same(
	'Standart mode must leave selected semantic full-mode phrases breakable like Lebedev.',
	'для приготовления, что командование, они какую-то симпатию',
	russian_typography_process_text( 'для приготовления, что командование, они какую-то симпатию', RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_STANDART )
);

russian_typography_assert_same(
	'Standart mode must glue selected short words without the full-mode next-word length limit.',
	'от' . $russian_typography_nbsp . 'необходимости, он' . $russian_typography_nbsp . 'преимущественно, и' . $russian_typography_nbsp . 'могущественными',
	russian_typography_process_text( 'от необходимости, он преимущественно, и могущественными', RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_STANDART )
);

russian_typography_assert_same(
	'Standart mode must glue postpositive particles.',
	'как' . $russian_typography_nbsp . 'же, сделал' . $russian_typography_nbsp . 'бы, знал' . $russian_typography_nbsp . 'ли, кто' . $russian_typography_nbsp . 'б, что' . $russian_typography_nbsp . 'бы',
	russian_typography_process_text( 'как же, сделал бы, знал ли, кто б, что бы', RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_STANDART )
);

russian_typography_assert_same(
	'Standart mode must glue short-word chains like Lebedev.',
	'да' . $russian_typography_nbsp . 'и' . $russian_typography_nbsp . 'материковая; из' . $russian_typography_nbsp . 'их' . $russian_typography_nbsp . 'союзников; но' . $russian_typography_nbsp . 'не' . $russian_typography_nbsp . 'христианским; не' . $russian_typography_nbsp . 'в' . $russian_typography_nbsp . 'монастырских; на' . $russian_typography_nbsp . 'их' . $russian_typography_nbsp . 'масштабы',
	russian_typography_process_text( 'да и материковая; из их союзников; но не христианским; не в монастырских; на их масштабы', RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_STANDART )
);

russian_typography_assert_same(
	'Standart mode must glue selected object pronouns backward only when they are not followed by another word.',
	'увидел' . $russian_typography_nbsp . 'их; увидел' . $russian_typography_nbsp . 'её; увидел' . $russian_typography_nbsp . 'им; помог' . $russian_typography_nbsp . 'ей; написал ему; его книга; их' . $russian_typography_nbsp . 'книга; помог ей' . $russian_typography_nbsp . 'быстро',
	russian_typography_process_text( 'увидел их; увидел её; увидел им; помог ей; написал ему; его книга; их книга; помог ей быстро', RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_STANDART )
);

russian_typography_assert_same(
	'Standart mode must glue terminal short conjunctions backward.',
	'в' . $russian_typography_nbsp . 'соперников' . $russian_typography_nbsp . 'и,; упражнялись' . $russian_typography_nbsp . 'и,; были' . $russian_typography_nbsp . 'те,',
	russian_typography_process_text( 'в соперников и,; упражнялись и,; были те,', RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_STANDART )
);

russian_typography_assert_same(
	'Standart mode must keep hyphenated words breakable after the hyphen.',
	'что-то важное, кто-то пришёл, какую-то симпатию',
	russian_typography_process_text( 'что-то важное, кто-то пришёл, какую-то симпатию', RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_STANDART )
);

russian_typography_assert_same(
	'Standart mode must follow Lebedev-like spacing for years and historical abbreviations.',
	'в' . $russian_typography_nbsp . '480 году до' . $russian_typography_nbsp . 'н.' . $russian_typography_nbsp . 'э.; 480' . $russian_typography_nbsp . 'год, 480' . $russian_typography_nbsp . 'года, 480 году',
	russian_typography_process_text( 'в 480 году до н. э.; 480 год, 480 года, 480 году', RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_STANDART )
);

russian_typography_assert_same(
	'Standart mode must glue centuries, abbreviated years, units, percentages, and thousands.',
	'V' . $russian_typography_nbsp . 'век, V' . $russian_typography_nbsp . 'веке, 20' . $russian_typography_nbsp . 'век, 20' . $russian_typography_nbsp . 'века; 421' . $russian_typography_nbsp . 'г.' . $russian_typography_nbsp . 'до' . $russian_typography_nbsp . 'н.' . $russian_typography_nbsp . 'э.; 1181–1260' . $russian_typography_nbsp . 'гг.' . $russian_typography_nbsp . 'н.' . $russian_typography_nbsp . 'э.; 5' . $russian_typography_nbsp . 'км, 3' . $russian_typography_nbsp . '%, 65' . $russian_typography_nbsp . '536',
	russian_typography_process_text( 'V век, V веке, 20 век, 20 века; 421 г. до н. э.; 1181–1260 гг. н. э.; 5 км, 3 %, 65 536', RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_STANDART )
);

russian_typography_assert_same(
	'Standart mode must glue additional Lebedev-like historical and numeric forms.',
	'ок.' . $russian_typography_nbsp . '421' . $russian_typography_nbsp . 'гг.' . $russian_typography_nbsp . 'н.' . $russian_typography_nbsp . 'э.; ок. 1181–1260' . $russian_typography_nbsp . 'гг.' . $russian_typography_nbsp . 'н.' . $russian_typography_nbsp . 'э.; века' . $russian_typography_nbsp . 'н.' . $russian_typography_nbsp . 'э.' . $russian_typography_nbsp . 'в' . $russian_typography_nbsp . 'аббатстве; веку' . $russian_typography_nbsp . 'н.' . $russian_typography_nbsp . 'э.; до' . $russian_typography_nbsp . 'н.' . $russian_typography_nbsp . 'э.' . $russian_typography_nbsp . 'в' . $russian_typography_nbsp . 'благодарность; 16' . $russian_typography_nbsp . 'квадратных; 60' . $russian_typography_nbsp . 'процентов; О' . $russian_typography_nbsp . 'девственности',
	russian_typography_process_text( 'ок. 421 гг. н. э.; ок. 1181–1260 гг. н. э.; века н. э. в аббатстве; веку н. э.; до н. э. в благодарность; 16 квадратных; 60 процентов; О девственности', RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_STANDART )
);

russian_typography_assert_same(
	'Standart mode must not glue a historical abbreviation to a capitalized next sentence.',
	'до' . $russian_typography_nbsp . 'н.' . $russian_typography_nbsp . 'э. В' . $russian_typography_nbsp . 'следующем',
	russian_typography_process_text( 'до н. э. В следующем', RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_STANDART )
);

russian_typography_assert_same(
	'Standart mode must glue full year words only before punctuation.',
	'в' . $russian_typography_nbsp . '1066' . $russian_typography_nbsp . 'году; в' . $russian_typography_nbsp . '1066' . $russian_typography_nbsp . 'году.; 1945' . $russian_typography_nbsp . 'года,; в' . $russian_typography_nbsp . '1051–52' . $russian_typography_nbsp . 'годах,',
	russian_typography_process_text( 'в 1066 году; в 1066 году.; 1945 года,; в 1051–52 годах,', RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_STANDART )
);

russian_typography_assert_same(
	'Standart mode must glue selected number-word contexts like Lebedev.',
	'1000' . $russian_typography_nbsp . 'человек; 1000' . $russian_typography_nbsp . 'человек,; 400' . $russian_typography_nbsp . 'воинов,; 24' . $russian_typography_nbsp . 'таких; 15' . $russian_typography_nbsp . 'неожиданных; 6' . $russian_typography_nbsp . 'августа; 55' . $russian_typography_nbsp . 'лет; 26' . $russian_typography_nbsp . 'км²',
	russian_typography_process_text( '1000 человек; 1000 человек,; 400 воинов,; 24 таких; 15 неожиданных; 6 августа; 55 лет; 26 км²', RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_STANDART )
);

russian_typography_assert_same(
	'Standart mode must not over-glue number-word contexts that Lebedev leaves breakable.',
	'150 городов-государств.; и' . $russian_typography_nbsp . '172 союзных; 172 союзных',
	russian_typography_process_text( '150 городов-государств.; и 172 союзных; 172 союзных', RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_STANDART )
);

russian_typography_assert_same(
	'Standart mode must glue short roman numerals like Lebedev, but leave longer roman numerals breakable.',
	'II' . $russian_typography_nbsp . 'Македонского | II' . $russian_typography_nbsp . 'одобрил | III Македонского | XII века | XII' . $russian_typography_nbsp . 'века, | Генрихом' . $russian_typography_nbsp . 'I, | Генрихом III,',
	russian_typography_process_text( 'II Македонского | II одобрил | III Македонского | XII века | XII века, | Генрихом I, | Генрихом III,', RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_STANDART )
);

russian_typography_assert_same(
	'Standart mode must handle roman centuries after beginning and middle words like Lebedev.',
	'в' . $russian_typography_nbsp . 'середине' . $russian_typography_nbsp . 'IV века | В' . $russian_typography_nbsp . 'начале' . $russian_typography_nbsp . 'V века | в' . $russian_typography_nbsp . 'конце' . $russian_typography_nbsp . 'XI века | в' . $russian_typography_nbsp . 'середине XII века | в' . $russian_typography_nbsp . 'конце XII века | с' . $russian_typography_nbsp . 'IV века' . $russian_typography_nbsp . 'н.' . $russian_typography_nbsp . 'э. | в' . $russian_typography_nbsp . 'начале XI' . $russian_typography_nbsp . 'века,',
	russian_typography_process_text( 'в середине IV века | В начале V века | в конце XI века | в середине XII века | в конце XII века | с IV века н. э. | в начале XI века,', RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_STANDART )
);

russian_typography_assert_same(
	'Standart mode must glue te to the following word.',
	'те' . $russian_typography_nbsp . 'помогли; был' . $russian_typography_nbsp . 'он,; увидел' . $russian_typography_nbsp . 'он; увидел он' . $russian_typography_nbsp . 'и; лишь' . $russian_typography_nbsp . 'то,',
	russian_typography_process_text( 'те помогли; был он,; увидел он; увидел он и; лишь то,', RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_STANDART )
);

russian_typography_assert_same(
	'Standart mode must glue Lebedev-like vot and malformed go fragments.',
	'А' . $russian_typography_nbsp . 'вот' . $russian_typography_nbsp . 'то,; вот' . $russian_typography_nbsp . 'он; И' . $russian_typography_nbsp . 'вот мы' . $russian_typography_nbsp . 'приходим; региону.' . $russian_typography_nbsp . 'го' . $russian_typography_nbsp . 'всегда',
	russian_typography_process_text( 'А вот то,; вот он; И вот мы приходим; региону. го всегда', RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_STANDART )
);

russian_typography_assert_same(
	'Standart mode must glue calibrated short proper-name parts like Lebedev.',
	'де' . $russian_typography_nbsp . 'Ландой; Хунаб' . $russian_typography_nbsp . 'Ку; Хунаб Ку' . $russian_typography_nbsp . 'является; Хунаб Ку. Эта; Хунаб-Ку является; Юм' . $russian_typography_nbsp . 'Симиль; Симиром/Юм Симиром; Ах' . $russian_typography_nbsp . 'Пучем; Эк' . $russian_typography_nbsp . 'Ахау; как' . $russian_typography_nbsp . 'Иш; как Иван; как' . $russian_typography_nbsp . 'вы,; Иш' . $russian_typography_nbsp . '(север); богиней' . $russian_typography_nbsp . 'О; Великий Бог',
	russian_typography_process_text( 'де Ландой; Хунаб Ку; Хунаб Ку является; Хунаб Ку. Эта; Хунаб-Ку является; Юм Симиль; Симиром/Юм Симиром; Ах Пучем; Эк Ахау; как Иш; как Иван; как вы,; Иш (север); богиней О; Великий Бог', RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_STANDART )
);

russian_typography_assert_same(
	'Standart mode must glue short words before opening quotes.',
	'в' . $russian_typography_nbsp . '«Пополь-Вух»; не' . $russian_typography_nbsp . '«ломается»; и' . $russian_typography_nbsp . '«Последнее»',
	russian_typography_process_text( 'в «Пополь-Вух»; не «ломается»; и «Последнее»', RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_STANDART )
);

russian_typography_assert_same(
	'Standart mode must glue short Latin particles before capitalized words.',
	'of' . $russian_typography_nbsp . 'Japan; de' . $russian_typography_nbsp . 'Hastingae',
	russian_typography_process_text( 'of Japan; de Hastingae', RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_STANDART )
);

russian_typography_assert_same(
	'Standart mode must glue the space before a dash and before a single initial.',
	'слово' . $russian_typography_nbsp . '— слово, слово' . $russian_typography_nbsp . '— Слово, слово,' . $russian_typography_nbsp . '— слово; предложение. —' . $russian_typography_nbsp . 'Слово; когда' . $russian_typography_nbsp . 'К. пришёл; Историк А. Смит',
	russian_typography_process_text( 'слово — слово, слово — Слово, слово, — слово; предложение. — Слово; когда К. пришёл; Историк А. Смит', RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_STANDART )
);

russian_typography_assert_same(
	'Standart mode must combine forward, dash, and post-dash gluing like Lebedev.',
	'Он' . $russian_typography_nbsp . 'сказал' . $russian_typography_nbsp . '— то' . $russian_typography_nbsp . 'слово важно',
	russian_typography_process_text( 'Он сказал — то слово важно', RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_STANDART )
);

russian_typography_assert_same(
	'Standart mode must glue opening dashes before capitalized speech.',
	'—' . $russian_typography_nbsp . 'Но; —' . $russian_typography_nbsp . '«Насколько»; Бейтс.' . "\n\n" . '—' . $russian_typography_nbsp . 'Он,',
	russian_typography_process_text( '— Но; — «Насколько»; Бейтс.' . "\n\n" . '— Он,', RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_STANDART )
);

if ( array() !== $russian_typography_failures ) {
	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
	fwrite( STDERR, implode( "\n\n", $russian_typography_failures ) . "\n" );
	exit( 1 );
}

echo "Russian Typography tests passed.\n";
