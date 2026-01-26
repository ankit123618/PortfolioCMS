<?php
session_start(); require '../config/db.php';
if(!$_SESSION['admin']) die('Denied');


if ($_POST) {
$stmt = $pdo->prepare("INSERT INTO skills (name) VALUES (?)");
$stmt->execute([$_POST['name']]);
}


$skills = $pdo->query("SELECT * FROM skills")->fetchAll();
?>


<form method="post">
<input name="name" placeholder="Skill Name">
<button>Add</button>
</form>


<?php foreach($skills as $s): ?>
<p><b><?= $s['name'] ?></b></p>
<?php endforeach; ?>