<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$script = $root . '/bin/cc-deploy-local';
$composer = $root . '/composer.json';
$readme = $root . '/README.md';

function cc_d5sr_local_deploy_assert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException('LOCAL DEPLOY CONTRACT FAILED: ' . $message);
    }

    echo 'OK: ' . $message . "\n";
}

foreach ([$script, $composer, $readme] as $file) {
    cc_d5sr_local_deploy_assert(is_file($file), basename($file) . ' exists');
}

$source = (string) file_get_contents($script);
$config = json_decode(
    (string) file_get_contents($composer),
    true,
    512,
    JSON_THROW_ON_ERROR
);
$docs = (string) file_get_contents($readme);

cc_d5sr_local_deploy_assert(
    ($config['scripts']['deploy:local:check'] ?? '') === 'bash bin/cc-deploy-local --dry-run'
        && ($config['scripts']['deploy:local'] ?? '') === 'bash bin/cc-deploy-local --apply',
    'Composer exposes dry-run and apply commands'
);

foreach (
    [
        '$HOME/Documents/barbagiamusei-lab-wp',
        'wp_cron',
        '/var/www/html',
        'cc-divi5-search-results',
        '_BACKUPS/cc-divi5-search-results',
    ] as $token
) {
    cc_d5sr_local_deploy_assert(
        str_contains($source, $token),
        'local deploy contains default target token ' . $token
    );
}

cc_d5sr_local_deploy_assert(
    str_contains($source, 'bash bin/cc-deploy-vm --package-only')
        && str_contains($source, 'CC_D5SR_LOCAL_REQUIRE_CLEAN')
        && str_contains($source, 'BACKUP')
        && str_contains($source, 'docker compose')
        && str_contains($source, 'plugin activate cc-divi5-search-results')
        && str_contains($source, 'byte-identical')
        && str_contains($source, 'CodeCorn\\\\Divi5SearchResults\\\\Api\\\\SearchEngine'),
    'local deploy packages, backs up, mirrors, activates and verifies the public API'
);

cc_d5sr_local_deploy_assert(
    str_contains($source, 'case "$plugin_path" in')
        && str_contains($source, '"$wp_root"/wp-content/plugins/*'),
    'plugin target is constrained to the WordPress plugins directory'
);

cc_d5sr_local_deploy_assert(
    str_contains($docs, '## Local LAB deployment')
        && str_contains($docs, 'composer deploy:local:check')
        && str_contains($docs, 'composer deploy:local'),
    'README documents the local LAB contract'
);

echo "LOCAL DEPLOY CONTRACT: OK\n";
