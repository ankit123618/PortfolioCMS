<?php
session_start();
require '../config/db.php';
if (!$_SESSION['admin']) die('Denied');

if ($_POST) {
  $stmt = $pdo->prepare("UPDATE site_content SET about=?, vision=?, email=?, github=?, youtube=? WHERE id=1");
  $stmt->execute([$_POST['about'], $_POST['vision'], $_POST['email'], $_POST['github'], $_POST['youtube']]);
}

$data = $pdo->query("SELECT * FROM site_content WHERE id=1")->fetch();
?>

<form method="post">
  <h3>About</h3>
  <textarea name="about" rows="6" style="width:100%"><?= htmlspecialchars($data['about']) ?></textarea>

  <h3>Vision</h3>
  <textarea name="vision" rows="6" style="width:100%"><?= htmlspecialchars($data['vision']) ?></textarea>

  <h3>Contact</h3>
  <p>Email: <input name="email" value="<?= htmlspecialchars($data['email']) ?>"></p>
  <p>GitHub: <input name="github" value="<?= htmlspecialchars($data['github']) ?>"></p>
  <p>YouTube: <input name="youtube" value="<?= htmlspecialchars($data['youtube']) ?>"></p>

  <br><br>
  <button>Save</button>
</form>

<?php foreach($data as $d): ?>
<p><?= $d['about'] ?></p>
<p><?= $d['vision'] ?></p>
<p><?= $d['email'] ?></p>
<p><?= $d['github'] ?></p>
<p><?= $d['youtube'] ?></p>
<?php endforeach; ?>