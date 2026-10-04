<?php use App\Support\View; ?>
<?= View::render('partials/hero-band') ?>

<section class="section section--soft">
  <div class="container">
    <h2 class="center">Everything a lead page needs. Nothing it doesn&rsquo;t.</h2>
    <p class="lede center" style="margin:0 auto 32px">Built from the layout that wins jobs for contractors, landscapers, remodelers and home-service pros.</p>
    <div class="grid-3">
      <div class="card"><div class="feature__icon">&#9997;</div><h3>A form that qualifies</h3><p>Services, timeline, budget and project details on every lead &mdash; so you know who to call first.</p></div>
      <div class="card"><div class="feature__icon feature__icon--green">&#127873;</div><h3>Offers that convert</h3><p>Limited-time specials with expiry dates, shown right on top of the form where they get claimed.</p></div>
      <div class="card"><div class="feature__icon feature__icon--purple">&#128205;</div><h3>Local, by design</h3><p>Service cities and zip codes, project photos, reviews and video build trust before they ever call.</p></div>
    </div>
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
