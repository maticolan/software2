<?php
$host = "localhost";
$port = 3306;
$user = "root";
$contraseña = "al72571011";
$database = "software";

$conn = new mysqli($host, $user, $contraseña, $database, $port);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>