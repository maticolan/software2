<?php
session_start();
include 'conexion.php'; // Conexión con $conn

$tipo = $_GET['tipo'] ?? 'imagen';


$titulo = match ($tipo) {
    'video' => 'Categorías de Video',
    'audio' => 'Categorías de Audio',
    'imagen' => 'Categorías de Imagen',
    default => 'Categorías'
};



//PRO-26: obtener_categorias_por_tipo (IN tipo VARCHAR(20)): Devuelve categorías (nombre, ruta_preview) que contienen al menos un contenido del tipo especificado.
$categorias = [];
$stmt = $conn->prepare("
    SELECT DISTINCT c.nombre, c.ruta_preview 
    FROM categoria c
    JOIN contenido co ON c.nombre = co.categoria_nombre
    JOIN tipo_archivo ta ON co.tipo_archivo_id = ta.id
    WHERE ta.tipo = ?
");
$stmt->bind_param("s", $tipo);
$stmt->execute();
$resultado = $stmt->get_result();


while ($row = $resultado->fetch_assoc()) {
    $categorias[] = $row;
}
$stmt->close();
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($titulo) ?> | MediaShop</title>
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

<!-- Barra superior -->
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

<!-- Sección de categorías -->
<div class="seccion-productos">
    <h3><?= htmlspecialchars($titulo) ?></h3>
    <div class="productos">
        <?php foreach ($categorias as $cat): ?>
            <button class="producto" onclick="location.href='contenidos.php?tipo=<?= urlencode($tipo) ?>&categoria=<?= urlencode($cat['nombre']) ?>'">
                <img src="<?= htmlspecialchars($cat['ruta_preview']) ?>" alt="<?= htmlspecialchars($cat['nombre']) ?>">
                <p><?= htmlspecialchars($cat['nombre']) ?></p>
            </button>
        <?php endforeach; ?>
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
