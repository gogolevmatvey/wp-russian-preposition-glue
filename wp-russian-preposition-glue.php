<?php
/**
 * Plugin Name: Склейка предлогов
 * Description: Склеивает русские короткие служебные слова, сокращения и числа с единицами неразрывными пробелами без изменения текста записей.
 * Version: 0.1.0
 * Author: gogolevmatvey
 * Text Domain: wp-russian-preposition-glue
 *
 * @package WpRussianPrepositionGlue
 */

defined( 'ABSPATH' ) || exit;

const WP_RUSSIAN_PREPOSITION_GLUE_SCOPE_OPTION = 'wp_russian_preposition_glue_scope';
const WP_RUSSIAN_PREPOSITION_GLUE_SCOPE_ALL    = 'all';
const WP_RUSSIAN_PREPOSITION_GLUE_SCOPE_SINGLE = 'singular';
const WP_RUSSIAN_PREPOSITION_GLUE_SKIP_HEADING_SHORT_WORDS_OPTION = 'wp_russian_preposition_glue_skip_heading_short_words';
const WP_RUSSIAN_PREPOSITION_GLUE_SHORT_WORD_MODE_OPTION          = 'wp_russian_preposition_glue_short_word_mode';
const WP_RUSSIAN_PREPOSITION_GLUE_SHORT_WORD_MODE_SOFT            = 'soft';
const WP_RUSSIAN_PREPOSITION_GLUE_SHORT_WORD_MODE_FULL            = 'full';
const WP_RUSSIAN_PREPOSITION_GLUE_SHORT_WORD_MODE_OFF             = 'off';
const WP_RUSSIAN_PREPOSITION_GLUE_SOFT_MAX_NEXT_WORD_LENGTH_OPTION = 'wp_russian_preposition_glue_soft_max_next_word_length';
const WP_RUSSIAN_PREPOSITION_GLUE_SOFT_MAX_NEXT_WORD_LENGTH_DEFAULT = 10;

/**
 * Sanitizes the typography scope setting.
 *
 * @param mixed $value Raw option value.
 */
function wp_russian_preposition_glue_sanitize_scope( mixed $value ): string {
	$scope = is_string( $value ) ? sanitize_key( $value ) : '';

	if ( in_array( $scope, array( WP_RUSSIAN_PREPOSITION_GLUE_SCOPE_ALL, WP_RUSSIAN_PREPOSITION_GLUE_SCOPE_SINGLE ), true ) ) {
		return $scope;
	}

	return WP_RUSSIAN_PREPOSITION_GLUE_SCOPE_SINGLE;
}

/**
 * Returns the active typography scope.
 */
function wp_russian_preposition_glue_get_scope(): string {
	return wp_russian_preposition_glue_sanitize_scope(
		get_option( WP_RUSSIAN_PREPOSITION_GLUE_SCOPE_OPTION, WP_RUSSIAN_PREPOSITION_GLUE_SCOPE_SINGLE )
	);
}

/**
 * Sanitizes the short-word glue mode setting.
 *
 * @param mixed $value Raw option value.
 */
function wp_russian_preposition_glue_sanitize_short_word_mode( mixed $value ): string {
	$mode = is_string( $value ) ? sanitize_key( $value ) : '';

	if (
		in_array(
			$mode,
			array(
				WP_RUSSIAN_PREPOSITION_GLUE_SHORT_WORD_MODE_SOFT,
				WP_RUSSIAN_PREPOSITION_GLUE_SHORT_WORD_MODE_FULL,
				WP_RUSSIAN_PREPOSITION_GLUE_SHORT_WORD_MODE_OFF,
			),
			true
		)
	) {
		return $mode;
	}

	return WP_RUSSIAN_PREPOSITION_GLUE_SHORT_WORD_MODE_SOFT;
}

/**
 * Returns the active short-word glue mode.
 */
function wp_russian_preposition_glue_get_short_word_mode(): string {
	return wp_russian_preposition_glue_sanitize_short_word_mode(
		get_option( WP_RUSSIAN_PREPOSITION_GLUE_SHORT_WORD_MODE_OPTION, WP_RUSSIAN_PREPOSITION_GLUE_SHORT_WORD_MODE_SOFT )
	);
}

/**
 * Sanitizes the next-word length threshold for soft short-word glue mode.
 *
 * @param mixed $value Raw option value.
 */
function wp_russian_preposition_glue_sanitize_soft_max_next_word_length( mixed $value ): int {
	$length = absint( $value );

	if ( $length < 4 ) {
		return 4;
	}

	if ( $length > 20 ) {
		return 20;
	}

	return $length;
}

/**
 * Returns the maximum next-word length used in soft short-word glue mode.
 */
function wp_russian_preposition_glue_get_soft_max_next_word_length(): int {
	return wp_russian_preposition_glue_sanitize_soft_max_next_word_length(
		get_option(
			WP_RUSSIAN_PREPOSITION_GLUE_SOFT_MAX_NEXT_WORD_LENGTH_OPTION,
			WP_RUSSIAN_PREPOSITION_GLUE_SOFT_MAX_NEXT_WORD_LENGTH_DEFAULT
		)
	);
}

/**
 * Sanitizes checkbox-like settings.
 *
 * @param mixed $value Raw option value.
 */
function wp_russian_preposition_glue_sanitize_checkbox( mixed $value ): string {
	return '1' === (string) $value ? '1' : '0';
}

/**
 * Returns true when short service words should not be glued inside headings.
 */
function wp_russian_preposition_glue_skip_short_words_in_headings(): bool {
	return '1' === wp_russian_preposition_glue_sanitize_checkbox(
		get_option( WP_RUSSIAN_PREPOSITION_GLUE_SKIP_HEADING_SHORT_WORDS_OPTION, '1' )
	);
}

/**
 * Registers plugin settings.
 */
function wp_russian_preposition_glue_register_settings(): void {
	register_setting(
		'wp_russian_preposition_glue',
		WP_RUSSIAN_PREPOSITION_GLUE_SCOPE_OPTION,
		array(
			'type'              => 'string',
			'sanitize_callback' => 'wp_russian_preposition_glue_sanitize_scope',
			'default'           => WP_RUSSIAN_PREPOSITION_GLUE_SCOPE_SINGLE,
		)
	);

	register_setting(
		'wp_russian_preposition_glue',
		WP_RUSSIAN_PREPOSITION_GLUE_SKIP_HEADING_SHORT_WORDS_OPTION,
		array(
			'type'              => 'string',
			'sanitize_callback' => 'wp_russian_preposition_glue_sanitize_checkbox',
			'default'           => '1',
		)
	);

	register_setting(
		'wp_russian_preposition_glue',
		WP_RUSSIAN_PREPOSITION_GLUE_SHORT_WORD_MODE_OPTION,
		array(
			'type'              => 'string',
			'sanitize_callback' => 'wp_russian_preposition_glue_sanitize_short_word_mode',
			'default'           => WP_RUSSIAN_PREPOSITION_GLUE_SHORT_WORD_MODE_SOFT,
		)
	);

	register_setting(
		'wp_russian_preposition_glue',
		WP_RUSSIAN_PREPOSITION_GLUE_SOFT_MAX_NEXT_WORD_LENGTH_OPTION,
		array(
			'type'              => 'integer',
			'sanitize_callback' => 'wp_russian_preposition_glue_sanitize_soft_max_next_word_length',
			'default'           => WP_RUSSIAN_PREPOSITION_GLUE_SOFT_MAX_NEXT_WORD_LENGTH_DEFAULT,
		)
	);
}
add_action( 'admin_init', 'wp_russian_preposition_glue_register_settings' );

/**
 * Adds the settings page to the WordPress admin.
 */
function wp_russian_preposition_glue_add_settings_page(): void {
	add_options_page(
		__( 'Склейка предлогов', 'wp-russian-preposition-glue' ),
		__( 'Склейка предлогов', 'wp-russian-preposition-glue' ),
		'manage_options',
		'wp-russian-preposition-glue',
		'wp_russian_preposition_glue_render_settings_page'
	);
}
add_action( 'admin_menu', 'wp_russian_preposition_glue_add_settings_page' );

/**
 * Adds a settings shortcut to the plugin row on the Plugins page.
 *
 * @param array<int|string, string> $links Existing plugin action links.
 */
function wp_russian_preposition_glue_add_plugin_action_links( array $links ): array {
	$settings_link = sprintf(
		'<a href="%s">%s</a>',
		esc_url( admin_url( 'options-general.php?page=wp-russian-preposition-glue' ) ),
		esc_html__( 'Настройки', 'wp-russian-preposition-glue' )
	);

	array_unshift( $links, $settings_link );

	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wp_russian_preposition_glue_add_plugin_action_links' );

/**
 * Renders the settings page.
 */
function wp_russian_preposition_glue_render_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$scope = wp_russian_preposition_glue_get_scope();
	$skip_heading_short_words = wp_russian_preposition_glue_skip_short_words_in_headings();
	$short_word_mode = wp_russian_preposition_glue_get_short_word_mode();
	$soft_max_next_word_length = wp_russian_preposition_glue_get_soft_max_next_word_length();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Склейка предлогов', 'wp-russian-preposition-glue' ); ?></h1>

		<form method="post" action="options.php">
			<?php settings_fields( 'wp_russian_preposition_glue' ); ?>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Область применения', 'wp-russian-preposition-glue' ); ?></th>
					<td>
						<fieldset>
							<legend class="screen-reader-text">
								<?php esc_html_e( 'Область применения типографики', 'wp-russian-preposition-glue' ); ?>
							</legend>

							<p>
								<label>
									<input type="radio" name="<?php echo esc_attr( WP_RUSSIAN_PREPOSITION_GLUE_SCOPE_OPTION ); ?>" value="<?php echo esc_attr( WP_RUSSIAN_PREPOSITION_GLUE_SCOPE_SINGLE ); ?>" <?php checked( WP_RUSSIAN_PREPOSITION_GLUE_SCOPE_SINGLE, $scope ); ?>>
									<?php esc_html_e( 'Только одиночные записи и страницы', 'wp-russian-preposition-glue' ); ?>
								</label>
							</p>
							<p class="description">
								<?php esc_html_e( 'Обрабатывает основной текст, заголовок и комментарии на страницах записей и страниц. Карточки, архивы и главная лента не меняются.', 'wp-russian-preposition-glue' ); ?>
							</p>

							<p>
								<label>
									<input type="radio" name="<?php echo esc_attr( WP_RUSSIAN_PREPOSITION_GLUE_SCOPE_OPTION ); ?>" value="<?php echo esc_attr( WP_RUSSIAN_PREPOSITION_GLUE_SCOPE_ALL ); ?>" <?php checked( WP_RUSSIAN_PREPOSITION_GLUE_SCOPE_ALL, $scope ); ?>>
									<?php esc_html_e( 'Весь фронтенд', 'wp-russian-preposition-glue' ); ?>
								</label>
							</p>
							<p class="description">
								<?php esc_html_e( 'Обрабатывает записи, страницы, карточки, архивы, заголовки, анонсы и комментарии на всём фронтенде.', 'wp-russian-preposition-glue' ); ?>
							</p>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Заголовки', 'wp-russian-preposition-glue' ); ?></th>
					<td>
						<input type="hidden" name="<?php echo esc_attr( WP_RUSSIAN_PREPOSITION_GLUE_SKIP_HEADING_SHORT_WORDS_OPTION ); ?>" value="0">
						<label>
							<input type="checkbox" name="<?php echo esc_attr( WP_RUSSIAN_PREPOSITION_GLUE_SKIP_HEADING_SHORT_WORDS_OPTION ); ?>" value="1" <?php checked( $skip_heading_short_words ); ?>>
							<?php esc_html_e( 'Не склеивать короткие слова в заголовках', 'wp-russian-preposition-glue' ); ?>
						</label>
						<p class="description">
							<?php esc_html_e( 'Оставляет обычные пробелы после коротких союзов, предлогов, частиц и местоимений в заголовках записей и внутри h1-h6, чтобы mobile-переносы не ломались неразрывными пробелами.', 'wp-russian-preposition-glue' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Склейка коротких слов', 'wp-russian-preposition-glue' ); ?></th>
					<td>
						<fieldset>
							<legend class="screen-reader-text">
								<?php esc_html_e( 'Режим склейки коротких слов', 'wp-russian-preposition-glue' ); ?>
							</legend>

							<p>
								<label>
									<input type="radio" name="<?php echo esc_attr( WP_RUSSIAN_PREPOSITION_GLUE_SHORT_WORD_MODE_OPTION ); ?>" value="<?php echo esc_attr( WP_RUSSIAN_PREPOSITION_GLUE_SHORT_WORD_MODE_SOFT ); ?>" <?php checked( WP_RUSSIAN_PREPOSITION_GLUE_SHORT_WORD_MODE_SOFT, $short_word_mode ); ?>>
									<?php esc_html_e( 'Мягкий', 'wp-russian-preposition-glue' ); ?>
								</label>
							</p>
							<p class="description">
								<?php esc_html_e( 'Склеивает только односимвольные служебные слова: в, к, с, у, о, и, а. На узких экранах этот режим оставляет больше естественных мест переноса.', 'wp-russian-preposition-glue' ); ?>
							</p>

							<p>
								<label for="wp-russian-preposition-glue-soft-max-next-word-length">
									<?php esc_html_e( 'Максимальная длина следующего слова в мягком режиме', 'wp-russian-preposition-glue' ); ?>
								</label>
								<input
									type="number"
									id="wp-russian-preposition-glue-soft-max-next-word-length"
									name="<?php echo esc_attr( WP_RUSSIAN_PREPOSITION_GLUE_SOFT_MAX_NEXT_WORD_LENGTH_OPTION ); ?>"
									value="<?php echo esc_attr( (string) $soft_max_next_word_length ); ?>"
									min="4"
									max="20"
									step="1"
									class="small-text"
								>
							</p>
							<p class="description">
								<?php esc_html_e( 'Если следующее слово длиннее этого значения, пробел остаётся обычным. Это помогает избежать рваных строк на ширине около 320 px.', 'wp-russian-preposition-glue' ); ?>
							</p>

							<p>
								<label>
									<input type="radio" name="<?php echo esc_attr( WP_RUSSIAN_PREPOSITION_GLUE_SHORT_WORD_MODE_OPTION ); ?>" value="<?php echo esc_attr( WP_RUSSIAN_PREPOSITION_GLUE_SHORT_WORD_MODE_FULL ); ?>" <?php checked( WP_RUSSIAN_PREPOSITION_GLUE_SHORT_WORD_MODE_FULL, $short_word_mode ); ?>>
									<?php esc_html_e( 'Полный', 'wp-russian-preposition-glue' ); ?>
								</label>
							</p>
							<p class="description">
								<?php esc_html_e( 'Склеивает широкий список коротких союзов, предлогов, частиц и местоимений с последующим словом. Даёт более плотную типографику, но на узких экранах может усиливать рваность строк.', 'wp-russian-preposition-glue' ); ?>
							</p>

							<p>
								<label>
									<input type="radio" name="<?php echo esc_attr( WP_RUSSIAN_PREPOSITION_GLUE_SHORT_WORD_MODE_OPTION ); ?>" value="<?php echo esc_attr( WP_RUSSIAN_PREPOSITION_GLUE_SHORT_WORD_MODE_OFF ); ?>" <?php checked( WP_RUSSIAN_PREPOSITION_GLUE_SHORT_WORD_MODE_OFF, $short_word_mode ); ?>>
									<?php esc_html_e( 'Выключено', 'wp-russian-preposition-glue' ); ?>
								</label>
							</p>
							<p class="description">
								<?php esc_html_e( 'Не склеивает короткие слова. Исторические сокращения и числа с единицами всё равно остаются склеенными.', 'wp-russian-preposition-glue' ); ?>
							</p>
						</fieldset>
					</td>
				</tr>
			</table>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * Returns true when front-end text output can be typographed.
 *
 * @param string $context   Output context: post, title, comment.
 * @param int    $object_id Related post/comment ID.
 */
function wp_russian_preposition_glue_should_process( string $context = 'post', int $object_id = 0 ): bool {
	if ( is_admin() || is_feed() || wp_doing_ajax() ) {
		return false;
	}

	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return false;
	}

	if ( WP_RUSSIAN_PREPOSITION_GLUE_SCOPE_ALL === wp_russian_preposition_glue_get_scope() ) {
		return true;
	}

	return wp_russian_preposition_glue_is_singular_post_page_context( $context, $object_id );
}

/**
 * Returns true when the current output belongs to the main single post/page.
 *
 * @param string $context   Output context: post, title, comment.
 * @param int    $object_id Related post/comment ID.
 */
function wp_russian_preposition_glue_is_singular_post_page_context( string $context, int $object_id = 0 ): bool {
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
 * Returns the short-word list for the selected glue mode.
 *
 * @param string $mode Short-word glue mode.
 */
function wp_russian_preposition_glue_get_short_words_for_mode( string $mode ): array {
	if ( WP_RUSSIAN_PREPOSITION_GLUE_SHORT_WORD_MODE_SOFT === $mode ) {
		return array(
			'а',
			'в',
			'и',
			'к',
			'о',
			'с',
			'у',
		);
	}

	if ( WP_RUSSIAN_PREPOSITION_GLUE_SHORT_WORD_MODE_FULL !== $mode ) {
		return array();
	}

	return array(
		'а',
		'без',
		'бы',
		'в',
		'во',
		'все',
		'всё',
		'вы',
		'да',
		'для',
		'до',
		'её',
		'ее',
		'же',
		'за',
		'и',
		'из',
		'изо',
		'их',
		'им',
		'к',
		'как',
		'ко',
		'кто',
		'ли',
		'мы',
		'на',
		'над',
		'не',
		'ни',
		'но',
		'о',
		'об',
		'обо',
		'он',
		'она',
		'они',
		'оно',
		'от',
		'по',
		'под',
		'при',
		'про',
		'с',
		'со',
		'то',
		'тот',
		'тут',
		'ты',
		'у',
		'уже',
		'что',
		'это',
		'эта',
		'эти',
		'я',
	);
}

/**
 * Returns UTF-8 string length without relying only on mbstring.
 *
 * @param string $text Text to count.
 */
function wp_russian_preposition_glue_utf8_length( string $text ): int {
	if ( function_exists( 'mb_strlen' ) ) {
		return mb_strlen( $text, 'UTF-8' );
	}

	return preg_match_all( '/./us', $text ) ?: strlen( $text );
}

/**
 * Glues short Russian service words according to the selected mode.
 *
 * @param string $text Plain text.
 * @param string $mode Short-word glue mode.
 */
function wp_russian_preposition_glue_glue_short_words( string $text, string $mode ): string {
	$mode        = wp_russian_preposition_glue_sanitize_short_word_mode( $mode );
	$short_words = wp_russian_preposition_glue_get_short_words_for_mode( $mode );

	if ( array() === $short_words ) {
		return $text;
	}

	$nbsp                      = "\xC2\xA0";
	$soft_max_next_word_length = WP_RUSSIAN_PREPOSITION_GLUE_SHORT_WORD_MODE_SOFT === $mode
		? wp_russian_preposition_glue_get_soft_max_next_word_length()
		: 0;
	$words_pattern             = implode(
		'|',
		array_map(
			static fn( string $word ): string => preg_quote( $word, '/' ),
			$short_words
		)
	);

	return preg_replace_callback(
		'/(\A|[^\p{L}\p{N}])(' . $words_pattern . ')[ \t]+(?=([\p{L}\p{N}][\p{L}\p{N}-]*))/iu',
		static function ( array $matches ) use ( $mode, $nbsp, $soft_max_next_word_length ): string {
			if (
				WP_RUSSIAN_PREPOSITION_GLUE_SHORT_WORD_MODE_SOFT === $mode
				&& isset( $matches[3] )
				&& wp_russian_preposition_glue_utf8_length( $matches[3] ) > $soft_max_next_word_length
			) {
				return $matches[0];
			}

			return $matches[1] . $matches[2] . $nbsp;
		},
		$text
	) ?? $text;
}

/**
 * Glues short Russian service words and common history units in plain text.
 *
 * @param string           $text            Plain text.
 * @param string|bool|null $short_word_mode Short-word glue mode or legacy boolean.
 */
function wp_russian_preposition_glue_process_text( string $text, string|bool|null $short_word_mode = null ): string {
	if ( '' === $text ) {
		return $text;
	}

	$nbsp = "\xC2\xA0";

	if ( is_bool( $short_word_mode ) ) {
		$short_word_mode = $short_word_mode ? wp_russian_preposition_glue_get_short_word_mode() : WP_RUSSIAN_PREPOSITION_GLUE_SHORT_WORD_MODE_OFF;
	} elseif ( null === $short_word_mode ) {
		$short_word_mode = wp_russian_preposition_glue_get_short_word_mode();
	} else {
		$short_word_mode = wp_russian_preposition_glue_sanitize_short_word_mode( $short_word_mode );
	}

	$text = preg_replace( '/\bдо[ \t]+н\.[ \t]*э\./iu', 'до' . $nbsp . 'н.' . $nbsp . 'э.', $text ) ?? $text;
	$text = preg_replace( '/\bн\.[ \t]*э\./iu', 'н.' . $nbsp . 'э.', $text ) ?? $text;

	$text = preg_replace(
		'/(\d+)[ \t]+(км|м|см|мм|кг|г|век(?:а|е|ов)?|год(?:а|у|ом|ов)?|час(?:а|ов)?|мин(?:ут(?:а|ы)?|\.?)|сек(?:унд(?:а|ы)?|\.?))/iu',
		'$1' . $nbsp . '$2',
		$text
	) ?? $text;

	return wp_russian_preposition_glue_glue_short_words( $text, $short_word_mode );
}

/**
 * Processes HTML text nodes while leaving tags, attributes and code-like blocks intact.
 *
 * @param string $html Rendered HTML.
 */
function wp_russian_preposition_glue_process_html( string $html ): string {
	if ( '' === $html || ! wp_russian_preposition_glue_should_process( 'post', (int) get_the_ID() ) ) {
		return $html;
	}

	return wp_russian_preposition_glue_process_html_nodes( $html );
}

/**
 * Processes HTML text nodes while leaving tags, attributes and code-like blocks intact.
 *
 * @param string $html Rendered HTML.
 */
function wp_russian_preposition_glue_process_html_nodes( string $html ): string {
	if ( false === strpos( $html, '<' ) ) {
		return wp_russian_preposition_glue_process_text( $html );
	}

	$parts       = preg_split( '/(<[^>]+>)/u', $html, -1, PREG_SPLIT_DELIM_CAPTURE );
	$result      = '';
	$skip_depth  = 0;
	$heading_depth = 0;
	$skip_tags   = array( 'code', 'kbd', 'pre', 'samp', 'script', 'style', 'textarea' );
	$skip_lookup = array_fill_keys( $skip_tags, true );
	$heading_tags = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' );
	$heading_lookup = array_fill_keys( $heading_tags, true );

	if ( ! is_array( $parts ) ) {
		return $html;
	}

	foreach ( $parts as $part ) {
		if ( '' === $part ) {
			continue;
		}

		if ( '<' === $part[0] ) {
			if ( preg_match( '#^</\s*([a-z0-9:-]+)#i', $part, $close_matches ) ) {
				$tag = strtolower( $close_matches[1] );

				if ( isset( $skip_lookup[ $tag ] ) && $skip_depth > 0 ) {
					--$skip_depth;
				}

				if ( isset( $heading_lookup[ $tag ] ) && $heading_depth > 0 ) {
					--$heading_depth;
				}
			} elseif ( preg_match( '#^<\s*([a-z0-9:-]+)(?:\s|>|/)#i', $part, $open_matches ) ) {
				$tag = strtolower( $open_matches[1] );

				if ( isset( $skip_lookup[ $tag ] ) && ! preg_match( '#/>\s*$#', $part ) ) {
					++$skip_depth;
				}

				if ( isset( $heading_lookup[ $tag ] ) && ! preg_match( '#/>\s*$#', $part ) ) {
					++$heading_depth;
				}
			}

			$result .= $part;
			continue;
		}

		$glue_short_words = ! ( $heading_depth > 0 && wp_russian_preposition_glue_skip_short_words_in_headings() );
		$result .= $skip_depth > 0 ? $part : wp_russian_preposition_glue_process_text( $part, $glue_short_words );
	}

	return $result;
}

/**
 * Processes plain title-like strings.
 *
 * @param string $text    Plain text.
 * @param int    $post_id Related post ID.
 */
function wp_russian_preposition_glue_process_plain_output( string $text, int $post_id = 0 ): string {
	if ( '' === $text || ! wp_russian_preposition_glue_should_process( 'title', $post_id ) ) {
		return $text;
	}

	return wp_russian_preposition_glue_process_text( $text, ! wp_russian_preposition_glue_skip_short_words_in_headings() );
}

/**
 * Processes rendered comment text.
 *
 * @param string $html    Rendered comment HTML.
 * @param mixed  $comment Optional comment object or ID.
 */
function wp_russian_preposition_glue_process_comment_html( string $html, mixed $comment = null ): string {
	$comment_id = 0;

	if ( $comment instanceof WP_Comment ) {
		$comment_id = (int) $comment->comment_ID;
	} elseif ( is_numeric( $comment ) ) {
		$comment_id = (int) $comment;
	}

	if ( '' === $html || ! wp_russian_preposition_glue_should_process( 'comment', $comment_id ) ) {
		return $html;
	}

	return wp_russian_preposition_glue_process_html_nodes( $html );
}

add_filter( 'the_content', 'wp_russian_preposition_glue_process_html', 99 );
add_filter( 'the_excerpt', 'wp_russian_preposition_glue_process_html', 99 );
add_filter( 'comment_text', 'wp_russian_preposition_glue_process_comment_html', 99, 2 );
add_filter( 'the_title', 'wp_russian_preposition_glue_process_plain_output', 99, 2 );
