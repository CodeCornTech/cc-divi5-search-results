<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$files = [
    'engine' => $root . '/includes/Api/SearchEngine.php',
    'set' => $root . '/includes/Api/SearchResultSet.php',
    'item' => $root . '/includes/Api/SearchResultItem.php',
    'context' => $root . '/includes/Context/ResultContext.php',
    'renderer' => $root . '/includes/Rendering/SearchResultsRenderer.php',
];

function cc_d5sr_api_assert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException('PUBLIC API CONTRACT FAILED: ' . $message);
    }

    echo 'OK: ' . $message . "\n";
}

foreach ($files as $name => $file) {
    cc_d5sr_api_assert(is_file($file), $name . ' file exists');
}

$engine = (string) file_get_contents($files['engine']);
$set = (string) file_get_contents($files['set']);
$item = (string) file_get_contents($files['item']);
$context = (string) file_get_contents($files['context']);
$renderer = (string) file_get_contents($files['renderer']);

cc_d5sr_api_assert(
    str_contains($engine, 'final class SearchEngine')
        && str_contains($engine, 'public function resolve(')
        && str_contains($engine, 'SearchQueryResolver')
        && str_contains($engine, 'ResultContext::resolve'),
    'SearchEngine exposes the public resolver and delegates to canonical services'
);

cc_d5sr_api_assert(
    str_contains($set, 'public readonly array $items')
        && str_contains($set, 'public readonly int $foundPosts')
        && str_contains($set, 'public readonly int $maxPages')
        && str_contains($set, 'public readonly int $currentPage')
        && str_contains($set, 'public readonly string $searchTerm'),
    'SearchResultSet exposes canonical pagination and term data'
);

foreach (
    [
        '$contextKey',
        '$contextLabel',
        '$badgeLabel',
        '$accentColor',
        '$title',
        '$url',
        '$excerpt',
        '$imageUrl',
        '$dateLabel',
        '$ctaLabel',
    ] as $field
) {
    cc_d5sr_api_assert(
        str_contains($item, $field),
        'SearchResultItem exposes ' . $field
    );
}

cc_d5sr_api_assert(
    str_contains($context, 'cc_d5sr_result_context')
        && str_contains($context, "'key'")
        && str_contains($context, "'label'")
        && str_contains($context, "'badge_label'")
        && str_contains($context, "'accent_color'"),
    'semantic result context is filterable and independent from post type'
);

cc_d5sr_api_assert(
    str_contains($renderer, 'ResultContext::resolve')
        && str_contains($renderer, 'data-result-context=')
        && str_contains($renderer, '--cc-d5sr-accent:'),
    'native renderer consumes the same context resolver as external integrations'
);

echo "PUBLIC SEARCH API CONTRACT: OK\n";
