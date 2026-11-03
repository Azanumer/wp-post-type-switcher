# Post Type Switcher

Convert posts, pages, and custom post types into another post type from
Tools → Post Type Switcher — without losing content, custom fields, or
taxonomies shared by both types.

## Features

- **Bulk by ID** — paste comma- or space-separated post IDs and convert them in one run.
- **Safe by default** — missing posts, posts already of the target type, and posts you cannot edit are skipped and counted, never force-converted.
- **Only sensible targets** — public, UI-visible post types only; attachments are excluded.
- **`wpts_switchable_types` filter** — add or remove offered types from your own code.
- **No leftover data** — the plugin stores no options or tables, so uninstalling leaves nothing behind.

## Notes

Taxonomies registered only for the old post type stay in the database but are hidden while the post lives under the new type; converting back restores them. Converted posts keep their new type after uninstall — that is the intended, permanent result.

## Installation

1. Upload the plugin folder to `wp-content/plugins/`.
2. Activate it.
3. Go to Tools → Post Type Switcher.

## License

MIT — see `LICENSE`.
