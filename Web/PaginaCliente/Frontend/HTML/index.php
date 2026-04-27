<?php

declare(strict_types=1);

require_once __DIR__ . '/../../Backend/PHP/controllers/router.php';

$state = handleRequest();
$view = $state['view'];
$currentUser = $state['currentUser'];
$flashSuccess = $state['flashSuccess'];
$flashError = $state['flashError'];
$errorConexion = $state['errorConexion'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda DS9</title>
    <link rel="stylesheet" href="../../../Styles/app.css">
    <script src="/jquery-4.0.0.min.js"></script>
</head>
<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main class="page-shell">
        <?php if ($flashSuccess !== null): ?>
            <div class="flash flash-success"><?= e($flashSuccess) ?></div>
        <?php endif; ?>

        <?php if ($flashError !== null): ?>
            <div class="flash flash-error"><?= e($flashError) ?></div>
        <?php endif; ?>

        <?php if ($errorConexion !== null): ?>
            <div class="flash flash-error"><?= e($errorConexion) ?></div>
        <?php endif; ?>

        <?php
        $viewFile = __DIR__ . '/../Views/' . $view . '.php';

        if (!is_file($viewFile)) {
            $viewFile = __DIR__ . '/../Views/catalogo.php';
        }

        include $viewFile;
        ?>
    </main>
</body>
</html>
