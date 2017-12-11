<?php

if(isset($_GET['PID'])) {

		$connect = mysqli_connect("localhost", "root", "dreamquery", "classicmodels");
		$output  = '';
		$query	 = "SELECT * FROM products WHERE productCode = '" . $_GET['PID'] . "'";
		$result  = mysqli_query($connect, $query);

		if (mysqli_num_rows($result) > 0) {
			while ($row = mysqli_fetch_array($result)) {
				$output = $row['productName'];
			}
		}
}

?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Dream Query</title>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
	<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container">
<?php include_once 'header.php'; ?>
<br />
	<h2 align="center"><?php echo $output; ?></h2><br />
</div>
</body>
</html>
