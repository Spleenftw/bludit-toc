# bludit-toc

A [Bludit](https://www.bludit.com/) plugin that automatically generates a **Table of Contents** from the `h2`/`h3`/`h4` headings in an article.

- **Desktop** — sticky sidebar fixed to the left of the viewport
- **Mobile** — floating button that opens a slide-up drawer
- Scroll-spy highlights the currently visible section
- Works with any Bludit theme; no theme modification required

---

## Installation

1. Copy the `bludit-toc` folder into your Bludit installation's `bl-plugins/` directory.
2. Go to **Admin → Plugins** and activate **Bludit ToC**.

---

## Settings

| Setting | Default | Description |
|---|---|---|
| Sidebar title | `On this page` | Label shown above the ToC list and in the mobile drawer. |
| Navbar height (px) | `80` | Pixel offset applied to heading anchors so they clear a fixed navbar on hash-jump. |
| Fixed sidebar min-width (px) | `1280` | Viewport width at which the desktop sidebar appears. Below this the mobile FAB is used. |

---

## Theme compatibility

The plugin looks for the article content in this order:

1. `.content`
2. `.entry-content`
3. `.post-content`
4. `article`
5. `main`

Bludit's default page template uses `.content`, so it works out of the box.

Colors adapt automatically:

- Light/dark mode via `prefers-color-scheme`
- If the host theme exposes `--accent`, `--text-muted`, or `--bg-primary` as CSS custom properties, the plugin inherits them seamlessly

---

## License

MIT © [spleenftw](https://github.com/spleenftw)
