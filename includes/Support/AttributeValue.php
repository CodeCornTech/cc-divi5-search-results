<?php

namespace CodeCorn\Divi5SearchResults\Support;

final class AttributeValue {
    /**
     * Read a Divi responsive attribute value from a nested module attribute array.
     *
     * @param array<string, mixed> $attrs
     * @param list<string>         $path
     * @param mixed                $default
     *
     * @return mixed
     */
    public static function get( array $attrs, array $path, $default = null ) {
        $value = $attrs;

        foreach ( $path as $segment ) {
            if ( ! is_array( $value ) || ! array_key_exists( $segment, $value ) ) {
                return $default;
            }

            $value = $value[ $segment ];
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $attrs
     * @param list<string>         $path
     */
    public static function desktop( array $attrs, array $path, string $default = '' ): string {
        $value = self::get(
            $attrs,
            array_merge( $path, array( 'desktop', 'value' ) ),
            $default
        );

        return is_scalar( $value ) ? (string) $value : $default;
    }

    /**
     * @param array<string, mixed> $attrs
     * @param list<string>         $path
     */
    public static function desktopField(
        array $attrs,
        array $path,
        string $field,
        string $default = ''
    ): string {
        $value = self::get(
            $attrs,
            array_merge( $path, array( 'desktop', 'value', $field ) ),
            $default
        );

        return is_scalar( $value ) ? (string) $value : $default;
    }

    public static function isOn( string $value ): bool {
        return 'on' === $value || '1' === $value || 'true' === strtolower( $value );
    }
}
