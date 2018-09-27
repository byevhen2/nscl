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
    // Pattern example: "/<title[^>]*>|<\/title>/i";
    $pattern = '/<' . $tag . '[^>]*>|<\/' . $tag . '>/i';
    return preg_replace($pattern, '', $html);
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
    // Pattern example: "/<(title)[^>]*>.*?<\/\1>/si"
    $pattern = '/<(' . $tag . ')[^>]*>.*?<\/\1>/si';
    return preg_replace($pattern, '', $html);
}
