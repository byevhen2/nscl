<?php

declare(strict_types = 1);

/**
 * @param int $id
 * @param string $postType Optional. "any" by default.
 * @param bool $returnOriginal Optional. True by default.
 * @return int
 */
function get_current_id(int $id, $postType = 'any', bool $returnOriginal = true)
{
    return translate_id($id, $postType, get_current_language(), $returnOriginal);
}

/**
 * @return string Language code like "en", "uk", "ru" etc.
 */
function get_current_language(): string
{
    return apply_filters('wpml_current_language', null);
}

/**
 * Get preferred language for the current user.
 *
 * User can set custom language in Users -> User Profile -> Language instead
 * of using of the default WordPress language from Settings -> General
 * -> Site Language.
 *
 * @return string Language code like "en", "uk", "ru" etc.
 */
function get_current_user_language(): string
{
    if (is_admin()) {
        return get_language_code_from_locale(get_current_user_locale());
    } else {
        return get_current_language();
    }
}

/**
 * @return string Locale like "en", "uk", "ru_RU" etc.
 */
function get_current_user_locale(): string
{
    if (function_exists('get_user_locale')) {
        return get_user_locale(); // Available since WordPress 4.7
    } else {
        $locale = get_user_meta(get_current_user_id(), 'locale', true);

        if (!empty($locale)) {
            return $locale;
        } else {
            return get_locale();
        }
    }
}

/**
 * @param int $id
 * @param string $postType Optional. "any" by default.
 * @param bool $returnOriginal Optional. True by default.
 * @return int
 */
function get_default_id(int $id, $postType = 'any', bool $returnOriginal = true)
{
    return translate_id($id, $postType, get_default_language(), $returnOriginal);
}

/**
 * @return string Language code like "en", "uk", "ru" etc.
 */
function get_default_language(): string
{
    return apply_filters('wpml_default_language', null);
}

function get_language_code_from_locale(string $locale): string
{
    global $sitepress;

    $code = isset($sitepress) ? $sitepress->get_language_code_from_locale($locale) : null;

    if (!is_null($code)) {
        return $code;
    } else {
        $language = explode('_', $locale); // "en" => ["en"], "ru_RU" -> ["ru", "RU"]
        return $language[0];
    }
}

/**
 * @return string Language code like "en", "uk", "ru" etc.
 */
function get_wp_language(): string
{
    return get_language_code_from_locale(get_locale());
}

function is_active_language(string $language): bool
{
    return apply_filters('wpml_language_is_active', null, $language);
}

/**
 * @param int $id
 * @param string $postType Optional. "any" by default.
 * @return boolean
 */
function is_default_id(int $id, $postType = 'any'): bool
{
    return $id == get_default_id($id, $postType);
}

function is_translatable_post_type(string $postType): bool
{
    return (bool)apply_filters('wpml_is_translated_post_type', null, $postType);
}

function is_wpml_active(): bool
{
    return defined('ICL_SITEPRESS_VERSION');
}

/**
 * @param string $language Optional. Default language by default.
 * @return string Current user language (before switch).
 */
function switch_language($language = null): string
{
    if (is_null($language)) {
        $language = get_default_language();
    }

    $currentLanguage = get_current_user_language();

    do_action('wpml_switch_language', $language);

    return $currentLanguage;
}

/**
 * @return The language before switch (current language).
 */
function switch_to_all_languages(): string
{
    return switch_language('all');
}

/**
 * @return The language before switch (current language).
 */
function switch_to_default_language(): string
{
    return switch_language(get_default_language());
}

/**
 * @param int $id
 * @param string $postType Optional. "any" by default.
 * @param string $language Optional. Default language by default.
 * @param bool $returnOriginal Optional. True by default.
 * @return int
 */
function translate_id(int $id, $postType = 'any', $language = null, bool $returnOriginal = true)
{
    return apply_filters('wpml_object_id', $id, $postType, $returnOriginal, $language);
}

/**
 * @param int $id
 * @param string $language Optional. Default language by default.
 * @return int
 */
function translate_page_id(int $id, $language = null)
{
    return translate_id($id, 'page', $language);
}

/**
 * @param int $id
 * @param string $language Optional. Default language by default.
 * @return int
 */
function translate_post_id(int $id, $language = null)
{
    return translate_id($id, 'any', $language);
}

/**
 * @param string $name
 * @param string $value
 * @param string $context For example - plugin name.
 * @param string $language Optional. Default language by default.
 * @return string
 */
function translate_string(string $name, string $value, string $context, $language = null): string
{
    return apply_filters('wpml_translate_single_string', $value, $context, $name, $language);
}

/**
 * Get translation IDs of the post on all languages.
 *
 * @param int $id ID of the post on any language.
 * @param string $postType Optional. "post" or custom post type. "post" by default.
 * @return array [Language => ID]. For example: ["en" => 768, "uk" => 771].
 */
function translate_posts_to_all_languages(int $id, string $postType = 'post'): array
{
    return translate_to_all_languages($id, 'post_' . $postType);
}

/**
 * Get translation IDs of the taxonomy term on all languages.
 *
 * @param int $id ID of the taxonomy term on any language.
 * @param string $postType Optional. Taxonomy name. "category" by default.
 * @return array [Language => ID]. For example: ["en" => 768, "uk" => 771].
 */
function translate_taxonomies_to_all_languages(int $id, string $taxonomyType = 'category'): array
{
    return translate_to_all_languages($id, 'tax_' . $taxonomyType);
}

/**
 * Get translation IDs of the object on all languages.
 *
 * <b>Note:</b> better use functions <i>translate_posts_to_all_languages()</i>
 * or <i>translate_taxonomies_to_all_languages()</i> to get IDs of the post or
 * the taxonomy.
 *
 * @param int $id ID of the object on any language.
 * @param string $objectType Object type with prefix "post_", "tax_" etc.
 * @return array [Language => ID]. For example: ["en" => 768, "uk" => 771].
 *
 * @global \SitePress $sitepress
 */
function translate_to_all_languages(int $id, string $objectType): array
{
    global $sitepress;

    // Return current ID (for current language) if WPML not installed or not ready
    if (!isset($sitepress)) {
        $currentLanguage = get_current_user_language();
        return [$currentLanguage => $id];
    }

    // Get all translations by trid
    $trid = $sitepress->get_element_trid($id, $objectType);
    $translations = $sitepress->get_element_translations($trid, $objectType);

    $ids = array_combine(
        wp_list_pluck($translations, 'language_code'),
        wp_list_pluck($translations, 'element_id')
    );

    return $ids;
}
