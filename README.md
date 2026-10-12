# Mattarikon schedule

The schedule for the Mattarikon convention: a public page, a monitor view and an admin form for the activities. The server side is PHP with MariaDB, and the browser side is plain JavaScript.

## Run it locally

```
docker compose --profile dev up -d
```

The site is then at http://127.0.0.1:8081. When the database is empty, the container writes `config.php` from `config.template.php` and loads `dump_schema.sql`, `dump_baseline_data.sql` and `dump_dev_data.sql`. To start from your own data, save a full export as `dump_dev_data_local.sql` before the first start. It is then loaded instead of the three files, and git ignores it.

Add `?m=1` to the address for the monitor view, and `?t=14:30` to see the schedule as it is at that time of day.

## Settings

`config.php` holds the database login and two settings:

- `CONVENTION_DATE`: on that date the page shows what is ongoing and what is coming. On other days it lists the whole schedule, unless the address has `?t` or `?c=1`. The list of activities has a field that writes a new date into the file, which the web server has to be allowed to write. In the dev container it is not.
- `SUMMARY_WARNING_LENGTH`: the activity form warns when a summary is longer than this many characters. Without the line the limit is 300.

## What the web server hands out

`.htaccess` lets through the PHP pages in the root, the scripts in `js/` except the tests, `style.css`, the PNG files in `icons/`, the logo and `.well-known/`. Everything else gets a 403. A file of a new kind, such as an SVG icon or an image outside `icons/`, has to be added to the `RewriteRule` that starts with `!^(` before a browser can fetch it. So does a name the rule does not allow, such as one with a capital letter, or a page or script with a digit in its name.

## Tests and lint

```
npm install
npm test
npm run lint
```

The tests are the `*.test.js` files in `js/`. `npm run format:js` and `npm run format:css` format the scripts and the stylesheet.
