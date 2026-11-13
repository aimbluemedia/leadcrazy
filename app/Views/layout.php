<?php /** @var string $content @var ?string $current */ use App\Support\View; ?>
<!doctype html>
<html lang="en">
<head>
<?= View::render('partials/head', get_defined_vars()) ?>
</head>
<body>
<header class="site-header">
  <div class="container site-header__in">
    <?= View::render('partials/logo') ?>
    <button class="nav__toggle" type="button" data-nav-toggle aria-expanded="false" aria-controls="site-nav">Menu</button>
    <nav class="nav" id="site-nav" data-nav>
      <a href="/" <?= ($current ?? '') === 'home' ? 'aria-current="page"' : '' ?>>Home</a>
      <a href="/how-it-works" <?= ($current ?? '') === 'how' ? 'aria-current="page"' : '' ?>>How it works</a>
      <a href="/pricing" <?= ($current ?? '') === 'pricing' ? 'aria-current="page"' : '' ?>>Pricing</a>
      <a href="/auth/toutify/login">Log in</a>
      <a class="btn btn--primary btn--sm" href="/signup">Get Your Free Page</a>
    </nav>
  </div>
</header>
<main><?= $content ?></main>
<?= View::render('partials/footer') ?>
<script src="<?= View::e(View::asset('/assets/js/app.js')) ?>" defer></script>
</body>
</html>
