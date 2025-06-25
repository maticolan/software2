<?php
include('conexion.php');
session_start();

if (!isset($_GET['id'])) {
    header("Location: promociones_admin.php");
    exit();
}

$id = intval($_GET['id']);

//PRO-43: eliminar_promocion(id_promocion INT) Elimina una promoción específica de la tabla promocion usando el ID de la promoción.
$stmt = $conn->prepare("DELETE FROM promocion WHERE id_promocion = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: promociones_admin.php?eliminado=1");
exit();
?>
