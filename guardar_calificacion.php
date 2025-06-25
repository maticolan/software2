<?php
session_start();
include 'conexion.php';

/*
FUN-073: Validar sesión de usuario
Verifica que el usuario haya iniciado sesión antes de permitir guardar la calificación.
*/
if (!isset($_SESSION['correo'])) {
    http_response_code(403);
    echo "No autorizado.";
    exit();
}

$correo = $_SESSION['correo'];
$contenido = $_POST['contenido'] ?? '';
$nota = $_POST['nota'] ?? '';

/*
FUN-074: Validar datos recibidos para la calificación
Verifica que el contenido no esté vacío, que la nota sea numérica y esté en el rango permitido (1 a 10).
*/
if (empty($contenido) || !is_numeric($nota) || $nota < 1 || $nota > 10) {
    http_response_code(400);
    echo "Datos inválidos.";
    exit();
}

/*
FUN-075: Guardar calificación en la base de datos
Actualiza la nota del producto descargado por el usuario en la tabla descarga.
*/
$stmt = $conn->prepare("UPDATE descarga SET nota = ? WHERE correo_usuario = ? AND contenido = ?");
$stmt->bind_param("iss", $nota, $correo, $contenido);

if ($stmt->execute()) {
    echo "Calificación guardada correctamente.";
} else {
    http_response_code(500);
    echo "Error al guardar la calificación.";
}
?>
