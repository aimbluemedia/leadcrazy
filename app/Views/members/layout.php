<?php
/**
 * Members area shell: sidebar with the main action, Workspace and Account
 * sections, and the plan box; the page content on the right.
 *
 * @var string $content @var string $current @var array<string,mixed> $account @var array<string,mixed> $user
 * @var ?string $flash @var int $navNew @var int $navMonth
 */
use App\Support\Pages;
use App\Support\Plans;
use App\Support\View;

$icon = static fn (string $d): string => '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $d . '</svg>';
$workspace = [
    'overview' => ['/members', 'Dashboard', '<path d="M3 11 12 4l9 7"/><path d="M5 10v10h14V10"/><path d="M10 20v-5h4v5"/>', 0],
    'leads' => ['/members/leads', 'Leads', '<path d="M4 6h16M4 12h16M4 18h10"/>', $navNew ?? 0],
    'page' => ['/members/page', 'Your page', '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/>', 0],
    'embed' => ['/members/embed', 'Share &amp; embed', '<path d="m8 8-5 4 5 4M16 8l5 4-5 4M14 5l-4 14"/>', 0],
];
$accountNav = [
    'profile' => ['/members/profile', 'Profile', '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>', 0],
    'billing' => ['/members/billing', 'My plan', '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M7 15h4"/>', 0],
];
$plan = (string) $account['plan'];
$live = $account['page_status'] === 'live';
$cap = Plans::FREE_MONTHLY_LEADS;
$month = (int) ($navMonth ?? 0);
?>
<!doctype html>
<html lang="en">
<head>
<?= View::render('partials/head', ['title' => ($title ?? 'Members') . ' - LeadCrazy']) ?>
<meta name="robots" content="noindex, nofollow">
</head>
<body>
<div class="app app--members">
  <aside class="app__side">
    <?= View::render('partials/logo') ?>

    <?php if ($live): ?>
      <a class="side-cta" href="/members/embed"><?= $icon('<path d="M4 12v7a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-7"/><path d="m16 6-4-4-4 4M12 2v13"/>') ?>Share my lead page</a>
    <?php else: ?>
      <a class="side-cta" href="/members/intake"><?= $icon('<path d="M12 5v14M5 12h14"/>') ?>Finish my lead page</a>
    <?php endif; ?>

    <nav class="side-nav" aria-label="Members">
      <?php foreach (['Workspace' => $workspace, 'Account' => $accountNav] as $heading => $items): ?>
        <p class="side-nav__label"><?= $heading ?></p>
        <?php foreach ($items as $key => [$href, $label, $path, $badge]): ?>
          <a href="<?= $href ?>"<?= $current === $key ? ' aria-current="page"' : '' ?>>
            <?= $icon($path) ?><span><?= $label ?></span>
            <?php if ($badge > 0): ?><b class="side-nav__badge" title="<?= $badge ?> new"><?= $badge ?></b><?php endif; ?>
          </a>
        <?php endforeach; ?>
        <?php if ($heading === 'Workspace' && $live): ?>
          <a href="<?= View::e(Pages::url((string) $account['slug'])) ?>" target="_blank" rel="noopener"><?= $icon('<path d="M14 4h6v6M20 4l-9 9"/><path d="M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/>') ?><span>View my page</span></a>
        <?php endif; ?>
      <?php endforeach; ?>
    </nav>

    <div class="side-plan">
      <p class="side-plan__row"><strong><?= View::e(Plans::name($plan)) ?> plan</strong>
        <?php if (Plans::hasLeadCap($plan)): ?><span><?= $month ?>/<?= $cap ?></span><?php endif; ?></p>
      <?php if (Plans::hasLeadCap($plan)): ?>
        <div class="meter"><i style="width:<?= min(100, (int) round($month / max(1, $cap) * 100)) ?>%"></i></div>
        <p class="side-plan__note">Leads this month</p>
        <a class="side-plan__up" href="/members/billing">Upgrade for unlimited &rarr;</a>
      <?php else: ?>
        <p class="side-plan__note">Unlimited leads &middot; <?= View::e(Plans::priceLabel($plan)) ?></p>
      <?php endif; ?>
    </div>

    <div class="side-me">
      <span><?= View::e($user['email'] ?? '') ?></span>
      <a href="/auth/toutify/logout">Sign out</a>
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
