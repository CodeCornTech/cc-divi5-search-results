# CC Divi 5 Search Results

> Native Divi 5 search results. The Blog Module workaround ends here.

A public Divi 5 extension for type-aware WordPress search results. The native parent module owns the query, layout and navigation; native child modules are repeatable post-type rules. The optional shortcode calls the same PHP resolver and renderer.

## Current implementation

The `0.1.0` development branch currently includes:

- safe plugin bootstrap with Composer PSR-4 autoloading and a source-checkout fallback autoloader;
- reuse of the real current WordPress search query without calling `query_posts()` or replacing global `$wp_query`;
- isolated `WP_Query` resolution for Visual Builder previews, normal pages and shortcode use;
- parent module `codecorn/search-results`, displayed as **CC Search Results**;
- child module `codecorn/search-result-type`, displayed as **CC Result Type**;
- a dynamic post-type selector populated from the public post types registered by the current WordPress site;
- repeatable post-type rules for labels, badge, accent, image, excerpt, date and CTA;
- optional result summary and optional search form above the first result;
- three output presets: editorial grid, compact vertical list and classic image card;
- responsive grid columns controlled independently for desktop, tablet and phone;
- progressive AJAX pagination with loading state, URL history, focus restoration and classic-link fallback;
- Italian frontend defaults for search, count, empty state, pagination and CTA labels;
- shared rendering used by the native module and `[cc_divi5_search_results]`;
- responsive frontend styling for form, cards, metadata, pagination, empty state and reduced motion;
- Visual Builder TypeScript sources and webpack build;
- deterministic runtime packaging with build manifest and SHA-256 checksum;
- VM deployment for host and Docker WordPress installations with backup and rollback;
- a Barbagia Musei wrapper pinned to the real `docker02` Compose stack and `wp_cron` service.

The Visual Builder bundle and `modules-json/` metadata are generated locally and are not committed. Run the build before module insertion, Visual Builder testing or packaging.

## Native module structure

```text
CC Search Results (codecorn/search-results)
└── CC Result Type (codecorn/search-result-type)
    ├── post
    ├── page
    └── any registered public custom post type
```

`CC Result Type` children are configuration objects. They do not render independent frontend cards. The parent reads their saved Divi attributes and applies them to posts returned by the search query.

## Parent controls

### Query

- current WordPress search query or isolated query;
- explicit search term for isolated queries and Builder previews;
- results per page.

### Layout and navigation

- show or hide the result count;
- show or hide the complete search form above the results;
- output preset;
- responsive columns for desktop, tablet and phone;
- AJAX pagination on or off.

### Empty state

- title;
- message.

## Output presets

### Editorial grid — `grid`

Metadata and badge appear before the title. Cards have equal-height content, optional image, excerpt and CTA. The responsive column control is applied at each Divi breakpoint.

### Compact vertical list — `compact-list`

One low horizontal item per row, designed for podcast/archive-like sections. The image sits on the left, content remains compact, the excerpt is clamped and the mobile item reduces to a small thumbnail with a two-line title.

The columns control is ignored by this preset because it is intentionally a one-column vertical list.

### Classic image card — `classic-card`

The image appears above the content. The title is the first element immediately below the image, followed by metadata, description and CTA. The responsive column control is applied at each Divi breakpoint.

## Result-type controls

- public WordPress post type selected from the current site's registered list;
- singular, plural and badge labels;
- rule priority;
- accent color;
- image, excerpt and date visibility;
- excerpt word count;
- CTA label.

The post-type selector lists the translated WordPress label and technical slug, for example `Articoli (post)`, `Pagine (page)` or `Eventi (eventi)`. Attachments are excluded. If Builder data is unavailable, the selector falls back to `post` and `page`; it never becomes a comma-separated free-text field.

Integrations can change the exposed post types:

```php
add_filter(
    'cc_d5sr_builder_post_type_options',
    function ( array $options, array $post_type_objects ): array {
        return $options;
    },
    10,
    2
);
```

Missing labels fall back to the registered post-type labels. Missing rules fall back to a generic rule derived from the result's post type. Existing child modules that still store the old exact default CTA `View result` render as `Vedi risultato`; any custom CTA text remains unchanged.

## Query contract

For `source=current`, the module reads the already-resolved main WordPress search query. It does not mutate it.

For `source=isolated`, the plugin creates its own query using the search term, configured result types, result limit and current page. Integrations can adjust only that isolated query:

```php
add_filter( 'cc_d5sr_query_args', function ( array $query_args, array $options ): array {
    return $query_args;
}, 10, 2 );
```

## AJAX pagination contract

AJAX pagination is progressive enhancement rather than a second query endpoint:

1. pagination still renders normal WordPress links;
2. the frontend runtime fetches the real target page;
3. it locates the same module instance in the returned document;
4. it replaces only that module's inner result output;
5. it updates browser history, scrolls to the module and restores keyboard focus;
6. a failed request or missing module instance falls back to the normal page navigation.

This preserves the current WordPress search query, Theme Builder layout, third-party query filters and canonical paginated URLs. Disabling **AJAX pagination** leaves the normal links untouched.

## Optional shortcode

```text
[cc_divi5_search_results]
```

Supported attributes:

```text
source="current|isolated"
search="museum"
post_types="post,page,event"
posts_per_page="10"
preset="grid|compact-list|classic-card"
columns="3"
columns_tablet="2"
columns_phone="1"
ajax_pagination="on|off"
show_summary="on|off"
show_search_form="on|off"
show_image="on|off"
show_excerpt="on|off"
show_date="on|off"
excerpt_length="28"
cta_label="Vedi risultato"
```

The shortcode is an adapter, not the primary implementation.

## Development

Requirements:

- WordPress 6.6+;
- Divi 5.9.0 reference target for this increment;
- PHP 8.1+;
- Node.js 18+ and npm 10+;
- Composer.

Install from the committed locks and build:

```bash
composer install
npm ci
npm run check
npm run build
```

Rebuild the installed npm tree without changing the committed graph:

```bash
npm run reset-install
```

`reset-install` removes only `node_modules`, then runs `npm ci`. Changes to `package.json` require an intentional `npm install`, review of `package-lock.json` and inclusion of both files in the same PR. The same rule applies to `composer.json` and `composer.lock`.

Validation:

```bash
composer check:syntax
npm run check:types
npm run check:json
node --check assets/js/search-results.js
```

`ts-loader` performs type checking during `npm run build`; it is not configured with `transpileOnly`. The TypeScript configuration follows the compatibility surface used by the official Divi 5 example modules.

The published Divi compatibility aliases contain incompatible development-only React peer ranges. `.npmrc` therefore commits `legacy-peer-deps=true` and `engine-strict=true`. Do not run `npm audit fix --force` because it can replace Divi-compatible development dependencies with breaking versions.

Generated build files:

```text
scripts/bundle.js
styles/vb-bundle.css
modules-json/search-results/module.json
modules-json/search-result-type/module.json
```

Local proprietary references under `.reference/` are ignored and must never be committed.

## Runtime package

```bash
composer package
```

The runtime archive contains:

```text
cc-divi5-search-results.php
assets/css/search-results.css
assets/js/search-results.js
includes/
modules/
shortcodes/
languages/
vendor/
modules-json/
scripts/bundle.js
styles/vb-bundle.css
README.md
CHANGELOG.md
LICENSE
build-manifest.json
```

It excludes `src/`, `node_modules/`, development manifests, local references and Git metadata. A sibling `.sha256` file records the archive checksum.

Packaging requires committed lockfiles, a clean working tree unless explicitly overridden, and successful Composer, npm, TypeScript, JSON, JavaScript and PHP validation.

## VM deployment

Generic commands:

```bash
composer deploy:check
composer deploy
```

`deploy:check` performs local packaging and validation without SSH or remote changes. `deploy` uploads the runtime archive, verifies SHA-256, creates a timestamped backup, stages and lints the plugin, switches directories, activates through WP-CLI when enabled and rolls back on failure.

### Generic Docker example

`CC_D5SR_DEPLOY_CONTAINER` is a container name or ID, never a Compose service name.

```bash
export CC_D5SR_DEPLOY_TARGET="user@example-host"
export CC_D5SR_DEPLOY_MODE="docker"
export CC_D5SR_DEPLOY_CONTAINER="wordpress-container-name"
export CC_D5SR_REMOTE_WP_ROOT="/var/www/html"

composer deploy:check
composer deploy
```

### Barbagia Musei on `docker02`

Run from the local repository:

```bash
cd "$GH_PATH/cc-divi5-search-results" || return 1

composer deploy:barbagia:check
composer deploy:barbagia
```

Pinned deployment contract:

```text
Local repository:    $GH_PATH/cc-divi5-search-results
SSH target:          docker02
Remote Compose file: /home/fgirolami/docker/barbagiamusei/compose.yaml
Compose service:     wp_cron
WordPress root:      /var/www/html
Activation:          enabled
Backup retention:    10
```

The wrapper resolves the running container with the absolute Compose path:

```bash
docker compose \
    -f /home/fgirolami/docker/barbagiamusei/compose.yaml \
    ps -q wp_cron
```

The current container name is `cron_barbagiamusei`, but it is not hardcoded. Docker runs on `docker02`; local Docker Desktop is irrelevant.

The apply command is non-interactive and requires:

```bash
ssh -o BatchMode=yes docker02 true
ssh -o BatchMode=yes docker02 docker info
```

The first check verifies SSH public-key authentication. The second verifies that remote user `fgirolami` can access `/var/run/docker.sock` without sudo. The deploy script never stores or pipes SSH or sudo passwords.

One-time Docker group bootstrap when required:

```bash
ssh -tt docker02 'sudo usermod -aG docker fgirolami'
ssh -O exit docker02 2>/dev/null || true
```

Open a new SSH session after changing group membership. Membership in the Docker group grants root-equivalent Docker-host control; this requirement is explicit because the deployment manages containers.

Optional Barbagia overrides:

```text
CC_D5SR_DEPLOY_TARGET       default docker02
CC_D5SR_COMPOSE_FILE        default /home/fgirolami/docker/barbagiamusei/compose.yaml
CC_D5SR_COMPOSE_SERVICE     default wp_cron
CC_D5SR_REMOTE_WP_ROOT      default /var/www/html
```

## Not implemented yet

The README does not present these as available:

- live query results inside the Visual Builder canvas;
- per-type custom-field and taxonomy mappings;
- type filters with counts;
- query-term highlighting;
- per-rule image fallback and configurable image ratios;
- automated GitHub release publication.

## Documentation rule

Every PR that changes behavior, controls, requirements, generated paths, build commands, dependency requirements, deployment behavior or public APIs must update this README in the same PR. Documentation may describe included code only; planned behavior belongs under **Not implemented yet**.

## License

MIT © 2026 CodeCorn Technology S.R.L.S.
