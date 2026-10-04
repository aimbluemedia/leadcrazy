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
      <a href="/members/login">Sign in</a>
      <a class="btn btn--primary btn--sm" href="/members/signup">Get Your Free Page</a>
    </nav>
  </div>
</header>
<main><?= $content ?></main>
<footer class="site-footer">
  <div class="container site-footer__in">
    <div><?= View::render('partials/logo') ?><p class="muted" style="margin-top:8px">The Million Dollar Lead Form for local service businesses.</p></div>
    <div><a href="/pricing">Pricing</a><a href="/privacy">Privacy</a><a href="/terms">Terms</a><a href="/members/login">Sign in</a></div>
  </div>
  <div class="container"><p class="muted" style="font-size:12px;margin-top:16px">&copy; <?= date('Y') ?> LeadCrazy. Pages listed on MonsterList.</p></div>
</footer>
<script src="<?= View::e(View::asset('/assets/js/app.js')) ?>" defer></script>
</body>
</html>
