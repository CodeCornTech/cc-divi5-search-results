<?php

namespace CodeCorn\Divi5SearchResults\Rendering;

use CodeCorn\Divi5SearchResults\Support\AttributeValue;
use WP_Post_Type;

final class ResultTypeRule {
    public function __construct(
        public readonly string $postType,
        public readonly string $singularLabel,
        public readonly string $pluralLabel,
        public readonly string $badgeLabel,
        public readonly string $accentColor,
        public readonly int $priority,
        public readonly bool $showImage,
        public readonly bool $showExcerpt,
        public readonly bool $showDate,
        public readonly int $excerptLength,
        public readonly string $ctaLabel
    ) {
    }

    /**
     * @param array<string, mixed> $attrs
     */
    public static function fromDiviAttrs( array $attrs ): self {
        $values = AttributeValue::get(
            $attrs,
            array( 'rule', 'innerContent', 'desktop', 'value' ),
            array()
        );
        $values = is_array( $values ) ? $values : array();

        return self::fromFlatArray( $values );
    }

    /**
     * @param array<string, mixed> $values
     */
    public static function fromFlatArray( array $values ): self {
        $post_type = sanitize_key( (string) ( $values['postType'] ?? $values['post_type'] ?? 'post' ) );
        $object    = post_type_exists( $post_type ) ? get_post_type_object( $post_type ) : null;

        $singular = self::label( $values['singularLabel'] ?? $values['singular_label'] ?? '', $object, false, $post_type );
        $plural   = self::label( $values['pluralLabel'] ?? $values['plural_label'] ?? '', $object, true, $singular );
        $badge    = sanitize_text_field( (string) ( $values['badgeLabel'] ?? $values['badge_label'] ?? $singular ) );
        $accent   = sanitize_hex_color( (string) ( $values['accentColor'] ?? $values['accent_color'] ?? '#2b2f36' ) );

        return new self(
            $post_type,
            $singular,
            $plural,
            $badge ?: $singular,
            $accent ?: '#2b2f36',
            max( 0, (int) ( $values['priority'] ?? 10 ) ),
            self::flag( $values['showImage'] ?? $values['show_image'] ?? 'on' ),
            self::flag( $values['showExcerpt'] ?? $values['show_excerpt'] ?? 'on' ),
            self::flag( $values['showDate'] ?? $values['show_date'] ?? 'on' ),
            max( 0, min( 200, (int) ( $values['excerptLength'] ?? $values['excerpt_length'] ?? 28 ) ) ),
            sanitize_text_field( (string) ( $values['ctaLabel'] ?? $values['cta_label'] ?? __( 'Vedi risultato', 'cc-divi5-search-results' ) ) )
        );
    }

    public static function fallback( string $post_type ): self {
        return self::fromFlatArray( array( 'postType' => $post_type ) );
    }

    /**
     * @param mixed $value
     */
    private static function flag( $value ): bool {
        if ( is_bool( $value ) ) {
            return $value;
        }

        return AttributeValue::isOn( (string) $value );
    }

    /**
     * @param mixed $candidate
     */
    private static function label( $candidate, ?WP_Post_Type $object, bool $plural, string $fallback ): string {
        $candidate = sanitize_text_field( (string) $candidate );

        if ( '' !== $candidate ) {
            return $candidate;
        }

        if ( $object instanceof WP_Post_Type ) {
            return $plural ? $object->labels->name : $object->labels->singular_name;
        }

        return ucfirst( str_replace( array( '-', '_' ), ' ', $fallback ) );
    }
}
