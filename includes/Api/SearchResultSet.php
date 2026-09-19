<?php

namespace CodeCorn\Divi5SearchResults\Api;

final class SearchResultSet {
    /**
     * @param list<SearchResultItem> $items
     * @param list<SearchFacet>      $facets
     */
    public function __construct(
        public readonly array $items,
        public readonly int $foundPosts,
        public readonly int $maxPages,
        public readonly int $currentPage,
        public readonly string $searchTerm,
        public readonly array $facets = array()
    ) {
    }
}
