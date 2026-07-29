# Changelog

## [Unreleased]

### Added

- Native Divi 5 `codecorn/search-results` parent module metadata and server registration.
- Native `codecorn/search-result-type` child-rule metadata and server registration.
- Current-query and isolated-query resolver without global query mutation.
- Shared accessible renderer for result cards, summary, search form, pagination and empty state.
- Optional `[cc_divi5_search_results]` adapter backed by the shared resolver and renderer.
- Visual Builder TypeScript source, webpack build and responsive frontend styles.
- README documentation contract requiring code and documentation to remain synchronized in every PR.
- Explicit TypeScript validation through `npm run check:types` and aggregate validation through `npm run check`.
- Repository `.npmrc` with strict engine enforcement and deterministic handling of incompatible Divi peer ranges.
- Lockfile-safe `npm run reset-install` for rebuilding `node_modules` through `npm ci`.
- Git exclusion for local proprietary references under `.reference/`.
- Runtime-only package generation with build manifest and SHA-256 checksum.
- VM deployment workflow supporting host and Docker WordPress installations.
- Timestamped remote backups, retention pruning and automatic rollback on failed remote validation or activation.
- Composer commands `package`, `deploy:check` and `deploy`.

### Changed

- Plugin bootstrap now supports Composer autoloading and a source-checkout fallback autoloader.
- TypeScript compiler settings now emit the Divi Visual Builder bundle and follow the compatibility settings used by the official Divi 5 example modules.
- Visual Builder attribute interfaces now use native Divi element types instead of generic unknown records.
- Visual Builder edit and style renderers now guard optional props exposed by the published Divi types.
- Development dependencies now include the transitive declarations required by the Divi 5.9.0 type packages.
- Development documentation now requires committed npm and Composer lockfiles to remain synchronized with their manifests.
- Release documentation now distinguishes ignored source-build outputs from files required in a distributable plugin archive.
- Deployment documentation now defines reproducible packaging, dry-run behavior, remote requirements, backup location and rollback guarantees.
