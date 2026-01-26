<?php


// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);



session_start();
// var_dump($_POST);

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