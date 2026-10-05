<?php use App\Support\View; ?>
<?= View::render('partials/hero-band') ?>

<section class="section">
  <div class="container split">
    <div class="split__media">
      <img src="<?= View::e(View::asset('/assets/img/hero.jpg')) ?>" width="1122" height="1402" loading="lazy"
           alt="A home service professional outside a customer&rsquo;s home, holding a tablet.">
      <div class="split__float" aria-hidden="true">
        <span class="split__dot"></span>
        <span><strong>New lead &middot; Pavers &middot; $10k&ndash;25k</strong>
          <small>Dana R. &middot; Tempe, AZ &middot; starting in 1&ndash;3 months</small></span>
      </div>
    </div>

    <div>
      <h2 class="split__title">Capture. Qualify. <em>Close.</em></h2>
      <p class="lede">Everything a lead page needs, nothing it doesn&rsquo;t. Built from the layout that wins jobs for
        contractors, landscapers, remodelers and home-service pros &mdash; so every visitor knows exactly what to do next.</p>

      <div class="split__cards">
        <?php foreach ([
          ['01', '&#9997;', 'Qualify', 'Services, timeline, budget and project details on every lead &mdash; so you know who to call first.'],
          ['02', '&#127873;', 'Convert', 'Limited-time offers with expiry dates, right on top of the form where they get claimed.'],
          ['03', '&#128205;', 'Win local', 'Service cities, zip codes, photos, reviews and video build trust before they ever call.'],
        ] as [$num, $icon, $title, $body]): ?>
          <div class="step-card">
            <span class="step-card__icon" aria-hidden="true"><?= $icon ?></span>
            <span class="step-card__num"><?= $num ?></span>
            <h3><?= $title ?></h3>
            <p><?= $body ?></p>
          </div>
        <?php endforeach; ?>
      </div>

      <ul class="split__pills">
        <li>Services</li><li>Timeline</li><li>Budget</li><li>Offers</li><li>Zip codes</li>
      </ul>

      <p class="split__note">Every lead lands in your dashboard &mdash; track it from new to won.</p>

      <div class="hero__ctas">
        <a class="btn btn--primary btn--xl" href="/members/signup">Get My Free Lead Page</a>
        <a class="btn btn--ghost btn--xl" href="/how-it-works">See How It Works</a>
      </div>
    </div>
  </div>
</section>

<section class="section section--soft" id="calculator">
  <div class="container">
    <div class="center" style="margin-bottom:32px">
      <span class="eyebrow">&#128176; Lead calculator</span>
      <h2>What Is a Better Form <span class="accent">Worth to You?</span></h2>
      <p class="lede" style="margin:0 auto">Move the sliders to match your business and see the leads, jobs and profit a form that converts 3&times; better could add.</p>
    </div>
    <?= View::render('partials/lead-calculator') ?>
  </div>
</section>

<section class="section">
  <div class="container">
    <h2 class="center">How it works</h2>
    <ol class="steps" style="margin-top:28px">
      <li><h3>Sign up</h3><p>Create your account. Takes two minutes.</p></li>
      <li><h3>Tell us about you</h3><p>Services, areas, offers, photos and reviews &mdash; one short form.</p></li>
      <li><h3>We build it</h3><p>Our team builds your Million Dollar Lead Form page, usually within one business day.</p></li>
      <li><h3>Get leads</h3><p>Requests land in your dashboard. Track each one from new to won.</p></li>
    </ol>
  </div>
</section>


<section class="section">
  <div class="container grid-2" style="align-items:center">
    <div>
      <h2>Listed on MonsterList</h2>
      <p>Pro and Premium pages are listed on MonsterList automatically, so local customers find you where they are already searching. Every request still comes straight to your LeadCrazy dashboard.</p>
    </div>
    <div>
      <h2>On your own website</h2>
      <p>Premium members paste one line of code to put the full page &mdash; or just the lead form &mdash; on their own site. WordPress, Wix, Squarespace or plain HTML.</p>
      <pre class="card" style="font-size:12px;white-space:pre-wrap;word-break:break-all;margin:0">&lt;script src="<?= View::e(View::url('/widget/your-business.js')) ?>" async&gt;&lt;/script&gt;</pre>
    </div>
  </div>
</section>

<section class="section section--soft center">
  <div class="container">
    <h2>Ready for more quote requests?</h2>
    <p class="lede" style="margin:0 auto 22px">Your free page is a few minutes away.</p>
    <a class="btn btn--primary" href="/members/signup">Get Your Free Page &rarr;</a>
  </div>
</section>
