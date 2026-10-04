<?php /** @var ?string $title @var ?string $description */ use App\Support\View; ?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= View::e($title ?? 'LeadCrazy') ?></title>
<?php if (!empty($description)): ?><meta name="description" content="<?= View::e($description) ?>"><?php endif; ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= View::e(View::asset('/assets/css/app.css')) ?>">
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='8' fill='%23f5a300'/><text x='16' y='23' font-size='20' text-anchor='middle'>%E2%9A%A1</text></svg>">
