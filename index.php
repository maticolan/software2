<?php session_start();  // Inicia la sesión PHP para manejar variables de sesión    ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>MediaShop - Login</title>
    <style>
        /* Estilos generales para la página, incluyendo fondo, navbar, formularios y notificaciones */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }

        body {
            background: url('https://via.placeholder.com/1200x800') no-repeat center center fixed;
            background-size: cover;
        }

        .overlay {
            background-color: rgba(0, 0, 0, 0.5);
            width: 100%;
            height: 100vh;
            position: absolute;
            top: 0;
            left: 0;
        }

        .navbar {
            width: 100%;
            background-color: white;
            padding: 15px 0;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            z-index: 2;
            position: relative;
        }

        .container {
            position: relative;
            z-index: 3;
            width: 100%;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .box {
            background-color: white;
            width: 400px;
            padding: 30px 20px 80px 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
            position: relative;
        }

        .top-buttons {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 20px;
        }

        .top-buttons button {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            border-bottom: 2px solid transparent;
        }

        .top-buttons button.active {
            border-bottom: 2px solid #6a0dad;
        }

        .box h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .box input, .box select {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .forgot-contraseña {
            display: block;
            text-align: left;
            margin-bottom: 20px;
            font-size: 14px;
            color: #6a0dad;
            text-decoration: none;
        }

        .box button.submit-btn {
            width: 100%;
            padding: 12px;
            background-color: #6a0dad;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        .checkboxes {
            margin-top: 15px;
            font-size: 14px;
        }

        .checkboxes label {
            display: block;
            margin-top: 5px;
        }

        .social-login {
            position: absolute;
            bottom: 15px;
            right: 20px;
            font-size: 14px;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .social-login img {
            width: 24px;
            height: 24px;
            cursor: pointer;
        }

        .notification {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.6);
            display: flex;
            justify-content: center;
            align-items: center;
            display: none;
        }

        .notification-box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
        }

        .notification-box img {
            width: 40px;
            margin-top: 10px;
        }

        .close-notification {
            position: absolute;
            top: 10px;
            right: 20px;
            cursor: pointer;
            font-size: 20px;
        }
    </style>
</head>
<body>
    <div class="navbar">MediaShop</div> <!-- Barra superior con el nombre de la aplicación -->
    <div class="overlay"></div> <!-- Capa oscura sobre el fondo para resaltar el contenido -->

    <div class="container">
        <div class="box" id="formBox">
            <!-- Botones para alternar entre login y registro -->
            <div class="top-buttons">
                <button class="active" onclick="mostrar('login')">Login</button>
                <button onclick="mostrar('registro')">Registro</button>
            </div>

            <!-- Formulario de inicio de sesión -->
            <div id="loginForm">
                <h2>Bienvenido</h2>
                <form action="procesar_login.php" method="POST">
                    <input type="email" name="correo" placeholder="Correo electrónico" required>
                    <input type="contraseña" name="contraseña" placeholder="Contraseña" required>
                    <a href="#" class="forgot-contraseña" onclick="mostrar('recuperar')">¿Olvidaste tu contraseña?</a>
                    <button type="submit" class="submit-btn">Login</button>
                </form>
                <div class="social-login">
                    <span>Inicio sesión con:</span>
                    <img src="https://cdn-icons-png.flaticon.com/512/145/145802.png" alt="Facebook">
                    <img src="https://cdn-icons-png.flaticon.com/512/281/281764.png" alt="Google">
                    <img src="https://cdn-icons-png.flaticon.com/512/2111/2111463.png" alt="Instagram">
                </div>
            </div>

            <!-- Formulario de registro de usuario -->
            <div id="registroForm" style="display: none;">
                <h2>Crear cuenta</h2>
                <form action="procesar_registro.php" method="POST" onsubmit="return validarRegistro()">
                    <input type="text" name="nombre" placeholder="Nombre de usuario">
                    <input type="email" name="correo" placeholder="Correo electrónico">
                    <div style="display: flex; gap: 10px;">
                        <select name="prefijo">
                            <option value="+51" selected>+51</option>
                            <option value="+54">+54</option>
                            <option value="+57">+57</option>
                        </select>
                        <input type="text" name="numero" placeholder="Número de teléfono">
                    </div>
                    <input type="contraseña" name="contraseña" placeholder="Contraseña">
                    <div class="checkboxes">
                        <label><input type="checkbox" name="terminos" required> Acepto los términos y condiciones</label>
                    </div>
                    <button type="submit" class="submit-btn">Registrarse</button>
                </form>
            </div>

            <!-- Formulario para recuperación de contraseña -->
            <div id="recuperarForm" style="display: none;">
                <h2>Recuperar contraseña</h2>
                <p>Si olvidaste tu contraseña, puedes iniciar un proceso de recuperación.<br>
                   Te enviaremos los detalles a tu correo electrónico.</p>
                <form action="procesar_recuperacion.php" method="POST" onsubmit="alert('Correo enviado'); setTimeout(() => location.href='index.php', 1000);">
                    <input type="email" name="correo" placeholder="Correo electrónico" required>
                    <button type="submit" class="submit-btn">Recuperar contraseña</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Notificación visual para mostrar éxito en el registro -->
    <div class="notification" id="registroNoti">
        <div class="notification-box">
            <div class="close-notification" onclick="cerrarNoti()">✖</div>
            <h2>¡Registro Completo!</h2>
            <img src="https://cdn-icons-png.flaticon.com/512/845/845646.png" alt="Check">
        </div>
    </div>

    <script>

        //FUW-19: mostrar(): muestra el formulario entre login, registro o recuperación
        function mostrar(tipo) {
            document.querySelectorAll('.top-buttons button').forEach(btn => btn.classList.remove('active'));
            if (tipo === 'login') {
                document.querySelector('.top-buttons button:nth-child(1)').classList.add('active');
                document.getElementById("loginForm").style.display = "block";
                document.getElementById("registroForm").style.display = "none";
                document.getElementById("recuperarForm").style.display = "none";
            } else if (tipo === 'registro') {
                document.querySelector('.top-buttons button:nth-child(2)').classList.add('active');
                document.getElementById("loginForm").style.display = "none";
                document.getElementById("registroForm").style.display = "block";
                document.getElementById("recuperarForm").style.display = "none";
            } else {
                document.getElementById("loginForm").style.display = "none";
                document.getElementById("registroForm").style.display = "none";
                document.getElementById("recuperarForm").style.display = "block";
            }
        }


        //FUW-20: validarRegistro() valida el formulario de registro antes de enviarlo
        function validarRegistro() {
            const inputs = document.querySelectorAll("#registroForm input");
            for (let i = 0; i < inputs.length; i++) {
                if (inputs[i].type !== "checkbox" && inputs[i].value.trim() === "") {
                    alert("Completa todos los campos.");
                    return false;
                }
            }
            if (!document.querySelector('input[name="terminos"]').checked) {
                alert("Debes aceptar los términos y condiciones.");
                return false;
            }
            return true;
        }

        //FUW-21: cerrarNoti(): cierra la notificación de registro exitoso
        function cerrarNoti() {
            document.getElementById("registroNoti").style.display = "none";
            mostrar('login');
        }

        <?php if (isset($_SESSION['registro_exitoso'])): ?>
            document.getElementById("registroNoti").style.display = "flex";
            <?php unset($_SESSION['registro_exitoso']); ?>
        <?php endif; ?>
    </script>
</body>
</html>

