<?php
/**
 * Site footer: the Toutify membership (one free account, every tool), then
 * the link columns and legal line.
 */
use App\Support\View;

$icon = static fn (string $d): string => '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $d . '</svg>';
$tools = [
    ['LeadCrazy', 'The Million Dollar Lead Form and your leads dashboard', '<path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/>', true],
    ['SearchMonster', 'Get found in AI search, with a free listing', '<circle cx="11" cy="11" r="6.5"/><path d="m20 20-4.2-4.2"/>', false],
    ['MonsterList', 'Free business listings everywhere', '<rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 8h6M9 12h6M9 16h4"/>', false],
    ['PromoMonster', 'Reviews and reputation on autopilot', '<path d="m12 3 2.7 5.6 6.1.8-4.5 4.2 1.1 6.1L12 16.8 6.6 19.7l1.1-6.1-4.5-4.2 6.1-.8z"/>', false],
    ['ContentVendor', 'AI-written posts and content', '<path d="M4 20h4L19 9l-4-4L4 16z"/><path d="m13.5 6.5 4 4"/>', false],
    ['Official Directories', 'Free state directory listings', '<path d="M3 21h18M4 10h16M12 3 3 8v2h18V8z"/><path d="M6 10v8M10 10v8M14 10v8M18 10v8"/>', false],
];
?>
<footer class="site-footer">
  <section class="member-band">
    <div class="container">
      <div class="member-band__head">
        <div>
          <p class="member-band__eyebrow"><span aria-hidden="true"></span>Toutify membership</p>
          <h2>One membership. <span>Every tool.</span></h2>
          <p class="member-band__lede">Your free Toutify account unlocks LeadCrazy and the whole Toutify family &mdash; one login, every feature, free forever.</p>
          <ul class="member-band__perks">
            <?php foreach (['Free forever', 'No credit card', 'Every feature included', 'One login for every tool'] as $perk): ?>
              <li><?= $perk ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <div class="member-band__cta">
          <a class="member-band__btn" href="/signup">Create my free account &rarr;</a>
          <a class="member-band__login" href="/auth/toutify/login">Already a member? Log in</a>
        </div>
      </div>

      <p class="member-band__label">Your membership includes</p>
      <div class="member-band__grid">
        <?php foreach ($tools as [$name, $what, $path, $here]): ?>
          <div class="member-tool<?= $here ? ' is-here' : '' ?>">
            <span class="member-tool__icon"><?= $icon($path) ?></span>
            <span class="member-tool__text"><strong><?= $name ?></strong><small><?= $what ?></small></span>
            <?php if ($here): ?><em class="member-tool__here">You are here</em><?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <div class="container site-footer__cols">
    <div class="site-footer__brand">
      <?= View::render('partials/logo') ?>
      <p>The Million Dollar Lead Form for local businesses. Turn every visitor into your next client.</p>
    </div>
    <nav aria-label="LeadCrazy">
      <p>LeadCrazy</p>
      <a href="/">Home</a><a href="/how-it-works">Build your lead form</a><a href="/pricing">Pricing</a>
    </nav>
    <nav aria-label="Account">
      <p>Account</p>
      <a href="/signup">Create a free account</a><a href="/auth/toutify/login">Log in</a><a href="/members">Members dashboard</a>
    </nav>
    <nav aria-label="Legal">
      <p>Legal</p>
      <a href="/privacy">Privacy</a><a href="/terms">Terms</a>
    </nav>
  </div>
  <div class="container site-footer__bottom">
    <span>&copy; <?= date('Y') ?> LeadCrazy by Toutify. All rights reserved.</span>
    <span>Lead pages are listed on MonsterList.</span>
  </div>
</footer>
