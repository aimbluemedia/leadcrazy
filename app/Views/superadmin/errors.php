<?php /** @var list<string> $entries @var string $ref @var bool $exists @var bool $appKeyMissing */ use App\Support\View; ?>
<div class="app__head"><h1>Errors</h1>
  <form method="get" action="/superadmin/errors" class="row">
    <input class="field" style="width:200px" type="search" name="ref" value="<?= View::e($ref) ?>" placeholder="Reference, e.g. 029B8C89">
    <button class="btn btn--ghost btn--sm" type="submit">Find</button>
    <?php if ($ref !== ''): ?><a class="btn btn--ghost btn--sm" href="/superadmin/errors">Show all</a><?php endif; ?>
  </form>
</div>
<?php if ($appKeyMissing): ?>
  <div class="panel panel--warn"><h2>app_key is empty</h2>
    <p>The site is using a key it generated itself in <code>storage/app.key</code>, so lead forms work. For a permanent setup, put a long random value in <code>'app_key'</code> in <code>app/config.php</code>.</p></div>
<?php endif; ?>
<p class="muted">Newest first, from <code>storage/logs/error.log</code>. Each visitor-facing error shows a reference you can search for here.</p>
<?php if (!$exists): ?>
  <div class="panel"><p class="muted" style="margin:0">No errors logged yet.</p></div>
<?php elseif ($entries === []): ?>
  <div class="panel"><p class="muted" style="margin:0"><?= $ref !== '' ? 'No entry with reference ' . View::e($ref) . ' in the recent log.' : 'No recent entries.' ?></p></div>
<?php else: ?>
  <?php foreach ($entries as $entry): $lines = explode("\n", $entry); ?>
    <div class="panel">
      <p style="margin:0 0 8px;font-weight:600;color:var(--ink);word-break:break-word"><?= View::e($lines[0]) ?></p>
      <?php if (count($lines) > 1): ?>
        <details><summary class="muted" style="cursor:pointer;font-size:14px">Stack trace</summary>
          <pre style="white-space:pre-wrap;word-break:break-all;font-size:12px;margin:8px 0 0"><?= View::e(implode("\n", array_slice($lines, 1))) ?></pre></details>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
