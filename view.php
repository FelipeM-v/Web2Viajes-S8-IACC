<?php
require_once 'conexion.php';

// Para determinar qué tipo de datos mostrar según el parámetro GET
$type = $_GET['type'] ?? 'vuelos';

// Para obtener los datos según el tipo
switch ($type) {
    case 'vuelos':
        $datos = $pdo->query("SELECT * FROM VUELO ORDER BY fecha")->fetchAll();
        $titulo = "Vuelos Disponibles";
        break;
    case 'hoteles':
        $datos = $pdo->query("SELECT * FROM HOTEL ORDER BY nombre")->fetchAll();
        $titulo = "Hoteles Registrados";
        break;
    case 'reservas':
        $datos = $pdo->query("
            SELECT r.*, v.origen, v.destino, v.fecha as fecha_vuelo, h.nombre as nombre_hotel, h.ubicacion 
            FROM RESERVA r 
            JOIN VUELO v ON r.id_vuelo = v.id_vuelo 
            JOIN HOTEL h ON r.id_hotel = h.id_hotel 
            ORDER BY r.fecha_reserva DESC
        ")->fetchAll();
        $titulo = "Reservas Realizadas";
        break;
    case 'estadisticas':
        $datos = $pdo->query("
            SELECT h.nombre, h.ubicacion, COUNT(r.id_reserva) as total_reservas 
            FROM HOTEL h 
            LEFT JOIN RESERVA r ON h.id_hotel = r.id_hotel 
            GROUP BY h.id_hotel 
            HAVING total_reservas > 2 
            ORDER BY total_reservas DESC
        ")->fetchAll();
        $titulo = "Hoteles con Más Reservas";
        break;
    default:
        $datos = [];
        $titulo = "Datos no encontrados";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title><?php echo $titulo; ?> - Agencia de Viajes</title>
</head>
<body>
    <div class="container">
        <h1><?php echo $titulo; ?></h1>
        
        <!-- Para navegar entre diferentes tipos de datos -->
        <div class="nav-menu">
            <a href="index.php">← Volver al Inicio</a>
            <a href="view.php?type=vuelos">Ver Vuelos</a>
            <a href="view.php?type=hoteles">Ver Hoteles</a>
            <a href="view.php?type=reservas">Ver Reservas</a>
            <a href="view.php?type=estadisticas">Estadísticas</a>
        </div>

        <!-- Para mostrar los datos según el tipo -->
        <div class="grid-container">
            <?php if (empty($datos)): ?>
                <div class="card">
                    <h2>No hay datos disponibles</h2>
                    <p>No se encontraron registros para mostrar.</p>
                </div>
            <?php else: ?>
                <?php foreach ($datos as $item): ?>
                    <div class="card">
                        <?php if ($type === 'vuelos'): ?>
                            <h3><?php echo $item['origen']; ?> → <?php echo $item['destino']; ?></h3>
                            <p><strong>Fecha:</strong> <?php echo $item['fecha']; ?></p>
                            <p><strong>Precio:</strong> $<?php echo number_format($item['precio'], 2); ?></p>
                            <p><strong>Plazas:</strong> <?php echo $item['plazas_disponibles']; ?></p>
                            <form method="POST" action="carrito.php" style="margin-top: 15px;">
                                <input type="hidden" name="id_vuelo" value="<?php echo $item['id_vuelo']; ?>">
                                <button type="submit" style="width: 100%;">Agregar al Carrito</button>
                            </form>
                            
                        <?php elseif ($type === 'hoteles'): ?>
                            <h3><?php echo $item['nombre']; ?></h3>
                            <p><strong>Ubicación:</strong> <?php echo $item['ubicacion']; ?></p>
                            <p><strong>Estrellas:</strong> <?php echo str_repeat('⭐', $item['estrellas']); ?></p>
                            <p><strong>Precio por noche:</strong> $<?php echo number_format($item['precio_noche'], 2); ?></p>
                            
                        <?php elseif ($type === 'reservas'): ?>
                            <h3>Reserva #<?php echo $item['id_reserva']; ?></h3>
                            <p><strong>Vuelo:</strong> <?php echo $item['origen']; ?> → <?php echo $item['destino']; ?></p>
                            <p><strong>Fecha del vuelo:</strong> <?php echo $item['fecha_vuelo']; ?></p>
                            <p><strong>Hotel:</strong> <?php echo $item['nombre_hotel']; ?> en <?php echo $item['ubicacion']; ?></p>
                            <p><strong>Estado:</strong> <span style="color: <?php echo $item['estado'] === 'confirmada' ? 'green' : 'orange'; ?>;"><?php echo ucfirst($item['estado']); ?></span></p>
                            <p><strong>Fecha de reserva:</strong> <?php echo $item['fecha_reserva']; ?></p>
                            
                        <?php elseif ($type === 'estadisticas'): ?>
                            <h3><?php echo $item['nombre']; ?></h3>
                            <p><strong>Ubicación:</strong> <?php echo $item['ubicacion']; ?></p>
                            <p><strong>Total de reservas:</strong> <span style="color: #667eea; font-weight: bold; font-size: 1.2em;"><?php echo $item['total_reservas']; ?></span></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 