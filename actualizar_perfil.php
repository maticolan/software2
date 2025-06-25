<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['correo'])) {
    header("Location: login.php");
    exit();
}

$correo = $_SESSION['correo'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nuevo_nombre = $_POST['nombre'] ?? '';
    $nuevo_numero = $_POST['numero'] ?? '';
    $nueva_contraseña = $_POST['contraseña'] ?? '';

    if (empty($nuevo_nombre) || empty($nuevo_numero)) {
        echo "<script>alert('Nombre y número son obligatorios'); window.history.back();</script>";
        exit();
    }

    if (!empty($nueva_contraseña)) {
// PRO-02: actualizar_perfil_con_contraseña (IN correo_in VARCHAR(100), IN nombre_in VARCHAR(100), IN numero_in INT, 
// IN contraseña_in VARCHAR(100)): Actualiza los datos de un usuario incluyendo su contraseña
        $stmt = $conn->prepare("CALL UpdateUsuario(?, ?, ?, ?)");
        $stmt->bind_param("siss", $nuevo_nombre, $nuevo_numero, $nueva_contraseña, $correo);
    } else {
// PRO-03: actualizar_perfil_con_contraseña (IN correo_in VARCHAR(100), IN nombre_in VARCHAR(100), IN numero_in INT): 
// Actualiza los datos de un usuario sin incluir su contraseña
        $stmt = $conn->prepare("CALL UpdateUsuarioNombreNumero(?, ?, ?)");
        $stmt->bind_param("sis", $nuevo_nombre, $nuevo_numero, $correo);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Perfil actualizado correctamente'); window.location='perfil.php';</script>";
    } else {
        echo "<script>alert('Error al actualizar perfil'); window.history.back();</script>";
    }

    $stmt->close();
} else {
    header("Location: perfil.php");
    exit();
}
?>
