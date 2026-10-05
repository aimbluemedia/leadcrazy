<?php
/**
 * "What is a better form worth?" calculator.
 *
 * Plain arithmetic, shown so a visitor can check it:
 *   leads  = visitors x conversion rate
 *   jobs   = leads x close rate
 *   profit = jobs x average job value x margin
 * The LeadCrazy column multiplies the conversion rate by the lift slider
 * (default 3x). The lift is the visitor's assumption to move, not a promise,
 * and the note under the panel says so.
 *
 * Rendered with the defaults filled in, so it reads correctly before any JS.
 */
$d = ['visitors' => 1000, 'rate' => 2, 'lift' => 3, 'value' => 5000, 'close' => 20, 'margin' => 25];
$calc = static function (array $v, float $lift): array {
    $leads = $v['visitors'] * $v['rate'] / 100 * $lift;
    $jobs = $leads * $v['close'] / 100;
    $revenue = $jobs * $v['value'];
    return ['leads' => $leads, 'jobs' => $jobs, 'revenue' => $revenue, 'profit' => $revenue * $v['margin'] / 100];
};
$now = $calc($d, 1);
$lc = $calc($d, $d['lift']);
$money = static fn (float $n): string => '$' . number_format(round($n));
$num = static fn (float $n): string => rtrim(rtrim(number_format($n, 1), '0'), '.');
$fields = [
    ['visitors', 'Website visitors a month', 100, 20000, 100, '', '', 'Everyone who lands on your site or lead page.'],
    ['rate', 'Your form converts today', 0.5, 10, 0.5, '', '%', 'A typical contact form converts 1&ndash;3% of visitors.'],
    ['value', 'Average job value', 500, 50000, 500, '$', '', 'What a typical job is worth to you.'],
    ['close', 'Leads you close', 5, 80, 5, '', '%', 'Out of every 100 quote requests, how many become jobs.'],
    ['margin', 'Profit per job', 5, 70, 5, '', '%', 'What you keep after materials and labor.'],
    ['lift', 'Lead form conversion boost', 1, 5, 0.5, '', '&times;', 'The Million Dollar Lead Form is built to convert 3&times; a regular form. Slide it down for a conservative case.'],
];
?>
<div class="lcalc" data-lcalc>
  <div class="lcalc__panel">
    <h3>Your business today</h3>
    <?php foreach ($fields as [$key, $label, $min, $max, $step, $pre, $post, $hint]): ?>
      <div class="lcalc__field">
        <label class="lcalc__label" for="lc-<?= $key ?>">
          <span><?= $label ?></span>
          <output id="lc-<?= $key ?>-out" data-pre="<?= $pre ?>" data-post="<?= $post ?>"><?= $pre . ($key === 'value' || $key === 'visitors' ? number_format($d[$key]) : $d[$key]) . $post ?></output>
        </label>
        <input type="range" id="lc-<?= $key ?>" name="<?= $key ?>" min="<?= $min ?>" max="<?= $max ?>" step="<?= $step ?>" value="<?= $d[$key] ?>">
        <p class="lcalc__hint"><?= $hint ?></p>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="lcalc__result" aria-live="polite">
    <p class="lcalc__eyebrow">Extra profit a year with the Million Dollar Lead Form</p>
    <p class="lcalc__hero" data-out="yearProfit"><?= $money(($lc['profit'] - $now['profit']) * 12) ?></p>
    <p class="lcalc__unit">Your regular form is leaving <strong data-out="monthGap"><?= $money($lc['profit'] - $now['profit']) ?></strong> a month on the table.</p>

    <div class="table-wrap">
      <table class="lcalc__table">
        <thead><tr><th></th><th>Regular form</th><th class="is-lc">LeadCrazy form</th></tr></thead>
        <tbody>
          <tr><th>Leads a month</th><td data-out="leadsNow"><?= $num($now['leads']) ?></td><td class="is-lc" data-out="leadsLc"><?= $num($lc['leads']) ?></td></tr>
          <tr><th>Jobs won a month</th><td data-out="jobsNow"><?= $num($now['jobs']) ?></td><td class="is-lc" data-out="jobsLc"><?= $num($lc['jobs']) ?></td></tr>
          <tr><th>Revenue a month</th><td data-out="revNow"><?= $money($now['revenue']) ?></td><td class="is-lc" data-out="revLc"><?= $money($lc['revenue']) ?></td></tr>
          <tr><th>Profit a month</th><td data-out="profitNow"><?= $money($now['profit']) ?></td><td class="is-lc" data-out="profitLc"><?= $money($lc['profit']) ?></td></tr>
        </tbody>
      </table>
    </div>

    <dl class="lcalc__rows">
      <div><dt>Extra leads a month</dt><dd data-out="extraLeads">+<?= $num($lc['leads'] - $now['leads']) ?></dd></div>
      <div><dt>Extra jobs a month</dt><dd data-out="extraJobs">+<?= $num($lc['jobs'] - $now['jobs']) ?></dd></div>
      <div><dt>Extra revenue a year</dt><dd data-out="yearRev"><?= $money(($lc['revenue'] - $now['revenue']) * 12) ?></dd></div>
      <div><dt>Every lead is worth</dt><dd data-out="perLead"><?= $money($d['value'] * $d['close'] / 100 * $d['margin'] / 100) ?> profit</dd></div>
    </dl>

    <a class="btn btn--primary btn--block btn--xl" href="/members/signup">Get My Free Lead Page &rarr;</a>
    <p class="lcalc__note">Estimates from the numbers you enter. Real results depend on your traffic, offers and how fast you follow up.</p>
  </div>
</div>
