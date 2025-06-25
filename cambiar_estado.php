<?php
include('conexion.php');
session_start();

if (isset($_GET['correo'])) {
    $correo = $_GET['correo'];

// PRO-22: 
    $stmt = $conn->prepare("CALL GetUsuarioPorCorreo(?)");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    if ($result->num_rows > 0) {
// PRO-23: 
        $update = $conn->prepare("CALL UpdateUsuarioExCliente(?)");
        $update->bind_param("s", $correo);
        $update->execute();
        $update->close();
    }
}

// Redirigir con confirmación para mostrar modal
header("Location: perfiles_admin.php?eliminado=1");
exit;
?>
