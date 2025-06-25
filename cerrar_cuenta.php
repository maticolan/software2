<?php
session_start();
include 'conexion.php';


if (!isset($_SESSION['correo'])) {
    header("Location: login.php");
    exit();
}

$correo = $_SESSION['correo'];



//PRO-27: eliminar_cuenta_usuario (IN correo VARCHAR(100)): Actualiza el tipo de cliente a "ex-cliente" y reinicia el saldo a 0 para el usuario especificado.
$stmt = $conn->prepare("UPDATE usuario SET tipo_cli = 'ex-cliente', saldo = 0 WHERE correo = ?");
$stmt->bind_param("s", $correo);
$stmt->execute();


session_unset();
session_destroy();


echo "<script>
    alert('Cuenta cerrada con éxito. Su saldo ha sido eliminado. Contacte al administrador si desea reactivarla.');
    window.location='index.php';
</script>";
exit();
?>
