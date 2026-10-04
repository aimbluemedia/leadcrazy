<?php
/** @var string $content @var ?string $current @var array<string,mixed> $user @var ?string $flash */
use App\Support\Csrf;
use App\Support\Database;
use App\Support\View;
$open = (int) (Database::first("SELECT COUNT(*) AS n FROM change_requests WHERE status = 'open'")['n'] ?? 0);
$nav = [
    'overview' => ['/superadmin', 'Overview'],
    'accounts' => ['/superadmin/accounts', 'Accounts & pages'],
    'leads' => ['/superadmin/leads', 'All leads'],
    'requests' => ['/superadmin/requests', 'Change requests'],
];
?>
<!doctype html>
<html lang="en">
<head>
<?= View::render('partials/head', ['title' => ($title ?? 'Superadmin') . ' - LeadCrazy admin']) ?>
<meta name="robots" content="noindex, nofollow">
</head>
<body>
<div class="app">
  <aside class="app__side">
    <?= View::render('partials/logo') ?>
    <nav class="app__nav">
      <?php foreach ($nav as $key => [$href, $label]): ?>
        <a href="<?= $href ?>" <?= ($current ?? '') === $key ? 'aria-current="page"' : '' ?>><?= $label ?>
          <?php if ($key === 'requests' && $open > 0): ?><span class="count"><?= $open ?></span><?php endif; ?></a>
      <?php endforeach; ?>
      <a href="/feed/monsterlist" target="_blank" rel="noopener">MonsterList feed &#8599;</a>
    </nav>
    <div class="app__me">
      Superadmin &middot; <?= View::e($user['email'] ?? '') ?>
      <form method="post" action="/superadmin/logout"><?= Csrf::field() ?><button type="submit">Sign out</button></form>
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
