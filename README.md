# Portfolio CMS – Schema‑Driven Visual Admin Panel

> **Status:** Production‑ready (single‑page portfolio CMS)
>
> **Core Idea:** Design is **never touched**. Only **content + rendering logic** is managed.

This project has evolved from a simple admin panel into a **schema‑driven CMS** with a **visual editor**, image uploads, and dynamic sections — all stored in a single JSON schema per page.

---

## 1. What This System Is (High‑Level)

This is **not** a page builder like WordPress.

It is:

* A **content system**
* With **explicit rendering rules**
* Stored as **JSON schema** in the database
* Rendered by a **custom renderer** on the frontend

You control:

* What content exists
* Where it renders
* How it renders

All without touching HTML/CSS layout.

---

## 2. Key Features (Latest Version)

### 🔧 Admin / Backend

* Visual page editor (no raw JSON editing)
* Auto‑filled data from DB
* UTF‑8 safe schema storage
* Image upload support

  * Hero (header photo)
  * Project images
* Image preview before save
* Add / Remove dynamically:

  * Projects
  * Skills
  * Contact items
* Safe schema merge (old data preserved)

### 🎨 Frontend

* Renderer driven entirely by schema
* No hard‑coded content
* No design changes required
* Images served from `/uploads/`

### 🧠 Architecture

* One page = one schema
* One schema = many sections
* Each section:

  * `id`
  * `type`
  * `enabled`
  * `data`

---

## 3. Folder Structure (Current)

```
portfolio/
├── admin/
│   ├── page_editor_visual.php   # Main visual editor (core)
│
├── api/
│   └── get_page.php             # Fetch page schema by slug
│
├── config/
│   └── db.php                   # PDO + utf8mb4
│
├── public/
│   ├── index.php                # Frontend entry
│   ├── renderer.js              # Schema → HTML renderer
│   └── style.css                # Your original theme
│
├── uploads/                      # All images (hero + projects)
│
├── sql/
│   └── schema.sql               # Database structure
│
├── .env
├── .gitignore
└── README.md
```

---

## 4. Database Design

### Table: `pages`

```sql
CREATE TABLE pages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(50) UNIQUE,
  schema LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  updated_at TIMESTAMP NULL
);
```

### Why `LONGTEXT + utf8mb4`

* JSON schema can grow
* Supports emojis, bullets, em‑dashes
* Prevents UTF‑8 corruption

---

## 5. Schema Philosophy

Each page stores **one JSON schema**.

Example (simplified):

```json
{
  "page": "home",
  "sections": [
    {
      "type": "hero",
      "id": "hero",
      "enabled": true,
      "data": {
        "title": "Ankit Sharma",
        "tagline": "Software Engineer • Researcher • Educator",
        "photo": "hero_123.jpg"
      }
    },
    {
      "type": "projects",
      "id": "projects",
      "enabled": true,
      "data": {
        "items": [
          {
            "title": "Fitness Platform",
            "description": "Athlete‑first system",
            "image": "project_1.jpg"
          }
        ]
      }
    }
  ]
}
```

> ⚠️ JSON is **never** edited manually. Always saved via PHP `json_encode()`.

---

## 6. Visual Page Editor (`admin/page_editor_visual.php`)

### What it does

* Reads schema from DB
* Decodes JSON → PHP array
* Auto‑fills UI inputs
* Allows visual editing
* Rebuilds schema safely on save

### Important rules implemented

* **Old images preserved** if no new upload
* Schema merged, not overwritten
* Section matched by `id` (fallback to `type`)

### Image Upload Logic

* Files uploaded to `/uploads/`
* Only filename stored in schema
* Preview shown instantly via JS

---

## 7. Frontend Renderer (`public/renderer.js`)

### Responsibility

* Fetch schema from API
* Loop sections
* Render HTML per `type`

### Example: Projects Renderer

```js
case 'projects':
  section.innerHTML = data.items.map(p => `
    <div class="card">
      <img src="uploads/${p.image}">
      <h3>${p.title}</h3>
      <p>${p.description}</p>
    </div>
  `).join('');
  break;
```

> Renderer never mutates data. It only reads schema.

---

## 8. UTF‑8 Safety (Critical)

### Required

* DB charset: `utf8mb4`
* PDO DSN: `charset=utf8mb4`

```php
$pdo = new PDO(
  "mysql:host=$host;dbname=$db;charset=utf8mb4",
  $user,
  $pass,
  [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
```

### Why

Prevents:

* `Malformed UTF‑8 characters`
* Broken JSON decode

---

## 9. .env

```env
DB_HOST=localhost
DB_NAME=portfolio
DB_USER=portfolio_user
DB_PASS=strongpassword
```

---

## 10. .gitignore

```
.env
/uploads/*
```

---

## 11. Design Guarantees

* ❌ No inline styles generated

* ❌ No theme changes

* ❌ No layout overrides

* ✅ Only content changes

* ✅ Renderer respects your CSS

---

## 12. Current Capabilities Summary

✔ Schema‑driven pages
✔ Visual editor
✔ Image upload + preview
✔ Add / remove items dynamically
✔ Safe JSON storage
✔ Clean frontend rendering

---

## 13. Planned / Easy Extensions

* Drag & drop reorder
* Live preview (split screen)
* Image delete button
* Version history
* Multi‑page support

---

## 14. Mental Model (Important)

> **Database stores intent.**
>
> **Renderer decides appearance.**

This separation is the core strength of this system.

---

## Final Note

This project is intentionally:

* Minimal
* Explicit
* Understandable

It is built to be **owned**, not abstracted away.
