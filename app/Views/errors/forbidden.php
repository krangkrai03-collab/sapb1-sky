<?php helper('url'); ?>
<!DOCTYPE html>
<html lang="th">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>403 <?= lang("App.forbidden") ?></title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.2/dist/css/adminlte.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700&display=swap">
	<style> body { font-family: 'Sarabun', sans-serif; } </style>
</head>
<body class="hold-transition">
<div class="error-page" style="margin-top:8%;">
	<h2 class="headline text-warning">403</h2>
	<div class="error-content">
		<h3><i class="fas fa-exclamation-triangle text-warning"></i> <?= lang("App.forbidden") ?></h3>
		<p><?= lang("App.forbiddenMsg") ?></p>
		<a href="<?= site_url('dashboard') ?>" class="btn btn-primary"><?= lang("App.backDashboard") ?></a>
	</div>
</div>
</body>
</html>
