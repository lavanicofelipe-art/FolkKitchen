<?php
require_once __DIR__ . '/includes/bootstrap.php';
$user = requireLogin();

$entries = ActivityLog::recent(60);

$activeNav = 'activity';
require __DIR__ . '/includes/header.php';
?>
<div class="topbar">
  <h2 class="page-title">Activity Log</h2>
  <div class="page-sub">Who added or updated what, and when.</div>
</div>

<table class="data-table">
  <thead><tr><th>When</th><th>Family member</th><th>Action</th><th>Recipe</th></tr></thead>
  <tbody>
    <?php if ($entries): foreach ($entries as $entry): ?>
      <tr>
        <td><?= formatDateTime($entry['log']->logDate) ?></td>
        <td><?= e($entry['userName']) ?></td>
        <td><?= e($entry['log']->action) ?></td>
        <td><?= $entry['recipeTitle'] ? e($entry['recipeTitle']) : '—' ?></td>
      </tr>
    <?php endforeach; else: ?>
      <tr><td colspan="4">No activity yet.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<?php require __DIR__ . '/includes/footer.php'; ?>
