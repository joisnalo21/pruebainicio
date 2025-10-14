<?php
$conn = new mysqli('localhost', 'root', 'root123', 'mysql');
if ($conn->connect_error) {
  die('Error: ' . $conn->connect_error);
}
echo 'Conexión exitosa a MySQL';
?>
