<?php
/** @var string $content @var string $current @var array<string,mixed> $account @var array<string,mixed> $user @var ?string $flash */
use App\Support\Pages;
use App\Support\Plans;
use App\Support\View;
$nav = [
    'overview' => ['/members', 'Dashboard'],
    'leads' => ['/members/leads', 'Leads'],
    'page' => ['/members/page', 'Your page'],
    'embed' => ['/members/embed', 'Embed on your site'],
    'billing' => ['/members/billing', 'Plan & billing'],
    'profile' => ['/members/profile', 'Profile'],
];
?>
<!doctype html>
<html lang="en">
<head>
<?= View::render('partials/head', ['title' => ($title ?? 'Members') . ' - LeadCrazy']) ?>
<meta name="robots" content="noindex, nofollow">
</head>
<body>
<div class="app">
  <aside class="app__side">
    <?= View::render('partials/logo') ?>
    <nav class="app__nav">
      <?php foreach ($nav as $key => [$href, $label]): ?>
        <a href="<?= $href ?>" <?= $current === $key ? 'aria-current="page"' : '' ?>><?= $label ?></a>
      <?php endforeach; ?>
      <?php if ($account['page_status'] === 'live'): ?>
        <a href="<?= View::e(Pages::url((string) $account['slug'])) ?>" target="_blank" rel="noopener">View my page &#8599;</a>
      <?php endif; ?>
    </nav>
    <div class="app__me">
      <strong style="color:#fff"><?= View::e($account['business_name']) ?></strong><br>
      <?= View::e(Plans::name((string) $account['plan'])) ?> plan &middot; <?= View::e($user['email'] ?? '') ?>
      <div class="row" style="margin-top:8px">
        <a href="/auth/toutify/logout" class="app__signout">Sign out</a>
      </div>
    </div>
  </aside>
  <main class="app__main">
    <?php if ($flash): ?><div class="alert alert--ok" role="status" style="margin-bottom:18px"><?= View::e($flash) ?></div><?php endif; ?>
    <?= $content ?>
  </main>
</div>
<script src="<?= View::e(View::asset('/assets/js/app.js')) ?>" defer></script>
</body>
</html>
