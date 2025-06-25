<?php
include('conexion.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $original = trim($_POST['original']);

    if (empty($nombre)) {
        header("Location: categorias_admin.php?error=Nombre vacío");
        exit();
    }

    // Validación de nombre duplicado
    $stmt = $conn->prepare("SELECT COUNT(*) FROM categoria WHERE nombre = ? AND nombre != ?");
    $stmt->bind_param("ss", $nombre, $original);
    $stmt->execute();
    $stmt->bind_result($existe);
    $stmt->fetch();
    $stmt->close();

    if ($existe > 0) {
        header("Location: categorias_admin.php?error=Nombre duplicado");
        exit();
    }

    // AGREGAR NUEVO
    if (empty($original)) {
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
            $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, ['jpg','jpeg','png','gif','webp'])) {
                header("Location: categorias_admin.php?error=Formato inválido");
                exit();
            }
            $nombreArchivo = uniqid() . "." . $extension;
            $ruta = "imagenes/categorias/" . $nombreArchivo;
            if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta)) {
                header("Location: categorias_admin.php?error=Error al subir imagen");
                exit();
            }
        } else {
            header("Location: categorias_admin.php?error=Imagen requerida");
            exit();
        }

        $stmt = $conn->prepare("INSERT INTO categoria (nombre, ruta_preview) VALUES (?, ?)");
        $stmt->bind_param("ss", $nombre, $ruta);
        $stmt->execute();
        header("Location: categorias_admin.php?success=1");
        exit();
    }

    // ACTUALIZAR
    if (!empty($original)) {
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
            $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, ['jpg','jpeg','png','gif','webp'])) {
                header("Location: categorias_admin.php?error=Formato inválido");
                exit();
            }
            $nombreArchivo = uniqid() . "." . $extension;
            $ruta = "imagenes/categorias/" . $nombreArchivo;
            if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta)) {
                header("Location: categorias_admin.php?error=Error al subir imagen");
                exit();
            }
            $stmt = $conn->prepare("UPDATE categoria SET nombre = ?, ruta_preview = ? WHERE nombre = ?");
            $stmt->bind_param("sss", $nombre, $ruta, $original);
        } else {
            $stmt = $conn->prepare("UPDATE categoria SET nombre = ? WHERE nombre = ?");
            $stmt->bind_param("ss", $nombre, $original);
        }
        $stmt->execute();
        header("Location: categorias_admin.php?success=1");
        exit();
    }
}

// ELIMINACIÓN
if (isset($_GET['eliminar'])) {
    $nombre = $_GET['eliminar'];
    $stmt = $conn->prepare("DELETE FROM categoria WHERE nombre = ?");
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    header("Location: categorias_admin.php?success=1");
    exit();
}
?>
