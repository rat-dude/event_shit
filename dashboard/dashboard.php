<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();

$pageTitle = 'Dashboard';
$counts = [];

foreach (
    [
        'events' => 'SELECT COUNT(*) FROM events',
        'categories' => 'SELECT COUNT(*) FROM categories',
        'organizers' => 'SELECT COUNT(*) FROM organizers',
        'participants' => 'SELECT COUNT(*) FROM participants',
        'registrations' => 'SELECT COUNT(*) FROM registrations'
    ] as $key => $query
) {
    $counts[$key] = (int) $pdo->query($query)->fetchColumn();
}

$counts['upcoming'] = (int) $pdo->query(
    "SELECT COUNT(*) FROM events 
     WHERE event_date >= CURRENT_DATE 
     AND status <> 'Cancelled'"
)->fetchColumn();

$counts['completed'] = (int) $pdo->query(
    "SELECT COUNT(*) FROM events 
     WHERE status = 'Completed'"
)->fetchColumn();

$upcoming = $pdo->query(
    "SELECT 
        e.event_name, 
        e.event_date, 
        e.location, 
        e.status, 
        c.category_name, 
        o.organizer_name 
     FROM events e 
     LEFT JOIN categories c ON c.category_id = e.category_id 
     LEFT JOIN organizers o ON o.organizer_id = e.organizer_id 
     WHERE e.event_date >= CURRENT_DATE 
     ORDER BY e.event_date 
     LIMIT 8"
)->fetchAll();

$recent = $pdo->query(
    "SELECT 
        CONCAT(p.first_name, ' ', p.last_name) AS participant_name, 
        e.event_name, 
        r.registration_date, 
        r.status 
     FROM registrations r 
     JOIN participants p ON p.participant_id = r.participant_id 
     JOIN events e ON e.event_id = r.event_id 
     ORDER BY r.created_at DESC 
     LIMIT 8"
)->fetchAll();

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
?>

<main class="col-md-9 col-lg-10 p-4">

    <?php require __DIR__ . '/../includes/alerts.php'; ?>

    <!-- Dashboard Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h1 class="h3 mb-1">Dashboard</h1>
            <p class="text-secondary mb-0">
                A live view of your event workspace.
            </p>
        </div>

        <!-- Dashboard Buttons -->
        <div class="d-flex gap-2">

            <a class="btn btn-primary"
               href="/event_shit/events/events.php">
                Create event
            </a>

            <a class="btn btn-danger"
               href="/event_shit/auth/logout.php">
                Logout
            </a>

        </div>

    </div>

    <!-- Statistics -->
    <div class="row g-3 mb-4">

        <?php
        foreach (
            [
                ['events', 'Total events'],
                ['upcoming', 'Upcoming'],
                ['completed', 'Completed'],
                ['categories', 'Categories'],
                ['organizers', 'Organizers'],
                ['participants', 'Participants'],
                ['registrations', 'Registrations']
            ] as [$key, $label]
        ):
        ?>

            <div class="col-6 col-xl">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body">

                        <div class="text-secondary small">
                            <?= $label ?>
                        </div>

                        <div class="display-6 fw-semibold">
                            <?= $counts[$key] ?>
                        </div>

                    </div>
                </div>
            </div>

        <?php endforeach; ?>

    </div>

    <!-- Tables -->
    <div class="row g-4">

        <!-- Upcoming Events -->
        <div class="col-xl-7">

            <section class="card shadow-sm border-0">

                <div class="card-header bg-white py-3">
                    <h2 class="h5 mb-0">
                        Upcoming events
                    </h2>
                </div>

                <div class="table-responsive">

                    <table class="table align-middle mb-0">

                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Category</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($upcoming as $event): ?>

                                <tr>

                                    <td>
                                        <strong>
                                            <?= e($event['event_name']) ?>
                                        </strong>

                                        <div class="small text-secondary">
                                            <?= e($event['location']) ?>
                                        </div>
                                    </td>

                                    <td>
                                        <?= e($event['category_name']) ?>
                                    </td>

                                    <td>
                                        <?= e($event['event_date']) ?>
                                    </td>

                                    <td>
                                        <span class="badge text-bg-light">
                                            <?= e($event['status']) ?>
                                        </span>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                            <?php if (!$upcoming): ?>

                                <tr>
                                    <td colspan="4"
                                        class="text-center text-secondary py-4">
                                        No upcoming events.
                                    </td>
                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </section>

        </div>

        <!-- Recent Registrations -->
        <div class="col-xl-5">

            <section class="card shadow-sm border-0">

                <div class="card-header bg-white py-3">
                    <h2 class="h5 mb-0">
                        Recent registrations
                    </h2>
                </div>

                <div class="table-responsive">

                    <table class="table align-middle mb-0">

                        <thead>
                            <tr>
                                <th>Participant</th>
                                <th>Event</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($recent as $registration): ?>

                                <tr>

                                    <td>
                                        <?= e($registration['participant_name']) ?>
                                    </td>

                                    <td>
                                        <?= e($registration['event_name']) ?>

                                        <div class="small text-secondary">
                                            <?= e($registration['registration_date']) ?>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="badge text-bg-light">
                                            <?= e($registration['status']) ?>
                                        </span>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                            <?php if (!$recent): ?>

                                <tr>
                                    <td colspan="3"
                                        class="text-center text-secondary py-4">
                                        No registrations yet.
                                    </td>
                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </section>

        </div>

    </div>

</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>