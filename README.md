# CC Divi 5 Search Results

> Native Divi 5 search results. The Blog Module workaround ends here.

A public Divi 5 extension for type-aware WordPress search results. The native parent module owns the query and result layout; native child modules act as repeatable rules for individual post types. The optional shortcode calls the same PHP query and rendering services.

## Current implementation

The `0.1.0` development branch currently includes:

- safe plugin bootstrap with Composer PSR-4 autoloading and a source-checkout fallback autoloader;
- isolated search-query resolution that never calls `query_posts()` and never replaces the global `$wp_query`;
- reuse of the real current WordPress search query when the module runs on a search-results request;
- an isolated `WP_Query` fallback for Visual Builder previews, non-search pages and shortcode use;
- the native Divi 5 parent module `codecorn/search-results`, displayed in the Builder as `CC Search Results`;
- the native configuration child module `codecorn/search-result-type`, displayed as `CC Result Type`;
- repeatable post-type rules for labels, badge, accent, image, excerpt, date and CTA;
- a dynamic result-type selector populated from the public post types registered by WordPress;
- accessible result count, search form, result cards, pagination and empty state;
- shared rendering used by both the native module and `[cc_divi5_search_results]`;
- Visual Builder TypeScript sources, Divi-compatible compiler configuration and webpack build;
- repository-level npm configuration for the incompatible peer ranges published by the Divi 5 type aliases;
- deterministic runtime packaging with build manifest and SHA-256 checksum;
- VM deployment workflow for host and Docker WordPress installations, including backup and automatic rollback;
- a Barbagia Musei deployment wrapper pinned to the real `docker02` Compose stack and `wp_cron` service;
- non-interactive Barbagia preflight for SSH public-key authentication and remote Docker API access;
- frontend CSS with responsive grid behavior.

The Visual Builder bundle and `modules-json/` metadata are generated locally and are not committed. Server registration consumes the generated `modules-json/` files, so run the build before testing module insertion and editing.

## Native module structure

```text
CC Search Results (codecorn/search-results)
└── CC Result Type (codecorn/search-result-type)
    ├── post
    ├── page
    └── any registered public custom post type
```

`CC Result Type` children are configuration objects. They do not render independent frontend cards. The parent reads their saved Divi block attributes and applies them to posts returned by the search query.

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

- public WordPress post type selected from the list registered by the current site;
- singular, plural and badge labels;
- rule priority;
- accent color;
- image, excerpt and date visibility;
- excerpt word count;
- CTA label.

The post-type selector is populated when the Visual Builder assets are registered. It lists the translated WordPress label followed by the technical slug, for example `Articoli (post)`, `Pagine (page)` or `Eventi (eventi)`. Attachments are excluded. If Builder data is unavailable, the control falls back to `post` and `page` instead of becoming a free-text field.

Integrations can adjust the available options without changing module code:

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

Local proprietary references, including an extracted Divi theme under `.reference/`, are ignored by Git and must never be committed.

## Runtime package

A source checkout is not a distributable plugin archive by itself. The package command performs a clean dependency install from both committed lockfiles, runs all TypeScript/JSON/PHP checks, builds the Visual Builder assets and creates a runtime-only archive under `dist/`:

```bash
composer package
```

The generated archive contains:

```text
cc-divi5-search-results.php
assets/
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

It deliberately excludes `src/`, `node_modules/`, development manifests, local references and repository metadata. A sibling `.sha256` file records the archive checksum. The build manifest records plugin version, exact Git commit, branch and UTC build time.

Packaging requires:

- committed `composer.lock` and `package-lock.json`;
- a clean working tree unless `CC_D5SR_REQUIRE_CLEAN=0` is set explicitly;
- successful `composer install --no-dev`, `npm ci`, `npm run check`, `npm run build` and `composer check:syntax`.

The package path, archive contents and checksum flow have been exercised against a disposable mock repository. The first real VM deployment remains a required integration test before this PR can leave draft state.

## VM deployment

The same workflow deploys the generated runtime archive to a WordPress installation reachable over SSH:

```bash
composer deploy:check
composer deploy
```

`deploy:check` performs the full local package and validation sequence but never opens an SSH connection and never modifies the VM.

`deploy` performs:

1. local clean-tree and lockfile checks;
2. reproducible package generation;
3. SSH upload without `scp`;
4. SHA-256 verification on the VM;
5. timestamped backup of the installed plugin when present;
6. staged copy into the WordPress plugins directory;
7. remote PHP lint before switching versions;
8. same-filesystem atomic directory switch;
9. optional activation and status verification through WP-CLI;
10. automatic rollback when the remote lint, switch, activation or status check fails;
11. retention pruning for old backups.

### Required deployment variable

```bash
export CC_D5SR_DEPLOY_TARGET="user@example-host"
```

### Docker WordPress example

`CC_D5SR_DEPLOY_CONTAINER` is a Docker container name or ID, not a Compose service name.

```bash
export CC_D5SR_DEPLOY_TARGET="user@example-host"
export CC_D5SR_DEPLOY_MODE="docker"
export CC_D5SR_DEPLOY_CONTAINER="wordpress-container-name"
export CC_D5SR_REMOTE_WP_ROOT="/var/www/html"

composer deploy:check
composer deploy
```

The VM must provide Docker. The selected container must provide `php`, `tar` and, when activation is enabled, `wp`.

### Barbagia Musei on `docker02`

The dedicated wrapper removes all manual container and path discovery. Run it from the local repository root:

```bash
cd "$GH_PATH/cc-divi5-search-results" || return 1

composer deploy:barbagia:check
composer deploy:barbagia
```

The wrapper is pinned to this deployment contract:

```text
Local repository:   $GH_PATH/cc-divi5-search-results
SSH target:         docker02
Remote Compose file:/home/fgirolami/docker/barbagiamusei/compose.yaml
Compose service:    wp_cron
WordPress root:     /var/www/html
Activation:         enabled
Backup retention:   10
```

`composer deploy:barbagia:check` runs the complete local package validation without SSH or remote changes.

`composer deploy:barbagia` connects to `docker02` and resolves the running container dynamically with:

```bash
docker compose \
    -f /home/fgirolami/docker/barbagiamusei/compose.yaml \
    ps -q wp_cron
```

The resolved container is currently named `cron_barbagiamusei`, but that name is not hardcoded because it can change after a Compose recreate. Before deployment, the wrapper requires exactly one running container for `wp_cron` and verifies inside it:

- `/var/www/html/wp-load.php`;
- PHP CLI;
- `tar`;
- WP-CLI.

The remote Compose command uses the absolute `-f` path. It does not depend on the SSH login directory or on the repository path shown by the remote shell prompt. Docker is executed on `docker02`; a stopped Docker Desktop installation on the local Mac has no effect on this workflow.

The Barbagia apply command is intentionally non-interactive. Before packaging or upload it requires both of these checks to pass:

```bash
ssh -o BatchMode=yes docker02 true
ssh -o BatchMode=yes docker02 docker info
```

The first check proves that the Mac can authenticate with the configured SSH key without falling back to the account password. The second proves that the remote `fgirolami` session can access `/var/run/docker.sock` without `sudo`. The deploy script never stores, pipes or retries SSH or sudo passwords.

One-time SSH-key bootstrap, only when the first check fails:

```bash
ssh-add --apple-use-keychain ~/.ssh/id_ed25519

cat ~/.ssh/id_ed25519.pub | ssh docker02 '
    umask 077
    mkdir -p ~/.ssh
    touch ~/.ssh/authorized_keys
    IFS= read -r key
    grep -qxF "$key" ~/.ssh/authorized_keys || printf "%s\n" "$key" >> ~/.ssh/authorized_keys
    chmod 700 ~/.ssh
    chmod 600 ~/.ssh/authorized_keys
'

ssh -o BatchMode=yes docker02 true
```

The `ssh` command used to install the public key may request the normal account password once. The final `BatchMode=yes` verification must not request any password.

One-time Docker-access bootstrap, only when the second check fails:

```bash
ssh -tt docker02 'sudo usermod -aG docker fgirolami'
```

After changing group membership, terminate the old SSH session and open a new one. If SSH connection multiplexing is configured globally, close the existing master connection before verification:

```bash
ssh -O exit docker02 2>/dev/null || true

ssh -o BatchMode=yes docker02 '
    id
    docker info >/dev/null
    docker compose \
        -f /home/fgirolami/docker/barbagiamusei/compose.yaml \
        ps -q wp_cron
'
```

Membership in the `docker` group grants root-equivalent control of the Docker host. This stack already requires Docker administration for deployment, so the requirement is explicit rather than hidden behind an interactive sudo prompt.

Optional Barbagia overrides are available only when the stack changes intentionally:

```text
CC_D5SR_DEPLOY_TARGET       default docker02
CC_D5SR_COMPOSE_FILE        default /home/fgirolami/docker/barbagiamusei/compose.yaml
CC_D5SR_COMPOSE_SERVICE     default wp_cron
CC_D5SR_REMOTE_WP_ROOT      default /var/www/html
```

### Host WordPress example

```bash
export CC_D5SR_DEPLOY_TARGET="user@example-host"
export CC_D5SR_DEPLOY_MODE="host"
export CC_D5SR_REMOTE_WP_ROOT="/var/www/html"

composer deploy:check
composer deploy
```

The host must provide `php` and, when activation is enabled, `wp`.

### Deployment variables

```text
CC_D5SR_DEPLOY_TARGET       required for generic deploy; SSH user/host
CC_D5SR_DEPLOY_MODE         auto, host or docker; default auto
CC_D5SR_DEPLOY_CONTAINER    Docker container name or ID; never a Compose service
CC_D5SR_REMOTE_WP_ROOT      default /var/www/html
CC_D5SR_REMOTE_STATE_DIR    default .local/state/cc-divi5-search-results
CC_D5SR_SSH_PORT            default 22
CC_D5SR_ACTIVATE            1 or 0; default 1
CC_D5SR_BACKUP_KEEP         default 10; 0 disables pruning
CC_D5SR_REQUIRE_CLEAN       1 or 0; default 1
CC_D5SR_COLOR               auto, always or never
NO_COLOR                    disables ANSI colors
```

The default remote state path is relative to the SSH user's home directory. It contains incoming archives, temporary extraction directories and timestamped backups. No credentials belong in the repository. The Barbagia wrapper intentionally contains the non-secret stack path and service contract so deployment does not rely on operator memory.

## Not implemented yet

The README deliberately does not present the following as available:

- live query results inside the Visual Builder canvas;
- per-type custom-field and taxonomy mappings;
- type filters with counts;
- query-term highlighting;
- per-breakpoint column controls;
- image fallback and ratio controls;
- card presets;
- AJAX navigation;
- automated GitHub release publication.

Those capabilities will be added in later reviewable increments without changing the shared renderer contract.

## Documentation rule

Every pull request that changes behavior, controls, requirements, generated paths, build commands, dependency requirements, deployment behavior or public APIs must update this README in the same pull request. Documentation may describe merged or included code only; planned behavior belongs exclusively in **Not implemented yet**.

## License

MIT © 2026 CodeCorn Technology S.R.L.S.
