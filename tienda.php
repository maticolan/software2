<?php
include('conexion.php'); 
session_start(); 
// FUN-014: Si el usuario tiene la novedad activa, muestra un mensaje y la desactiva
$correo = $_SESSION['correo']; // Usamos el correo como identificador único (PK)
// Consulta preparada para obtener el nombre del usuario con novedad activa
$check = $conn->prepare("SELECT u.nombre AS nombre_usuario, r.correo_emisor AS correo_emisor_regalo, f.fecha AS fecha_regalo
    FROM usuario u
    RIGHT JOIN regalo r ON u.correo = r.correo_receptor
    RIGHT JOIN finanza f ON u.correo = f.correo
    WHERE u.correo = ? AND u.novedad = 1
    ORDER BY f.fecha DESC;
    ");
$check->bind_param("s", $correo);
$check->execute();
$res = $check->get_result();
if ($res->num_rows > 0) {
    $row = $res->fetch_assoc();

    $correoEmisor = $row['correo_emisor_regalo'];
    echo "<script>alert('¡Has recibido un regalo de $correoEmisor!');</script>";
    $conn->query("UPDATE usuario SET novedad = 0 WHERE correo = '$correo'");
}

// FUN-015: Consulta para obtener productos aleatorios para "Recientemente añadidos"
// Selecciona productos de la tabla contenido, mostrando nombre, preview y tipo
$consulta = $conn->prepare("
    SELECT c.nombre_prod, c.ruta_preview, c.precio, ta.tipo, p.porcentaje 
    FROM contenido c 
    LEFT JOIN tipo_archivo ta ON c.tipo_archivo_id = ta.id 
    LEFT JOIN promocion p ON c.nombre_prod = p.contenido AND CURDATE() BETWEEN p.fecha_ini AND p.fecha_fin
    WHERE c.fecha IS NOT NULL
    ORDER BY c.fecha DESC 
    LIMIT 10
");
$consulta->execute();
$result = $consulta->get_result();
$productos = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Si el producto tiene un descuento
        if (!empty($row['porcentaje'])) {
            // Calcular el precio con descuento
            $precio_con_descuento = $row['precio'] - ($row['precio'] * ($row['porcentaje'] / 100));
            $row['precio_con_descuento'] = $precio_con_descuento;
        } else {
            // Si no tiene descuento, asignar el precio original
            $row['precio_con_descuento'] = $row['precio'];
        }
        $productos[] = $row;
    }
}

$categorias_disponibles = [
    'video' => [],
    'imagen' => [],
    'audio' => []
];

$query = "SELECT DISTINCT categoria_nombre, tipo_archivo_id FROM contenido";
$result = $conn->query($query);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tipo = strtolower($row['tipo_archivo_id']);
        $cat = $row['categoria_nombre'];
        if (isset($categorias_disponibles[$tipo]) && !in_array($cat, $categorias_disponibles[$tipo])) {
            $categorias_disponibles[$tipo][] = $cat;
        }
    }
}

// Consulta para obtener productos con promociones activas
$consulta_promociones = $conn->prepare("
    SELECT c.nombre_prod, c.ruta_preview, c.precio, ta.tipo, p.porcentaje 
    FROM contenido c 
    LEFT JOIN tipo_archivo ta ON c.tipo_archivo_id = ta.id 
    INNER JOIN promocion p ON c.nombre_prod = p.contenido
    WHERE CURDATE() BETWEEN p.fecha_ini AND p.fecha_fin
    ORDER BY c.fecha DESC
    LIMIT 10
");

$consulta_promociones->execute();
$result_promociones = $consulta_promociones->get_result();
$productos_promocion = [];
if ($result_promociones->num_rows > 0) {
    while ($row = $result_promociones->fetch_assoc()) {
        // Calcular el precio con descuento
        $precio_con_descuento = $row['precio'] - ($row['precio'] * ($row['porcentaje'] / 100));
        $row['precio_con_descuento'] = $precio_con_descuento;
        $productos_promocion[] = $row;
    }
} else {
    echo "<p>No se encontraron productos con promociones activas.</p>";
}

$consulta_mejores_valorados = $conn->prepare("
    SELECT c.nombre_prod, c.ruta_preview, c.precio, ta.tipo, AVG(NULLIF(d.nota, '')) AS promedio, p.porcentaje
    FROM contenido c
    LEFT JOIN tipo_archivo ta ON c.tipo_archivo_id = ta.id
    LEFT JOIN descarga d ON c.nombre_prod = d.contenido
    LEFT JOIN promocion p ON c.nombre_prod = p.contenido
    GROUP BY c.nombre_prod, c.ruta_preview, c.precio, ta.tipo, p.porcentaje
    HAVING promedio > 0
    ORDER BY promedio DESC
    LIMIT 10
");

$consulta_mejores_valorados->execute();
$result_mejores_valorados = $consulta_mejores_valorados->get_result();
$productos_mejores_valorados = [];
if ($result_mejores_valorados->num_rows > 0) {
    while ($row = $result_mejores_valorados->fetch_assoc()) {
        $precio_con_descuento = $row['precio'] - ($row['precio'] * ($row['porcentaje'] / 100));
        $row['precio_con_descuento'] = $precio_con_descuento;
        $productos_mejores_valorados[] = $row;
    }
} else {
    echo "<p>No se encontraron productos con calificaciones.</p>";
}

$consulta_mas_vendidos = $conn->prepare("
    SELECT c.nombre_prod, c.ruta_preview, c.precio, ta.tipo, COUNT(d.descarga_id) AS num_descargas, p.porcentaje
    FROM contenido c
    LEFT JOIN tipo_archivo ta ON c.tipo_archivo_id = ta.id
    LEFT JOIN descarga d ON c.nombre_prod = d.contenido
    LEFT JOIN promocion p ON c.nombre_prod = p.contenido
    GROUP BY c.nombre_prod, c.ruta_preview, c.precio, ta.tipo, p.porcentaje
    ORDER BY num_descargas DESC
    LIMIT 10
");

$consulta_mas_vendidos->execute();
$result_mas_vendidos = $consulta_mas_vendidos->get_result();
$productos_mas_vendidos = [];
if ($result_mas_vendidos->num_rows > 0) {
    while ($row = $result_mas_vendidos->fetch_assoc()) {
        $precio_con_descuento = $row['precio'] - ($row['precio'] * ($row['porcentaje'] / 100));
        $row['precio_con_descuento'] = $precio_con_descuento;
        $productos_mas_vendidos[] = $row;
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>MediaShop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #ffffff;
            color: #333;
        }

        .top-bar {
            background-color: #fff;
            color: #777;
            padding: 10px 0;
            text-align: center;
            font-size: 14px;
            display: flex;
            justify-content: space-around;
            border-bottom: 1px solid #ddd;
        }

        .main-bar {
            background-color: #e0e0e0;
            color: #333;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .main-bar .icons i {
            font-size: 20px;
            margin-left: 20px;
            cursor: pointer;
        }

        .main-bar h1 {
            font-size: 28px;
        }

        .search-bar {
            display: flex;
            justify-content: center;
            padding: 20px;
        }

        .search-bar input {
            width: 60%;
            padding: 12px;
            font-size: 16px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        .categorias-section {
            background-color: #000;
            color: white;
            padding: 80px 0;
            text-align: center;
        }

        .categorias-buttons {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-top: 20px;
        }

        .categoria {
            background: white;
            color: #333;
            width: 100px;
            padding: 10px;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
        }

        .categoria img {
            width: 60px;
            height: 60px;
        }

        .seccion-productos {
            padding: 40px;
        }

        .productos {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 20px;
        }

        .producto {
            background: #f1f1f1;
            padding: 10px;
            border-radius: 10px;
            text-align: center;
            border: none;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .producto:hover {
            transform: scale(1.03);
        }

        .producto img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 5px;
        }

        .footer {
            background-color: #e0e0e0;
            padding: 40px;
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            color: #333;
        }

        .footer-section {
            margin: 20px;
        }

        .footer-section h4 {
            margin-bottom: 15px;
        }

        .footer-section i {
            margin-right: 8px;
        }

        .copy-bar {
            background-color: #111;
            color: white;
            text-align: center;
            padding: 10px;
        }
    </style>
</head>
<body>

<!-- Top bar -->
<div class="top-bar">
    <span>Entrega a tiempo garantizada</span>
    <span>Atención ágil y eficiente</span>
    <span>100% original</span>
    <span>De confianza</span>
</div>

<!-- Main bar -->
<div class="main-bar">
    <h1>MediaShop</h1>
    <div class="icons">
        <i class="fas fa-search" onclick="window.location='tienda.php'"></i>
        <i class="fas fa-user" onclick="window.location='perfil.php'"></i>
        <i class="fas fa-heart" onclick="window.location='deseos.php'"></i>
        <i class="fas fa-shopping-cart" onclick="window.location='carrito.php'"></i>
        <i class="fas fa-download" onclick="window.location='coleccion.php'"></i>
    </div>
</div>

<!-- Buscador -->
<div class="search-bar" style="text-align:center; padding:20px;">
    <form action="buscar_productos.php" method="GET" style="display:inline-block; width: 60%;">
        <input type="text" name="busqueda" placeholder="Buscar en MediaShop..." 
               style="width: 80%; padding: 10px; font-size: 16px; border-radius: 5px; border: 1px solid #ccc;" required>
        <button type="submit" style="padding: 10px 15px; font-size: 16px; border-radius: 5px; cursor:pointer;">Buscar</button>
    </form>
</div>

<!-- Categorías -->
<div class="categorias-section">
    <h2>Tipos de Contenido</h2>
    <div class="categorias-buttons">
        <div class="categoria" onclick="window.location.href='categorias.php?tipo=video'">
            <img src="imagenes/icons/video.png" alt="Videos">
            <div>Videos</div>
        </div>
        <div class="categoria" onclick="window.location.href='categorias.php?tipo=imagen'">
            <img src="imagenes/icons/imagen.png" alt="Imágenes">
            <div>Imágenes</div>
        </div>
        <div class="categoria" onclick="window.location.href='categorias.php?tipo=audio'">
            <img src="imagenes/icons/audio.png" alt="Audios">
            <div>Audios</div>
        </div>
    </div>
</div>

<!-- Recientemente Añadidos -->
<div class="seccion-productos">
    <h2>Recientemente Añadidos</h2>
    <div class="productos">
        <?php if (empty($productos)): ?>
            <p style="color: red;">No se encontraron productos recientes.</p>
        <?php else: ?>
            <?php foreach ($productos as $producto): ?>
                <?php
                    $tipo = strtolower($producto['tipo']);
                    $nombre = htmlspecialchars($producto['nombre_prod']);
                    $vista_previa = htmlspecialchars($producto['ruta_preview']);
                    $nombre_url = urlencode($nombre);
                    $descuento = isset($producto['porcentaje']) ? $producto['porcentaje'] : 0;                    
                    $precio_original = $producto['precio'];
                    $precio_con_descuento = $producto['precio_con_descuento'];

                    // Determina la página de detalles según la categoría
                    if ($tipo == "imagen") {
                        $pagina_detalle = "detalle_imagen.php?nombre=$nombre_url";
                    } elseif ($tipo == "video") {
                        $pagina_detalle = "detalle_video.php?nombre=$nombre_url";
                    } elseif ($tipo == "audio") {
                        $pagina_detalle = "detalle_audio.php?nombre=$nombre_url";
                    } else {
                        $pagina_detalle = "#";
                    }
                ?>
                <button class="producto" onclick="location.href='<?= $pagina_detalle ?>'">
                    <img src="<?= $vista_previa ?>" alt="Vista previa de <?= $nombre ?>">
                    <p><?= $nombre ?></p>
                    <?php if ($descuento > 0): ?>
                        <p><span style="text-decoration: line-through;">$ <?= number_format($precio_original, 2) ?></span> <strong>$ <?= number_format($precio_con_descuento, 2) ?></strong></p>
                    <?php else: ?>
                        <p>$ <?= number_format($precio_original, 2) ?></p>
                    <?php endif; ?>                
                </button>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>


<!-- Promociones -->
<div class="seccion-productos">
    <h2>Promociones</h2>
    <div class="productos">
        <?php if (empty($productos_promocion)): ?>
            <p style="color: red;">No hay promociones disponibles en este momento.</p>
        <?php else: ?>
            <?php foreach ($productos_promocion as $producto): ?>
                <?php
                    $tipo = strtolower($producto['tipo']);
                    $nombre = htmlspecialchars($producto['nombre_prod']);
                    $vista_previa = htmlspecialchars($producto['ruta_preview']);
                    $nombre_url = urlencode($nombre);
                    $descuento = isset($producto['porcentaje']) ? $producto['porcentaje'] : 0;
                    $precio_original = $producto['precio'];
                    $precio_con_descuento = $producto['precio_con_descuento'];

                    // Determina la página de detalles según la categoría
                    if ($tipo == "imagen") {
                        $pagina_detalle = "detalle_imagen.php?nombre=$nombre_url";
                    } elseif ($tipo == "video") {
                        $pagina_detalle = "detalle_video.php?nombre=$nombre_url";
                    } elseif ($tipo == "audio") {
                        $pagina_detalle = "detalle_audio.php?nombre=$nombre_url";
                    } else {
                        $pagina_detalle = "#";
                    }
                ?>
                <button class="producto" onclick="location.href='<?= $pagina_detalle ?>'">
                    <img src="<?= $vista_previa ?>" alt="Vista previa de <?= $nombre ?>">
                    <p><?= $nombre ?></p>
                    <p><span style="text-decoration: line-through;">$ <?= number_format($precio_original, 2) ?></span> <strong>$ <?= number_format($precio_con_descuento, 2) ?></strong></p>
                    <?php if ($descuento > 0): ?>
                        <p><strong>Descuento: <?= number_format($descuento, 2) ?>%</strong></p>
                    <?php endif; ?>
                </button>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Mejores Valorados -->
<div class="seccion-productos">
    <h2>Mejores Valorados</h2>
    <div class="productos">
        <?php if (empty($productos_mejores_valorados)): ?>
            <p style="color: red;">No se encontraron productos mejor valorados.</p>
        <?php else: ?>
            <?php foreach ($productos_mejores_valorados as $producto): ?>
                <?php
                    $tipo = strtolower($producto['tipo']);
                    $nombre = htmlspecialchars($producto['nombre_prod']);
                    $vista_previa = htmlspecialchars($producto['ruta_preview']);
                    $nombre_url = urlencode($nombre);
                    $descuento = isset($producto['porcentaje']) ? $producto['porcentaje'] : 0;
                    $precio_original = $producto['precio'];
                    $precio_con_descuento = $producto['precio_con_descuento'];
                    $promedio = number_format($producto['promedio'], 2);

                    // Determina la página de detalles según la categoría
                    if ($tipo == "imagen") {
                        $pagina_detalle = "detalle_imagen.php?nombre=$nombre_url";
                    } elseif ($tipo == "video") {
                        $pagina_detalle = "detalle_video.php?nombre=$nombre_url";
                    } elseif ($tipo == "audio") {
                        $pagina_detalle = "detalle_audio.php?nombre=$nombre_url";
                    } else {
                        $pagina_detalle = "#";
                    }
                ?>
                <button class="producto" onclick="location.href='<?= $pagina_detalle ?>'">
                    <img src="<?= $vista_previa ?>" alt="Vista previa de <?= $nombre ?>">
                    <p><?= $nombre ?></p>
                    <?php if ($descuento > 0): ?>
                        <p><span style="text-decoration: line-through;">$ <?= number_format($precio_original, 2) ?></span> <strong>$ <?= number_format($precio_con_descuento, 2) ?></strong></p>
                    <?php else: ?>
                        <p>$ <?= number_format($precio_original, 2) ?></p>
                    <?php endif; ?>   
                    <p>Promedio: <?= $promedio ?></p>
                </button>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Contenidos Más Vendidos -->
<div class="seccion-productos">
    <h2>Más Vendidos</h2>
    <div class="productos">
        <?php if (empty($productos_mas_vendidos)): ?>
            <p style="color: red;">No se encontraron productos más vendidos.</p>
        <?php else: ?>
            <?php foreach ($productos_mas_vendidos as $producto): ?>
                <?php
                    $tipo = strtolower($producto['tipo']);
                    $nombre = htmlspecialchars($producto['nombre_prod']);
                    $vista_previa = htmlspecialchars($producto['ruta_preview']);
                    $nombre_url = urlencode($nombre);
                    $descuento = isset($producto['porcentaje']) ? $producto['porcentaje'] : 0;
                    $precio_original = $producto['precio'];
                    $precio_con_descuento = $producto['precio_con_descuento'];
                    $num_descargas = $producto['num_descargas']; // Número de descargas

                    // Determina la página de detalles según la categoría
                    if ($tipo == "imagen") {
                        $pagina_detalle = "detalle_imagen.php?nombre=$nombre_url";
                    } elseif ($tipo == "video") {
                        $pagina_detalle = "detalle_video.php?nombre=$nombre_url";
                    } elseif ($tipo == "audio") {
                        $pagina_detalle = "detalle_audio.php?nombre=$nombre_url";
                    } else {
                        $pagina_detalle = "#";
                    }
                ?>
                <button class="producto" onclick="location.href='<?= $pagina_detalle ?>'">
                    <img src="<?= $vista_previa ?>" alt="Vista previa de <?= $nombre ?>">
                    <p><?= $nombre ?></p>
                    <?php if ($descuento > 0): ?>
                        <p><span style="text-decoration: line-through;">$ <?= number_format($precio_original, 2) ?></span> <strong>$ <?= number_format($precio_con_descuento, 2) ?></strong></p>
                    <?php else: ?>
                        <p>$ <?= number_format($precio_original, 2) ?></p>
                    <?php endif; ?>   
                    <p>Comprado <?= $num_descargas ?> veces</p>
                </button>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>


<!-- Footer -->
<div class="footer">
    <div class="footer-section">
        <h4>MediaShop</h4>
        <div><i class="fab fa-facebook"></i><i class="fab fa-twitter"></i><i class="fab fa-instagram"></i></div>
    </div>
    <div class="footer-section">
        <h4>Métodos de pago</h4>
        <div><i class="fab fa-cc-visa"></i> Visa</div>
        <div><i class="fab fa-cc-mastercard"></i> Mastercard</div>
        <div><i class="fas fa-mobile-alt"></i> Yape</div>
        <div><i class="fab fa-cc-amex"></i> American Express</div>
    </div>
    <div class="footer-section">
        <h4>Ayuda</h4>
        <div>Servicio al Cliente</div>
        <div>Preguntas Frecuentes</div>
        <div>Términos y Condiciones</div>
    </div>
    <div class="footer-section">
        <h4>Contáctanos</h4>
        <div><i class="fas fa-phone"></i> +51 999-999-999</div>
        <div><i class="fas fa-envelope"></i> mediashop@gmail.com</div>
    </div>
</div>

<div class="copy-bar">
    © <?= date('Y') ?> MediaShop - Todos los derechos reservados
</div>

</body>
</html>
