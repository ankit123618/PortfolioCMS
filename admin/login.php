<?php
session_start();
require '../config/db.php';


if ($_POST) {
$stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username=?");
$stmt->execute([$_POST['username']]);
$user = $stmt->fetch();


if ($user && password_verify($_POST['password'], $user['password'])) {
$_SESSION['admin'] = true;
header('Location: page_editor_visual.php');
exit;
}
}
?>


<form method="post">
<input name="username" placeholder="Username">
<input name="password" type="password" placeholder="Password">
<button>Login</button>
</form>