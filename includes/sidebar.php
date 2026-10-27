<aside class="col-md-3 col-lg-2 sidebar p-3">
    <a class="d-block text-decoration-none fs-5 fw-semibold mb-4" href="/event_shit/dashboard/dashboard.php">Event Management</a>
    <div class="small text-uppercase text-white-50 mb-2">Workspace</div>
    <?php $currentPage = basename(dirname($_SERVER['SCRIPT_NAME'])); ?>
    <nav class="nav flex-column gap-1">
        <?php foreach ([['dashboard','Dashboard','/event_shit/dashboard/dashboard.php'],['categories','Categories','/event_shit/categories/categories.php'],['events','Events','/event_shit/events/events.php'],['organizers','Organizers','/event_shit/organizers/organizers.php'],['participants','Participants','/event_shit/participants/participants.php'],['registrations','Registrations','/event_shit/registrations/registrations.php']] as [$key, $label, $url]): ?>
            <a class="nav-link rounded <?= $currentPage === $key ? 'active' : '' ?>" href="<?= $url ?>"><?= $label ?></a>
        <?php endforeach; ?>
    </nav>
    <div class="border-top border-light border-opacity-25 mt-4 pt-3 text-white-50 small">
        Local administrator
    </div>
</aside>
