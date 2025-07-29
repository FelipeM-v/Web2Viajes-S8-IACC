<?php
require_once 'conexion.php';

function mostrarNotificacionOferta() {
    $oferta = "¡Oferta especial! 20% de descuento en paquetes a Cancún esta semana.";
    echo "<script>
        window.onload = function() {
            alert('$oferta');
        }
    </script>";
}

// Para obtener datos actualizados de la base de datos
$totalVuelos = $pdo->query("SELECT COUNT(*) FROM VUELO")->fetchColumn();
$totalHoteles = $pdo->query("SELECT COUNT(*) FROM HOTEL")->fetchColumn();
$totalReservas = $pdo->query("SELECT COUNT(*) FROM RESERVA")->fetchColumn();
$vuelosDisponibles = $pdo->query("SELECT COUNT(*) FROM VUELO WHERE plazas_disponibles > 0")->fetchColumn();

// Para mostrar los destinos más populares según las reservas
$destinosPopulares = $pdo->query("SELECT destino, COUNT(*) as total FROM VUELO GROUP BY destino ORDER BY total DESC LIMIT 3")->fetchAll();

// Para mostrar ofertas especiales (vuelos con precios bajos)
$ofertasEspeciales = $pdo->query("SELECT * FROM VUELO WHERE precio < 1000 AND plazas_disponibles > 0 ORDER BY precio ASC LIMIT 3")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="styles.css">
  <title>Agencia de Viajes</title>
</head>
<body>
<?php mostrarNotificacionOferta(); ?>
  <div class="container">
    <!-- Encabezado principal de la página -->
    <h1>Agencia de Viajes</h1>
    <p style="text-align: center; color: #fff; font-size: 1.2em; margin-bottom: 2rem; text-shadow: 1px 1px 2px rgba(0,0,0,0.3);">La plataforma de tus vuelos</p>
    
    <!-- Menú principal para navegar por las diferentes secciones -->
    <div class="nav-menu">
      <a href="buscar_vuelos.php">Buscar Vuelos</a>
      <a href="view.php?type=vuelos">Ver Vuelos</a>
      <a href="view.php?type=hoteles">Ver Hoteles</a>
      <a href="carrito.php">Carrito</a>
      <a href="view.php?type=reservas">Mis Reservas</a>
      <a href="admin.php?action=add_vuelo">Registrar Vuelo</a>
      <a href="admin.php?action=add_hotel">Registrar Hotel</a>
      <a href="admin.php?action=create_reservas">Crear Reservas</a>
      <a href="view.php?type=estadisticas">Estadísticas</a>
    </div>

    <!-- Sección principal con servicios, ofertas y formularios -->
    <div class="grid-container">
      <!-- Para mostrar los servicios principales que ofrece la agencia -->
      <div class="card">
        <h2>Nuestros Servicios</h2>
        <div class="flex-container">
          <div style="text-align: center; padding: 15px; background: rgba(102, 126, 234, 0.1); border-radius: 10px; margin-bottom: 15px;">
            <h4>Reserva de Vuelos</h4>
            <p>Encuentra los mejores precios en vuelos nacionales e internacionales</p>
            <a href="buscar_vuelos.php" style="display: inline-block; margin-top: 10px; padding: 8px 16px; background: linear-gradient(135deg, #667eea, #764ba2); color: white; border-radius: 8px; text-decoration: none;">Buscar Vuelos</a>
          </div>
          <div style="text-align: center; padding: 15px; background: rgba(102, 126, 234, 0.1); border-radius: 10px; margin-bottom: 15px;">
            <h4>Reserva de Hoteles</h4>
            <p>Hoteles de calidad en los mejores destinos del mundo</p>
            <a href="view.php?type=hoteles" style="display: inline-block; margin-top: 10px; padding: 8px 16px; background: linear-gradient(135deg, #667eea, #764ba2); color: white; border-radius: 8px; text-decoration: none;">Ver Hoteles</a>
          </div>
          <div style="text-align: center; padding: 15px; background: rgba(102, 126, 234, 0.1); border-radius: 10px; margin-bottom: 15px;">
            <h4>Paquetes Personalizados</h4>
            <p>Déjanos saber tus preferencias y te ayudamos a planificar tu viaje</p>
            <a href="#registro" style="display: inline-block; margin-top: 10px; padding: 8px 16px; background: linear-gradient(135deg, #667eea, #764ba2); color: white; border-radius: 8px; text-decoration: none;">Registrar Interés</a>
          </div>
        </div>
      </div>

      <!-- Para mostrar ofertas especiales con precios reducidos -->
      <?php if (!empty($ofertasEspeciales)): ?>
      <div class="card">
        <h2>Ofertas Especiales</h2>
        <div class="flex-container">
          <?php foreach ($ofertasEspeciales as $oferta): ?>
            <div style="border: 2px solid #ff6b6b; border-radius: 10px; padding: 15px; background: rgba(255, 107, 107, 0.1); margin-bottom: 15px;">
              <h4><?php echo $oferta['origen']; ?> → <?php echo $oferta['destino']; ?></h4>
              <p><strong>Fecha:</strong> <?php echo $oferta['fecha']; ?></p>
              <p><strong>Precio:</strong> <span style="color: #ff6b6b; font-weight: bold; font-size: 1.2em;">$ <?php echo $oferta['precio']; ?></span></p>
              <p><strong>Plazas:</strong> <?php echo $oferta['plazas_disponibles']; ?></p>
              <form method="POST" action="carrito.php" style="margin-top: 15px;">
                <input type="hidden" name="id_vuelo" value="<?php echo $oferta['id_vuelo']; ?>">
                <button type="submit" style="background: linear-gradient(135deg, #ff6b6b, #ee5a52); width: 100%;">¡Reservar Ahora!</button>
              </form>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Para mostrar los destinos más solicitados por los usuarios -->
      <?php if (!empty($destinosPopulares)): ?>
      <div class="card">
        <h2>Destinos Populares</h2>
        <div class="flex-container">
          <?php foreach ($destinosPopulares as $destino): ?>
            <div style="text-align: center; padding: 15px; background: rgba(102, 126, 234, 0.1); border-radius: 10px; margin-bottom: 15px;">
              <h4><?php echo $destino['destino']; ?></h4>
              <p><strong><?php echo $destino['total']; ?> vuelos disponibles</strong></p>
              <a href="buscar_vuelos.php?destino=<?php echo urlencode($destino['destino']); ?>" style="display: inline-block; margin-top: 10px; padding: 8px 16px; background: linear-gradient(135deg, #667eea, #764ba2); color: white; border-radius: 8px; text-decoration: none;">Ver Vuelos</a>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Formulario para que los usuarios registren su intención de viaje -->
      <div class="card" id="registro">
        <h2>Registro de intención de viaje</h2>
        <form method="POST" action="procesar_viaje.php">
            <label>Nombre del hotel:</label>
            <input type="text" name="nombreHotel" required><br>
            <label>Ciudad:</label>
            <input type="text" name="ciudad" required><br>
            <label>País:</label>
            <input type="text" name="pais" required><br>
            <label>Fecha de viaje:</label>
            <input type="date" name="fechaViaje" required><br>
            <label>Duración (días):</label>
            <input type="number" name="duracion" min="1" required><br>
            <button type="submit">Registrar intención de viaje</button>
        </form>
      </div>
    </div>

    <!-- Para mostrar estadísticas actualizadas del negocio -->
    <div style="margin-top: 40px; text-align: center;">
      <h2 style="margin-bottom: 30px; color: #1a237e;">📊 Estadísticas en Tiempo Real</h2>
      <div style="display: flex; flex-direction: row; gap: 15px; justify-content: center; flex-wrap: wrap; max-width: 1200px; margin: 0 auto;">
        <div class="stat-card">
          <div class="stat-number"><?php echo $totalVuelos; ?></div>
          <div class="stat-label">Vuelos Disponibles</div>
        </div>
        <div class="stat-card">
          <div class="stat-number"><?php echo $totalHoteles; ?></div>
          <div class="stat-label">Hoteles Registrados</div>
        </div>
        <div class="stat-card">
          <div class="stat-number"><?php echo $totalReservas; ?></div>
          <div class="stat-label">Reservas Realizadas</div>
        </div>
        <div class="stat-card">
          <div class="stat-number"><?php echo $vuelosDisponibles; ?></div>
          <div class="stat-label">Plazas Disponibles</div>
        </div>
      </div>
    </div>
  </div>
</body>
</html> 