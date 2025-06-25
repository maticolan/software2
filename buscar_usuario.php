<?php
include 'conexion.php';
session_start();

$correo_emisor = $_SESSION['correo'] ?? '';
$q = $_GET['q'] ?? '';

if (strlen($q) >= 2) {
    // PRO-14: Permite buscar usuarios (clientes) por nombre o correo para mostrarlos como posibles destinatarios en operaciones de regalo o transferencia dentro de la plataforma. Además, excluye al propio usuario que está realizando la búsqueda (para que no pueda seleccionarse a sí mismo como destinatario).
    // 1. Se prepara la ejecución del procedimiento GetUsuariosPorBusqueda(), que recibe dos parámetros:
    //    - q -> texto de búsqeuda parcial
    //    - correo_emisor -> el correo del usuario actual
    // 2. Se asignan los valores de los parámetros al procedimiento:
    //    - s -> string
    //    - $q -> texto parcial que busca coincidencias
    //    - $correo_emisor ->  el usuario actual, que será excluido de los resultados.
    // 3. Se ejecuta el procedimiento almacenado con los valores de búsqueda proporcionados.
    // 4. Se recupera el conjunto de resultados (usuarios encontrados que cumplen los filtros).
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
