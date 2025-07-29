<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $clave = $_POST['clave'];

    // Simulación de usuario válido
    if ($usuario === 'admin' && $clave === '1234') {
        $_SESSION['usuario'] = $usuario;
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Credenciales inválidas";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
</head>
<body>
  <h1>Iniciar sesión</h1>

  <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>

  <form action="login.php" method="POST">
    <label for="usuario">Usuario:</label>
    <input type="text" name="usuario" required><br><br>
    
    <label for="clave">Contraseña:</label>
    <input type="password" name="clave" required><br><br>

    <button type="submit">Ingresar</button>
  </form>
</body>
</html>
