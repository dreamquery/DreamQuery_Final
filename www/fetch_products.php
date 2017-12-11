<?php

$connect = mysqli_connect("localhost", "root", "dreamquery", "classicmodels");
$output  = '';
if (isset($_POST["query"])) {
	$search = mysqli_real_escape_string($connect, $_POST["query"]);
	$query	= " SELECT productName, productLine, productScale, productVendor, productDescription, quantityInStock, buyPrice
				FROM products
				WHERE productName LIKE '%" . $search . "%'
				  OR productLine LIKE '%" . $search . "%' 
				  OR productScale LIKE '%" . $search . "%'
				  OR productVendor LIKE '%" . $search . "%' 
				  OR productDescription LIKE '%" . $search . "%'
				ORDER BY productCode";
} else {
	$query = "SELECT productName, productLine, productScale, productVendor, productDescription, quantityInStock, buyPrice FROM products ORDER BY productCode LIMIT 20";
}

$result = mysqli_query($connect, $query);

if (mysqli_num_rows($result) > 0) {
	$output .= '<div class="table-responsive">
				<table class="table table bordered">
				<tr>
					<th>Product Name</th>
					<th>Product Line</th>
					<th>Scale</th>
					<th>Vendor</th>
					<th>Description</th>
					<th>Qty</th>
					<th>Price</th>
				</tr>';
	while ($row = mysqli_fetch_array($result)) {
		$output .= '<tr>
						<td>' . $row["productName"] . '</td>
						<td>' . $row["productLine"] . '</td>
						<td>' . $row["productScale"] . '</td>
						<td>' . $row["productVendor"] . '</td>
						<td>' . $row["productDescription"] . '</td>
						<td>' . $row["quantityInStock"] . '</td>
						<td>' . $row["buyPrice"] . '</td>
					</tr>';
    }
	echo $output;
} else {
    echo 'Data Not Found';
}

?>