<?php
/**
 * The business profile card under the offers. No phone number on purpose:
 * the page is there to turn visitors into form leads.
 *
 * @var array<string,mixed> $account @var array<string,mixed> $page
 * @var list<string> $services @var list<string> $locations @var list<string> $zipcodes
 * @var bool $full @var array<string,mixed> $copy
 */
use App\Support\View;

$name = (string) $account['business_name'];
$words = preg_split('/\s+/', trim($name)) ?: [];
$initials = mb_strtoupper(implode('', array_map(static fn ($w) => mb_substr($w, 0, 1), array_slice($words, 0, 2))));
$place = trim((string) ($page['city'] ?? '') . (!empty($page['state']) ? ', ' . $page['state'] : ''), ', ');
$website = (string) ($page['website'] ?? '');
$websiteHost = $website !== '' ? preg_replace('#^www\.#i', '', (string) parse_url($website, PHP_URL_HOST)) : '';
$rating = $full ? trim((string) ($page['stat_rating'] ?? '')) : '';
$response = $full ? trim((string) ($page['stat_response'] ?? '')) : '';
$areas = count($locations) + count($zipcodes);
$since = !empty($account['created_at']) ? date('Y', strtotime((string) $account['created_at'])) : '';
?>
<section class="lp-profile" aria-label="<?= View::e($name) ?> profile">
  <div class="lp-profile__head">
    <?php if (!empty($page['logo_path'])): ?>
      <img class="lp-profile__avatar" src="<?= View::e($page['logo_path']) ?>" alt="">
    <?php else: ?>
      <span class="lp-profile__avatar lp-profile__avatar--text" aria-hidden="true"><?= View::e($initials ?: '?') ?></span>
    <?php endif; ?>
    <div>
      <h2 class="lp-profile__name"><?= View::e($name) ?></h2>
      <p class="lp-profile__verified">
        <svg viewBox="0 0 20 20" width="15" height="15" aria-hidden="true"><path fill="currentColor" d="M10 1.5l2.2 1.6 2.7-.1.9 2.6 2.2 1.6-.8 2.6.8 2.6-2.2 1.6-.9 2.6-2.7-.1L10 18.5l-2.2-1.6-2.7.1-.9-2.6-2.2-1.6.8-2.6-.8-2.6 2.2-1.6.9-2.6 2.7.1z"/><path fill="#fff" d="M8.7 12.6L6.2 10.1l1.1-1.1 1.4 1.4 3.9-3.9 1.1 1.1z"/></svg>
        Verified business
      </p>
      <?php if ($rating !== ''): ?>
        <p class="lp-profile__rating"><span aria-hidden="true">&#9733;&#9733;&#9733;&#9733;&#9733;</span> <?= View::e($rating) ?></p>
      <?php endif; ?>
    </div>
  </div>

  <dl class="lp-profile__rows">
    <?php if ($place !== ''): ?>
      <div><dt>Based in</dt><dd><?= View::e($place) ?></dd></div>
    <?php endif; ?>
    <?php if ($services !== []): ?>
      <div><dt>Specialties</dt><dd><?= View::e(implode(', ', array_slice($services, 0, 3))) ?><?= count($services) > 3 ? ' +' . (count($services) - 3) . ' more' : '' ?></dd></div>
    <?php endif; ?>
    <?php if ($areas > 0): ?>
      <div><dt>Service area</dt><dd><?= View::e(implode(', ', array_slice($locations, 0, 3)) ?: $areas . ' zip codes') ?><?= count($locations) > 3 ? ' +' . (count($locations) - 3) . ' more' : '' ?></dd></div>
    <?php endif; ?>
    <div><dt>Response</dt><dd><?= View::e($response !== '' ? $response : (string) $copy['response']) ?></dd></div>
    <?php if (trim((string) ($page['license_number'] ?? '')) !== '' && $copy['license_label'] !== null): ?>
      <div><dt><?= View::e($copy['license_label']) ?></dt><dd><?= View::e((string) $page['license_number']) ?></dd></div>
    <?php endif; ?>
    <?php if ($websiteHost !== ''): ?>
      <div><dt>Website</dt><dd><a href="<?= View::e($website) ?>" target="_blank" rel="noopener nofollow"><?= View::e($websiteHost) ?></a></dd></div>
    <?php endif; ?>
    <?php if ($since !== ''): ?>
      <div><dt>On LeadCrazy since</dt><dd><?= View::e($since) ?></dd></div>
    <?php endif; ?>
  </dl>

  <a class="lp-profile__cta" href="#quote" data-lc-top>Get my free quote</a>
</section>
