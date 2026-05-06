
<?php
$hostname = "sql308.infinityfree.com";
$username = "if0_41841887";
$password = "MANABAT08";
$database = "if0_41841887_crud_sample";

$conn = new mysqli($hostname, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
