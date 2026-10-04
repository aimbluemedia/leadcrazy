<?php
/** @var array<string,mixed> $account @var bool $canEmbed @var bool $live */
use App\Support\View;
$slug = (string) $account['slug'];
$src = View::url('/widget/' . rawurlencode($slug) . '.js');
$pageCode = '<script src="' . $src . '" data-view="page" async></script>';
$formCode = '<script src="' . $src . '" data-view="form" async></script>';
?>
<div class="app__head"><h1>Embed on your website</h1></div>

<?php if (!$canEmbed): ?>
  <div class="panel panel--warn">
    <h2>Put your lead page on your own website</h2>
    <p>With Premium ($49/month) you get one line of code that puts your full page &mdash; or just the lead form &mdash; on any website: WordPress, Wix, Squarespace, Shopify or plain HTML. Leads from your site land in this same dashboard.</p>
    <a class="btn btn--primary" href="/members/billing">Upgrade to Premium</a>
  </div>
<?php else: ?>
  <?php if (!$live): ?><div class="alert alert--info" style="margin-bottom:18px">Your code is ready, but it will show nothing until your page is live.</div><?php endif; ?>
  <div class="panel">
    <h2>Full page</h2>
    <p class="muted">Your whole lead page: offers, gallery, reviews, service areas and the form.</p>
    <div class="copy"><pre id="code-page"><?= View::e($pageCode) ?></pre>
      <button class="btn btn--ghost btn--sm" type="button" data-copy="code-page">Copy code</button></div>
  </div>
  <div class="panel">
    <h2>Just the lead form</h2>
    <p class="muted">The Million Dollar Lead Form on its own &mdash; ideal for a sidebar or a contact page.</p>
    <div class="copy"><pre id="code-form"><?= View::e($formCode) ?></pre>
      <button class="btn btn--ghost btn--sm" type="button" data-copy="code-form">Copy code</button></div>
  </div>
  <div class="panel">
    <h2>How to add it</h2>
    <ul>
      <li><strong>WordPress:</strong> edit the page, add a <em>Custom HTML</em> block, paste the code.</li>
      <li><strong>Wix:</strong> Add &rarr; Embed Code &rarr; Embed HTML, paste the code.</li>
      <li><strong>Squarespace:</strong> add a <em>Code</em> block, paste the code.</li>
      <li><strong>Any other site:</strong> paste it into the page's HTML where the form should appear.</li>
    </ul>
    <p class="hint">The form resizes itself to fit. Changes we make to your page show up on your site automatically.</p>
  </div>
<?php endif; ?>
