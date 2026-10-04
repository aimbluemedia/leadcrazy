<?php
/**
 * The page builder for one business.
 *
 * @var array<string,mixed> $account @var array<string,mixed> $page
 * @var list<array<string,mixed>> $offers @var list<array<string,mixed>> $gallery @var list<array<string,mixed>> $testimonials
 * @var list<array<string,mixed>> $users @var ?array<string,mixed> $intake @var ?string $intakeAt
 * @var list<array<string,mixed>> $requests @var int $leadCount @var bool $liveSubscription @var string $tab
 */
use App\Support\Csrf;
use App\Support\Pages;
use App\Support\Plans;
use App\Support\View;

$id = (int) $account['id'];
$p = static fn (string $k): string => View::e(isset($page[$k]) ? (string) $page[$k] : '');
$lines = static fn (string $k): string => View::e(Pages::toLines($page[$k] ?? null));
$hidden = Csrf::field() . '<input type="hidden" name="account_id" value="' . $id . '">';
$tabs = ['content' => 'Page content', 'media' => 'Logo & photos', 'offers' => 'Offers (' . count($offers) . ')',
    'testimonials' => 'Reviews (' . count($testimonials) . ')', 'settings' => 'Plan & settings', 'intake' => 'Intake'];
if (!isset($tabs[$tab])) {
    $tab = 'content';
}
?>
<div class="app__head">
  <div>
    <h1><?= View::e($account['business_name']) ?></h1>
    <p class="muted" style="margin:4px 0 0">
      <span class="tag tag--<?= View::e($account['plan']) ?>"><?= View::e(Plans::name((string) $account['plan'])) ?></span>
      <span class="tag tag--<?= View::e($account['page_status']) ?>"><?= View::e($account['page_status']) ?></span>
      &middot; /<?= View::e($account['slug']) ?> &middot; <?= $leadCount ?> leads
    </p>
  </div>
  <div class="row">
    <a class="btn btn--ghost btn--sm" href="/superadmin/preview?id=<?= $id ?>" target="_blank" rel="noopener">Preview</a>
    <?php if ($account['page_status'] === 'live'): ?><a class="btn btn--ghost btn--sm" href="/<?= View::e($account['slug']) ?>" target="_blank" rel="noopener">Live page &#8599;</a><?php endif; ?>
  </div>
</div>

<?php if ($requests !== []): ?>
  <div class="panel panel--warn"><h2>Open change requests</h2>
    <?php foreach ($requests as $r): ?>
      <div class="row" style="justify-content:space-between;border-top:1px solid #f6d488;padding:10px 0">
        <div style="white-space:pre-wrap;flex:1;min-width:200px"><span class="muted"><?= View::e(date('M j', strtotime((string) $r['created_at']) ?: 0)) ?>:</span> <?= View::e($r['body']) ?></div>
        <form method="post" action="/superadmin/requests/done"><?= Csrf::field() ?><input type="hidden" name="request_id" value="<?= (int) $r['id'] ?>"><input type="hidden" name="back" value="/superadmin/account?id=<?= $id ?>&tab=<?= View::e($tab) ?>"><button class="btn btn--ghost btn--sm" type="submit">Done</button></form>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<nav class="tabs">
  <?php foreach ($tabs as $key => $label): ?>
    <a href="/superadmin/account?id=<?= $id ?>&tab=<?= $key ?>" <?= $tab === $key ? 'aria-current="page"' : '' ?>><?= View::e($label) ?></a>
  <?php endforeach; ?>
</nav>

<?php if ($tab === 'content'): ?>
<form class="form" method="post" action="/superadmin/account/page">
  <?= $hidden ?>
  <div class="panel">
    <h2>Headline</h2>
    <div class="stack">
      <div><label>Badge (pill above the headline)</label><input type="text" name="badge" value="<?= $p('badge') ?>" placeholder="Free Landscape Quote"></div>
      <div><label>Headline</label><input type="text" name="headline" value="<?= $p('headline') ?>" placeholder="Storm Landscaping Phoenix AZ - Serving the East Valley Community Since 1998."></div>
      <div><label>Sub-headline (orange)</label><input type="text" name="subheadline" value="<?= $p('subheadline') ?>" placeholder="Your Dream Yard, Affordable, Professional, Done Right."></div>
      <div><label>Intro line (bold)</label><input type="text" name="intro" value="<?= $p('intro') ?>" placeholder="Bring Your Outdoor Vision to Life - Without Breaking the Bank"></div>
      <div><label>About (blank line between paragraphs)</label><textarea name="about" rows="6"><?= $p('about') ?></textarea></div>
      <div class="form__row">
        <div><label>"Why choose" heading</label><input type="text" name="why_title" value="<?= $p('why_title') ?>" placeholder="Why Choose Storm Landscaping:"></div>
        <div><label>Accent color</label><input type="color" name="accent_color" value="<?= $p('accent_color') ?: '#f5a300' ?>"></div>
      </div>
      <div><label>Why-choose points</label><textarea name="why_points" rows="5" placeholder="Affordable: transparent pricing with no surprises"><?= $lines('why_points') ?></textarea><p class="hint">One per line. Text before a colon is shown in bold.</p></div>
      <div><label>Closing paragraph</label><textarea name="closing" rows="3"><?= $p('closing') ?></textarea></div>
    </div>
  </div>

  <div class="panel">
    <h2>Contact &amp; stats</h2>
    <div class="stack">
      <div class="form__row">
        <div><label>Phone</label><input type="tel" name="phone" value="<?= $p('phone') ?>"></div>
        <div><label>Public email</label><input type="email" name="public_email" value="<?= $p('public_email') ?>"></div>
      </div>
      <div class="form__row form__row--3">
        <div><label>City</label><input type="text" name="city" value="<?= $p('city') ?>"></div>
        <div><label>State</label><input type="text" name="state" value="<?= $p('state') ?>"></div>
        <div><label>Website</label><input type="url" name="website" value="<?= $p('website') ?>"></div>
      </div>
      <div class="form__row form__row--3">
        <div><label>Rating</label><input type="text" name="stat_rating" value="<?= $p('stat_rating') ?>" placeholder="4.9/5"></div>
        <div><label>Projects completed</label><input type="text" name="stat_projects" value="<?= $p('stat_projects') ?>" placeholder="500+"></div>
        <div><label>Quote response</label><input type="text" name="stat_response" value="<?= $p('stat_response') ?>" placeholder="24h"></div>
      </div>
      <div><label>Video (YouTube or Vimeo link)</label><input type="url" name="video_url" value="<?= $p('video_url') ?>"></div>
      <p class="hint">Stats, video, gallery, zip codes, reviews and offers show on Pro and Premium only.</p>
    </div>
  </div>

  <div class="panel">
    <h2>Services &amp; areas</h2>
    <div class="stack">
      <div><label>Services (pills)</label><textarea name="services" rows="4"><?= $lines('services') ?></textarea><p class="hint">One per line or comma separated.</p></div>
      <div><label>Service cities</label><textarea name="locations" rows="3"><?= $lines('locations') ?></textarea></div>
      <div><label>Zip codes</label><textarea name="zipcodes" rows="3"><?= $lines('zipcodes') ?></textarea><p class="hint">Ranges are fine: 85003 - 85009.</p></div>
    </div>
  </div>

  <div class="panel">
    <h2>Lead form</h2>
    <div class="stack">
      <div class="form__row">
        <div><label>Form title</label><input type="text" name="form_title" value="<?= $p('form_title') ?>" placeholder="<?= View::e($account['business_name']) ?>"></div>
        <div><label>Button text</label><input type="text" name="cta_label" value="<?= $p('cta_label') ?>" placeholder="Get My Free Quote & Consultation"></div>
      </div>
      <div><label>Form intro</label><input type="text" name="form_intro" value="<?= $p('form_intro') ?>"></div>
      <div><label>"Choose your services" checkboxes</label><textarea name="form_services" rows="6" placeholder="Leave empty to use the services list above"><?= $lines('form_services') ?></textarea><p class="hint">One per line, e.g. Pavers, Artificial Turf, New Lawn Install, Package Special.</p></div>
      <div class="form__row">
        <div><label>"When are you looking to start?" options</label><textarea name="form_timelines" rows="4" placeholder="<?= View::e(implode("\n", Pages::DEFAULT_TIMELINES)) ?>"><?= $lines('form_timelines') ?></textarea></div>
        <div><label>Budget options</label><textarea name="form_budgets" rows="4" placeholder="<?= View::e(implode("\n", Pages::DEFAULT_BUDGETS)) ?>"><?= $lines('form_budgets') ?></textarea></div>
      </div>
      <p class="hint">Leave the last two empty to use the defaults shown.</p>
    </div>
  </div>
  <div><button class="btn btn--primary" type="submit">Save page</button></div>
</form>

<?php elseif ($tab === 'media'): ?>
<div class="grid-2" style="align-items:start">
  <?php foreach (['logo' => 'Logo', 'hero' => 'Hero banner (top of the form card)'] as $which => $label): $path = $page[$which . '_path'] ?? null; ?>
    <div class="panel">
      <h2><?= $label ?></h2>
      <?php if ($path): ?><img src="<?= View::e($path) ?>" alt="" style="max-height:160px;border-radius:8px;margin-bottom:12px;<?= $which === 'logo' ? 'background:#f6f8fb;padding:8px' : '' ?>"><?php endif; ?>
      <form class="form" method="post" action="/superadmin/account/image" enctype="multipart/form-data">
        <?= $hidden ?><input type="hidden" name="which" value="<?= $which ?>">
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif" required>
        <div class="row"><button class="btn btn--primary btn--sm" type="submit">Upload</button></div>
      </form>
      <?php if ($path): ?>
        <form method="post" action="/superadmin/account/image" style="margin-top:8px"><?= $hidden ?><input type="hidden" name="which" value="<?= $which ?>"><input type="hidden" name="remove" value="1"><button class="btn btn--danger btn--sm" type="submit">Remove</button></form>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>
<div class="panel">
  <h2>Gallery (<?= count($gallery) ?>)</h2>
  <form class="form" method="post" action="/superadmin/gallery/add" enctype="multipart/form-data" style="margin-bottom:16px">
    <?= $hidden ?>
    <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple required>
    <div><button class="btn btn--primary btn--sm" type="submit">Add photos</button></div>
  </form>
  <div class="thumbs">
    <?php foreach ($gallery as $g): ?>
      <figure><img src="<?= View::e($g['path']) ?>" alt="">
        <figcaption><form method="post" action="/superadmin/gallery/delete" data-confirm="Remove this photo?"><?= $hidden ?><input type="hidden" name="image_id" value="<?= (int) $g['id'] ?>"><button class="btn btn--danger btn--sm btn--block" type="submit">Remove</button></form></figcaption>
      </figure>
    <?php endforeach; ?>
  </div>
  <p class="hint" style="margin-top:10px">The first 6 show on the page; the rest appear under "Load More Images".</p>
</div>

<?php elseif ($tab === 'offers'): ?>
<?php foreach (array_merge($offers, [['id' => 0, 'label' => 'LIMITED TIME', 'title' => '', 'body' => '', 'expires_on' => '', 'sort_order' => count($offers) + 1, 'is_active' => 1]]) as $o): $new = (int) $o['id'] === 0; ?>
  <div class="panel">
    <h2><?= $new ? 'Add an offer' : View::e($o['title']) ?><?= !$new && !(int) $o['is_active'] ? ' <span class="tag tag--paused">hidden</span>' : '' ?><?= !$new && $o['expires_on'] && $o['expires_on'] < date('Y-m-d') ? ' <span class="tag tag--lost">expired</span>' : '' ?></h2>
    <form class="form" method="post" action="/superadmin/offer/save">
      <?= $hidden ?><input type="hidden" name="offer_id" value="<?= (int) $o['id'] ?>">
      <div class="form__row form__row--3">
        <div><label>Tag</label><input type="text" name="label" value="<?= View::e($o['label']) ?>"></div>
        <div><label>Title</label><input type="text" name="title" value="<?= View::e($o['title']) ?>" required placeholder="Saguaro"></div>
        <div><label>Expires</label><input type="date" name="expires_on" value="<?= View::e($o['expires_on']) ?>"></div>
      </div>
      <div><label>Details</label><textarea name="body" rows="4" placeholder="Saguaro (1000-1500 sq ft) Irrigation - Install drip system with timer: $750.00 ..."><?= View::e($o['body']) ?></textarea></div>
      <div class="row">
        <div style="width:110px"><label>Order</label><input type="number" name="sort_order" value="<?= (int) $o['sort_order'] ?>"></div>
        <label class="check" style="margin-top:22px"><input type="checkbox" name="is_active" value="1"<?= (int) $o['is_active'] ? ' checked' : '' ?>> Show on page</label>
      </div>
      <div class="row"><button class="btn btn--primary btn--sm" type="submit"><?= $new ? 'Add offer' : 'Save' ?></button></div>
    </form>
    <?php if (!$new): ?>
      <form method="post" action="/superadmin/offer/delete" data-confirm="Delete this offer?" style="margin-top:8px"><?= $hidden ?><input type="hidden" name="offer_id" value="<?= (int) $o['id'] ?>"><button class="btn btn--danger btn--sm" type="submit">Delete</button></form>
    <?php endif; ?>
  </div>
<?php endforeach; ?>
<p class="hint">The first active offer also appears at the top of the form card. Expired offers hide themselves.</p>

<?php elseif ($tab === 'testimonials'): ?>
<?php foreach (array_merge($testimonials, [['id' => 0, 'author' => '', 'location' => '', 'rating' => 5, 'body' => '', 'sort_order' => count($testimonials) + 1]]) as $t): $new = (int) $t['id'] === 0; ?>
  <div class="panel">
    <h2><?= $new ? 'Add a review' : View::e($t['author']) ?></h2>
    <form class="form" method="post" action="/superadmin/testimonial/save">
      <?= $hidden ?><input type="hidden" name="testimonial_id" value="<?= (int) $t['id'] ?>">
      <div class="form__row form__row--3">
        <div><label>Name</label><input type="text" name="author" value="<?= View::e($t['author']) ?>" required placeholder="Jimmy E."></div>
        <div><label>Location</label><input type="text" name="location" value="<?= View::e($t['location']) ?>" placeholder="Queen Creek"></div>
        <div><label>Stars</label><select name="rating"><?php for ($s = 5; $s >= 1; $s--): ?><option value="<?= $s ?>"<?= (int) $t['rating'] === $s ? ' selected' : '' ?>><?= $s ?></option><?php endfor; ?></select></div>
      </div>
      <div><label>Review</label><textarea name="body" rows="4" required><?= View::e($t['body']) ?></textarea></div>
      <div class="row"><div style="width:110px"><label>Order</label><input type="number" name="sort_order" value="<?= (int) $t['sort_order'] ?>"></div></div>
      <div class="row"><button class="btn btn--primary btn--sm" type="submit"><?= $new ? 'Add review' : 'Save' ?></button></div>
    </form>
    <?php if (!$new): ?>
      <form method="post" action="/superadmin/testimonial/delete" data-confirm="Delete this review?" style="margin-top:8px"><?= $hidden ?><input type="hidden" name="testimonial_id" value="<?= (int) $t['id'] ?>"><button class="btn btn--danger btn--sm" type="submit">Delete</button></form>
    <?php endif; ?>
  </div>
<?php endforeach; ?>
<p class="hint">Only reviews the business supplied from real customers. The page labels them "provided by" the business.</p>

<?php elseif ($tab === 'settings'): ?>
<div class="panel">
  <h2>Plan &amp; status</h2>
  <form class="form" method="post" action="/superadmin/account/settings">
    <?= $hidden ?>
    <div class="form__row">
      <div><label>Business name</label><input type="text" name="business_name" value="<?= View::e($account['business_name']) ?>" required></div>
      <div><label>Page address</label><input type="text" name="slug" value="<?= View::e($account['slug']) ?>" required pattern="[a-z0-9-]+"><p class="hint"><?= View::e(Pages::url('')) ?>&hellip; Changing it breaks old links.</p></div>
    </div>
    <div class="form__row">
      <div><label>Plan</label>
        <select name="plan"<?= $liveSubscription ? ' disabled' : '' ?>>
          <?php foreach (Plans::ALL as $plan): ?><option value="<?= $plan ?>"<?= $account['plan'] === $plan ? ' selected' : '' ?>><?= View::e(Plans::name($plan) . ' - ' . Plans::priceLabel($plan)) ?></option><?php endforeach; ?>
        </select>
        <?php if ($liveSubscription): ?><input type="hidden" name="plan" value="<?= View::e($account['plan']) ?>"><p class="hint">Set by Stripe (subscription <?= View::e($account['stripe_status']) ?>). Change it in Stripe.</p>
        <?php elseif ($account['requested_plan']): ?><p class="hint">Member asked for <?= View::e(Plans::name((string) $account['requested_plan'])) ?>.</p><?php endif; ?>
      </div>
      <div><label>Page status</label>
        <select name="page_status">
          <?php foreach (['intake' => 'Intake - waiting on member', 'building' => 'Building - not public', 'live' => 'Live - public', 'paused' => 'Paused - hidden'] as $s => $label): ?>
            <option value="<?= $s ?>"<?= $account['page_status'] === $s ? ' selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select></div>
    </div>
    <div><button class="btn btn--primary" type="submit">Save settings</button></div>
  </form>
</div>
<div class="panel">
  <h2>Logins</h2>
  <div class="table-wrap"><table class="table">
    <thead><tr><th>Email</th><th>Name</th><th>Last sign-in</th><th></th></tr></thead>
    <tbody><?php foreach ($users as $u): ?>
      <tr><td><?= View::e($u['email']) ?></td><td><?= View::e(trim($u['first_name'] . ' ' . $u['last_name'])) ?></td>
          <td><?= $u['last_login_at'] ? View::e(date('M j, Y g:ia', strtotime((string) $u['last_login_at']) ?: 0)) : 'never' ?></td>
          <td><form method="post" action="/superadmin/account/password" data-confirm="Set a temporary password for <?= View::e($u['email']) ?>?"><?= $hidden ?><input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>"><button class="btn btn--ghost btn--sm" type="submit">Temporary password</button></form></td></tr>
    <?php endforeach; ?></tbody>
  </table></div>
</div>
<div class="panel">
  <h2>Billing</h2>
  <dl class="dl">
    <dt>Stripe customer</dt><dd><?= View::e($account['stripe_customer_id'] ?: '-') ?></dd>
    <dt>Subscription</dt><dd><?= View::e(($account['stripe_subscription_id'] ?: '-') . ($account['stripe_status'] ? ' (' . $account['stripe_status'] . ')' : '')) ?></dd>
    <dt>Renews</dt><dd><?= View::e($account['plan_renews_at'] ?: '-') ?></dd>
  </dl>
</div>

<?php else: ?>
<div class="panel">
  <h2>Latest intake<?= $intakeAt ? ' &middot; ' . View::e(date('M j, Y g:ia', strtotime((string) $intakeAt) ?: 0)) : '' ?></h2>
  <?php if ($intake === null): ?>
    <p class="muted">The member has not sent the intake form yet.</p>
  <?php else: ?>
    <dl class="dl">
      <?php foreach (['business_name' => 'Business', 'phone' => 'Phone', 'public_email' => 'Email', 'website' => 'Website', 'city' => 'City', 'state' => 'State', 'years' => 'Since', 'services' => 'Services', 'locations' => 'Cities', 'zipcodes' => 'Zip codes', 'about' => 'About', 'why' => 'Why choose', 'offers' => 'Offers', 'testimonials' => 'Testimonials', 'video_url' => 'Video', 'notes' => 'Notes'] as $k => $label): ?>
        <?php if (!empty($intake[$k])): ?><dt><?= $label ?></dt><dd style="white-space:pre-wrap"><?= View::e((string) $intake[$k]) ?></dd><?php endif; ?>
      <?php endforeach; ?>
    </dl>
    <?php $imgs = array_filter(array_merge([$intake['logo'] ?? null], (array) ($intake['photos'] ?? []))); ?>
    <?php if ($imgs): ?>
      <h2 style="margin-top:18px">Uploaded images</h2>
      <div class="thumbs"><?php foreach ($imgs as $img): ?><figure><a href="<?= View::e((string) $img) ?>" target="_blank" rel="noopener"><img src="<?= View::e((string) $img) ?>" alt=""></a></figure><?php endforeach; ?></div>
      <p class="hint">Photos were added to the gallery and the first became the hero; the logo was set if the page had none.</p>
    <?php endif; ?>
  <?php endif; ?>
</div>
<?php endif; ?>
