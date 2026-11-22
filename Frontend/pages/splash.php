<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$pageTitle = 'EasyPrint'; $bodyClass = 'splash';
$headExtra = '  <meta http-equiv="refresh" content="2.6;url=index.php">' . "\n";
include __DIR__ . '/../components/head-pub.php';
?>
  <main class="splash__brand" role="status" aria-label="Memuat EasyPrint">
    <img src="assets/images/Icon.png" alt="" class="splash__logo">
    <span>EasyPrint</span>
  </main>
  <script>setTimeout(function () { location.replace('index.php'); }, 2600);</script>
</body>
</html>
