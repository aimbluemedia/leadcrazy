<?php use App\Support\Plans; use App\Support\View; ?>
<section class="section">
  <div class="container">
    <div class="center" style="margin-bottom:36px">
      <span class="eyebrow">&#9889; The Million Dollar Lead Form</span>
      <h1>Pick your plan</h1>
      <p class="lede" style="margin:0 auto">Every plan includes a page built for you by our team. Paid plans are monthly &mdash; cancel any time.</p>
    </div>
    <?= View::render('partials/plans') ?>
  </div>
</section>
<section class="section section--soft">
  <div class="container prose" style="margin:0 auto">
    <h2>Questions</h2>
    <h3>What happens when a Free page passes <?= Plans::FREE_MONTHLY_LEADS ?> leads in a month?</h3>
    <p>Nothing is lost. Extra leads are saved to your dashboard and unlock the moment you upgrade.</p>
    <h3>Who builds my page?</h3>
    <p>We do. After you sign up you fill in a short form about your business, and our team builds the page. Want a change later? Ask from your dashboard.</p>
    <h3>What is MonsterList?</h3>
    <p>A local business directory. Pro and Premium pages are listed there automatically and link back to your lead page.</p>
    <h3>How does the $49 embed work?</h3>
    <p>You get one line of code. Paste it anywhere on your website and your full page, or just the form, appears there. Leads from your site and from LeadCrazy land in the same dashboard.</p>
    <h3>Can I cancel?</h3>
    <p>Yes, any time, from your billing page. Your page moves to the Free plan at the end of the period you paid for.</p>
  </div>
</section>
