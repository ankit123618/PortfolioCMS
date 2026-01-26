<?php
session_start();
require '../config/db.php';
if (!$_SESSION['admin']) die('Denied');


if ($_POST) {

    $image = null;

    if (!empty($_FILES['image']['name'])) {
        $image = time() . '_' . basename($_FILES['image']['name']);
        $uploadDir = __DIR__ . '/../uploads/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        move_uploaded_file(
            $_FILES['image']['tmp_name'],
            $uploadDir . $image
        );
    }

    $stmt = $pdo->prepare(
        "INSERT INTO projects (title, description, image) VALUES (?,?,?)"
    );
    $stmt->execute([$_POST['title'], $_POST['description'], $image]);
}
$projects = $pdo->query("SELECT * FROM projects ORDER BY id DESC")->fetchAll();
?>


<form method="post" enctype="multipart/form-data">
    <input name="title" required>
    <textarea name="description"></textarea>
    <input type="file" name="image">
    <button>Add</button>
</form>


<?php foreach ($projects as $p): ?>
    <p><b><?= $p['title'] ?></b></p>
    <p><?= $p['description'] ?></p>
    <?php if ($p['image']): ?>
        <img src="../uploads/<?= htmlspecialchars($p['image']) ?>" style="max-width:200px;">
    <?php endif; ?>
    <hr>
<?php endforeach; ?>