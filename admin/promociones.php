<?php
session_start();
include_once("../includes/db.php");
include_once("../includes/mail_config.php");

// Cargar PHPMailer con Composer
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// --- ELIMINAR PROMOCIÓN ---
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $conn->query("DELETE FROM promociones WHERE id=$id");
}

// --- APROBAR PROMOCIÓN ---
if (isset($_GET['accion']) && $_GET['accion'] == 'aprobar' && isset($_GET['id'])) {
    $promo_id = intval($_GET['id']);
    $conn->query("UPDATE promociones SET estado='aprobada' WHERE id=$promo_id");
    
    // Obtener datos del dueño y la promoción
    $result = $conn->query("SELECT u.email, u.nombre, p.titulo, p.descripcion, p.fecha_inicio, p.fecha_fin 
                            FROM usuarios u 
                            JOIN locales l ON l.id_duenio=u.id 
                            JOIN promociones p ON p.id_local=l.id
                            WHERE p.id=$promo_id LIMIT 1");
    
    if ($result && $duenio = $result->fetch_assoc()) {
        $mail = new PHPMailer(true);
        
        try {
            configurarMail($mail);
            $mail->addAddress($duenio['email'], $duenio['nombre']);
            $mail->Subject = 'Promoción aprobada';
            $mail->Body = "Hola {$duenio['nombre']},\n\n" .
                          "¡Buenas noticias! Tu promoción ha sido aprobada por el administrador.\n\n" .
                          "Título: {$duenio['titulo']}\n" .
                          "Descripción: {$duenio['descripcion']}\n" .
                          "Vigencia: del {$duenio['fecha_inicio']} al {$duenio['fecha_fin']}\n\n" .
                          "Tu promoción ya está visible para los clientes del shopping.\n\n" .
                          "Atentamente,\n" .
                          "Equipo Ofertópolis";
            
            $mail->send();
            $notification = "Promoción aprobada y dueño notificado exitosamente.";
        } catch (Exception $e) {
            $notification = "Promoción aprobada, pero no se pudo enviar el correo. Error: {$mail->ErrorInfo}";
        }
    }
}

// --- RECHAZAR PROMOCIÓN ---
if (isset($_GET['accion']) && $_GET['accion'] == 'rechazar' && isset($_GET['id'])) {
    $promo_id = intval($_GET['id']);
    $conn->query("UPDATE promociones SET estado='rechazada' WHERE id=$promo_id");
    
    // Obtener datos del dueño y la promoción
    $result = $conn->query("SELECT u.email, u.nombre, p.titulo, p.descripcion 
                            FROM usuarios u 
                            JOIN locales l ON l.id_duenio=u.id 
                            JOIN promociones p ON p.id_local=l.id
                            WHERE p.id=$promo_id LIMIT 1");
    
    if ($result && $duenio = $result->fetch_assoc()) {
        $mail = new PHPMailer(true);
        
        try {
            configurarMail($mail);
            $mail->addAddress($duenio['email'], $duenio['nombre']);
            $mail->Subject = 'Promoción rechazada';
            $mail->Body = "Hola {$duenio['nombre']},\n\n" .
                          "Lamentamos informarte que tu promoción ha sido rechazada.\n\n" .
                          "Título: {$duenio['titulo']}\n" .
                          "Descripción: {$duenio['descripcion']}\n\n" .
                          "Por favor, revisa los términos y condiciones para crear promociones y " .
                          "vuelve a intentarlo con una promoción que cumpla con nuestras políticas.\n\n" .
                          "Atentamente,\n" .
                          "Equipo Ofertópolis";
            
            $mail->send();
            $notification = "Promoción rechazada y dueño notificado exitosamente.";
        } catch (Exception $e) {
            $notification = "Promoción rechazada, pero no se pudo enviar el correo. Error: {$mail->ErrorInfo}";
        }
    }
}

// --- PAGINACIÓN Y FILTROS ---
$limite = 10;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina < 1) $pagina = 1;
$inicio = ($pagina - 1) * $limite;

// Construir filtros
$where_conditions = [];
$params_url = [];

// Filtro por estado
if (isset($_GET['estado']) && !empty($_GET['estado'])) {
    $estado_filter = $conn->real_escape_string($_GET['estado']);
    $where_conditions[] = "p.estado='$estado_filter'";
    $params_url[] = "estado=$estado_filter";
}

// Filtro por categoría
if (isset($_GET['categoria']) && !empty($_GET['categoria'])) {
    $categoria_filter = $conn->real_escape_string($_GET['categoria']);
    $where_conditions[] = "p.categoria_minima='$categoria_filter'";
    $params_url[] = "categoria=$categoria_filter";
}

// Filtro por local
if (isset($_GET['local']) && !empty($_GET['local'])) {
    $local_filter = intval($_GET['local']);
    $where_conditions[] = "p.id_local=$local_filter";
    $params_url[] = "local=$local_filter";
}

// Búsqueda por título
if (isset($_GET['buscar']) && !empty($_GET['buscar'])) {
    $buscar = $conn->real_escape_string($_GET['buscar']);
    $where_conditions[] = "(p.titulo LIKE '%$buscar%' OR p.descripcion LIKE '%$buscar%')";
    $params_url[] = "buscar=$buscar";
}

// Construir WHERE clause
$where_sql = "";
if (count($where_conditions) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where_conditions);
}

// Ordenamiento
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'id_desc';
$order_sql = "ORDER BY ";
switch ($orden) {
    case 'id_asc':
        $order_sql .= "p.id ASC";
        break;
    case 'id_desc':
        $order_sql .= "p.id DESC";
        break;
    case 'titulo_asc':
        $order_sql .= "p.titulo ASC";
        break;
    case 'titulo_desc':
        $order_sql .= "p.titulo DESC";
        break;
    case 'fecha_inicio_asc':
        $order_sql .= "p.fecha_inicio ASC";
        break;
    case 'fecha_inicio_desc':
        $order_sql .= "p.fecha_inicio DESC";
        break;
    default:
        $order_sql .= "p.id DESC";
}
$params_url[] = "orden=$orden";

// Contar total de promociones con filtros
$total_result = $conn->query("SELECT COUNT(*) as total FROM promociones p $where_sql");
$total_row = $total_result->fetch_assoc();
$total_promociones = $total_row['total'];
$total_paginas = ceil($total_promociones / $limite);

// Traer promociones con paginación y filtros
$promos = $conn->query("SELECT p.*, l.nombre AS local 
                        FROM promociones p 
                        LEFT JOIN locales l ON p.id_local = l.id 
                        $where_sql 
                        $order_sql 
                        LIMIT $inicio, $limite");

// Obtener lista de locales para el filtro
$locales_filter = $conn->query("SELECT id, nombre FROM locales ORDER BY nombre");

// Construir URL para paginación con filtros
$params_url_string = implode("&", array_filter($params_url, function($p) { return strpos($p, 'orden=') !== 0; }));
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Promociones - Panel Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../css/estilos.css" rel="stylesheet">
<link href="../css/header.css" rel="stylesheet">
<link href="../css/footer.css" rel="stylesheet">
<link href="../css/panels.css" rel="stylesheet">
</head>
<body>

<?php include("../includes/header.php"); ?>

<main id="main-content" class="main-content">
<div class="container mt-4 mb-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 style="color: var(--primary-color); font-weight: 700;">Gestión de Promociones</h3>
    <a href="admin.php" class="btn btn-secondary">Volver al Panel</a>
  </div>

  <div class="alert alert-info">
    <strong>Información:</strong> Los dueños de locales crean las promociones. Como administrador, puedes aprobarlas, rechazarlas o eliminarlas.
  </div>

  <?php if(isset($notification)): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
      <?= $notification ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Filtros y Ordenamiento -->
  <div class="card shadow-sm mb-4">
    <div class="card-header" style="background-color: var(--light);">
      <h5 class="mb-0">Filtros y Ordenamiento</h5>
    </div>
    <div class="card-body">
      <form method="GET" class="row g-3">
        <div class="col-md-3">
          <label class="form-label fw-bold">Buscar:</label>
          <input type="text" name="buscar" class="form-control" 
                 placeholder="Título o descripción..." 
                 value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>">
        </div>
        <div class="col-md-2">
          <label class="form-label fw-bold">Estado:</label>
          <select name="estado" class="form-select">
            <option value="">Todos</option>
            <option value="pendiente" <?= (isset($_GET['estado']) && $_GET['estado'] == 'pendiente') ? 'selected' : '' ?>>Pendiente</option>
            <option value="aprobada" <?= (isset($_GET['estado']) && $_GET['estado'] == 'aprobada') ? 'selected' : '' ?>>Aprobada</option>
            <option value="rechazada" <?= (isset($_GET['estado']) && $_GET['estado'] == 'rechazada') ? 'selected' : '' ?>>Rechazada</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label fw-bold">Categoría:</label>
          <select name="categoria" class="form-select">
            <option value="">Todas</option>
            <option value="inicial" <?= (isset($_GET['categoria']) && $_GET['categoria'] == 'inicial') ? 'selected' : '' ?>>Inicial</option>
            <option value="medium" <?= (isset($_GET['categoria']) && $_GET['categoria'] == 'medium') ? 'selected' : '' ?>>Medium</option>
            <option value="premium" <?= (isset($_GET['categoria']) && $_GET['categoria'] == 'premium') ? 'selected' : '' ?>>Premium</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label fw-bold">Local:</label>
          <select name="local" class="form-select">
            <option value="">Todos</option>
            <?php while($loc = $locales_filter->fetch_assoc()): ?>
              <option value="<?= $loc['id'] ?>" <?= (isset($_GET['local']) && $_GET['local'] == $loc['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($loc['nombre']) ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label fw-bold">Ordenar por:</label>
          <select name="orden" class="form-select">
            <option value="id_desc" <?= ($orden == 'id_desc') ? 'selected' : '' ?>>ID (Más reciente)</option>
            <option value="id_asc" <?= ($orden == 'id_asc') ? 'selected' : '' ?>>ID (Más antiguo)</option>
            <option value="titulo_asc" <?= ($orden == 'titulo_asc') ? 'selected' : '' ?>>Título (A-Z)</option>
            <option value="titulo_desc" <?= ($orden == 'titulo_desc') ? 'selected' : '' ?>>Título (Z-A)</option>
            <option value="fecha_inicio_asc" <?= ($orden == 'fecha_inicio_asc') ? 'selected' : '' ?>>Fecha inicio (Ascendente)</option>
            <option value="fecha_inicio_desc" <?= ($orden == 'fecha_inicio_desc') ? 'selected' : '' ?>>Fecha inicio (Descendente)</option>
          </select>
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
          <a href="promociones.php" class="btn btn-secondary">Limpiar</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Tabla -->
  <div class="card shadow-sm">
    <div class="card-header text-white" style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));">
      <h5 class="mb-0">Lista de Promociones</h5>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead style="background-color: var(--light);">
            <tr>
              <th>ID</th>
              <th>Título</th>
              <th>Descripción</th>
              <th>Local</th>
              <th>Fecha Inicio</th>
              <th>Fecha Fin</th>
              <th>Categoría Mínima</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
      <?php while($p = $promos->fetch_assoc()): ?>
        <tr>
          <td><strong>#<?= $p['id'] ?></strong></td>
          <td><?= htmlspecialchars($p['titulo']) ?></td>
          <td><?= htmlspecialchars(substr($p['descripcion'], 0, 50)) ?>...</td>
          <td><?= htmlspecialchars($p['local']) ?></td>
          <td><?= $p['fecha_inicio'] ? date('d/m/Y', strtotime($p['fecha_inicio'])) : 'N/A' ?></td>
          <td><?= $p['fecha_fin'] ? date('d/m/Y', strtotime($p['fecha_fin'])) : 'N/A' ?></td>
          <td><span class="badge bg-info"><?= htmlspecialchars($p['categoria_minima']) ?></span></td>
          <td>
            <?php if($p['estado'] == 'pendiente'): ?>
              <span class="badge bg-warning">Pendiente</span>
            <?php elseif($p['estado'] == 'aprobada'): ?>
              <span class="badge bg-success">Aprobada</span>
            <?php elseif($p['estado'] == 'rechazada'): ?>
              <span class="badge bg-danger">Rechazada</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if($p['estado'] == 'pendiente'): ?>
              <a href="?accion=aprobar&id=<?= $p['id'] ?>&<?= $params_url_string ?>&pagina=<?= $pagina ?>" 
                 class="btn btn-success btn-sm" 
                 onclick="return confirm('¿Aprobar esta promoción?')">
                Aprobar
              </a>
              <a href="?accion=rechazar&id=<?= $p['id'] ?>&<?= $params_url_string ?>&pagina=<?= $pagina ?>" 
                 class="btn btn-warning btn-sm" 
                 onclick="return confirm('¿Rechazar esta promoción?')">
                Rechazar
              </a>
            <?php endif; ?>
            <a href="?eliminar=<?= $p['id'] ?>&<?= $params_url_string ?>&pagina=<?= $pagina ?>" 
               class="btn btn-danger btn-sm" 
               onclick="return confirm('¿Eliminar promoción?')">
              Eliminar
            </a>
          </td>
        </tr>
      <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Paginación -->
  <nav aria-label="Paginación de promociones" class="mt-4">
    <ul class="pagination justify-content-center">
      <!-- Botón Anterior -->
      <?php if ($pagina > 1): ?>
        <li class="page-item">
          <a class="page-link" href="?<?= $params_url_string ?>&pagina=<?= $pagina - 1 ?>">← Anterior</a>
        </li>
      <?php else: ?>
        <li class="page-item disabled">
          <span class="page-link">← Anterior</span>
        </li>
      <?php endif; ?>

      <!-- Números de página -->
      <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
        <li class="page-item <?= ($i == $pagina) ? 'active' : '' ?>">
          <a class="page-link" href="?<?= $params_url_string ?>&pagina=<?= $i ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>

      <!-- Botón Siguiente -->
      <?php if ($pagina < $total_paginas): ?>
        <li class="page-item">
          <a class="page-link" href="?<?= $params_url_string ?>&pagina=<?= $pagina + 1 ?>">Siguiente →</a>
        </li>
      <?php else: ?>
        <li class="page-item disabled">
          <span class="page-link">Siguiente →</span>
        </li>
      <?php endif; ?>
    </ul>
  </nav>

  <!-- Información de paginación -->
  <p class="text-center text-muted">
    Página <?= $pagina ?> de <?= $total_paginas ?> | Total: <?= $total_promociones ?> promociones
  </p>
</div>
</main>

<?php include("../includes/footer.php"); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
