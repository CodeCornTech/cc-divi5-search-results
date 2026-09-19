<?php

namespace CodeCorn\Divi5SearchResults\Api;

use CodeCorn\Divi5SearchResults\Context\ResultContext;
use CodeCorn\Divi5SearchResults\Rendering\ResultTypeRule;
use WP_Post;

final class SearchResultItem {
    public function __construct(
        public readonly WP_Post $post,
        public readonly int $id,
        public readonly string $postType,
        public readonly string $contextKey,
        public readonly string $contextLabel,
        public readonly string $badgeLabel,
        public readonly string $accentColor,
        public readonly string $title,
        public readonly string $url,
        public readonly string $excerpt,
        public readonly int $imageId,
        public readonly string $imageUrl,
        public readonly string $dateIso,
        public readonly string $dateLabel,
        public readonly string $ctaLabel,
        public readonly bool $showImage,
        public readonly bool $showExcerpt,
        public readonly bool $showDate
    ) {
    }

    public static function fromPost(
        WP_Post $post,
        ResultTypeRule $rule,
        ResultContext $context
    ): self {
        $url = get_permalink( $post );
        $url = is_string( $url ) ? $url : '';

        $title = trim( wp_strip_all_tags( get_the_title( $post ) ) );

        $excerpt = '';
        if ( $rule->showExcerpt && 0 < $rule->excerptLength ) {
            $excerpt = wp_trim_words(
                wp_strip_all_tags( get_the_excerpt( $post ) ),
                $rule->excerptLength,
                '…'
            );
        }

        $image_id  = $rule->showImage ? (int) get_post_thumbnail_id( $post ) : 0;
        $image_url = 0 < $image_id ? wp_get_attachment_image_url( $image_id, 'large' ) : '';
        $image_url = is_string( $image_url ) ? $image_url : '';

        $date_iso   = $rule->showDate ? (string) get_the_date( DATE_W3C, $post ) : '';
        $date_label = $rule->showDate ? (string) get_the_date( '', $post ) : '';

        return new self(
            $post,
            (int) $post->ID,
            $post->post_type,
            $context->key,
            $context->label,
            $context->badgeLabel,
            $context->accentColor,
            $title,
            $url,
            $excerpt,
            $image_id,
            $image_url,
            $date_iso,
            $date_label,
            $rule->ctaLabel,
            $rule->showImage,
            $rule->showExcerpt,
            $rule->showDate
        );
    }
}
