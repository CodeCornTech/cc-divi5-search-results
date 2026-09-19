<?php

namespace CodeCorn\Divi5SearchResults\Api;

final class SearchFacet {
    public function __construct(
        public readonly string $postType,
        public readonly string $contextKey,
        public readonly string $contextLabel,
        public readonly string $badgeLabel,
        public readonly string $accentColor,
        public readonly int $count
    ) {
    }
}
