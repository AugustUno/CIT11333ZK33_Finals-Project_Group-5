<?php
// ============================================================
//   classes/Subjects.php
// ============================================================

class Subjects extends BaseModel
{
    protected $table = 'subjects';

    // Get all subjects
    public function getAll()
    {
        return $_SESSION['subjects'] ?? [];
    }

    // Get subject by ID
    public function findById($id)
    {
        $subjects = $this->getAll();
        foreach ($subjects as $subject) {
            if ($subject['id'] == $id) {
                return $subject;
            }
        }
        return null;
    }

    // Create / Add new subject
    public function create(array $data)
    {
        if (!isset($_SESSION['subjects'])) {
            $_SESSION['subjects'] = [];
        }

        $subjects = $_SESSION['subjects'];

        $last_id = count($subjects) > 0
            ? max(array_column($subjects, 'id'))
            : 0;

        $newSubject = [
            "id"       => $last_id + 1,
            "code"     => strtoupper(trim($data['code'])),
            "name"     => trim($data['name']),
            "teacher"  => trim($data['teacher']),
            "units"    => (int)$data['units'],
            "schedule" => trim($data['schedule']),
        ];

        $_SESSION['subjects'][] = $newSubject;
        return $newSubject;
    }

    // Delete subject (FIXED: array_values moved outside loop)
    public function delete($id)
    {
        if (!isset($_SESSION['subjects'])) return false;

        $deleted = false;
        foreach ($_SESSION['subjects'] as $index => $subject) {
            if ($subject['id'] == $id) {
                unset($_SESSION['subjects'][$index]);
                $deleted = true;
                break;
            }
        }

        if ($deleted) {
            $_SESSION['subjects'] = array_values($_SESSION['subjects']);
            return true;
        }

        return false;
    }

    // Count subjects
    public function count()
    {
        return count($this->getAll());
    }

    // Total units
    public function totalUnits()
    {
        return array_sum(array_column($this->getAll(), 'units'));
    }
}