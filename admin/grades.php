<?php
require 'auth.php';
require_once '../config.php';

$gradeModel = new Grade($conn);
$userId = (int) $logged_in_user['id'];

$success_message = '';
$error_message = '';
$editing_grade = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save';
    $gradeId = isset($_POST['grade_id']) ? (int) $_POST['grade_id'] : 0;

    if ($action === 'delete') {
        if ($gradeId > 0 && $gradeModel->deleteForUser($gradeId, $userId)) {
            $_SESSION['flash'] = 'Grade record deleted.';
        } else {
            $_SESSION['flash'] = 'Unable to delete that grade record.';
        }

        header('Location: grades.php');
        exit;
    }

    $new_subject = trim($_POST['subject']);
    $new_prelim  = (int) $_POST['prelim'];
    $new_midterm = (int) $_POST['midterm'];
    $new_final   = (int) $_POST['final'];
    $new_grade   = round(($new_prelim + $new_midterm + $new_final) / 3);

    $payload = [
        'user_id' => $userId,
        'subject' => $new_subject,
        'prelim'  => $new_prelim,
        'midterm' => $new_midterm,
        'final'   => $new_final,
        'grade'   => $new_grade,
    ];

    if ($gradeId > 0) {
        $saved = $gradeModel->updateForUser($gradeId, $userId, $payload);
        $_SESSION['flash'] = $saved
            ? "Grade for \"$new_subject\" updated. Final grade: $new_grade"
            : 'Unable to update that grade record.';
    } else {
        $saved = $gradeModel->create($payload);
        $_SESSION['flash'] = $saved
            ? "Grade for \"$new_subject\" added. Final grade: $new_grade"
            : 'Unable to add the grade record.';
    }

    header('Location: grades.php');
    exit;
}

if (isset($_SESSION['flash'])) {
    $success_message = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

if (isset($_GET['edit'])) {
    $editing_grade = $gradeModel->findByIdAndUserId((int) $_GET['edit'], $userId);
}

$grades     = $gradeModel->getByUserId($userId);
$count      = count($grades);
$all_grades = array_column($grades, 'grade');
$avg_grade  = $count > 0 ? round(array_sum($all_grades) / $count, 1) : 0;
$highest    = $count > 0 ? max($all_grades) : 0;
$lowest     = $count > 0 ? min($all_grades) : 0;
$open_edit_modal = $editing_grade !== null;

$active_page = 'grades';
$page_title  = 'My Grades';
$page_icon   = '<i class="bi bi-trophy-fill"></i>';

include 'header.php';
?>

<main class="content">
<?php if ($success_message): ?>
<div class="alert-success">✅ <?= htmlspecialchars($success_message) ?></div>
<?php endif; ?>

<?php if ($error_message): ?>
<div class="alert-error"><?= htmlspecialchars($error_message) ?></div>
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

<div class="form-card" id="grade-form">
    <div class="form-card-header">
        <div class="form-card-title">Add Grade Record</div>
    </div>
    <div class="form-body">
        <p class="form-hint">Final Grade is auto-computed: (Prelim + Midterm + Final Exam) ÷ 3</p>
        <form method="POST" action="">
            <input type="hidden" name="action" value="save">
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
                <th>Actions</th>
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
                <td class="action-cell" style="display:flex; align-items:center; justify-content:center; gap:6px;">
                    <a href="grades.php?edit=<?= $g['id'] ?>" class="btn-submit" style="display:inline-flex; align-items:center; justify-content:center; margin-right:8px; text-decoration:none; padding:8px 12px;"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="" style="display:inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="grade_id" value="<?= $g['id'] ?>">
                        <button type="submit" class="btn-submit" style="padding:8px 12px; background:#8b2d2d;"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($editing_grade): ?>
<div class="modal-backdrop" id="grade-edit-modal" aria-hidden="false" style="position:fixed; inset:0; background:rgba(13, 17, 23, 0.72); backdrop-filter:blur(4px); display:flex; align-items:center; justify-content:center; padding:24px; z-index:200;">
    <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="edit-grade-title" style="width:min(760px, 100%); background:var(--surface); border:1px solid var(--border); border-radius:12px; box-shadow:0 24px 64px rgba(0, 0, 0, 0.45); overflow:hidden;">
        <div class="modal-header" style="padding:14px 18px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; background:rgba(88, 166, 255, 0.06);">
            <div class="modal-title" id="edit-grade-title">Edit Grade Record</div>
            <a href="grades.php" class="modal-close" aria-label="Close edit modal"><i class="bi bi-x-lg"></i></a>
        </div>
        <div class="modal-body" style="padding:20px;">
            <p class="form-hint">Final Grade is auto-computed: (Prelim + Midterm + Final Exam) ÷ 3</p>
            <form method="POST" action="">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="grade_id" value="<?= htmlspecialchars($editing_grade['id']) ?>">
                <div class="form-grid">
                    <div class="form-group" style="grid-column: span 2;">
                        <label for="edit-subject">Subject Name</label>
                        <input type="text" id="edit-subject" name="subject" placeholder="e.g. Statistics and Probability" required value="<?= htmlspecialchars($editing_grade['subject']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="edit-prelim">Prelim Score</label>
                        <input type="number" id="edit-prelim" name="prelim" min="0" max="100" placeholder="0 – 100" required value="<?= htmlspecialchars($editing_grade['prelim']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="edit-midterm">Midterm Score</label>
                        <input type="number" id="edit-midterm" name="midterm" min="0" max="100" placeholder="0 – 100" required value="<?= htmlspecialchars($editing_grade['midterm']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="edit-final">Final Exam Score</label>
                        <input type="number" id="edit-final" name="final" min="0" max="100" placeholder="0 – 100" required value="<?= htmlspecialchars($editing_grade['final']) ?>">
                    </div>
                </div>
                <div class="modal-actions" style="display:flex; align-items:center; justify-content:flex-end; gap:10px; margin-top:18px; flex-wrap:wrap;">
                    <a href="grades.php" class="btn-submit btn-secondary" style="display:inline-flex; align-items:center; justify-content:center; text-decoration:none;">Cancel</a>
                    <button type="submit" class="btn-submit"><i class="bi bi-pencil-square"></i> Update Grade Record</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
(() => {
    const modal = document.getElementById('grade-edit-modal');
    if (!modal) return;
    document.body.classList.add('modal-open');

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            window.location.href = 'grades.php';
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            window.location.href = 'grades.php';
        }
    });
})();
</script>

<?php include 'footer.php'; ?>
