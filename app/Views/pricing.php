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
    ['Do I need a credit card to start?',
     'No. The Free plan needs no card at all. You only add one if you decide to upgrade to Pro or Premium.'],
    ['How will I know when I get a new lead?',
     'Every lead lands in your dashboard with the customer\'s name, contact details, services, timeline, budget and project notes, and we email you the moment it comes in.'],
    ['What is the difference between Pro and Premium?',
     'Both remove the lead limit and the LeadCrazy branding, and add photos, video, reviews, stats, more offers and unlimited cities and zip codes. Pro is hosted on LeadCrazy and listed on MonsterList. Premium adds the embed code so the form also works on your own website.'],
    ['How do you keep out spam leads?',
     'Every form is protected by Cloudflare Turnstile, hidden bot traps and limits on repeat submissions, so the leads in your dashboard come from real people.'],
    ['Can I upgrade or switch plans later?',
     'Yes. Upgrade from your dashboard whenever you like. Paid features switch on right away, and any leads held back on the Free plan unlock instantly.'],
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
