<?php
// Incluye la conexión a la base de datos
include('conexion.php');
session_start();

// Verifica si se ha enviado el formulario por método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
     // Recupera los datos enviados desde el formulario
    $nombre_original = $_POST['nombre_original'];
    $nuevo_nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $categoria = $_POST['categoria'];

    // PRO-01: Obtiene los datos actuales del contenido para saber el tipo de archivo y rutas
    // 1. Creamos la variable $stmt_tipo, donde se prepara la ejecución del procedure almacenado GetContenidoByNombreProd que recibe un parámetro.
    //    Este procedure buscará en la tabla contenido el registro cuyo nombre_prod coincida con el parámetro. 
    // 2. bind_param("s", $nombre_original) indica que el parámetro es de tipo string (s).
    //    Se le pasa el valor de $nombre_original, que es el nombre actual del producto que se está editando.
    // 3. Se le pasa el valor de $nombre_original, que es el nombre actual del producto que se está editando con execute().
    // 4. Se obtiene el resultset devuelto por el procedure, que contiene (si existe) la fila completa del producto y se almacena en la variable $result.
    // 5. En el bloque de if se realiza lo siguiente:
    //    5.1. Se extrae la primera (y única) fila resultante.
    //    5.2. Se almacenan 3 datos claves en variables PHP:
    //         - $tipo_archivo_id: entero que indica el tipo de archivo (sirve para determinar la carpeta de almacenamiento).
    //         - $ruta_archivo_actual: ruta física actual del archivo principal.
    //         - $ruta_preview_actual: ruta física actual del archivo de vista previa. 
    // 6. Se libera el recurso del statement para evitar fugas de memoria.
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

    // Determina los directorios según el tipo de archivo
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
    // Procesa el nuevo archivo de previsualización (preview), si fue cargado
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
         // Si no se subió uno nuevo, mantiene el anterior
        $ruta_preview = $ruta_preview_actual;
    }

    // ARCHIVO PRINCIPAL
    // Procesa el nuevo archivo principal, si fue cargado
    if (!empty($_FILES['archivo']['tmp_name'])) {
        // Elimina el archivo anterior
        if (file_exists($ruta_archivo_actual)) {
            unlink($ruta_archivo_actual);
        }
        $ext = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
        $nuevo_nombre_archivo = uniqid() . "." . $ext;
        $ruta_archivo = $directorio_archivo . $nuevo_nombre_archivo;
        move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta_archivo);
    } else {
        // Si no se subió uno nuevo, mantiene el anterior
        $ruta_archivo = $ruta_archivo_actual;
    }

    // PRO-02: Actualiza el contenido en la base de datos con los nuevos valores
    $stmt = $conn->prepare("CALL UpdateContenido(?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdssss", $nuevo_nombre, $descripcion, $precio, $ruta_preview, $ruta_archivo, $categoria, $nombre_original);
    $stmt->execute();
    // Verifica si se actualizó correctamente
    if ($stmt->affected_rows > 0) {
        header("Location: contenidos_admin.php?actualizado=1");
        exit;
    } else {
        echo "Error al actualizar contenido.";
    }
    $stmt->close();

}
?>
