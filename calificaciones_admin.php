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
        // PRO-15: Permite obtener todos los datos de un usuario específico a partir de su correo electrónico.
        // 1. Se prepara el procedimiento almacenado GetUsuarioByCorreo(), el cual espera 1 parámetro (el correo).
        // 2. Se asigna el valor del parámetro:
        //    - s -> string
        //    - $detalle -> contiene el correo electrónico del usuario que se desea consultar.
        // 3. Se ejecuta el procedimiento con el correo especificado.
        // 4. Se obtiene el resultado de la consulta.
        //    - Como el procedimiento devuelve solo 1 usuario (si existe), se obtiene directamente el primer resultado con fetch_assoc() y se almacena en $usuario.
        // 5. Se libera la memoria y recursos del statement preparado.
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
        // PRO-16: Obtener el listado de calificaciones realizadas por un usuario determinado en todos los contenidos que ha descargado o evaluado.
        // 1. Se prepara el procedimiento almacenado GetCalificacionesPorUsuario() que tiene como parámetro correo.
        // 2. Se asigna el valor del parámetro
        //    - s -> string
        //    - $detalle -> contiene el correo electrónico del usuario a consultar.
        // 3. Se ejecuta el procedimiento almacenado pasando el correo especificado.
        // 4. Se recuperan los resultados de la consulta.
        // 5. Se recorre cada fila del resultado:
        //    5.1. Se muestra la vista previa del contenido (ruta_preview).
        //    5.2. El nombre del producto (nombre_prod) se presenta como enlace para ver el detalle de ese contenido.
        //    5.3. Se muestra la nota asignada.
        // 6. Se liberan los recursos del statement.
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
        // PRO-17: Recuperar todos los datos completos de un contenido específico, a partir de su nombre de producto (nombre_prod).
        // 1. Se prepara el llamado al procedimiento almacenado GetContenidoPorNombre().
        // 2. Se asigna el valor del parámetro
        //    - s -> string
        //    - $detalle -> contiene el nombre exacto del producto a consultar.
        // 3. Se ejecuta la consulta con el nombre de producto recibido.
        // 4. Se recuperan los resultados obtenidos de la ejecución del procedimiento.
        // 5. Solo esperamos 1 registro (por ser nombre_prod clave primaria).
        //    - Se almacena el contenido completo en el array asociativo $contenido.
        // 6. Se liberan los recursos asociados al statement.
        $stmt = $conn->prepare("CALL GetContenidoPorNombre(?)");
        $stmt->bind_param("s", $detalle);
        $stmt->execute();
        $res = $stmt->get_result();
        $contenido = $res->fetch_assoc();
        $stmt->close();

        // PRO-18: Calcular el promedio de las calificaciones (notas) que ha recibido un contenido específico, utilizando los datos almacenados en la tabla de descargas (descarga).
        // 1. Se invoca el procedimiento GetPromedioContenido().
        // 2. Se asigna el valor del parámetro
        //    - s -> string
        //    - $detalle -> contiene el nombre exacto del producto del que se calculará el promedio de notas.
        // 3. Ejecuta la llamada al procedimiento con el parámetro correspondiente.
        // 4. Se recupera el resultado de la consulta (único registro esperado) y se almacena como array asociativo.
        //    - El campo devuelto es promedio.
        // 5. Se redondea el promedio a dos decimales para presentar el resultado de forma más amigable al usuario.
        // 6. Se cierran los recursos asociados a la consulta.
        $stmt = $conn->prepare("CALL GetPromedioContenido(?)");
        $stmt->bind_param("s", $detalle);
        $stmt->execute();
        $resProm = $stmt->get_result()->fetch_assoc();
        $promedio = round($resProm['promedio'], 2);
        $stmt->close();

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
        // PRO-19: Obtener el listado de todos los usuarios que han realizado descargas de un contenido específico y que, además, han registrado una calificación (nota).
        // 1. Se invoca el procedimiento GetUsuariosConNotas().
        // 2. Se asigna el valor del parámetro
        //    - s -> string
        //    - $detalle -> contiene el nombre exacto del contenido que estamos consultando.
        // 3. Ejecuta el procedimiento con el parámetro establecido.
        // 4. Recupera el conjunto de resultados retornados por el procedimiento.
        // 5. Se recorre cada fila del resultado, obteniendo:
        //    - nombre -> nombre del usuario que calificó.
        //    - correo -> correo del usuario.
        //    - nota -> calificación otorgada.
        //    5.1. Se imprimen dinámicamente los datos en formato de tabla HTML.
        // 6. Libera los recursos asociados a la ejecución de la consulta.
        $stmt = $conn->prepare("CALL GetUsuariosConNotas(?)");
        $stmt->bind_param("s", $detalle);
        $stmt->execute();
        $res = $stmt->get_result();
        echo "<table><tr><th>Usuario</th><th>Nota</th></tr>";
        while ($fila = $res->fetch_assoc()) {
            echo "<tr>
                    <td><a href='calificaciones_admin.php?modo=usuario&detalle={$fila['correo']}'><div class='user-circle'>". strtoupper(substr($fila['nombre'], 0, 1)) ."</div> {$fila['nombre']}</a></td>
                    <td>{$fila['nota']}</td>
                </tr>";
        }
        echo "</table>";
        $stmt->close();

    } elseif ($modo == 'usuario') {
        // PRO-20: Realizar una búsqueda flexible de usuarios, permitiendo encontrar usuarios cuyos nombres contengan una subcadena parcial introducida por el administrador en el buscador.
        // 1. Se invoca el procedimiento almacenado GetUsuariosPorBusquedaAvanzada().
        // 2. Antes de enviar el parámetro al procedimiento, se agregan los caracteres % a la cadena de búsqueda, de modo que la búsqueda sea flexible.
        // 3. Se asigna el valor del parámetro
        //    - s -> string
        //    - Se envía el parámetro con los comodines aplicados.
        // 4. Se ejecuta el procedimiento con el parámetro dado.
        // 5. Se obtiene el conjunto de filas devuelto por el procedimiento.
        // 6.1. Se recorren todas las filas obtenidas.
        // 6.2. De cada fila, se recupera:
        //      - nombre -> nombre del usuario encontrado.
        //      - correo -> correo del usuario.
        // 6.3. Se imprime un bloque HTML con cada usuario encontrado, incluyendo:
        //      - Un círculo con la primera letra de su nombre.
        //      - Un enlace que permite acceder al detalle del usuario en la misma vista (detalle en el query string).
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
        // PRO-21: Permite buscar contenidos (productos) de forma flexible, basándose en coincidencias parciales sobre el campo nombre_prod. Es utilizado para filtrar contenidos desde el panel de Gestión de Calificaciones → Filtrar por Contenido, facilitando encontrar rápidamente los productos almacenados, incluso escribiendo sólo una parte del nombre.
        // 1. Se invoca el procedimiento almacenado GetContenidoPorBusqueda(), el cual espera un parámetro.
        // 2. Antes de enviar el parámetro, el código agrega los comodines % para que el LIKE en MySQL funcione como búsqueda parcial.
        // 3. Se asigna el valor del parámetro
        //    - s -> string
        //    - Se envía el parámetro con los comodines aplicados.
        // 4. Se ejecuta la llamada al procedimiento almacenado.
        // 5. Se recupera el resultado de la consulta para poder recorrer los registros encontrados.
        // 6. Por cada fila obtenida:
        //    - Se recupera nombre_prod (nombre del contenido) y ruta_preview (ruta de la imagen de vista previa).
        //    Se genera dinámicamente el bloque HTML que mostrará cada producto encontrado.
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
