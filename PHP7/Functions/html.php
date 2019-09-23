<?php

declare(strict_types = 1);

/**
 * Remove specified tag, but not it's content.
 *
 * @param string $html
 * @param string $tag
 * @return string HTML without specified tag.
 */
function remove_tag(string $html, string $tag): string
{
    $tag = preg_quote($tag);

    // Pattern example: "/<title[^>]*>|<\/title>/i";
    $pattern = '/<' . $tag . '[^>]*>|<\/' . $tag . '>/i';
    $replacement = preg_replace($pattern, '', $html);

    return !is_null($replacement) ? $replacement : $html;
}

/**
 * Remove specified tag and it's content.
 *
 * @param string $html
 * @param string $tag
 * @return string HTML without specified tag.
 */
function remove_tag_content(string $html, string $tag): string
{
    $tag = preg_quote($tag);

    // Pattern example: "/<(title)[^>]*>.*?<\/\1>/si"
    $pattern = '/<(' . $tag . ')[^>]*>.*?<\/\1>/si';
    $replacement = preg_replace($pattern, '', $html);

    return !is_null($replacement) ? $replacement : $html;
}
