<?php
require 'auth.php';
require_once '../config.php';

$subjectManager = new Subjects($conn);
$userId = (int) $logged_in_user['id'];
$success_message = '';

// --- EDIT ---
$edit_subject = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_subject = $subjectManager->findByIdAndUserId($edit_id, $userId);
}

// --- DELETE ---
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    if ($subjectManager->deleteForUser($id, $userId)) {
        $_SESSION['flash'] = "Subject deleted successfully.";
    }
    
    header("Location: subjects.php");
    exit;
}

// --- ADD / UPDATE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';

    if ($action === 'update') {
        $edit_id = (int) ($_POST['subject_id'] ?? 0);
        $updatedSubject = $subjectManager->updateForUser($edit_id, $userId, $_POST);

        $_SESSION['flash'] = $updatedSubject
            ? '"' . $updatedSubject['name'] . '" has been updated.'
            : 'Unable to update that subject.';

        header('Location: subjects.php');
        exit;
    }

    $payload = $_POST;
    $payload['user_id'] = $userId;
    $newSubject = $subjectManager->create($payload);

    $_SESSION['flash'] = $newSubject
        ? '"' . $newSubject['name'] . '" has been added to your subjects.'
        : 'Unable to add that subject.';
    header('Location: subjects.php');
    exit;
}

if (isset($_SESSION['flash'])) {
    $success_message = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

// 3. Collect statistics using your class metrics
$subjects       = $subjectManager->getByUserId($userId);
$total_subjects = $subjectManager->count($userId);
$total_units    = $subjectManager->totalUnits($userId);

$active_page = 'subjects';
$page_title  = 'Subjects';
$page_icon   = '<i class="bi bi-journal-text"></i>';
include 'header.php';
?>
<main class="content">
    <?php if ($success_message): ?>
        <div class="alert-success">✅ <?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>

    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-label">Total Subjects</div>
            <div class="stat-value blue"><?= $total_subjects ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Units</div>
            <div class="stat-value green"><?= $total_units ?></div>
        </div>
    </div>

    <div class="form-card">
        <div class="form-card-header">
            <div class="form-card-title">Add New Subject</div>
        </div>
        <div class="form-body">
            <form method="POST" action="">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="code">Subject Code</label>
                        <input type="text" id="code" name="code" placeholder="e.g. MATH102" required maxlength="10">
                    </div>
                    <div class="form-group">
                        <label for="name">Subject Name</label>
                        <input type="text" id="name" name="name" placeholder="e.g. Statistics and Probability" required>
                    </div>
                    <div class="form-group">
                        <label for="teacher">Teacher</label>
                        <input type="text" id="teacher" name="teacher" placeholder="e.g. Ms. Cruz" required>
                    </div>
                    <div class="form-group">
                        <label for="units">Units</label>
                        <select id="units" name="units" required>
                            <option value="">— Select —</option>
                            <option value="1">1 unit</option>
                            <option value="2">2 units</option>
                            <option value="3">3 units</option>
                            <option value="4">4 units</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="schedule">Schedule</label>
                        <input type="text" id="schedule" name="schedule" placeholder="e.g. MWF 7:30–8:30" required>
                    </div>
                </div>
                <button type="submit" class="btn-submit"><i class="bi bi-plus-square"></i> Add Subject</button>
            </form>
        </div>
    </div>

    <div class="table-card">
        <div class="table-card-header">
            <div class="table-card-title">Enrolled Subjects</div>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Code</th>
                    <th>Subject Name</th>
                    <th>Teacher</th>
                    <th>Units</th>
                    <th>Schedule</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($total_subjects === 0): ?>
                <tr>
                    <td colspan="7" style="text-align:center; padding:24px; color:var(--text-muted);">
                        No subjects yet. Use the form above to add one.
                    </td>
                </tr>
                <?php endif; ?>

                <?php foreach ($subjects as $i => $subject): ?>
                <tr>
                    <td class="id-cell"><?= $i + 1 ?></td>
                    <td class="code-cell"><?= htmlspecialchars($subject['code']) ?></td>
                    <td><?= htmlspecialchars($subject['name']) ?></td>
                    <td><?= htmlspecialchars($subject['teacher']) ?></td>
                    <td class="id-cell"><?= $subject['units'] ?> units</td>
                    <td class="schedule-tag"><?= htmlspecialchars($subject['schedule']) ?></td>
                    <td class="action-cell">
                        <a href="subjects.php?edit=<?= $subject['id'] ?>" class="btn-submit" style="display:inline-flex; align-items:center; justify-content:center; margin-right:8px; text-decoration:none; padding:8px 12px;"><i class="bi bi-pencil"></i></a>
                        <a href="subjects.php?delete=<?= $subject['id'] ?>" class="btn-submit" style="display:inline-flex; align-items:center; justify-content:center; text-decoration:none; padding:8px 12px; background:#8b2d2d;"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($edit_subject): ?>
    <div class="modal-backdrop" id="subject-edit-modal" aria-hidden="false">
        <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="edit-subject-title">
            <div class="modal-header">
                <div class="modal-title" id="edit-subject-title">Edit Subject</div>
                <a href="subjects.php" class="modal-close" aria-label="Close edit modal"><i class="bi bi-x-lg"></i></a>
            </div>
            <div class="modal-body">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="subject_id" value="<?= htmlspecialchars($edit_subject['id']) ?>">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="edit-code">Subject Code</label>
                            <input type="text" id="edit-code" name="code" placeholder="e.g. MATH102" required maxlength="10" value="<?= htmlspecialchars($edit_subject['code']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="edit-name">Subject Name</label>
                            <input type="text" id="edit-name" name="name" placeholder="e.g. Statistics and Probability" required value="<?= htmlspecialchars($edit_subject['name']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="edit-teacher">Teacher</label>
                            <input type="text" id="edit-teacher" name="teacher" placeholder="e.g. Ms. Cruz" required value="<?= htmlspecialchars($edit_subject['teacher']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="edit-units">Units</label>
                            <select id="edit-units" name="units" required>
                                <option value="">— Select —</option>
                                <option value="1" <?= (int) $edit_subject['units'] === 1 ? 'selected' : '' ?>>1 unit</option>
                                <option value="2" <?= (int) $edit_subject['units'] === 2 ? 'selected' : '' ?>>2 units</option>
                                <option value="3" <?= (int) $edit_subject['units'] === 3 ? 'selected' : '' ?>>3 units</option>
                                <option value="4" <?= (int) $edit_subject['units'] === 4 ? 'selected' : '' ?>>4 units</option>
                            </select>
                        </div>
                        <div class="form-group" style="grid-column: span 2;">
                            <label for="edit-schedule">Schedule</label>
                            <input type="text" id="edit-schedule" name="schedule" placeholder="e.g. MWF 7:30–8:30" required value="<?= htmlspecialchars($edit_subject['schedule']) ?>">
                        </div>
                    </div>
                    <div class="modal-actions">
                        <a href="subjects.php" class="btn-submit btn-secondary" style="display:inline-flex; align-items:center; justify-content:center; text-decoration:none;">Cancel</a>
                        <button type="submit" class="btn-submit"><i class="bi bi-pencil-square"></i> Update Subject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>
    (() => {
        const modal = document.getElementById('subject-edit-modal');
        if (!modal) return;
        document.body.classList.add('modal-open');

        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                window.location.href = 'subjects.php';
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                window.location.href = 'subjects.php';
            }
        });
    })();
    </script>
</main>
<?php include 'footer.php'; ?>


