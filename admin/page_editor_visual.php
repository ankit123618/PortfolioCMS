<?php
// admin/page_editor_visual.php

session_start();
require '../config/db.php';
if (!$_SESSION['admin']) die('Denied');

ini_set('display_errors', 1);
error_reporting(E_ALL);

$slug = 'home';

/* =========================
   FETCH SCHEMA
========================= */
$stmt = $pdo->prepare("SELECT * FROM pages WHERE slug=? LIMIT 1");
$stmt->execute([$slug]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$pageId = $row['id'];

$schema = json_decode($row['schema'] ?? '{}', true);
$sections = $schema['sections'] ?? [];

/* =========================
   HELPERS
========================= */
function getSectionById(array $sections, string $id)
{
    foreach ($sections as $s) {
        if (($s['id'] ?? '') === $id) {
            return $s;
        }
    }
    return null;
}

/* =========================
   HANDLE SAVE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    foreach ($sections as &$section) {

        /* HERO */
        if ($section['id'] === 'hero') {
            $section['enabled'] = isset($_POST['hero_enabled']);
            $section['data']['title'] = trim($_POST['hero_title'] ?? '');
            $section['data']['tagline'] = trim($_POST['hero_tagline'] ?? '');

            // HERO IMAGE UPLOAD
            if (!empty($_FILES['hero_photo']['name'])) {
                $ext = pathinfo($_FILES['hero_photo']['name'], PATHINFO_EXTENSION);
                $filename = time() . '_hero.' . $ext;

                move_uploaded_file(
                    $_FILES['hero_photo']['tmp_name'],
                    "../uploads/" . $filename
                );

                $section['data']['photo'] = $filename;
            }
        }


        /* ABOUT */
        if ($section['id'] === 'about') {
            $section['data']['text'] = trim($_POST['about_text'] ?? '');
        }

        /* SKILLS */
        if ($section['id'] === 'skills') {
            $items = $_POST['skills'] ?? [];
            $clean = [];

            foreach ($items as $s) {
                $s = trim($s);
                if ($s !== '') $clean[] = $s;
            }

            $section['data']['items'] = $clean;
        }


        /* PROJECTS */
        if ($section['id'] === 'projects') {

            $posted = $_POST['projects'] ?? [];
            $existing = $section['data']['items'] ?? [];
            $clean = [];

            foreach ($posted as $i => $p) {

                if (empty($p['title'])) continue;

                // 🔑 preserve old image by default
                $imageName = $existing[$i]['image'] ?? '';

                // ✅ overwrite ONLY if new file uploaded
                if (!empty($_FILES['project_image_' . $i]['name'])) {
                    $ext = pathinfo($_FILES['project_image_' . $i]['name'], PATHINFO_EXTENSION);
                    $imageName = time() . '_project_' . $i . '.' . $ext;

                    move_uploaded_file(
                        $_FILES['project_image_' . $i]['tmp_name'],
                        "../uploads/" . $imageName
                    );
                }

                $clean[] = [
                    'title' => trim($p['title']),
                    'description' => trim($p['description']),
                    'image' => $imageName
                ];
            }

            $section['data']['items'] = $clean;
        }

        /* VISION */
        if ($section['id'] === 'vision') {
            $section['data']['text'] = trim($_POST['vision_text'] ?? '');
        }

        /* CONTACT */
        if ($section['id'] === 'contact') {
            $items = $_POST['contact'] ?? [];
            $clean = [];

            foreach ($items as $c) {
                if (!empty($c['name'])) {
                    $clean[] = [
                        'name' => trim($c['name']),
                        'value' => trim($c['value'])
                    ];
                }
            }

            $section['data']['items'] = $clean;
        }
    }

    $schema['sections'] = $sections;

    $versionStmt = $pdo->prepare(
        "INSERT INTO page_versions (page_id, schema, note)
     VALUES (:page_id, :schema, :note)"
    );

    $versionStmt->execute([
        ':page_id' => $pageId,
        ':schema'  => json_encode(
            $schema,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        ),
        ':note'    => 'Auto save from visual editor'
    ]);


    $stmt = $pdo->prepare(
        "UPDATE pages SET schema=?, updated_at=NOW() WHERE slug=?"
    );
    $stmt->execute([
        json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        $slug
    ]);
}

/* =========================
   EXTRACT SECTIONS (AUTO-FILL)
========================= */
$hero     = getSectionById($sections, 'hero');
$about    = getSectionById($sections, 'about');
$skills   = getSectionById($sections, 'skills');
$projects = getSectionById($sections, 'projects');
$vision   = getSectionById($sections, 'vision');
$contact  = getSectionById($sections, 'contact');

/* SAFETY: ensure projects UI always renders */
$projectItems = $projects['data']['items'] ?? [];
if (empty($projectItems)) {
    $projectItems = [
        ['title' => '', 'description' => '', 'image' => '']
    ];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin · Visual Page Editor</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>

<body id="admin-body">

    <h1>Visual Page Editor — Home</h1>

    <form method="post" enctype="multipart/form-data">

        <!-- HERO -->
        <h2>Hero</h2>
        <div class="box">
            <label>Title</label>
            <input name="hero_title"
                value="<?= htmlspecialchars($hero['data']['title'] ?? '') ?>">

            <label>Tagline</label>
            <textarea name="hero_tagline"><?= htmlspecialchars($hero['data']['tagline'] ?? '') ?></textarea>

            <label>Header Photo</label>

            <div class="preview-wrap">
                <?php if (!empty($hero['data']['photo'])): ?>
                    <img src="../uploads/<?= htmlspecialchars($hero['data']['photo']) ?>"
                        class="image-preview"
                        id="hero-preview">
                <?php else: ?>
                    <img id="hero-preview" class="image-preview" style="display:none;">
                <?php endif; ?>
            </div>

            <input type="file" name="hero_photo" accept="image/*"
                onchange="previewImage(this, 'hero-preview')">


            <label>
                <input type="checkbox" name="hero_enabled"
                    <?= ($hero['enabled'] ?? true) ? 'checked' : '' ?>>
                Enabled
            </label>
        </div>


        <!-- ABOUT -->
        <h2>About</h2>
        <div class="box">
            <textarea name="about_text"><?= htmlspecialchars($about['data']['text'] ?? '') ?></textarea>
        </div>

        <!-- SKILLS -->
        <h2>Skills</h2>

        <div id="skills-container">
            <?php foreach (($skills['data']['items'] ?? []) as $i => $skill): ?>
                <div class="box skill-row">
                    <input name="skills[<?= $i ?>]" value="<?= htmlspecialchars($skill) ?>">
                    <button type="button" class="remove-skill">Remove</button>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="button" id="add-skill">➕ Add Skill</button>


        <!-- PROJECTS -->
        <h2>Projects</h2>

        <div id="projects-container">
            <?php foreach ($projectItems as $i => $p): ?>
                <div class="box project-box">
                    <label>Title</label>
                    <input name="projects[<?= $i ?>][title]"
                        value="<?= htmlspecialchars($p['title'] ?? '') ?>">

                    <label>Description</label>
                    <textarea name="projects[<?= $i ?>][description]"><?= htmlspecialchars($p['description'] ?? '') ?></textarea>

                    <label>Image</label>

                    <div class="preview-wrap">
                        <?php if (!empty($p['image'])): ?>
                            <img src="../uploads/<?= htmlspecialchars($p['image']) ?>"
                                class="image-preview"
                                id="project-preview-<?= $i ?>">
                        <?php else: ?>
                            <img id="project-preview-<?= $i ?>" class="image-preview" style="display:none;">
                        <?php endif; ?>
                    </div>

                    <input type="file"
                        name="project_image_<?= $i ?>"
                        accept="image/*"
                        onchange="previewImage(this, 'project-preview-<?= $i ?>')">


                    <button type="button" class="remove-project">Remove</button>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="button" id="add-project">➕ Add Project</button>


        <!-- VISION -->
        <h2>Vision</h2>
        <div class="box">
            <textarea name="vision_text"><?= htmlspecialchars($vision['data']['text'] ?? '') ?></textarea>
        </div>

        <!-- CONTACT -->
        <h2>Contact</h2>

        <div id="contact-container">
            <?php foreach (($contact['data']['items'] ?? []) as $i => $c): ?>
                <div class="box contact-row">
                    <input name="contact[<?= $i ?>][name]"
                        placeholder="Label"
                        value="<?= htmlspecialchars($c['name']) ?>">

                    <input name="contact[<?= $i ?>][value]"
                        placeholder="Value"
                        value="<?= htmlspecialchars($c['value']) ?>">

                    <button type="button" class="remove-contact">Remove</button>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="button" id="add-contact">➕ Add Contact</button>


        <br>
        <button type="submit">Save Changes</button>

    </form>
    <script src="../public/js/preview.js"></script>
    <script src="../public/js/projects.js"></script>
    <script src="../public/js/skills.js"></script>
    <script src="../public/js/contacts.js"></script>
</body>

</html>