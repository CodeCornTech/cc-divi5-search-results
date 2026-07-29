# CC Divi 5 Search Results

> Native Divi 5 search results. The Blog Module workaround ends here.

A public, reusable Divi 5 extension for type-aware WordPress search results with full Visual Builder control.

## Non-negotiable contract

- **Native Divi 5 module first.** No mandatory shortcode, Code Module or copied HTML.
- **Real WordPress query.** Current search by default; archive/custom sources can be added explicitly.
- **CPT-first.** Posts, pages and custom post types are treated as distinct result families.
- **Native configuration repeater.** A parent Search Results module contains child Result Type Rule modules.
- **Everything editable.** Labels, icons, colors, card variant, image source/fallback, ratio, excerpt, metadata mappings, CTA, filters, count, highlighting, pagination and empty state.
- **One rendering engine.** The optional shortcode calls the same query, normalization and renderer services as the native module.
- **Generic core.** No Barbagia Musei post types, labels, colors, meta keys or Theme Builder IDs hardcoded.

## Planned modules

### `codecorn/search-results`

Dynamic parent module controlling:

- query source, allowed post types, ordering and results per page;
- result summary and count;
- search form and clear action;
- type filters with counts;
- grid/list layout and responsive columns;
- term highlighting;
- pagination;
- zero-results experience;
- complete Content, Design and Advanced settings.

### `codecorn/search-result-type`

Child module used as a native repeater-like rule for each post type/result family:

- post type, state and priority;
- singular/plural labels, badge and icon;
- accent color and card preset;
- image source, fallback, ratio and object-fit;
- excerpt source, length and suffix;
- date, location, author, taxonomy and custom-field mappings;
- CTA text and link behavior;
- visibility rules when metadata is empty.

The child items configure dynamic result cards; they are not manually duplicated content cards.

## Shared architecture

1. `includes/Query` — current search/archive/custom query resolution.
2. `includes/Rendering` — accessible shared markup and view models.
3. `modules` — Divi 5 frontend module definitions.
4. `src/components` — Visual Builder components.
5. `shortcodes` — optional compatibility adapter only.

## Target shortcode

```text
[cc_divi5_search_results]
```

It is an adapter, never the primary implementation.

## Requirements

- WordPress 6.6+
- Divi 5
- PHP 8.1+
- Node.js 18+
- Composer

## License

MIT © 2026 CodeCorn Technology S.R.L.S.
