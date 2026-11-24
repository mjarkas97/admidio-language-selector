# Language switcher

An Admidio plugin that lets every visitor choose the language of the Admidio interface. A globe
button in the page header opens a menu with the languages the administrator offers.

* A logged-in user keeps the choice on all devices. It is stored in the database.
* A visitor who is not logged in keeps the choice on this device. It is stored in a cookie for one
  year.
* Only the texts of Admidio and of plugins are translated. Content that the organization entered,
  e.g. announcements or own menu entries, stays as it is.

## Requirements

* Admidio 5.1 or later
* PHP 8.2 or later
* A theme with the header navigation `#adm_main_navbar` (the themes `simple` and `basic` have it)

## Installation

1. Copy this folder to `plugins/language-switcher/` of your Admidio installation, or upload the
   plugin archive in the plugin management of Admidio.
2. Install and enable the plugin in the plugin management. The installation creates the table
   `adm_plg_language_switcher`.

## Configuration

Go to **Preferences → System → Language switcher** and enter the offered languages:

| Setting | Default | Description |
|---|---|---|
| Offered languages | `de,en` | Language codes separated by commas, e.g. `de,en,pl`. `de` is the informal German (du), `de-DE` the formal German (Sie). |

Codes of languages that are not installed in Admidio are ignored. The globe is only shown if at
least two installed languages are entered.

## How it works

1. On every request, the plugin switches to the chosen language. The language stored for a
   logged-in user wins over the cookie. Without either, the language of the organization stays.
2. Before a page is rendered, the plugin adds the globe button to the header.
3. A click on a language calls `modules/switch.php`. It stores the choice and returns to the page
   the visitor came from.

## Development

The styles are written in SCSS. Edit `scss/main.scss` and compile it to `assets/css/main.css`:

```bash
pnpm install
pnpm build    # compile once
pnpm watch    # compile on every change
```

Commit the compiled `assets/css/main.css` together with the SCSS file.

```text
plugins/language-switcher/
├── plugin.json                              manifest and setting
├── plugin.php                               entry file, applies the language and registers the hook
├── src/LanguageSwitcher.php                 the logic
├── modules/switch.php                       stores the chosen language
├── templates/plugin.language-switcher.tpl   the globe button and menu
├── scss/main.scss                           styles (source)
├── assets/css/main.css                      styles (compiled)
├── languages/                               texts in en, de (du) and de-DE (Sie)
└── db_scripts/                              creates and removes the table
```

## Support

The plugin is free. If it is useful for your organization, you can support its development on
[Ko-fi](https://ko-fi.com/mjarkas).

## License

GNU General Public License v2.0 only, the same license as Admidio. See [LICENSE](LICENSE).

© 2026 Mohammad Jarkas
