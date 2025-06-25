<?php
include('conexion.php');
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_original = $_POST['nombre_original'];
    $nuevo_nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $categoria = $_POST['categoria'];

    // PRO-01: 
    $stmt_tipo = $conn->prepare("CALL GetContenidoByNombreProd(?)");
    $stmt_tipo->bind_param("s", $nombre_original);
    $stmt_tipo->execute();
    $result = $stmt_tipo->get_result();
    if ($row = $result->fetch_assoc()) {
        $tipo_archivo_id = $row['tipo_archivo_id'];
        $ruta_archivo_actual = $row['ruta_archivo'];
        $ruta_preview_actual = $row['ruta_preview'];
    }
    $stmt_tipo->close();

    // Definimos carpeta según tipo
    if ($tipo_archivo_id >= 1 && $tipo_archivo_id <= 4) {
        $directorio_archivo = 'productos/audio/';
        $directorio_preview = 'preview/audio/';
    } elseif ($tipo_archivo_id >= 5 && $tipo_archivo_id <= 8) {
        $directorio_archivo = 'productos/video/';
        $directorio_preview = 'preview/video/';
    } elseif ($tipo_archivo_id >= 9 && $tipo_archivo_id <= 12) {
        $directorio_archivo = 'productos/imagen/';
        $directorio_preview = 'preview/imagen/';
    } else {
        echo "Tipo de archivo desconocido.";
        exit;
    }

    // PREVIEW
    if (!empty($_FILES['preview']['tmp_name'])) {
        // Eliminamos el preview anterior si existe
        if (file_exists($ruta_preview_actual)) {
            unlink($ruta_preview_actual);
        }
        $ext = strtolower(pathinfo($_FILES['preview']['name'], PATHINFO_EXTENSION));
        $nuevo_nombre_preview = uniqid() . "." . $ext;
        $ruta_preview = $directorio_preview . $nuevo_nombre_preview;
        move_uploaded_file($_FILES['preview']['tmp_name'], $ruta_preview);
    } else {
        $ruta_preview = $ruta_preview_actual;
    }

    // ARCHIVO PRINCIPAL
    if (!empty($_FILES['archivo']['tmp_name'])) {
        if (file_exists($ruta_archivo_actual)) {
            unlink($ruta_archivo_actual);
        }
        $ext = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
        $nuevo_nombre_archivo = uniqid() . "." . $ext;
        $ruta_archivo = $directorio_archivo . $nuevo_nombre_archivo;
        move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta_archivo);
    } else {
        $ruta_archivo = $ruta_archivo_actual;
    }

    // PRO-02:
    $stmt = $conn->prepare("CALL UpdateContenido(?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdssss", $nuevo_nombre, $descripcion, $precio, $ruta_preview, $ruta_archivo, $categoria, $nombre_original);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        header("Location: contenidos_admin.php?actualizado=1");
        exit;
    } else {
        echo "Error al actualizar contenido.";
    }
    $stmt->close();

}
?>
