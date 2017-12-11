<?php
	session_start();
	if (isset($_SESSION['username'])) {
		echo "Welcome " . $_SESSION['displayname'] . "!";
	} else {
		header("Location: login.php?message=please_log_in");
	}
?>

<link rel="stylesheet" type="text/css" href="style/header.css">
<nav class="main-navigationpbt" id="site-navigationpbt" role="navigation">
<div class="menu-pbt-container">
	<ul class="menupbt nav-menu">
		<li><a href="/">Home</a></li>
		<li><a href="customers.php">Customers</a></li>
		<li><a href="products.php">Products</a></li>
		<li><a href="store">Stores</a></li>
	</ul>
</div>
</nav>