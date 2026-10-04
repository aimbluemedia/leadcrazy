<?php /** @var array<string,mixed> $offer */ use App\Support\View; ?>
<article class="lp-offer">
  <?php if (!empty($offer['label'])): ?><span class="lp-offer__tag"><?= View::e($offer['label']) ?></span><?php endif; ?>
  <h3><?= View::e($offer['title']) ?></h3>
  <?php if (!empty($offer['body'])): ?><p><?= View::text($offer['body']) ?></p><?php endif; ?>
  <?php if (!empty($offer['expires_on'])): ?>
    <p class="lp-offer__exp">&#9719; Expires: <?= View::e(date('F j, Y', strtotime((string) $offer['expires_on']) ?: time())) ?></p>
  <?php endif; ?>
</article>
