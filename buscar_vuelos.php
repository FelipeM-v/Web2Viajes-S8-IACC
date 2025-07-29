<?php
require_once 'conexion.php';

$destino = $_GET['destino'] ?? '';
$vuelos = [];

if ($destino) {
    $stmt = $pdo->prepare("SELECT * FROM VUELO WHERE destino LIKE ? AND plazas_disponibles > 0 ORDER BY fecha");
    $stmt->execute(['%' . $destino . '%']);
    $vuelos = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Buscar Vuelos</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Buscar Vuelos</h2>
        
        <div class="card">
            <h3>Filtrar por destino</h3>
            <form method="GET">
                <label>Destino:</label>
                <input type="text" name="destino" value="<?php echo htmlspecialchars($destino); ?>" placeholder="Ej: Suiza, Francia, Tailandia">
                <button type="submit">Buscar</button>
            </form>
        </div>

        <?php if ($destino): ?>
            <div class="card">
                <h3>Vuelos disponibles a "<?php echo htmlspecialchars($destino); ?>"</h3>
                <?php if (!empty($vuelos)): ?>
                    <div class="grid-container">
                        <?php foreach ($vuelos as $vuelo): ?>
                            <div class="paquete">
                                <h4><?php echo $vuelo['origen']; ?> → <?php echo $vuelo['destino']; ?></h4>
                                <p><strong>Fecha:</strong> <?php echo $vuelo['fecha']; ?></p>
                                <p><strong>Precio:</strong> $ <?php echo $vuelo['precio']; ?></p>
                                <p><strong>Plazas disponibles:</strong> <?php echo $vuelo['plazas_disponibles']; ?></p>
                                <form method="POST" action="carrito.php" style="margin-top: 15px;">
                                    <input type="hidden" name="id_vuelo" value="<?php echo $vuelo['id_vuelo']; ?>">
                                    <button type="submit" style="background: linear-gradient(135deg, #667eea, #764ba2);">Agregar al carrito</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No se encontraron vuelos disponibles para este destino.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="flex-container">
            <a href="index.php">Volver al inicio</a>
            <a href="carrito.php">Ver carrito</a>
        </div>
    </div>
</body>
</html> 