# Portfolio CMS

A simple PHP portfolio CMS with:
- public homepage rendering from JSON schema stored in MySQL
- admin visual page editor for the home page
- image uploads stored in `uploads/`

## Project structure

- `public/` — public website entrypoint
  - `public/index.php` — SPA-like page loader
  - `public/api/get_page.php` — API endpoint returning the page schema
  - `public/js/renderer.js` — client-side renderer
  - `public/js/schema_contracts.js` — section validation rules
- `admin/` — admin login and visual editor
  - `admin/login.php` — admin authentication
  - `admin/page_editor_visual.php` — home page editor
- `config/db.php` — database connection
- `uploads/` — uploaded image assets
- `sql/schema.sql` — schema for initial database tables
- `.env` — database credentials

## Requirements

- PHP 8+
- MySQL / MariaDB
- Webserver or PHP built-in server

## Setup

1. Create the database and tables

   Import `sql/schema.sql` into your MySQL database:

   ```bash
   mysql -u <user> -p < portfolio_cms/sql/schema.sql
   ```

2. Configure database credentials

   Copy `.env` from `env_exmaple.txt` if needed, then update values:

   ```text
   DB_HOST=127.0.0.1
   DB_NAME=portfolio
   DB_USER=your user
   DB_PASS=your pass
   ```

3. Ensure the `.env` file is readable by PHP and matches your MySQL credentials.

4. If you need an admin user, insert one manually into `admin_users`.

   Example SQL:

   ```sql
   INSERT INTO admin_users (username, password)
   VALUES ('admin', '<password_hash>');
   ```

   Generate a password hash in PHP:

   ```php
   <?php
   echo password_hash('your-password', PASSWORD_DEFAULT);
   ```
   
## Run the app locally

From the project root:

```bash
php -S 127.0.0.1:8000
```

Then open in your browser:

- `http://127.0.0.1:8000/public/index.php` — public homepage
- `http://127.0.0.1:8000/admin/login.php` — admin login

> Important: start the server from the repository root so `public/`, `admin/`, and `uploads/` are all available.

## Notes

- The homepage loads JSON schema from `public/api/get_page.php?slug=home`.
- Uploaded images are saved under `uploads/` and referenced from the editor using `../uploads/...`.
- The admin UI only edits the `home` page schema.
- If `localhost` shows a blank page or 404, use `127.0.0.1:8000` and confirm the server is started from the project root.

## Troubleshooting

- `port already in use`: another server is already running on `8000`. Stop it or use another port:

  ```bash
  php -S 127.0.0.1:8001
  ```

- `404 Not Found` on `/`: use the full path `http://127.0.0.1:8000/public/index.php`.
- Images missing: make sure the server root includes `uploads/` and the image file exists in `uploads/`.
- Admin login fails: make sure an `admin_users` record exists and the password hash is correct.

## Maintenance

- Edit page content from `admin/page_editor_visual.php` after login.
- The editor saves schema updates to `pages.schema` and versions in `page_versions`.
- Use `uploads/` for image files referenced by the page schema.
