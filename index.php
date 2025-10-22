<?php
session_start();
include_once("includes/db.php");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shopping Promociones - CRUDs</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include("includes/navbar.php"); ?>
<?php include("includes/hero.php"); ?>

<div class="container mt-5 mb-5">
  <div class="row">
    <div class="col-md-6 mb-4">
      <?php include("includes/promociones_publicas.php"); ?>
    </div>
    <div class="col-md-6 mb-4">
      <?php include("includes/novedades_publicas.php"); ?>
    </div>
  </div>
</div>

<?php include("includes/footer.php"); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
