<?php
declare(strict_types=1);

session_start();
require '../config/db.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

/* ========================
   FETCH CONTENT (SAFE)
======================== */

$stmt = $pdo->prepare("SELECT * FROM site_content WHERE id = 1");
$stmt->execute();
$content = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$content) {
    $content = [
        'header' => '',
        'tag' => '',
        'navigation_links' => '',
        'photo' => '',
        'about' => '',
        'vision' => '',
        'email' => '',
        'github' => '',
        'youtube' => '',
        'footer' => ''
    ];
}

/* ========================
   SAVE CONTENT
======================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $photo = $content['photo'];

    if (!empty($_FILES['photo']['name'])) {
        $uploadDir = __DIR__ . '/../uploads/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $photo = time() . '_' . basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $photo);
    }

    $stmt = $pdo->prepare("
        UPDATE site_content SET
            header = ?,
            tag = ?,
            navigation_links = ?,
            photo = ?,
            about = ?,
            vision = ?,
            email = ?,
            github = ?,
            youtube = ?,
            footer = ?
        WHERE id = 1
    ");

    $stmt->execute([
        $_POST['header'] ?? '',
        $_POST['tag'] ?? '',
        $_POST['navigation_links'] ?? '',
        $photo,
        $_POST['about'] ?? '',
        $_POST['vision'] ?? '',
        $_POST['email'] ?? '',
        $_POST['github'] ?? '',
        $_POST['youtube'] ?? '',
        $_POST['footer'] ?? ''
    ]);

    header('Location: content.php');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Admin – Content</title>
</head>
<body>

<form method="post" enctype="multipart/form-data">

<h3>Header</h3>
<textarea name="header"><?= htmlspecialchars($content['header']) ?></textarea>

<h3>Tag</h3>
<textarea name="tag"><?= htmlspecialchars($content['tag']) ?></textarea>

<h3>Navigation (JSON)</h3>
<textarea name="navigation_links" rows="6" style="font-family:monospace;">
<?= htmlspecialchars($content['navigation_links']) ?>
</textarea>

<h3>Photo</h3>
<input type="file" name="photo">
<?php if ($content['photo']): ?>
<p>Current: <?= htmlspecialchars($content['photo']) ?></p>
<?php endif; ?>

<h3>About</h3>
<textarea name="about"><?= htmlspecialchars($content['about']) ?></textarea>

<h3>Vision</h3>
<textarea name="vision"><?= htmlspecialchars($content['vision']) ?></textarea>

<h3>Contact</h3>
<p>Email: <input name="email" value="<?= htmlspecialchars($content['email']) ?>"></p>
<p>GitHub: <input name="github" value="<?= htmlspecialchars($content['github']) ?>"></p>
<p>YouTube: <input name="youtube" value="<?= htmlspecialchars($content['youtube']) ?>"></p>

<h3>Footer</h3>
<textarea name="footer"><?= htmlspecialchars($content['footer']) ?></textarea>

<br><br>
<button type="submit">Save</button>
</form>

</body>
</html>
