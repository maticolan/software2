<?php
include('conexion.php');
session_start();

if (isset($_GET['id'])) {
    $nombre_prod = $_GET['id'];

    // Verificamos si existe el contenido
    //FUB-40: verificar_existencia_contenido(nombre_prod VARCHAR) Verifica si existe un producto con el nombre especificado en la tabla contenido.
    $stmt = $conn->prepare("SELECT * FROM contenido WHERE nombre_prod = ?");
    $stmt->bind_param("s", $nombre_prod);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        // Eliminar el contenido
        //PRO-42: eliminar_contenido(nombre_prod VARCHAR) Elimina un registro de la tabla contenido según el nombre del producto.
        $delete = $conn->prepare("DELETE FROM contenido WHERE nombre_prod = ?");
        $delete->bind_param("s", $nombre_prod);
        if ($delete->execute()) {
            header("Location: contenido_video.php?eliminado=1");
            exit;
        } else {
            echo "Error al eliminar el contenido.";
        }
    } else {
        echo "Contenido no encontrado.";
    }
} else {
    echo "ID de contenido no especificado.";
}
?>