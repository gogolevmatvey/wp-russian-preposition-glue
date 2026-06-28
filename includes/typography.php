<?php
/**
 * Typography transformations for Russian Typography.
 *
 * @package RussianTypography
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns short words glued to the following word for the selected glue mode.
 *
 * @param string $mode Short-word glue mode.
 */
function russian_typography_get_short_words_for_mode( string $mode ): array {
	if ( RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_SOFT === $mode ) {
		$words = array(
			'а',
			'в',
			'и',
			'к',
			'о',
			'с',
			'у',
		);

		return russian_typography_normalize_word_list(
			/**
			 * Filters short words glued to the following word.
			 *
			 * @param array<int, string> $words Short words.
			 * @param string             $mode  Short-word glue mode.
			 */
			apply_filters( 'russian_typography_short_words', $words, $mode )
		);
	}

	if ( RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_STANDART === $mode ) {
		$words = array(
			'а',
			'в',
			'во',
			'вот',
			'вы',
			'да',
			'до',
			'ей',
			'её',
			'ее',
			'ею',
			'го',
			'за',
			'и',
			'из',
			'их',
			'им',
			'к',
			'ко',
			'мы',
			'на',
			'не',
			'ни',
			'но',
			'ну',
			'о',
			'об',
			'он',
			'от',
			'по',
			'с',
			'со',
			'те',
			'то',
			'ты',
			'у',
			'уж',
			'я',
		);

		return russian_typography_normalize_word_list(
			/**
			 * Filters short words glued to the following word.
			 *
			 * @param array<int, string> $words Short words.
			 * @param string             $mode  Short-word glue mode.
			 */
			apply_filters( 'russian_typography_short_words', $words, $mode )
		);
	}

	if ( RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_FULL !== $mode ) {
		return array();
	}

	$words = array(
		'а',
		'без',
		'в',
		'во',
		'вот',
		'все',
		'всё',
		'вы',
		'да',
		'для',
		'до',
		'его',
		'ей',
		'ему',
		'её',
		'ее',
		'ею',
		'за',
		'и',
		'ибо',
		'или',
		'из',
		'изо',
		'их',
		'им',
		'к',
		'как',
		'ко',
		'кто',
		'мне',
		'мы',
		'на',
		'над',
		'нам',
		'нас',
		'не',
		'ни',
		'но',
		'ну',
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
		'ты',
		'у',
		'уж',
		'уже',
		'вам',
		'вас',
		'чем',
		'что',
		'это',
		'эта',
		'эти',
		'я',
	);

	return russian_typography_normalize_word_list(
		/**
		 * Filters short words glued to the following word.
		 *
		 * @param array<int, string> $words Short words.
		 * @param string             $mode  Short-word glue mode.
		 */
		apply_filters( 'russian_typography_short_words', $words, $mode )
	);
}

/**
 * Returns postpositive short particles for the selected glue mode.
 *
 * @param string $mode Short-word glue mode.
 */
function russian_typography_get_postpositive_short_words_for_mode( string $mode ): array {
	if (
		RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_FULL !== $mode
		&& RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_STANDART !== $mode
	) {
		return array();
	}

	$words = array(
		'б',
		'бы',
		'же',
		'ли',
	);

	if ( RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_STANDART === $mode ) {
		$words = array_merge(
			$words,
			array(
				'ей',
				'её',
				'ее',
				'ею',
				'их',
				'им',
				'и',
				'он',
				'те',
				'то',
			)
		);
	}

	return russian_typography_normalize_word_list(
		/**
		 * Filters postpositive short words glued to the previous word.
		 *
		 * @param array<int, string> $words Postpositive short words.
		 * @param string             $mode  Short-word glue mode.
		 */
		apply_filters( 'russian_typography_postpositive_short_words', $words, $mode )
	);
}

/**
 * Normalizes filtered word lists.
 *
 * @param array<int, mixed> $words Raw word list.
 * @return array<int, string>
 */
function russian_typography_normalize_word_list( array $words ): array {
	$normalized = array();

	foreach ( $words as $word ) {
		if ( ! is_string( $word ) ) {
			continue;
		}

		$word = trim( $word );

		if ( '' === $word ) {
			continue;
		}

		$normalized[ $word ] = true;
	}

	return array_keys( $normalized );
}

/**
 * Builds and caches a regex alternation pattern for a word list.
 *
 * @param array<int, string> $words Word list.
 */
function russian_typography_get_words_pattern( array $words ): string {
	static $cache = array();

	$cache_key = md5( implode( "\n", $words ) );

	if ( ! isset( $cache[ $cache_key ] ) ) {
		$cache[ $cache_key ] = implode(
			'|',
			array_map(
				static fn( string $word ): string => preg_quote( $word, '/' ),
				$words
			)
		);
	}

	return $cache[ $cache_key ];
}

/**
 * Returns UTF-8 string length without relying only on mbstring.
 *
 * @param string $text Text to count.
 */
function russian_typography_utf8_length( string $text ): int {
	if ( function_exists( 'mb_strlen' ) ) {
		return mb_strlen( $text, 'UTF-8' );
	}

	$length = preg_match_all( '/./us', $text );

	if ( false === $length ) {
		return strlen( $text );
	}

	return $length;
}

/**
 * Replaces URL and email-like fragments with temporary placeholders.
 *
 * @param string                $text               Plain text.
 * @param array<string, string> $protected_segments Protected fragments.
 */
function russian_typography_protect_text_segments( string $text, array &$protected_segments ): string {
	$protected_segments = array();

	return preg_replace_callback(
		'~\b(?:(?:https?|ftp)://|www\.)[^\s<>"\']+|(?<![\p{L}\p{N}._%+\-])[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}(?![\p{L}\p{N}._%+\-])~iu',
		static function ( array $matches ) use ( &$protected_segments ): string {
			$placeholder                        = "\x1A" . 'russian_typography_' . count( $protected_segments ) . "\x1A";
			$protected_segments[ $placeholder ] = $matches[0];

			return $placeholder;
		},
		$text
	) ?? $text;
}

/**
 * Restores fragments protected before typography processing.
 *
 * @param string                $text               Plain text.
 * @param array<string, string> $protected_segments Protected fragments.
 */
function russian_typography_restore_text_segments( string $text, array $protected_segments ): string {
	if ( array() === $protected_segments ) {
		return $text;
	}

	return strtr( $text, $protected_segments );
}

/**
 * Applies the historical spacing rules used by existing modes.
 *
 * @param string $text Plain text.
 * @param string $nbsp Non-breaking space.
 */
function russian_typography_apply_legacy_spacing_rules( string $text, string $nbsp ): string {
	$text = preg_replace( '/\bдо[ \t]+н\.[ \t]*э\./iu', 'до' . $nbsp . 'н.' . $nbsp . 'э.', $text ) ?? $text;
	$text = preg_replace( '/\bн\.[ \t]*э\./iu', 'н.' . $nbsp . 'э.', $text ) ?? $text;

	return preg_replace(
		'/(\d+)[ \t]+(км|м|см|мм|кг|г|век(?:а|е|ов)?|год(?:а|у|ом|ов)?|час(?:а|ов)?|мин(?:ут(?:а|ы)?|\.?)|сек(?:унд(?:а|ы)?|\.?))/iu',
		'$1' . $nbsp . '$2',
		$text
	) ?? $text;
}

/**
 * Applies spacing rules calibrated against Art. Lebedev Typograf.
 *
 * @param string $text Plain text.
 * @param string $nbsp Non-breaking space.
 */
function russian_typography_apply_standart_spacing_rules( string $text, string $nbsp ): string {
	$nbsp_pattern = preg_quote( $nbsp, '/' );
	$short_words  = russian_typography_get_words_pattern(
		russian_typography_get_short_words_for_mode( RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_STANDART )
	);

	$text = preg_replace( '/\b(век(?:а|е|у|ов)?)[ \t]+н\.[ \t]*э\./iu', '$1' . $nbsp . 'н.' . $nbsp . 'э.', $text ) ?? $text;
	$text = preg_replace( '/\bдо[ \t]+н\.[ \t]*э\./iu', 'до' . $nbsp . 'н.' . $nbsp . 'э.', $text ) ?? $text;
	$text = preg_replace( '/\bн\.[ \t]*э\./iu', 'н.' . $nbsp . 'э.', $text ) ?? $text;
	$text = preg_replace( '/(н\.' . $nbsp_pattern . 'э\.)[ \t]+(?=(' . $short_words . ')\b)/u', '$1' . $nbsp, $text ) ?? $text;

	$text = preg_replace( '/\b(ок\.)[ \t]+(?!\d+[–—-]\d)(?=\d)/iu', '$1' . $nbsp, $text ) ?? $text;
	$text = preg_replace( '/\b(\d{1,3})[ \t]+(?=\d{3}\b)/u', '$1' . $nbsp, $text ) ?? $text;

	$text = preg_replace(
		'/\b([0-9IVXLCDM]+(?:[–—-][0-9IVXLCDM]+)?)[ \t]+(гг?|вв?)\./iu',
		'$1' . $nbsp . '$2.',
		$text
	) ?? $text;
	$text = preg_replace(
		'/\b(\d+(?:[–—-]\d+)?)[ \t]+(год(?:а|у|ом|ов|ах)?)(?=[,.;:!?])/iu',
		'$1' . $nbsp . '$2',
		$text
	) ?? $text;
	$text = preg_replace(
		'/((?:гг?|вв?)\.)[ \t]+(?=до' . $nbsp_pattern . 'н\.|н\.)/iu',
		'$1' . $nbsp,
		$text
	) ?? $text;

	$text = preg_replace( '/\b([IVXLCDM]+)[ \t]+(век(?:а|е|у|ов)?)(?=[,.;:!?\)])/iu', '$1' . $nbsp . '$2', $text ) ?? $text;
	$text = preg_replace( '/\b([IVXLCDM]{1,2}|\d+)[ \t]+(век(?:а|е|у|ов)?)/iu', '$1' . $nbsp . '$2', $text ) ?? $text;
	$text = preg_replace(
		'/\b(начале|середине|конце)[ \t]+([IVXLCDM]{1,2})' . $nbsp_pattern . '(век(?:а|е|у|ов)?)(?=\b)(?![,.;:!?\)])/iu',
		'$1' . $nbsp . '$2 $3',
		$text
	) ?? $text;
	$text = preg_replace(
		'/\b(начале|середине|конце)[ \t]+([IVXLCDM]{3,})(?:[ \t]|' . $nbsp_pattern . ')(век(?:а|е|у|ов)?)(?=\b)/iu',
		'$1 $2 $3',
		$text
	) ?? $text;
	$text = preg_replace(
		'/\b((?:(?:в|во|с|со|к|ко|до|от|из|на|по|при|о|об|но|и|а)[ \t]+)+[IVXLCDM]{1,2})' . $nbsp_pattern . '(век(?:а|е|у|ов)?)/iu',
		'$1 $2',
		$text
	) ?? $text;
	$text = preg_replace(
		'/\b(начале|середине|конце)[ \t]+([IVXLCDM]{1,2})\b(?!' . $nbsp_pattern . 'век(?:а|е|у|ов)?[,.;:!?\)])/iu',
		'$1' . $nbsp . '$2',
		$text
	) ?? $text;
	$text = preg_replace( '/\b([IVXLCDM]{1,2})[ \t]+(?!(?:век(?:а|е|у|ов)?)(?=\b))(?=[\p{L}])/iu', '$1' . $nbsp, $text ) ?? $text;
	$text = preg_replace( '/\b([\p{L}][\p{L}-]*)[ \t]+([IVXLCDM]{1,2})(?=[,.;:!?])/u', '$1' . $nbsp . '$2', $text ) ?? $text;
	$text = preg_replace( '/([.!?])[ \t]+(?=го\b)/iu', '$1' . $nbsp, $text ) ?? $text;
	$text = preg_replace( '/\b(де)[ \t]+(?=[А-ЯЁ])/iu', '$1' . $nbsp, $text ) ?? $text;
	$text = preg_replace( '/\b(как)[ \t]+(?=Иш\b)/iu', '$1' . $nbsp, $text ) ?? $text;
	$text = preg_replace( '/\b(как)[ \t]+(?=(?:я|мы|вы|ты|он)\b)/iu', '$1' . $nbsp, $text ) ?? $text;
	$text = preg_replace( '/(?<!\/)\b(Ах|Юм|Иш|Эк)[ \t]+(?=[А-ЯЁ(])/u', '$1' . $nbsp, $text ) ?? $text;
	$text = preg_replace( '/\b([\p{L}][\p{L}-]*)[ \t]+(Ку|О)(?=\z|[,;:!?»"\)\]]|\.(?![ \t\r\n]+[А-ЯЁ]))/u', '$1' . $nbsp . '$2', $text ) ?? $text;
	$text = preg_replace( '/(?<![\x{002D}\x{2010}\x{2011}])\b(Ку)[ \t]+(?=[\p{L}])/u', '$1' . $nbsp, $text ) ?? $text;
	$text = preg_replace(
		'/\b(\d+)[ \t]+(января|февраля|марта|апреля|мая|июня|июля|августа|сентября|октября|ноября|декабря)\b/iu',
		'$1' . $nbsp . '$2',
		$text
	) ?? $text;
	$text = preg_replace(
		'/\b(\d+)[ \t]+(таких|неожиданных)(?=\z|[^\p{L}\p{N}])/iu',
		'$1' . $nbsp . '$2',
		$text
	) ?? $text;
	$text = preg_replace(
		'/\b(\d+)[ \t]+((?![\p{L}]+(?:ых|их)\b)[\p{L}]+)(?=[,.;:!?])/u',
		'$1' . $nbsp . '$2',
		$text
	) ?? $text;
	$text = preg_replace(
		'/(\d+)[ \t]+(%|км²|км2|км|м|см|мм|кг|г|лет|процент(?:а|ов|ами|ах|ом)?|квадратн(?:ый|ого|ому|ым|ом|ая|ой|ую|ое|ые|ых|ыми)?|час(?:а|ов)?|мин(?:ут(?:а|ы)?|\.?)|сек(?:унд(?:а|ы)?|\.?))(?=\z|[^\p{L}\p{N}])/iu',
		'$1' . $nbsp . '$2',
		$text
	) ?? $text;
	$text = preg_replace( '/\b(of|de)[ \t]+(?=[A-Z])/u', '$1' . $nbsp, $text ) ?? $text;

	$text = preg_replace( '/([\p{L}\p{N}\)\]\}»"”,])[ \t]+([—–])(?=[ \t])/u', '$1' . $nbsp . '$2', $text ) ?? $text;
	$text = preg_replace( '/(^|[ \t\r\n])([—–])[ \t]+(?=[А-ЯЁ«"“])/u', '$1$2' . $nbsp, $text ) ?? $text;

	return preg_replace(
		'/([\p{L}\p{N}\)\]\}»"”.,:;!?])[ \t]+([А-ЯЁA-Z]\.)(?![ \t]+[А-ЯЁA-Z][\p{L}\p{N}-])(?=[ \t]|$)/u',
		'$1' . $nbsp . '$2',
		$text
	) ?? $text;
}

/**
 * Glues short Russian service words according to the selected mode.
 *
 * @param string $text Plain text.
 * @param string $mode Short-word glue mode.
 */
function russian_typography_glue_short_words( string $text, string $mode ): string {
	$mode                     = russian_typography_sanitize_short_word_mode( $mode );
	$short_words              = russian_typography_get_short_words_for_mode( $mode );
	$postpositive_short_words = russian_typography_get_postpositive_short_words_for_mode( $mode );

	if ( array() === $short_words && array() === $postpositive_short_words ) {
		return $text;
	}

	$nbsp                 = "\xC2\xA0";
	$max_next_word_length = 0;

	if ( RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_SOFT === $mode ) {
		$max_next_word_length = russian_typography_get_soft_max_next_word_length();
	} elseif ( RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_FULL === $mode ) {
		$max_next_word_length = russian_typography_get_full_max_next_word_length();
	}

	if ( array() !== $short_words ) {
		$words_pattern = russian_typography_get_words_pattern( $short_words );
		$repeat_glue   = RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_STANDART === $mode;

		if ( RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_STANDART === $mode ) {
			$text = preg_replace(
				'/(\A|[^\p{L}\p{N}\x{002D}\x{2010}\x{2011}])(' . $words_pattern . ')[ \t]+(?=[«"“„][\p{L}\p{N}])/iu',
				'$1$2' . $nbsp,
				$text
			) ?? $text;
		}

		for ( $pass = 0; $pass < 8; ++$pass ) {
			$previous_text = $text;
			$text          = preg_replace_callback(
				'/(\A|[^\p{L}\p{N}\x{002D}\x{2010}\x{2011}])(' . $words_pattern . ')[ \t]+(?=([\p{L}\p{N}][\p{L}\p{N}-]*))/iu',
				static function ( array $matches ) use ( $nbsp, $max_next_word_length ): string {
					if (
						$max_next_word_length > 0
						&& isset( $matches[3] )
						&& russian_typography_utf8_length( $matches[3] ) > $max_next_word_length
					) {
						return $matches[0];
					}

					return $matches[1] . $matches[2] . $nbsp;
				},
				$text
			) ?? $text;

			if ( ! $repeat_glue || $previous_text === $text ) {
				break;
			}
		}
	}

	if ( array() !== $postpositive_short_words ) {
		$postpositive_words_pattern = russian_typography_get_words_pattern( $postpositive_short_words );

		$text = preg_replace(
			'/([\p{L}\p{N}][\p{L}\p{N}-]*)[ \t]+(' . $postpositive_words_pattern . ')(?=\z|[^\p{L}\p{N}\x{00A0}\x{202F}])/iu',
			'$1' . $nbsp . '$2',
			$text
		) ?? $text;
	}

	return $text;
}

/**
 * Glues short Russian service words and common history units in plain text.
 *
 * @param string           $text            Plain text.
 * @param string|bool|null $short_word_mode Short-word glue mode or legacy boolean.
 */
function russian_typography_process_text( string $text, string|bool|null $short_word_mode = null ): string {
	if ( '' === $text ) {
		return $text;
	}

	$nbsp               = "\xC2\xA0";
	$protected_segments = array();
	$text               = russian_typography_protect_text_segments( $text, $protected_segments );

	if ( is_bool( $short_word_mode ) ) {
		$short_word_mode = $short_word_mode ? russian_typography_get_short_word_mode() : RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_OFF;
	} elseif ( null === $short_word_mode ) {
		$short_word_mode = russian_typography_get_short_word_mode();
	} else {
		$short_word_mode = russian_typography_sanitize_short_word_mode( $short_word_mode );
	}

	if ( RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_STANDART === $short_word_mode ) {
		$text = russian_typography_apply_standart_spacing_rules( $text, $nbsp );
	} else {
		$text = russian_typography_apply_legacy_spacing_rules( $text, $nbsp );
	}

	$text = russian_typography_glue_short_words( $text, $short_word_mode );

	if ( RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_STANDART === $short_word_mode ) {
		$nbsp_pattern = preg_quote( $nbsp, '/' );
		$text         = preg_replace(
			'/\b((?:(?:[АаИи])' . $nbsp_pattern . ')?[Вв]от)' . $nbsp_pattern . '(мы|я|вы|ты)' . $nbsp_pattern . '(?=[\p{L}\p{N}])/u',
			'$1 $2' . $nbsp,
			$text
		) ?? $text;
	}

	return russian_typography_restore_text_segments( $text, $protected_segments );
}

/**
 * Processes HTML text nodes while leaving tags, attributes and code-like blocks intact.
 *
 * @param string $html Rendered HTML.
 */
function russian_typography_process_html_nodes( string $html ): string {
	if ( false === strpos( $html, '<' ) ) {
		return russian_typography_process_text( $html );
	}

	$parts          = function_exists( 'wp_html_split' )
		? wp_html_split( $html )
		: preg_split( '/(<[^>]+>)/u', $html, -1, PREG_SPLIT_DELIM_CAPTURE );
	$result         = '';
	$skip_depth     = 0;
	$heading_stack  = array();
	$skip_tags      = array( 'code', 'kbd', 'pre', 'samp', 'script', 'style', 'textarea' );
	$skip_lookup    = array_fill_keys( $skip_tags, true );
	$heading_tags   = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' );
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

				if ( isset( $heading_lookup[ $tag ] ) && array() !== $heading_stack ) {
					for ( $index = count( $heading_stack ) - 1; $index >= 0; --$index ) {
						if ( $heading_stack[ $index ] !== $tag ) {
							continue;
						}

						array_splice( $heading_stack, $index, 1 );
						break;
					}
				}
			} elseif ( preg_match( '#^<\s*([a-z0-9:-]+)(?:\s|>|/)#i', $part, $open_matches ) ) {
				$tag = strtolower( $open_matches[1] );

				if ( isset( $skip_lookup[ $tag ] ) && ! preg_match( '#/>\s*$#', $part ) ) {
					++$skip_depth;
				}

				if ( isset( $heading_lookup[ $tag ] ) && ! preg_match( '#/>\s*$#', $part ) ) {
					$heading_stack[] = $tag;
				}
			}

			$result .= $part;
			continue;
		}

		$current_heading = end( $heading_stack );

		if (
			$skip_depth > 0
			|| ( is_string( $current_heading ) && russian_typography_is_heading_typography_disabled( $current_heading ) )
		) {
			$result .= $part;
			continue;
		}

		$result .= russian_typography_process_text( $part );
	}

	return $result;
}
