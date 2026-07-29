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

### Changed

- Plugin bootstrap now supports Composer autoloading and a source-checkout fallback autoloader.
