<?php use App\Support\Plans; use App\Support\View; ?>
<div class="plans">
  <?php foreach (Plans::ALL as $plan): $d = Plans::DETAILS[$plan]; $featured = $plan === Plans::PRO; ?>
    <div class="plan<?= $featured ? ' plan--featured' : '' ?>">
      <?php if ($featured): ?><span class="plan__tag">Most popular</span><?php endif; ?>
      <p class="plan__name"><?= View::e($d['name']) ?></p>
      <p class="plan__price">$<?= (int) $d['price'] ?><small><?= $d['price'] > 0 ? '/month' : ' forever' ?></small></p>
      <p class="plan__tagline"><?= View::e($d['tagline']) ?></p>
      <ul><?php foreach ($d['features'] as $f): ?><li><?= View::e($f) ?></li><?php endforeach; ?></ul>
      <a class="btn <?= $featured ? 'btn--primary' : 'btn--ghost' ?> btn--block" href="/members/signup?plan=<?= $plan ?>">
        <?= $plan === Plans::FREE ? 'Start free' : 'Choose ' . View::e($d['name']) ?>
      </a>
    </div>
  <?php endforeach; ?>
</div>
