<?php
session_start();
include("conexion.php");

/*
FUN-018: Procesar solicitud de inicio de sesión
Esta función principal verifica si la solicitud es POST, limpia los datos recibidos, consulta la base de datos por el usuario, 
verifica si la cuenta está activa y compara la contraseña. Si todo es correcto, inicia la sesión y redirige según el tipo de usuario.
*/
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // FUN-019: Limpieza básica de datos recibidos por POST
    // Elimina espacios en blanco al inicio y final de los campos de correo y contraseña.
    $correo = trim($_POST["correo"]);
    $contraseña = trim($_POST["contraseña"]);

    // FUN-020: Preparar y ejecutar consulta para buscar usuario por correo
    // Utiliza una consulta preparada para evitar inyección SQL.
    $stmt = $conn->prepare("SELECT * FROM usuario WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    // FUN-021: Verificar si el usuario existe en la base de datos
    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();

        // FUN-022: Verificar si la cuenta está activa
        // Si el tipo de cliente no es "cliente", muestra alerta y redirige.
        if ($usuario["tipo_cli"] !== "cliente") {
            echo "<script>alert('Su cuenta ha sido cerrada. Contáctese con un administrador para reactivarla.'); window.location.href='index.php';</script>";
            exit();
        }

        // FUN-023: Comparar contraseña ingresada con la almacenada (sin hash)
        // Si la contraseña es correcta, inicia sesión y redirige según el tipo de usuario.
        if ($usuario["contraseña"] === $contraseña) {
            $_SESSION["nombre"] = $usuario["nombre"];
            $_SESSION["correo"] = $usuario["correo"];
            $_SESSION["admin"] = $usuario["admin"];

            // FUN-024: Redirigir según el tipo de usuario (admin o cliente)
            if ($usuario["admin"] == 1) {
                header("Location: bienvenida_admin.php");
            } else {
                header("Location: tienda.php");
            }
            exit();
        } else {
            // FUN-025: Contraseña incorrecta, mostrar alerta y redirigir
            echo "<script>alert('Contraseña incorrecta'); window.location.href='index.php';</script>";
        }
    } else {
        // FUN-026: Usuario no encontrado, mostrar alerta y redirigir
        echo "<script>alert('Usuario no encontrado'); window.location.href='index.php';</script>";
    }

    // FUN-027: Cerrar recursos de base de datos
    $stmt->close();
    $conn->close();
}
?>
