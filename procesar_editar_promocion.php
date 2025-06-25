<?php
include('conexion.php');
session_start();

if (!isset($_POST['id'])) {
    header("Location: promociones_admin.php");
    exit();
}

$id = intval($_POST['id']);
$fecha_ini = $_POST['fecha_ini'];
$fecha_fin = $_POST['fecha_fin'];
$porcentaje = floatval($_POST['porcentaje']);
$contenido = $_POST['contenido'];

$stmt = $conn->prepare("UPDATE promocion SET fecha_ini = ?, fecha_fin = ?, porcentaje = ?, contenido = ? WHERE id_promocion = ?");
$stmt->bind_param("ssdsi", $fecha_ini, $fecha_fin, $porcentaje, $contenido, $id);
$stmt->execute();

header("Location: promociones_admin.php");
exit();
?>
