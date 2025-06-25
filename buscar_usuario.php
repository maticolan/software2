<?php
include 'conexion.php';
session_start();

$correo_emisor = $_SESSION['correo'] ?? '';
$q = $_GET['q'] ?? '';

if (strlen($q) >= 2) {
    // PRO-14: 
    $stmt = $conn->prepare("CALL GetUsuariosPorBusqueda(?, ?)");
    $stmt->bind_param("ss", $q, $correo_emisor);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<div class='usuario-item'>No hay usuarios coincidentes</div>";
    } else {
        while ($row = $result->fetch_assoc()) {
            echo "<div class='usuario-item' onclick=\"seleccionarUsuario('" . htmlspecialchars($row['correo']) . "')\">" .
                    htmlspecialchars($row['nombre']) .
            "</div>";
        }
    }
}
?>
