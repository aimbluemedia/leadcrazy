<?php use App\Support\Plans; use App\Support\View; ?>
<section class="section">
  <div class="container">
    <div class="center" style="margin-bottom:36px">
      <span class="eyebrow">The Million Dollar Lead Form</span>
      <h1>Pick your plan</h1>
      <p class="lede" style="margin:0 auto">Every plan includes a page built for you by our team. Paid plans are monthly &mdash; cancel any time.</p>
    </div>
    <?= View::render('partials/plans') ?>
  </div>
</section>
<?php
$faqs = [
    ['What happens when a Free page passes ' . Plans::FREE_MONTHLY_LEADS . ' leads in a month?',
     'Nothing is lost. Extra leads are saved to your dashboard and unlock the moment you upgrade.'],
    ['Who builds my page?',
     'You do, in a few minutes, with our free builder on the How it works page: pick your trade, add your services, service area and offer. Our team checks it before it goes live. Want a change later? Ask from your dashboard.'],
    ['What is MonsterList?',
     'A local business directory. Pro and Premium pages are listed there automatically and link back to your lead page.'],
    ['How does the $49 embed work?',
     'You get one line of code. Paste it anywhere on your website and your full page, or just the form, appears there. Leads from your site and from LeadCrazy land in the same dashboard.'],
    ['Can I cancel?',
     'Yes, any time, from your billing page. Your page moves to the Free plan at the end of the period you paid for.'],
];
?>
<section class="section section--soft">
  <div class="container">
    <h2 class="center" style="margin-bottom:28px">Questions</h2>
    <div class="faq">
      <?php foreach ($faqs as [$q, $a]): ?>
        <details class="faq__item">
          <summary><?= View::e($q) ?></summary>
          <p><?= View::e($a) ?></p>
        </details>
      <?php endforeach; ?>
    </div>
  </div>
</section>
