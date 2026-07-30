<?php

namespace CodeCorn\Divi5SearchResults\Support;

final class AttributeValue {
    /**
     * Read a value from a nested module attribute array.
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
    public static function breakpoint( array $attrs, array $path, string $breakpoint, string $default = '' ): string {
        $value = self::get(
            $attrs,
            array_merge( $path, array( $breakpoint, 'value' ) ),
            $default
        );

        return is_scalar( $value ) ? (string) $value : $default;
    }

    /**
     * @param array<string, mixed> $attrs
     * @param list<string>         $path
     */
    public static function desktop( array $attrs, array $path, string $default = '' ): string {
        return self::breakpoint( $attrs, $path, 'desktop', $default );
    }

    /**
     * @param array<string, mixed> $attrs
     * @param list<string>         $path
     */
    public static function breakpointField(
        array $attrs,
        array $path,
        string $breakpoint,
        string $field,
        string $default = ''
    ): string {
        $value = self::get(
            $attrs,
            array_merge( $path, array( $breakpoint, 'value', $field ) ),
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
        return self::breakpointField( $attrs, $path, 'desktop', $field, $default );
    }

    /**
     * Read one responsive field with desktop -> tablet -> phone fallback.
     *
     * @param array<string, mixed> $attrs
     * @param list<string>         $path
     *
     * @return array{desktop:string,tablet:string,phone:string}
     */
    public static function responsiveField(
        array $attrs,
        array $path,
        string $field,
        string $desktop_default = '',
        string $tablet_default = '',
        string $phone_default = ''
    ): array {
        $desktop = self::breakpointField( $attrs, $path, 'desktop', $field, $desktop_default );
        $tablet  = self::breakpointField( $attrs, $path, 'tablet', $field, $tablet_default ?: $desktop );
        $phone   = self::breakpointField( $attrs, $path, 'phone', $field, $phone_default ?: $tablet );

        return array(
            'desktop' => $desktop,
            'tablet'  => $tablet,
            'phone'   => $phone,
        );
    }

    public static function isOn( string $value ): bool {
        return 'on' === $value || '1' === $value || 'true' === strtolower( $value );
    }
}
