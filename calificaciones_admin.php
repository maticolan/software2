<?php
include('conexion.php');
session_start();

// Modo de vista (usuario o contenido)
$modo = isset($_GET['modo']) ? $_GET['modo'] : '';
$detalle = isset($_GET['detalle']) ? $_GET['detalle'] : '';
$busqueda = isset($_GET['buscar']) ? $_GET['buscar'] : '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Calificaciones - MediaShop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background-color: #f4f4f4; }
        header { background-color: #000; color: white; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; position: fixed; top: 0; width: 100%; height: 50px; z-index: 1000; }
        header h1 a { color: white; text-decoration: none; font-size: 24px; }
        .header-icons { display: flex; gap: 15px; }
        .header-icons a { color: white; font-size: 20px; }
        .sidebar { width: 220px; background-color: #c0c0c0; height: 100vh; position: fixed; top: 50px; left: 0; padding-top: 20px; display: flex; flex-direction: column; }
        .sidebar button { background: none; border: none; padding: 15px 20px; text-align: left; font-size: 16px; cursor: pointer; }
        .main { margin-left: 240px; margin-top: 70px; padding: 20px; }
        .selector { margin-bottom: 20px; }
        .user-item, .contenido-item { background: #fcefff; padding: 10px; border-radius: 8px; display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
        .user-circle { width: 35px; height: 35px; background-color: #c0aaff; color: white; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-weight: bold; margin-right: 15px; }
        .contenido-preview { width: 80px; height: 80px; object-fit: cover; margin-right: 15px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; background-color: white; }
        th, td { border: 1px solid #999; padding: 8px; text-align: center; }
        th { background-color: #ddd; }
        .detalle-container { display: flex; gap: 40px; margin-bottom: 30px; }
        .detalle-imagen { width: 300px; height: 300px; border-radius: 8px; object-fit: cover; }
        .detalle-info { flex-grow: 1; }
        .detalle-info h1 { font-size: 32px; margin: 0 0 10px 0; }
        .detalle-info .promedio { font-size: 40px; font-weight: bold; margin-top: 20px; }
        .footer { display: flex; justify-content: space-around; flex-wrap: wrap; background-color: #c0c0c0; padding: 20px; margin-top: 40px; }
        .footer-section { flex: 1; min-width: 200px; margin: 10px; }
        .copy-bar { text-align: center; padding: 10px; background-color: #aaa; font-size: 14px; }
    </style>
</head>
<body>

<header>
    <h1><a href="bienvenida_admin.php">MediaShop</a></h1>
    <div class="header-icons">
    </div>
</header>

<div class="sidebar">
    <a href="perfiles_admin.php"><button>Gestión de Usuarios</button></a>
    <a href="contenidos_admin.php"><button>Gestión de Contenidos</button></a>
    <a href="categorias_admin.php"><button>Gestión de Categorías</button></a>
    <a href="promociones_admin.php"><button>Gestión de Promociones</button></a>
    <a href="calificaciones_admin.php"><button style="background:#bbb;font-weight:bold;">Gestión de Calificaciones</button></a>
</div>

<div class="main">
    <h2>Gestión de Calificaciones</h2>

    <div class="selector">
        <form method="GET">
            <select name="modo" onchange="this.form.submit()">
                <option value="">-- Seleccionar --</option>
                <option value="usuario" <?= $modo == 'usuario' ? 'selected' : '' ?>>Filtrar por Usuario</option>
                <option value="contenido" <?= $modo == 'contenido' ? 'selected' : '' ?>>Filtrar por Contenido</option>
            </select>
            <?php if ($modo != ''): ?>
            <input type="text" name="buscar" value="<?= htmlspecialchars($busqueda) ?>" placeholder="Buscar...">
            <button>Buscar</button>
            <?php endif; ?>
        </form>
    </div>

    <?php
    if ($detalle && $modo == 'usuario') {
        // PRO-15: 
        $stmt = $conn->prepare("CALL GetUsuarioByCorreo(?)");
        $stmt->bind_param("s", $detalle);
        $stmt->execute();
        $res = $stmt->get_result();
        $usuario = $res->fetch_assoc();
        $stmt->close();

        echo "<div class='detalle-container'>
            <div class='user-circle' style='width:80px;height:80px;font-size:32px;'>" . strtoupper(substr($usuario['nombre'], 0, 1)) . "</div>
            <div><h1>{$usuario['nombre']}</h1></div></div>";

        echo "<h3>Calificaciones realizadas:</h3>";
        // PRO-16:
        $stmt = $conn->prepare("CALL GetCalificacionesPorUsuario(?)");
        $stmt->bind_param("s", $detalle);
        $stmt->execute();
        $res = $stmt->get_result();

        echo "<table><tr><th>Vista</th><th>Contenido</th><th>Nota</th></tr>";
        while ($fila = $res->fetch_assoc()) {
            echo "<tr>
                <td><img src='{$fila['ruta_preview']}' width='60'></td>
                <td><a href='calificaciones_admin.php?modo=contenido&detalle={$fila['nombre_prod']}'><b>{$fila['nombre_prod']}</b></a></td>
                <td>{$fila['nota']}</td>
            </tr>";
        }
        echo "</table>";
        $stmt->close();

    } elseif ($detalle && $modo == 'contenido') {
        // PRO-17:
        $stmt = $conn->prepare("CALL GetContenidoPorNombre(?)");
        $stmt->bind_param("s", $detalle);
        $stmt->execute();
        $res = $stmt->get_result();
        $contenido = $res->fetch_assoc();

        // PRO-18: 
        $stmt = $conn->prepare("CALL GetPromedioContenido(?)");
        $stmt->bind_param("s", $detalle);
        $stmt->execute();
        $resProm = $stmt->get_result()->fetch_assoc();
        $promedio = round($resProm['promedio'], 2);

        echo "<div class='detalle-container'>
            <img class='detalle-imagen' src='{$contenido['ruta_preview']}'>
            <div class='detalle-info'>
                <h1><b>{$contenido['nombre_prod']}</b></h1>
                <p>Autor: {$contenido['autor']}</p>
                <p>Fecha: {$contenido['fecha']} {$contenido['hora']}</p>
                <p>Tipo: ";

        $tipo_id = $contenido['tipo_archivo_id'];
        if ($tipo_id >= 1 && $tipo_id <= 4) echo "🎵 Audio";
        elseif ($tipo_id >= 5 && $tipo_id <= 8) echo "🎥 Video";
        elseif ($tipo_id >= 9 && $tipo_id <= 12) echo "🖼 Imagen";
        else echo "Desconocido";

        echo "</p><div class='promedio'>Promedio: {$promedio}</div></div></div>";

        echo "<h3>Calificaciones de usuarios:</h3>";
        // PRO-19:
        $stmt = $conn->prepare("CALL GetUsuariosConNotas(?)");
        $stmt->bind_param("s", $detalle);
        $stmt->execute();
        $res = $stmt->get_result();

        echo "<table><tr><th>Usuario</th><th>Nota</th></tr>";
        while ($fila = $res->fetch_assoc()) {
            echo "<tr>
                <td><a href='calificaciones_admin.php?modo=usuario&detalle={$fila['correo']}'><div class='user-circle'>". strtoupper(substr($fila['nombre'],0,1)) ."</div> {$fila['nombre']}</a></td>
                <td>{$fila['nota']}</td>
            </tr>";
        }
        echo "</table>";

    } elseif ($modo == 'usuario') {
        // PRO-20: 
        $stmt = $conn->prepare("CALL GetUsuariosPorBusquedaAvanzada(?)");
        $like = "%$busqueda%";
        $stmt->bind_param("s", $like);
        $stmt->execute();
        $res = $stmt->get_result();

        while ($fila = $res->fetch_assoc()) {
            echo "<div class='user-item'>
                <div class='user-left'>
                    <div class='user-circle'>". strtoupper(substr($fila['nombre'], 0, 1)) ."</div>
                    <a href='?modo=usuario&detalle={$fila['correo']}'>{$fila['nombre']}</a>
                </div></div>";
        }

    } elseif ($modo == 'contenido') {
        // PRO-21:
        $stmt = $conn->prepare("CALL GetContenidoPorBusqueda(?)");
        $like = "%$busqueda%";
        $stmt->bind_param("s", $like);
        $stmt->execute();
        $res = $stmt->get_result();

        while ($fila = $res->fetch_assoc()) {
            echo "<div class='user-item'>
                <div class='user-left'>
                    <img class='contenido-preview' src='{$fila['ruta_preview']}'>
                    <a href='?modo=contenido&detalle={$fila['nombre_prod']}'>{$fila['nombre_prod']}</a>
                </div></div>";
        }
    }
    ?>

</div>


</body>
</html>
