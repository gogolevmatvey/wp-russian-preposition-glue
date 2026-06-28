# Improvements

Практичный порядок улучшений плагина:

1. [x] Обновить тексты после добавления постпозитивных частиц.
   В админке и README нельзя писать, что полный режим всегда склеивает короткие
   слова только с последующим словом: `б`, `бы`, `же`, `ли` склеиваются с
   предыдущим словом.

2. [x] Добавить метаданные публичного плагина.
   В plugin header должны быть минимальная версия WordPress, минимальная версия
   PHP, лицензия и ссылка на лицензию. Версию плагина не повышать до финального
   релизного шага перед публикацией.

3. [x] Сделать отдельные проверки для плагина.
   Плагин должен проверяться отдельным PHPCS-стандартом с text domain
   `russian-typography`, а не стандартом темы `history-alive`.

4. [x] Добавить минимальные автотесты типографики.
   Нужны кейсы для коротких служебных слов, постпозитивных частиц, исторических
   сокращений, чисел с единицами, заголовков с выключенной склейкой коротких
   слов и HTML с code-like тегами.

5. [x] Усилить HTML-разбор.
   Текущий разбор регуляркой достаточно прагматичен для обычного WordPress HTML,
   но его нужно покрыть тестами или заменить на более устойчивый подход.

6. [x] Кэшировать regex-паттерны.
   Списки слов можно компилировать в паттерны один раз на режим, чтобы не
   пересобирать их при каждом вызове фильтра.

7. [x] Добавить фильтры для списков слов.
   Для публичного плагина полезно дать разработчикам расширять или сужать списки
   слов без форка.

## Следующие улучшения

1. [x] Добавить `uninstall.php`.
   При удалении плагина через WordPress можно удалить сохраненные options:
   `russian_typography_scope`,
   `russian_typography_skip_heading_short_words`,
   `russian_typography_disabled_headings`,
   `russian_typography_disable_title_typography`,
   `russian_typography_short_word_mode`,
   `russian_typography_soft_max_next_word_length`.

2. [x] Добавить `readme.txt` для WordPress.org-формата.
   GitHub `README.md` оставить для репозитория, а `readme.txt` использовать для
   метаданных каталога WordPress: `Stable tag`, `Requires at least`,
   `Tested up to`, `Requires PHP`, `License`.

3. [x] Добавить `LICENSE`.
   В репозитории должен быть отдельный файл с текстом GPLv2.

4. [x] Подключить тесты к PHPCS полностью.
   Сейчас `composer run check:plugin` проверяет PHPCS главного файла плагина.
   Тесты вынесены в `tests/russian-typography`, PHPCS проверяет production-код
   плагина отдельным стандартом из `tools/phpcs-russian-typography.xml.dist`.

5. [ ] Добавить `Update URI`.
   Пропущено сознательно: плагин планируется публиковать в каталоге
   WordPress.org, поэтому `Update URI` сейчас не нужен.

6. [x] Подготовить интерфейс к локализации.
   Исходные пользовательские строки в коде переведены на английский и обёрнуты
   в gettext-функции WordPress с text domain `russian-typography`. Локальные
   `.po`/`.mo`, `load_plugin_textdomain()`, `Domain Path` и папка `languages`
   не добавлены: после публикации русский перевод должен приходить через
   языковые пакеты WordPress.org.

7. [x] Расширить тесты edge cases.
   Проверить верхний регистр, HTML-комментарии, атрибуты с `>`, self-closing
   tags, вложенные `code`/`pre`, `soft` с длинным следующим словом и режим `off`.

8. [x] Подумать о split на файлы.
   Код разделен на `includes/settings.php`, `includes/typography.php`,
   `includes/hooks.php`; главный файл оставлен загрузчиком с plugin header,
   константами и подключением модулей.

## Подготовка к WordPress.org

1. [x] Добавить команду для официального Plugin Check.
   Добавлена `composer run plugin-check:plugin`. Команда не включена в обычный
   `check:plugin`, потому что требует установленный WP-CLI и плагин Plugin Check.

2. [x] Добавить screenshots в `readme.txt`.
   В `readme.txt` добавлена секция `Screenshots`.

3. [x] Добавить PNG-assets для страницы плагина.
   Добавлены PNG-версии иконки, баннера и двух скриншотов. SVG-assets удалены:
   баннер и иконку лучше генерировать как PNG/JPG, а скриншоты заменить на
   настоящие снимки интерфейса.

4. [x] Добавить локальную проверку `readme.txt`.
   `tests/russian-typography/readme.php` проверяет ключевые поля, секции и
   наличие assets.

5. [x] Добавить тест uninstall-сценария.
   `tests/russian-typography/uninstall.php` проверяет, что удаляются текущие
   option keys плагина и старые `wp_russian_typography_*` ключи.

6. [x] Добавить тест idempotency.
   Повторная обработка plain text и HTML не должна менять уже обработанный текст.

7. [x] Добавить тесты mixed HTML.
   Проверяются ссылки, `figure`, `figcaption`, атрибуты с `>` и вложенная разметка.

8. [x] Улучшить доступность админ-страницы.
   Радиокнопки, checkbox и number input получили явные `id`, `for` и
   `aria-describedby`.

9. [x] Убрать `wp_` из публичного PHP API.
   Функции, константы, filters, settings group и option keys приведены к
   `russian_typography_*` / `RUSSIAN_TYPOGRAPHY_*`.

10. [x] Добавить release-скрипт.
    `composer run build:plugin` собирает `dist/russian-typography.zip` без
    `IMPROVEMENTS.md`, тестов и dev-конфигов.

11. [x] Разделить WordPress.org assets и runtime-код плагина.
    Иконки, баннер и скриншоты вынесены в
    `wordpress-org-assets/russian-typography`, а build-скрипт копирует их в
    `dist/wordpress-org-assets`.

12. [x] Добавить проверку production package.
    `composer run check:plugin` теперь собирает пакет и проверяет структуру
    `dist/russian-typography`.

13. [x] Добавить тесты HTML entities и Unicode-пунктуации.
    Проверяются сохранение `&nbsp;`, `&#160;`, `&amp;`, а также кавычки, тире,
    точка с запятой и скобки рядом с короткими словами.

14. [x] Удалять старые `wp_russian_typography_*` настройки без миграции.
    При активации и uninstall старые ключи удаляются, значения не переносятся в
    новые `russian_typography_*`.

15. [x] Не трогать URL/email-like текст.
    URL и email-like фрагменты временно защищаются перед regex-обработкой и
    восстанавливаются после неё.
