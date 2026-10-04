<?php
/**
 * Standalone document for a business page. Not the LeadCrazy marketing layout:
 * this page belongs to the business, and in an embed it sits inside their site.
 *
 * @var string $title @var string $mode @var string $body
 * @var array<string,mixed> $account @var array<string,mixed> $page
 */
use App\Support\Plans;
use App\Support\Turnstile;
use App\Support\View;

$embedded = str_starts_with($mode, 'embed');
$accent = (string) ($page['accent_color'] ?? '');
$desc = trim((string) ($page['intro'] ?? '' ?: ($page['subheadline'] ?? '')));
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= View::e($title) ?></title>
<?php if ($desc !== ''): ?><meta name="description" content="<?= View::e(mb_substr($desc, 0, 160)) ?>"><?php endif; ?>
<?php if ($mode === 'preview' || $embedded): ?><meta name="robots" content="noindex"><?php endif; ?>
<?php if (!$embedded && $mode !== 'preview'): ?>
<link rel="canonical" href="<?= View::e(App\Support\Pages::url((string) $account['slug'])) ?>">
<meta property="og:title" content="<?= View::e($title) ?>">
<?php if (!empty($page['hero_path'])): ?><meta property="og:image" content="<?= View::e(View::url((string) $page['hero_path'])) ?>"><?php endif; ?>
<?php endif; ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= View::e(View::asset('/assets/css/page.css')) ?>">
<?php if (preg_match('/^#[0-9a-f]{6}$/', $accent)): ?><style>:root{--lp-accent:<?= $accent ?>}</style><?php endif; ?>
<?php if (Turnstile::enabled() && $mode !== 'preview'): ?>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<?php endif; ?>
</head>
<body class="lp-body<?= $embedded ? ' is-embed' : '' ?>">
<?php if ($mode === 'preview'): ?>
  <div class="lp-preview">Preview of <strong>/<?= View::e($account['slug']) ?></strong> &middot; status: <?= View::e($account['page_status']) ?> &middot; plan: <?= View::e(Plans::name((string) $account['plan'])) ?>
    &middot; <a href="/superadmin/account?id=<?= (int) $account['id'] ?>">Back to editor</a></div>
<?php endif; ?>

<?php if (!$embedded): ?>
<header class="lp-top">
  <div class="lp-wrap lp-top__in">
    <a class="lp-brand" href="#top">
      <?php if (!empty($page['logo_path'])): ?>
        <img src="<?= View::e($page['logo_path']) ?>" alt="<?= View::e($account['business_name']) ?>">
      <?php else: ?>
        <span><?= View::e($account['business_name']) ?></span>
      <?php endif; ?>
    </a>
    <nav class="lp-nav">
      <a href="#quote" class="lp-nav__cta">Get Quote</a>
      <?php if (!empty($page['phone'])): ?>
        <a href="tel:<?= View::e(preg_replace('/[^0-9+]/', '', (string) $page['phone'])) ?>"><?= View::e($page['phone']) ?></a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<?php endif; ?>

<?= View::render($body, get_defined_vars()) ?>

<?php if (!$embedded): ?>
<footer class="lp-foot">
  <div class="lp-wrap">
    <p>&copy; <?= date('Y') ?> <?= View::e($account['business_name']) ?><?= !empty($page['city']) ? ' &middot; ' . View::e($page['city']) . (!empty($page['state']) ? ', ' . View::e($page['state']) : '') : '' ?></p>
    <?php if (Plans::showsBranding((string) $account['plan'])): ?>
      <p class="lp-powered">Powered by <a href="<?= View::e(View::url('/?ref=' . rawurlencode((string) $account['slug']))) ?>">LeadCrazy</a> &mdash; get your own free Million Dollar Lead Form.</p>
    <?php endif; ?>
  </div>
</footer>
<?php elseif (Plans::showsBranding((string) $account['plan'])): ?>
  <p class="lp-powered">Powered by <a href="<?= View::e(View::url('/')) ?>" target="_blank" rel="noopener">LeadCrazy</a></p>
<?php endif; ?>

<script src="<?= View::e(View::asset('/assets/js/page.js')) ?>" defer></script>
</body>
</html>
