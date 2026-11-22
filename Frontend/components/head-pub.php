<?php
// Kepala halaman publik. Set $pageTitle, $bodyClass (opsional), $extraCss (opsional, array) sebelum include.
$pageTitle = $pageTitle ?? 'EasyPrint'; $bodyClass = $bodyClass ?? ''; $extraCss = $extraCss ?? [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle) ?> - Restu Ibu Print &amp; Fotocopy</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/pill.css">
  <link rel="stylesheet" href="assets/css/public-pages.css">
  <?php foreach ($extraCss as $css): ?><link rel="stylesheet" href="assets/css/<?= $css ?>">
  <?php endforeach; ?>
<?= $headExtra ?? '' ?>
  <link rel="stylesheet" href="assets/css/integrasi.css">
</head>
<body class="pub <?= htmlspecialchars($bodyClass) ?>">
