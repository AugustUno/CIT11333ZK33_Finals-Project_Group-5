<?php
require 'auth.php';
// Make sure to include your class files if your app doesn't use an autoloader:
// require_once '../classes/BaseModel.php';
// require_once '../classes/Subjects.php';

// 1. Initialize the Session Seed Data if it doesn't exist
if (!isset($_SESSION['subjects'])) {
    $_SESSION['subjects'] = [
        ["id" => 1, "code" => "MATH101", "name" => "General Mathematics",    "teacher" => "Mr. Batumbakal",    "units" => 4, "schedule" => "MWF 7:30–8:30"],
        ["id" => 2, "code" => "ENG101",  "name" => "Oral Communication",     "teacher" => "Ms. Flores",        "units" => 2, "schedule" => "TTH 9:00–10:00"],
        ["id" => 3, "code" => "SCI101",  "name" => "Earth and Life Science", "teacher" => "Ms. Lim",           "units" => 4, "schedule" => "MWF 10:00–11:00"],
        ["id" => 4, "code" => "FIL101",  "name" => "Komunikasyon",           "teacher" => "Mr. Ramos",         "units" => 2, "schedule" => "TTH 1:00–2:00"],
        ["id" => 5, "code" => "PE101",   "name" => "Physical Education",     "teacher" => "Coach Delos Reyes", "units" => 2, "schedule" => "WF 2:00–3:00"],
        ["id" => 6, "code" => "HIST101", "name" => "Philippine History",     "teacher" => "Ms. Bautista",      "units" => 3, "schedule" => "MWF 1:00–2:00"],
    ];
}

// 2. Instantiate your helper class
$subjectManager = new Subjects();
$success_message = '';

// --- EDIT ---
$edit_subject = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_subject = $subjectManager->findById($edit_id);
}

// --- DELETE ---
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Use the class method to delete!
    if ($subjectManager->delete($id)) {
        $_SESSION['flash'] = "Subject deleted successfully.";
    }
    
    header("Location: subjects.php");
    exit;
}

// --- ADD ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pass the entire $_POST array safely into your class method
    $newSubject = $subjectManager->create($_POST);

    $_SESSION['flash'] = '"' . $newSubject['name'] . '" has been added to your subjects.';
    header('Location: subjects.php');
    exit;
}

if (isset($_SESSION['flash'])) {
    $success_message = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

// 3. Collect statistics using your class metrics
$subjects       = $subjectManager->getAll();
$total_subjects = $subjectManager->count();
$total_units    = $subjectManager->totalUnits();

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
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
<?php include 'footer.php'; ?>


