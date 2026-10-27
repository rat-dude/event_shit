<?php foreach (pull_flashes() as $alert): ?>
<div class="alert alert-<?= e($alert['type']) ?> alert-dismissible fade show" role="alert">
    <?= e($alert['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endforeach; ?>
