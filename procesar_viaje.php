<?php
require_once 'FiltroViaje.php';
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombreHotel = $_POST['nombreHotel'];
    $ciudad = $_POST['ciudad'];
    $pais = $_POST['pais'];
    $fechaViaje = $_POST['fechaViaje'];
    $duracion = $_POST['duracion'];

 
    // Guardar en la base de datos
    try {
        $stmt = $pdo->prepare("INSERT INTO INTENCIONES_VIAJE (nombre_hotel, ciudad, pais, fecha_viaje, duracion, fecha_registro) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$nombreHotel, $ciudad, $pais, $fechaViaje, $duracion]);
        $mensaje = "¡Intención de viaje registrada exitosamente!";
        $tipo = "exito";
    } catch (PDOException $e) {
        $mensaje = "Error al registrar: " . $e->getMessage();
        $tipo = "error";
    }
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="styles.css">
        <title>Resumen de intención de viaje</title>
    </head>
    <body>
        <div class="container">
            <div class="card">
                <h2>Resumen de tu intención de viaje</h2>
                <div style="background: <?php echo $tipo == 'exito' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $tipo == 'exito' ? '#155724' : '#721c24'; ?>; padding: 15px; border-radius: 10px; margin: 15px 0;">
                    <?php echo $mensaje; ?>
                </div>
                <div class="paquete">
                    <h3>Detalles del viaje:</h3>
                    <p><strong>Hotel:</strong> <?php echo htmlspecialchars($nombreHotel); ?></p>
                    <p><strong>Ciudad:</strong> <?php echo htmlspecialchars($ciudad); ?></p>
                    <p><strong>País:</strong> <?php echo htmlspecialchars($pais); ?></p>
                    <p><strong>Fecha de viaje:</strong> <?php echo $fechaViaje; ?></p>
                    <p><strong>Duración:</strong> <?php echo $duracion; ?> días</p>
                </div>
                <div class="flex-container">
                    <a href="index.php">Volver al inicio</a>
                    <a href="buscar_vuelos.php?destino=<?php echo urlencode($ciudad); ?>">Buscar vuelos a <?php echo htmlspecialchars($ciudad); ?></a>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
} else {
    header("Location: index.php");
    exit();
}
?> 