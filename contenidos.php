<?php
session_start();
include 'conexion.php';

$tipo = strtolower($_GET['tipo'] ?? 'imagen');
$categoria = $_GET['categoria'] ?? '';

if (empty($categoria)) {
    echo "<p>La categoría no fue especificada.</p>";
    exit;
}

$productos = [];


//PRO-31: listar_productos_categoria_tipo (categoria VARCHAR(255), tipo VARCHAR(255)) Devuelve los productos de la categoría y tipo de archivo especificados.
$stmt = $conn->prepare("
    SELECT c.nombre_prod, c.ruta_preview 
    FROM contenido c
    JOIN tipo_archivo t ON c.tipo_archivo_id = t.id
    WHERE c.categoria_nombre = ? AND t.tipo = ?
");

$stmt->bind_param("ss", $categoria, $tipo);
$stmt->execute();
$resultado = $stmt->get_result();


while ($row = $resultado->fetch_assoc()) {
    $productos[] = $row;
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars("Contenidos de " . $categoria) ?> | MediaShop</title>
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

        .main-bar {
            background-color: #e0e0e0;
            color: #333;
            padding: 15px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .main-bar h1 {
            font-size: 28px;
        }

        .main-bar .icons {
            display: flex;
            gap: 20px;
        }

        .main-bar .icons i {
            font-size: 20px;
            cursor: pointer;
        }

        .seccion-productos {
            padding: 40px;
        }

        .seccion-productos h3 {
            margin-bottom: 20px;
            color: #333;
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
    </style>
</head>
<body>

<!-- Barra superior -->
<div class="main-bar">
    <h1>MediaShop</h1>
    <div class="icons">
        <i class="fas fa-search" onclick="window.location='tienda.php'"></i>
        <i class="fas fa-user" onclick="window.location='perfil.php'"></i>
        <i class="fas fa-heart" onclick="window.location='deseos.php'"></i>
        <i class="fas fa-shopping-cart" onclick="window.location='carrito.php'"></i>
        <i class="fas fa-download" onclick="window.location='descargas.php'"></i>
    </div>
</div>

<!-- Productos -->
<div class="seccion-productos">
    <h3>Contenidos de la categoría: <?= htmlspecialchars($categoria) ?></h3>
    <div class="productos">
        <?php if (empty($productos)): ?>
            <p style="color: red;">No se encontraron contenidos para esta categoría y tipo.</p>
        <?php else: ?>
            <?php foreach ($productos as $prod): ?>
                <?php
                    $paginaDetalle = match ($tipo) {
                        'audio' => 'detalle_audio.php',
                        'video' => 'detalle_video.php',
                        'imagen' => 'detalle_imagen.php',
                        default => '#'
                    };
                ?>
                <button class="producto" onclick="location.href='<?= $paginaDetalle ?>?nombre=<?= urlencode($prod['nombre_prod']) ?>'">
                    <img src="<?= htmlspecialchars($prod['ruta_preview']) ?>" alt="Vista previa de <?= htmlspecialchars($prod['nombre_prod']) ?>">
                    <p><?= htmlspecialchars($prod['nombre_prod']) ?></p>
                </button>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
