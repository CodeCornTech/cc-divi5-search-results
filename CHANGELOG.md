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
- `npm run reset-install` for a clean dependency-tree rebuild after manifest or npm-policy changes.

### Changed

- Plugin bootstrap now supports Composer autoloading and a source-checkout fallback autoloader.
- TypeScript compiler settings now emit the Divi Visual Builder bundle and follow the compatibility settings used by the official Divi 5 example modules.
- Visual Builder attribute interfaces now use native Divi element types instead of generic unknown records.
- Visual Builder edit and style renderers now guard optional props exposed by the published Divi types.
- Development dependencies now include the transitive declarations required by the Divi 5.9.0 type packages.
- Development documentation now requires the committed reset command after changes to `package.json` or `.npmrc`.
