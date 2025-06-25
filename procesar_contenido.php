<?php
include('conexion.php');
session_start();

$tipo = $_POST['tipo'] ?? '';

if (!in_array($tipo, ['audio', 'video', 'imagen'])) {
    echo json_encode(['success' => false, 'message' => 'Tipo de producto no válido.']);
    exit();
}

$extensiones_permitidas = [
    'audio' => ['mp3', 'wav', 'ogg', 'flac'],
    'video' => ['mp4', 'avi', 'mkv', 'webm'],
    'imagen' => ['jpeg', 'jpg', 'png', 'gif', 'webp']
];

function obtenerTipoArchivoId($tipo, $extension) {
    $mapa = [
        'audio' => ['mp3' => 1, 'wav' => 2, 'ogg' => 3, 'flac' => 4],
        'video' => ['mp4' => 5, 'avi' => 6, 'mkv' => 7, 'webm' => 8],
        'imagen' => ['jpeg' => 9, 'jpg' => 9, 'png' => 10, 'gif' => 11, 'webp' => 12]
    ];
    return $mapa[$tipo][$extension] ?? null;
}

$nombre = trim($_POST['nombre']);
$descripcion = trim($_POST['descripcion']);
$precio = floatval($_POST['precio']);
$categoria = trim($_POST['categoria']);
$autor = trim($_POST['autor']);
$fecha = $_POST['fecha'];
$hora = $_POST['hora'];

if ($precio < 3) {
    echo json_encode(['success' => false, 'message' => 'El precio debe ser mayor o igual a 3.00']);
    exit();
}

$stmt = $conn->prepare("SELECT COUNT(*) FROM contenido WHERE nombre_prod = ?");
$stmt->bind_param("s", $nombre);
$stmt->execute();
$stmt->bind_result($existe);
$stmt->fetch();
$stmt->close();

if ($existe > 0) {
    echo json_encode(['success' => false, 'message' => 'Ya existe un producto con ese nombre']);
    exit();
}

$archivo = $_FILES['archivo'];
$extension_archivo = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
if (!in_array($extension_archivo, $extensiones_permitidas[$tipo])) {
    echo json_encode(['success' => false, 'message' => 'Extensión de archivo no permitida']);
    exit();
}

$tipo_archivo_id = obtenerTipoArchivoId($tipo, $extension_archivo);
if (!$tipo_archivo_id) {
    echo json_encode(['success' => false, 'message' => 'Error al identificar tipo de archivo']);
    exit();
}

$nuevoNombreArchivo = uniqid() . "." . $extension_archivo;
$ruta_archivo = "productos/$tipo/$nuevoNombreArchivo";
if (!move_uploaded_file($archivo['tmp_name'], $ruta_archivo)) {
    echo json_encode(['success' => false, 'message' => 'Error al subir el archivo principal']);
    exit();
}

if ($tipo == 'imagen') {
    $ruta_preview = $ruta_archivo;
} else {
    $preview = $_FILES['preview'];
    $extension_preview = strtolower(pathinfo($preview['name'], PATHINFO_EXTENSION));
    if (!in_array($extension_preview, $extensiones_permitidas['imagen'])) {
        echo json_encode(['success' => false, 'message' => 'La vista previa debe ser una imagen válida']);
        exit();
    }
    $nuevoNombrePreview = uniqid() . "." . $extension_preview;
    $ruta_preview = "preview/$tipo/$nuevoNombrePreview";
    if (!move_uploaded_file($preview['tmp_name'], $ruta_preview)) {
        echo json_encode(['success' => false, 'message' => 'Error al subir el preview']);
        exit();
    }
}

$sql = "INSERT INTO contenido (nombre_prod, descripcion, precio, fecha, hora, ruta_archivo, ruta_preview, categoria_nombre, autor, tipo_archivo_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssdssssssi", $nombre, $descripcion, $precio, $fecha, $hora, 
                  $ruta_archivo, $ruta_preview, $categoria, $autor, $tipo_archivo_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'tipo' => $tipo]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al insertar en la base de datos']);
}
?>
