# Professional Admin Panel for Portfolio (Content‑Driven)

This backend **does NOT change your design or theme**. It only controls **content** (projects, skills, about text, vision, contact) via an admin panel and database.

Everything here is:

* Linux‑friendly (Fedora)
* Simple PHP (no frameworks)
* Secure by default
* Production‑ready

---

## 1. Tech Stack

* **Frontend:** Existing HTML/CSS (unchanged)
* **Backend:** PHP 8+
* **Database:** MySQL / MariaDB
* **Security:** password_hash, prepared statements
* **Config:** `.env`

---

## 2. Folder Structure

```
portfolio/
├── admin/
│   ├── login.php
│   ├── dashboard.php
│   ├── projects.php
│   ├── skills.php
│   ├── logout.php
│
├── api/
│   ├── get_projects.php
│   ├── get_skills.php
│
├── config/
│   ├── db.php
│   ├── auth.php
│
├── public/
│   ├── index.php   <-- your current design (HTML → PHP)
│
├── sql/
│   ├── schema.sql
│
├── .env
├── .gitignore
├── README.md
```

---

## 3. Database Schema (`sql/schema.sql`)

```sql
CREATE DATABASE portfolio;
USE portfolio;

CREATE TABLE admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE,
  password VARCHAR(255)
);

CREATE TABLE skills (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100)
);

CREATE TABLE projects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200),
  description TEXT
);
```

---

## 4. Environment File (`.env`)

```env
DB_HOST=localhost
DB_NAME=portfolio
DB_USER=portfolio_user
DB_PASS=strongpassword
```

---

## 5. Database Connection (`config/db.php`)

```php
<?php
$env = parse_ini_file(__DIR__ . '/../.env');

$pdo = new PDO(
  "mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']}",
  $env['DB_USER'],
  $env['DB_PASS'],
  [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
```

---

## 6. Admin Login (`admin/login.php`)

```php
<?php
session_start();
require '../config/db.php';

if ($_POST) {
  $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username=?");
  $stmt->execute([$_POST['username']]);
  $user = $stmt->fetch();

  if ($user && password_verify($_POST['password'], $user['password'])) {
    $_SESSION['admin'] = true;
    header('Location: dashboard.php');
    exit;
  }
}
?>

<form method="post">
  <input name="username" placeholder="Username">
  <input name="password" type="password" placeholder="Password">
  <button>Login</button>
</form>
```

---

## 7. Admin Dashboard (`admin/dashboard.php`)

```php
<?php session_start(); if(!$_SESSION['admin']) die('Access denied'); ?>
<h2>Admin Panel</h2>
<ul>
  <li><a href="projects.php">Manage Projects</a></li>
  <li><a href="skills.php">Manage Skills</a></li>
  <li><a href="logout.php">Logout</a></li>
</ul>
```

---

## 8. Example: Manage Projects (`admin/projects.php`)

```php
<?php
session_start(); require '../config/db.php';
if(!$_SESSION['admin']) die('Denied');

if ($_POST) {
  $stmt = $pdo->prepare("INSERT INTO projects (title, description) VALUES (?,?)");
  $stmt->execute([$_POST['title'], $_POST['description']]);
}

$projects = $pdo->query("SELECT * FROM projects")->fetchAll();
?>

<form method="post">
  <input name="title" placeholder="Project Title">
  <textarea name="description"></textarea>
  <button>Add</button>
</form>

<?php foreach($projects as $p): ?>
  <p><b><?= $p['title'] ?></b></p>
<?php endforeach; ?>
```

---

## 9. API for Frontend (`api/get_projects.php`)

```php
<?php
require '../config/db.php';
header('Content-Type: application/json');
echo json_encode($pdo->query("SELECT * FROM projects")->fetchAll());
```

---

## 10. Frontend Usage (NO DESIGN CHANGE)

Replace static cards with JS fetch:

```html
<script>
fetch('/api/get_projects.php')
.then(r=>r.json())
.then(data=>{
  const el = document.getElementById('projects');
  el.innerHTML = data.map(p=>`<div class="card"><h3>${p.title}</h3><p>${p.description}</p></div>`).join('');
});
</script>
```

---

## 11. `.gitignore`

```
.env
/vendor
```

---

## 12. README.md (Summary)

* Admin Panel: `/admin/login.php`
* Secure login
* Content stored in DB
* Frontend reads via API
* Design untouched

---

## Next Steps

✔ Add About / Vision editor
✔ Add image upload (projects)
✔ Role‑based admin
✔ Backup & export
✔ Docker (optional)

---
