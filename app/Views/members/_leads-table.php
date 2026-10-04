<?php
/** @var list<array<string,mixed>> $leads @var array<string,mixed> $account */
use App\Support\Leads;
use App\Support\View;
?>
<div class="table-wrap">
<table class="table">
  <thead><tr><th>Received</th><th>Name</th><th>Phone</th><th>City</th><th>Services</th><th>Budget</th><th>Status</th></tr></thead>
  <tbody>
  <?php foreach ($leads as $l): $open = Leads::visible($l, $account); ?>
    <tr>
      <td style="white-space:nowrap"><?= View::e(date('M j, g:ia', strtotime((string) $l['created_at']) ?: 0)) ?></td>
      <td><a href="/members/lead?id=<?= (int) $l['id'] ?>"><?= View::e($l['first_name'] . ' ' . ($open ? $l['last_name'] : '')) ?></a></td>
      <td><?= $open ? View::e($l['phone']) : '<span class="tag tag--locked">Locked</span>' ?></td>
      <td><?= View::e($l['city']) ?></td>
      <td><?= View::e(implode(', ', Leads::services($l['services']))) ?></td>
      <td><?= View::e($l['budget']) ?></td>
      <td><span class="tag tag--<?= View::e($l['status']) ?>"><?= View::e($l['status']) ?></span></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
