<?php

namespace CodeCorn\Divi5SearchResults\Query;

use WP_Post;

final class QueryResult {
    /**
     * @param list<WP_Post> $posts
     */
    public function __construct(
        public readonly array $posts,
        public readonly int $foundPosts,
        public readonly int $maxPages,
        public readonly int $currentPage,
        public readonly string $searchTerm
    ) {
    }
}
