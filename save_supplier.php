<?php

include "db.php";

$supplier_name = $_POST['supplier_name'];
$contact_person = $_POST['contact_person'];
$phone = $_POST['phone'];
$email = $_POST['email'];
$address = $_POST['address'];
$status = $_POST['status'];

$sql = "INSERT INTO supplier 
        (supplier_name, contact_person, phone, email, address, status)
        VALUES 
        ('$supplier_name', '$contact_person', '$phone', '$email', '$address', '$status')";

if ($conn->query($sql) === TRUE) {
    echo "Supplier added successfully!<br><br>";
    echo "<a href='supplier.php'>View Supplier List</a>";
} else {
    echo "Error: " . $conn->error;
}

?>