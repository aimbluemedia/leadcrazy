<?php
/**
 * The Million Dollar Lead Form page. Form card on the left, the pitch on the
 * right; on a phone the headline comes first, then the form, then the rest.
 *
 * Free plans show the form, the pitch, services and service areas. Pro and
 * Premium add stats, gallery, video, zip codes, testimonials and offers.
 *
 * @var array<string,mixed> $account @var array<string,mixed> $page
 * @var list<array<string,mixed>> $offers @var list<array<string,mixed>> $gallery
 * @var list<array<string,mixed>> $testimonials
 * @var list<string> $services @var list<string> $locations @var list<string> $zipcodes @var list<string> $whyPoints
 * @var bool $full @var string $mode
 */
use App\Support\Pages;
use App\Support\View;

$name = (string) $account['business_name'];
$video = $full ? Pages::videoEmbed($page['video_url'] ?? null) : null;
$heroOffer = $full ? ($offers[0] ?? null) : null;
$stats = array_filter([
    ['value' => $page['stat_rating'] ?? null, 'label' => 'Average Rating', 'icon' => '&#9733;'],
    ['value' => $page['stat_projects'] ?? null, 'label' => 'Projects Completed', 'icon' => '&#10004;'],
    ['value' => $page['stat_response'] ?? null, 'label' => 'Quote Response', 'icon' => '&#9201;'],
], static fn ($s) => trim((string) $s['value']) !== '');
$showGallery = $full && $gallery !== [];
?>
<div class="lp-wrap lp-grid" id="top">

  <section class="lp-head">
    <?php if (!empty($page['badge'])): ?>
      <a class="lp-badge" href="#quote">&#9997; <?= View::e($page['badge']) ?></a>
    <?php endif; ?>
    <h1 class="lp-h1"><?= View::e($page['headline'] ?? '' ?: $name) ?></h1>
    <?php if (!empty($page['subheadline'])): ?>
      <p class="lp-h2"><?= View::e($page['subheadline']) ?></p>
    <?php endif; ?>
  </section>

  <aside class="lp-side" id="quote">
    <div class="lp-card lp-card--form">
      <?php if (!empty($page['hero_path'])): ?>
        <img class="lp-hero" src="<?= View::e($page['hero_path']) ?>" alt="<?= View::e($name) ?>" loading="eager">
      <?php endif; ?>
      <p class="lp-pill">&#9889; Get Your Quote in 24 Hours</p>
      <?php if ($heroOffer !== null): ?>
        <?= View::render('business/_offer', ['offer' => $heroOffer]) ?>
      <?php endif; ?>
      <?= View::render('business/_form', get_defined_vars()) ?>
    </div>
  </aside>

  <section class="lp-main">
    <?php if (!empty($page['intro'])): ?>
      <p class="lp-intro"><?= View::e($page['intro']) ?></p>
    <?php endif; ?>
    <?php if (!empty($page['about'])): ?>
      <div class="lp-prose">
        <?php foreach (preg_split('/\R{2,}/', trim((string) $page['about'])) ?: [] as $para): ?>
          <p><?= View::text($para) ?></p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($whyPoints !== []): ?>
      <h2 class="lp-why"><?= View::e($page['why_title'] ?? '' ?: 'Why Choose ' . $name . ':') ?></h2>
      <ul class="lp-points">
        <?php foreach ($whyPoints as $point): ?>
          <?php $parts = explode(':', $point, 2); ?>
          <li><?= count($parts) === 2 ? '<strong>' . View::e(trim($parts[0])) . ':</strong> ' . View::e(trim($parts[1])) : View::e($point) ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <?php if (!empty($page['closing'])): ?>
      <p class="lp-closing"><?= View::text($page['closing']) ?></p>
    <?php endif; ?>

    <?php if ($full && $stats !== []): ?>
      <div class="lp-stats">
        <?php foreach ($stats as $s): ?>
          <div class="lp-stat">
            <span class="lp-stat__icon" aria-hidden="true"><?= $s['icon'] ?></span>
            <strong><?= View::e((string) $s['value']) ?></strong>
            <span><?= View::e($s['label']) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($full): ?>
      <div class="lp-tiles">
        <a class="lp-tile lp-tile--quote" href="#quote" data-lc-top><span aria-hidden="true">&#9993;</span>Free Quote</a>
        <?php if ($showGallery): ?>
          <a class="lp-tile lp-tile--images" href="#gallery"><span aria-hidden="true">&#128247;</span>View Images</a>
        <?php endif; ?>
        <?php if ($video !== null): ?>
          <a class="lp-tile lp-tile--videos" href="#video"><span aria-hidden="true">&#9654;</span>View Videos</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if ($showGallery): ?>
      <div class="lp-gallery" id="gallery" data-gallery>
        <?php foreach ($gallery as $i => $img): ?>
          <a class="lp-gallery__item<?= $i >= 6 ? ' is-more' : '' ?>" href="<?= View::e($img['path']) ?>" target="_blank" rel="noopener">
            <img src="<?= View::e($img['path']) ?>" alt="<?= View::e($img['caption'] ?? '' ?: $name . ' project photo') ?>" loading="lazy">
          </a>
        <?php endforeach; ?>
      </div>
      <?php if (count($gallery) > 6): ?>
        <p class="lp-center"><button class="lp-more" type="button" data-gallery-more>Load More Images</button></p>
      <?php endif; ?>
    <?php endif; ?>

    <?php if ($video !== null): ?>
      <div class="lp-video" id="video">
        <iframe src="<?= View::e($video) ?>" title="<?= View::e($name) ?> video" loading="lazy"
                allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
      </div>
    <?php endif; ?>

    <?php if ($services !== []): ?>
      <h2 class="lp-h3">Our Services:</h2>
      <ul class="lp-pills lp-pills--service">
        <?php foreach ($services as $item): ?><li><?= View::e($item) ?></li><?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <?php if ($locations !== []): ?>
      <h2 class="lp-h3">Our Service Locations:</h2>
      <ul class="lp-pills lp-pills--location">
        <?php foreach ($locations as $item): ?><li><?= View::e($item) ?></li><?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <?php if ($full && $zipcodes !== []): ?>
      <h2 class="lp-h3">Our Service Zipcodes:</h2>
      <ul class="lp-pills lp-pills--zip">
        <?php foreach ($zipcodes as $item): ?><li><?= View::e($item) ?></li><?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <?php if ($full && $testimonials !== []): ?>
      <h2 class="lp-h3">Our Reviews:</h2>
      <div class="lp-reviews" data-reviews>
        <?php foreach ($testimonials as $i => $t): ?>
          <figure class="lp-review<?= $i > 0 ? ' is-more' : '' ?>">
            <div class="lp-stars" aria-label="<?= (int) $t['rating'] ?> out of 5 stars"><?= str_repeat('&#9733;', (int) $t['rating']) ?></div>
            <blockquote>&ldquo;<?= View::text($t['body']) ?>&rdquo;</blockquote>
            <figcaption>&mdash; <?= View::e($t['author']) ?><?= !empty($t['location']) ? ', ' . View::e($t['location']) : '' ?></figcaption>
          </figure>
        <?php endforeach; ?>
        <p class="lp-note">Testimonials provided by <?= View::e($name) ?>.</p>
      </div>
      <?php if (count($testimonials) > 1): ?>
        <p class="lp-center"><button class="lp-more" type="button" data-reviews-more>View All Reviews</button></p>
      <?php endif; ?>
    <?php endif; ?>

    <?php if ($full && $offers !== []): ?>
      <h2 class="lp-h3">Special Offers:</h2>
      <div class="lp-offers" data-offers>
        <?php foreach ($offers as $i => $offer): ?>
          <div class="<?= $i > 0 ? 'is-more' : '' ?>"><?= View::render('business/_offer', ['offer' => $offer]) ?></div>
        <?php endforeach; ?>
      </div>
      <?php if (count($offers) > 1): ?>
        <p class="lp-center"><button class="lp-more" type="button" data-offers-more>View All Offers</button></p>
      <?php endif; ?>
    <?php endif; ?>
  </section>
</div>
