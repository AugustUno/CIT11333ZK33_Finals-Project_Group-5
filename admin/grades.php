<?php
require 'auth.php';   // blocks access if not logged in

if (!isset($_SESSION['grades'])) {
    $_SESSION['grades'] = [
        ["id" => 1, "subject" => "General Mathematics",    "prelim" => 88, "midterm" => 91, "final" => 90, "grade" => 90],
        ["id" => 2, "subject" => "Oral Communication",     "prelim" => 92, "midterm" => 89, "final" => 94, "grade" => 92],
        ["id" => 3, "subject" => "Earth and Life Science", "prelim" => 85, "midterm" => 87, "final" => 88, "grade" => 87],
        ["id" => 4, "subject" => "Komunikasyon",           "prelim" => 90, "midterm" => 92, "final" => 91, "grade" => 91],
        ["id" => 5, "subject" => "Physical Education",     "prelim" => 95, "midterm" => 97, "final" => 96, "grade" => 96],
        ["id" => 6, "subject" => "Philippine History",     "prelim" => 82, "midterm" => 85, "final" => 84, "grade" => 84],
    ];
}

$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- ADD ---
    $new_subject = trim($_POST['subject']);
    $new_prelim  = (int) $_POST['prelim'];
    $new_midterm = (int) $_POST['midterm'];
    $new_final   = (int) $_POST['final'];
    $new_grade   = round(($new_prelim + $new_midterm + $new_final) / 3);

    $last_id = count($_SESSION['grades']) > 0
               ? max(array_column($_SESSION['grades'], 'id'))
               : 0;

    $_SESSION['grades'][] = [
        "id"      => $last_id + 1,
        "subject" => $new_subject,
        "prelim"  => $new_prelim,
        "midterm" => $new_midterm,
        "final"   => $new_final,
        "grade"   => $new_grade,
    ];

    // PRG: redirect after POST to prevent resubmission on reload
    $_SESSION['flash'] = "Grade for \"$new_subject\" added. Final grade: $new_grade";
    header('Location: grades.php');
    exit;
}

if (isset($_SESSION['flash'])) {
    $success_message = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

$grades     = $_SESSION['grades'];
$count      = count($grades);
$all_grades = array_column($grades, 'grade');
$avg_grade  = $count > 0 ? round(array_sum($all_grades) / $count, 1) : 0;
$highest    = $count > 0 ? max($all_grades) : 0;
$lowest     = $count > 0 ? min($all_grades) : 0;

$active_page = 'grades';
$page_title  = 'My Grades';
$page_icon   = '<i class="bi bi-trophy-fill"></i>';

include 'header.php';
?>

<?php if ($success_message): ?>
<div class="alert-success">✅ <?= htmlspecialchars($success_message) ?></div>
<?php endif; ?>

<div class="stats-row">
    <div class="stat-card">
        <div class="stat-label">Avg Grade</div>
        <div class="stat-value blue"><?= $avg_grade ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Highest</div>
        <div class="stat-value green"><?= $highest ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Lowest</div>
        <div class="stat-value red"><?= $lowest ?></div>
    </div>
</div>

<div class="form-card">
    <div class="form-card-header">
        <div class="form-card-title">Add Grade Record</div>
    </div>
    <div class="form-body">
        <p class="form-hint">Final Grade is auto-computed: (Prelim + Midterm + Final Exam) ÷ 3</p>
        <form method="POST" action="">
            <div class="form-grid">
                <div class="form-group" style="grid-column: span 2;">
                    <label for="subject">Subject Name</label>
                    <input type="text" id="subject" name="subject" placeholder="e.g. Statistics and Probability" required>
                </div>
                <div class="form-group">
                    <label for="prelim">Prelim Score</label>
                    <input type="number" id="prelim" name="prelim" min="0" max="100" placeholder="0 – 100" required>
                </div>
                <div class="form-group">
                    <label for="midterm">Midterm Score</label>
                    <input type="number" id="midterm" name="midterm" min="0" max="100" placeholder="0 – 100" required>
                </div>
                <div class="form-group">
                    <label for="final">Final Exam Score</label>
                    <input type="number" id="final" name="final" min="0" max="100" placeholder="0 – 100" required>
                </div>
            </div>
            <button type="submit" class="btn-submit"><i class="bi bi-plus-square"></i> Add Grade Record</button>
        </form>
    </div>
</div>

<div class="table-card">
    <div class="table-card-header">
        <div class="table-card-title">Grade Report – 1st Semester</div>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Subject</th>
                <th>Prelim</th>
                <th>Midterm</th>
                <th>Final Exam</th>
                <th>Final Grade</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($count === 0): ?>
            <tr>
                <td colspan="8" style="text-align:center; padding:24px; color:var(--text-muted);">
                    No grades yet. Use the form above to add one.
                </td>
            </tr>
            <?php endif; ?>

            <?php foreach ($grades as $i => $g): ?>
            <tr>
                <td class="id-cell"><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($g['subject']) ?></td>
                <td class="id-cell"><?= $g['prelim'] ?></td>
                <td class="id-cell"><?= $g['midterm'] ?></td>
                <td class="id-cell"><?= $g['final'] ?></td>
                <td>
                    <?php
                    $fg = $g['grade'];
                    $gc = $fg >= 90 ? 'grade-high' : ($fg >= 85 ? 'grade-mid' : 'grade-low');
                    ?>
                    <span class="<?= $gc ?>"><?= $fg ?></span>
                </td>
                <td>
                    <span class="badge <?= $fg >= 75 ? 'badge-active' : 'badge-probation' ?>">
                        <?= $fg >= 75 ? 'Passed' : 'Failed' ?>
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>
