<?php use App\Support\View; ?>
<section class="section">
  <div class="container prose">
    <h1><?= View::e($title ?? 'Page not found') ?></h1>
    <p class="lede">That page isn&rsquo;t here. If you were looking for a business, the link may have changed or the page may be paused.</p>
    <p><a class="btn btn--primary" href="/">Go to LeadCrazy</a></p>
  </div>
</section>
