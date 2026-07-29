# CC Divi 5 Search Results

> Native Divi 5 search results. The Blog Module workaround ends here.

A public Divi 5 extension for type-aware WordPress search results. The native parent module owns the query and result layout; native child modules act as repeatable rules for individual post types. The optional shortcode calls the same PHP query and rendering services.

## Current implementation

The `0.1.0` development branch currently includes:

- safe plugin bootstrap with Composer PSR-4 autoloading and a source-checkout fallback autoloader;
- isolated search-query resolution that never calls `query_posts()` and never replaces the global `$wp_query`;
- reuse of the real current WordPress search query when the module runs on a search-results request;
- an isolated `WP_Query` fallback for Visual Builder previews, non-search pages and shortcode use;
- the native Divi 5 parent module `codecorn/search-results`;
- the native configuration child module `codecorn/search-result-type`;
- repeatable post-type rules for labels, badge, accent, image, excerpt, date and CTA;
- accessible result count, search form, result cards, pagination and empty state;
- shared rendering used by both the native module and `[cc_divi5_search_results]`;
- Visual Builder TypeScript sources, Divi-compatible compiler configuration and webpack build;
- repository-level npm configuration for the incompatible peer ranges published by the Divi 5 type aliases;
- frontend CSS with responsive grid behavior.

The Visual Builder bundle and `modules-json/` metadata are generated locally and are not committed. Server registration consumes the generated `modules-json/` files, so run the build before testing module insertion and editing.

## Native module structure

```text
codecorn/search-results
└── codecorn/search-result-type
    ├── post
    ├── page
    └── any registered custom post type
```

`Result Type Rule` children are configuration objects. They do not render independent frontend cards. The parent reads their saved Divi block attributes and applies them to posts returned by the search query.

## Available parent controls

### Query

- current WordPress search query or isolated query;
- explicit search term for the isolated query and Visual Builder preview;
- results per page.

### Display

- result count;
- search form;
- one to six desktop columns.

### Empty state

- title;
- message.

## Available result-type controls

- post type slug;
- singular, plural and badge labels;
- rule priority;
- accent color;
- image, excerpt and date visibility;
- excerpt word count;
- CTA label.

Missing labels fall back to the registered WordPress post-type labels. Missing rules fall back to a generic rule derived from the result's post type.

## Query contract

For `source=current`, the module reads the already-resolved main WordPress search query. It does not alter it.

For `source=isolated`, the plugin creates its own `WP_Query` using the search term, configured result types, result limit and current page. Integrations can adjust only that isolated query through:

```php
add_filter( 'cc_d5sr_query_args', function ( array $query_args, array $options ): array {
    return $query_args;
}, 10, 2 );
```

## Optional shortcode

```text
[cc_divi5_search_results]
```

Supported attributes in this increment:

```text
source="current|isolated"
search="museum"
post_types="post,page,event"
posts_per_page="10"
columns="3"
show_summary="on"
show_search_form="on"
show_image="on"
show_excerpt="on"
show_date="on"
excerpt_length="28"
cta_label="View result"
```

The shortcode is an adapter, not the primary implementation.

## Development

Requirements:

- WordPress 6.6+;
- Divi 5.9.0 reference target for this development increment;
- PHP 8.1+;
- Node.js 18+ and npm 10+;
- Composer.

Install from the committed dependency locks and build:

```bash
composer install
npm ci
npm run check
npm run build
```

To rebuild the installed npm tree without changing the committed dependency graph:

```bash
npm run reset-install
```

`reset-install` removes only `node_modules`, then runs `npm ci`. It deliberately preserves `package-lock.json`. Changes to `package.json` must be followed by an intentional `npm install`, review of the resulting lockfile diff and inclusion of both files in the same pull request. The same rule applies to `composer.json` and `composer.lock`.

Validation:

```bash
composer check:syntax
npm run check:types
npm run check:json
```

`ts-loader` performs type checking during `npm run build`; it is not configured with `transpileOnly`. The compiler configuration intentionally follows the compatibility surface used by the official Divi 5 example modules: emitted ES5 JavaScript, classic React JSX and no strict-null checking inside the published Divi type sources.

The published Divi 5 type aliases contain mutually incompatible development-only peer ranges: `react-dates` requests React/ReactDOM 16 while current WordPress packages request React 18. The repository therefore commits `.npmrc` with `legacy-peer-deps=true`; this prevents npm 10 from attempting an impossible peer-resolution graph. React remains externalized from the Visual Builder bundle, so this setting does not select or ship a second runtime React version. The same file keeps `engine-strict=true`.

The npm installation can still report deprecation and audit warnings from the `divi-types*` compatibility aliases used by Elegant Themes. Those warnings are not treated as a successful build: the authoritative result is the exit status of `npm run check` and `npm run build`. Do not run `npm audit fix --force`, because it can replace Divi-compatible development dependencies with breaking versions.

After `npm run build`, the generated files are:

```text
scripts/bundle.js
styles/vb-bundle.css
modules-json/search-results/module.json
modules-json/search-result-type/module.json
```

Local proprietary references, including an extracted Divi theme under `.reference/`, are ignored by Git and must never be committed. A source checkout is therefore not a distributable plugin archive by itself: release packaging must run the build and include the generated Visual Builder assets and module metadata.

## Not implemented yet

The README deliberately does not present the following as available:

- live query results inside the Visual Builder canvas;
- dynamic post-type selector options populated from WordPress REST data;
- per-type custom-field and taxonomy mappings;
- type filters with counts;
- query-term highlighting;
- per-breakpoint column controls;
- image fallback and ratio controls;
- card presets;
- AJAX navigation;
- automated release archive generation.

Those capabilities will be added in later reviewable increments without changing the shared renderer contract.

## Documentation rule

Every pull request that changes behavior, controls, requirements, generated paths, build commands, dependency requirements or public APIs must update this README in the same pull request. Documentation may describe merged or included code only; planned behavior belongs exclusively in **Not implemented yet**.

## License

MIT © 2026 CodeCorn Technology S.R.L.S.
