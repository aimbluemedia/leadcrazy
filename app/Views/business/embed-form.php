<?php
/** Form only, for the $49 "just the form" embed. @var array<string,mixed> $page @var array<string,mixed> $account */
use App\Support\View;
?>
<div class="lp-embed-form" id="quote">
  <div class="lp-card">
    <?= View::render('business/_form', get_defined_vars()) ?>
  </div>
</div>
