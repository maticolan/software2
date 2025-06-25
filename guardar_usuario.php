<?php
include('conexion.php');
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['correo'];
    $nombre = $_POST['nombre'];
    $numero = $_POST['numero'];
    $saldo = $_POST['saldo'];
    $estado = isset($_POST['estado']) ? 'cliente' : 'ex-cliente';

    // Si pasa a ex-cliente, el saldo debe ir a 0
    if ($estado === 'ex-cliente') {
        $saldo = 0;
    }

    $stmt = $conn->prepare("UPDATE usuario SET nombre = ?, numero = ?, saldo = ?, tipo_cli = ? WHERE correo = ? AND admin = 0");
    $stmt->bind_param("sdsss", $nombre, $numero, $saldo, $estado, $correo);
    $stmt->execute();

    if ($stmt->affected_rows >= 0) {
        header("Location: perfiles_admin.php?actualizado=1");
        exit;
    } else {
        echo "Error al guardar cambios.";
    }
}
?>
