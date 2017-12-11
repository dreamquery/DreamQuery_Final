<?php

$connect = mysqli_connect("localhost", "root", "dreamquery", "classicmodels");
$output  = '';
if (isset($_POST["query"])) {
	$search = mysqli_real_escape_string($connect, $_POST["query"]);
	$query	= " SELECT customerName, contactFirstName, contactLastName, phone, addressLine1, addressLine2, city, state, postalCode, country
				FROM customers
				WHERE customerName LIKE '%" . $search . "%'
				  OR contactFirstName LIKE '%" . $search . "%' 
				  OR contactLastName LIKE '%" . $search . "%'
				ORDER BY contactFirstName";
} else {
	$query = "SELECT customerName, contactFirstName, contactLastName, phone, addressLine1, addressLine2, city, state, postalCode, country FROM customers ORDER BY customerName LIMIT 20";
}

$result = mysqli_query($connect, $query);

if (mysqli_num_rows($result) > 0) {
	$output .= '<div class="table-responsive">
				<table class="table table bordered">
				<tr>
					<th>Customer Name</th>
					<th>First Name</th>
					<th>Last Name</th>
					<th>Phone</th>
					<th>Address</th>
					<th>Address</th>
					<th>City</th>
					<th>State</th>
					<th>Postal Code</th>
					<th>Country</th>
				</tr>';
	while ($row = mysqli_fetch_array($result)) {
		$output .= '<tr>
						<td>' . $row["customerName"] . '</td>
						<td>' . $row["contactFirstName"] . '</td>
						<td>' . $row["contactLastName"] . '</td>
						<td>' . $row["phone"] . '</td>
						<td>' . $row["addressLine1"] . '</td>
						<td>' . $row["addressLine2"] . '</td>
						<td>' . $row["city"] . '</td>
						<td>' . $row["state"] . '</td>
						<td>' . $row["postalCode"] . '</td>
						<td>' . $row["country"] . '</td>
					</tr>';
    }
	echo $output;
} else {
    echo 'Data Not Found';
}

?>