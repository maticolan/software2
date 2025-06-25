<?php
session_start();
include 'conexion.php';

$nombre = $_GET['nombre'] ?? '';
$id = $_GET['id'] ?? '';

if (!empty($nombre)) {
    //FUB-24: obtener_detalle_imagen(nombre_prod VARCHAR(255)) Consulta los detalles completos del producto de tipo imagen (incluye posible promoción).
    $stmt = $conn->prepare("
        SELECT c.*, t.tipo, t.extension, p.porcentaje
        FROM contenido c
        JOIN tipo_archivo t ON c.tipo_archivo_id = t.id
        LEFT JOIN promocion p ON c.nombre_prod = p.contenido
        WHERE c.nombre_prod = ? AND t.tipo = 'imagen'
    ");
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $producto = $resultado->fetch_assoc();
        $nombre = htmlspecialchars($producto['nombre_prod']);
        $descripcion = htmlspecialchars($producto['descripcion']);
        $precio = htmlspecialchars($producto['precio']);
        $ruta = htmlspecialchars($producto['ruta_archivo']);
        $preview = htmlspecialchars($producto['ruta_preview']);
        $fecha = htmlspecialchars($producto['fecha']);
        $autor = htmlspecialchars($producto['autor']);
        
        // Calcular el precio con descuento si aplica
        $precio_original = $producto['precio'];
        $descuento = isset($producto['porcentaje']) ? $producto['porcentaje'] : 0;
        if ($descuento > 0) {
            $precio_con_descuento = $precio_original - ($precio_original * ($descuento / 100));
        } else {
            $precio_con_descuento = $precio_original; // Si no hay descuento, el precio es el original
        }
    } else {
        echo "<script>alert('Producto no encontrado'); window.location='tienda.php';</script>";
        exit();
    }
} else {
    echo "<script>alert('Nombre no válido'); window.location='tienda.php';</script>";
    exit();
}

$correo = $_SESSION['correo'] ?? null;

$ya_deseado = false;
if ($correo) {
    //FUB-25: verificar_producto_deseo(correo_usuario VARCHAR(255), nombre_prod VARCHAR(255)) Verifica si el producto ya está en la lista de deseos del usuario.
    $stmt = $conn->prepare("SELECT 1 FROM listadeseo WHERE correo_usuario = ? AND nombre_prod = ?");
    $stmt->bind_param("ss", $correo, $nombre);
    $stmt->execute();
    $ya_deseado = $stmt->get_result()->num_rows > 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!$correo) {
        echo "<script>alert('Debes iniciar sesión para guardar productos en tu lista de deseos');</script>";
    } else {
        if ($_POST['action'] === 'add') {
            //FUB-26: obtener_formatos_imagen_disponibles(nombre_prod VARCHAR(255)) Consulta las extensiones disponibles de imagen para el producto específico.
            $stmt = $conn->prepare("INSERT INTO listadeseo (correo_usuario, nombre_prod) VALUES (?, ?)");
            $stmt->bind_param("ss", $correo, $nombre);
            $stmt->execute();
        } elseif ($_POST['action'] === 'remove') {
            //PRO-37: eliminar_producto_deseo(correo_usuario VARCHAR(255), nombre_prod VARCHAR(255)) Elimina un producto de la lista de deseos del usuario.
            $stmt = $conn->prepare("DELETE FROM listadeseo WHERE correo_usuario = ? AND nombre_prod = ?");
            $stmt->bind_param("ss", $correo, $nombre);
            $stmt->execute();
        }
    header("Location: detalle_imagen.php?nombre=" . urlencode($nombre));
        exit();
    }
}


$ya_en_carrito = false;
if ($correo) {
    //FUB-27: verificar_producto_carrito(correo_usuario VARCHAR(255), nombre_prod VARCHAR(255)) Verifica si el producto ya está en el carrito del usuario.
    $stmt = $conn->prepare("SELECT 1 FROM carrito WHERE correo_usuario = ? AND nombre_prod = ?");
    $stmt->bind_param("ss", $correo, $nombre);
    $stmt->execute();
    $ya_en_carrito = $stmt->get_result()->num_rows > 0;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_carrito']) && $correo) {
    if (!$ya_en_carrito) {
        //PRO-38: agregar_producto_carrito(correo_usuario VARCHAR(255), nombre_prod VARCHAR(255)) Agrega un producto al carrito del usuario.
        $stmt = $conn->prepare("INSERT INTO carrito (correo_usuario, nombre_prod) VALUES (?, ?)");
        $stmt->bind_param("ss", $correo, $nombre);
        $stmt->execute();
        header("Location: detalle_imagen.php?nombre=" . urlencode($nombre));
        exit();
    }
}

$todos_formatos = [];
//FUB-28: obtener_todos_los_formatos_imagen() Lista general de extensiones registradas como imagen.
$stmt = $conn->prepare("SELECT extension FROM tipo_archivo WHERE tipo = 'imagen'");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $todos_formatos[] = $row['extension']; // Almacenar los formatos de imagen en el array
}

/*
FUN-046: Obtener extensiones permitidas para imágenes
Consulta la base de datos para obtener los formatos (extensiones) de imagen disponibles y los almacena en un arreglo.
*/
// Obtener los formatos permitidos para este producto
$formatos = [];

//FUB-29: obtener_formatos_imagen_disponibles(nombre_prod VARCHAR(255)) Consulta las extensiones disponibles de imagen para el producto específico.

$stmt = $conn->prepare("
    SELECT ta.extension
    FROM tipo_archivo ta
    JOIN contenido c ON ta.id = c.tipo_archivo_id
    WHERE c.nombre_prod = ? AND ta.tipo = 'imagen'
");
$stmt->bind_param("s", $nombre);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $formatos[] = $row['extension']; // Asignar los formatos obtenidos a la variable
}

// Obtener los formatos permitidos para este producto
$formatos_disponibles = [];
$stmt = $conn->prepare("
    SELECT ta.extension
    FROM tipo_archivo ta
    JOIN contenido c ON ta.id = c.tipo_archivo_id
    WHERE c.nombre_prod = ? AND ta.tipo = 'imagen'
");
$stmt->bind_param("s", $nombre);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $formatos_disponibles[] = $row['extension']; // Guardar los formatos permitidos para este producto
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $nombre ?> | MediaShop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; font-family: Arial, sans-serif; margin: 0; padding: 0; }
        body { background-color: #ffffff; color: #333; }

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

        .detalle {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: flex-start;
            padding: 60px 80px;
            gap: 80px;
        }

        .preview {
            flex: 1;
            max-width: 450px;
            display: flex;
            justify-content: flex-end;
        }

        .preview img {
            width: 100%;
            max-width: 420px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.15);
            cursor: pointer;
        }

        .info {
            flex: 1;
            max-width: 500px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        h2 { margin: 0; font-size: 26px; }

        .btn-carrito {
            padding: 10px 20px;
            background: #6a0dad;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-disabled {
            background: #999 !important;
            cursor: not-allowed;
        }

        .corazon {
            font-size: 24px;
            color: gray;
            cursor: pointer;
        }

        .corazon.activo {
            color: red;
        }

        .formato label { margin-right: 15px; }

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

        .preview-modal {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100vw; height: 100vh;
            background: rgba(0,0,0,0.8);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .preview-modal img {
            max-width: 90%;
            max-height: 90%;
        }
    </style>
    <script>

        //FUW-14: togglePreview(): abre una ventana en la que podremos ver una vista previa del producto seleccionado
        function togglePreview() {
            document.getElementById("modalPreview").style.display = "flex";
        }

        //FUW-15: closePreview(): cierra la ventana de la FUW-14
        function closePreview() {
            document.getElementById("modalPreview").style.display = "none";
        }
        window.addEventListener("keydown", function(e) {
            if (e.key === "Escape") closePreview();
        });

        //FUW-16: validarFormato(e): obliga al usuario a escoger un formato/tipo de archivo existente
        function validarFormato(e) {
            const formatos = document.getElementsByName('formato');
            let seleccionado = false;
            for (let f of formatos) {
                if (f.checked) seleccionado = true;
            }
            if (!seleccionado) {
                alert("Selecciona un formato antes de añadir al carrito.");
                e.preventDefault();
                return false;
            }
        }

        //FUW-17: advertirCarrito: muestra un mensaje en el que se prohibe al usuario añadir productos iguales al carrito
        function advertirCarrito() {
            alert("Este producto ya está en tu carrito.");
        }
    </script>
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

<div class="detalle">
    <div class="preview">
        <img src="<?= $preview ?>" alt="<?= $nombre ?>" onclick="togglePreview()">
    </div>
    <div class="info">
        <h2><?= $nombre ?></h2>
        <p><strong>Descripción:</strong> <?= $descripcion ?></p>
        <p><strong>Fecha de publicación:</strong> <?= $fecha ?></p>
        <p><strong>Autor:</strong> <?= $autor ?></p>
        
        <!-- Mostrar el precio con descuento si existe -->
        <?php if ($descuento > 0): ?>
            <p><span style="text-decoration: line-through;">$ <?= number_format($precio_original, 2) ?></span> 
            <strong>$ <?= number_format($precio_con_descuento, 2) ?></strong></p>
        <?php else: ?>
            <p><strong>Precio: $ <?= number_format($precio_original, 2) ?></strong></p>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="action" value="<?= $ya_deseado ? 'remove' : 'add' ?>">
            <button type="submit" class="corazon" style="color: <?= $ya_deseado ? 'red' : 'gray' ?>;">
                <i class="fas fa-heart"></i>
            </button>
        </form>

        <div class="formato">
            <strong>Selecciona el formato:</strong><br>
            <?php foreach ($todos_formatos as $f): ?>
                <?php if (in_array($f, $formatos_disponibles)): ?>
                    <!-- Si el formato está disponible para el producto, habilítalo -->
                    <label><input type="radio" name="formato" value="<?= $f ?>" checked> <?= strtoupper($f) ?></label><br>
                <?php else: ?>
                    <!-- Si el formato no está disponible para el producto, deshabilítalo -->
                    <label><input type="radio" name="formato" value="<?= $f ?>" disabled> <?= strtoupper($f) ?></label><br>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <?php if ($ya_en_carrito): ?>
            <button class="btn-carrito btn-disabled" onclick="advertirCarrito()" disabled>Ya en el carrito</button>
        <?php else: ?>
            <form method="POST" onsubmit="return validarFormato(event)">
                <input type="hidden" name="add_carrito" value="1">
                <button class="btn-carrito">Añadir al carrito</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="preview-modal" id="modalPreview" onclick="closePreview()">
    <img src="<?= $ruta ?>" alt="Vista previa">
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
