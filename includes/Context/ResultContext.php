<?php

namespace CodeCorn\Divi5SearchResults\Context;

use CodeCorn\Divi5SearchResults\Rendering\ResultTypeRule;
use WP_Post;

final class ResultContext {
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly string $badgeLabel,
        public readonly string $accentColor
    ) {
    }

    public static function resolve( WP_Post $post, ResultTypeRule $rule ): self {
        $fallback_key = sanitize_key( $post->post_type );

        $context = array(
            'key'          => '' !== $fallback_key ? $fallback_key : 'result',
            'label'        => $rule->singularLabel,
            'badge_label'  => $rule->badgeLabel,
            'accent_color' => $rule->accentColor,
        );

        /**
         * Filter the semantic context assigned to one search result.
         *
         * Context is intentionally independent from post type so integrations can
         * classify two posts of the same WordPress type differently.
         *
         * @param array{key:string,label:string,badge_label:string,accent_color:string} $context
         * @param WP_Post                                                              $post
         * @param ResultTypeRule                                                       $rule
         */
        $filtered = apply_filters( 'cc_d5sr_result_context', $context, $post, $rule );
        $filtered = is_array( $filtered ) ? $filtered : $context;

        $key = sanitize_key( (string) ( $filtered['key'] ?? $context['key'] ) );
        if ( '' === $key ) {
            $key = $context['key'];
        }

        $label = sanitize_text_field( (string) ( $filtered['label'] ?? $context['label'] ) );
        if ( '' === $label ) {
            $label = $context['label'];
        }

        $badge = sanitize_text_field( (string) ( $filtered['badge_label'] ?? $context['badge_label'] ) );
        if ( '' === $badge ) {
            $badge = $label;
        }

        $accent = sanitize_hex_color( (string) ( $filtered['accent_color'] ?? $context['accent_color'] ) );
        if ( ! is_string( $accent ) || '' === $accent ) {
            $accent = $rule->accentColor;
        }

        return new self( $key, $label, $badge, $accent );
    }
}
