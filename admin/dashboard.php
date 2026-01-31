<?php session_start(); if(!$_SESSION['admin']) die('Access denied'); ?>
<h2>Admin Panel</h2>
<ul>
<li><a href="projects.php">Manage Projects</a></li>
<li><a href="skills.php">Manage Skills</a></li>
<li><a href="content.php">Manage Site Content</a></li>

<li><a href="logout.php">Logout</a></li>
</ul>