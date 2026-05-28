<?php
// ============================================================
//   classes/Subjects.php
// ============================================================

class Subjects extends BaseModel
{
    protected $table = 'subjects';

    public function getAll($userId = null)
    {
        if ($userId === null) {
            return parent::getAll();
        }

        return $this->getByUserId($userId);
    }

    public function getByUserId($userId)
    {
        return $this->db
                    ->table($this->table)
                    ->select()
                    ->where('user_id', $userId)
                    ->orderBy('id', 'ASC')
                    ->get();
    }

    public function findById($id, $userId = null)
    {
        if ($userId === null) {
            return parent::find($id);
        }

        return $this->findByIdAndUserId($id, $userId);
    }

    public function findByIdAndUserId($id, $userId)
    {
        return $this->db
                    ->table($this->table)
                    ->select()
                    ->where('id', $id)
                    ->where('user_id', $userId)
                    ->first();
    }

    public function create(array $data)
    {
        $newSubject = [
            'user_id'  => (int) $data['user_id'],
            'code'     => strtoupper(trim($data['code'])),
            'name'     => trim($data['name']),
            'teacher'  => trim($data['teacher']),
            'units'    => (int) $data['units'],
            'schedule' => trim($data['schedule']),
        ];

        if (!$this->db->table($this->table)->insert($newSubject)) {
            return false;
        }

        $newSubject['id'] = (int) $this->db->lastInsertId();
        return $newSubject;
    }

    // Update subject by ID
    public function update($id, array $data)
    {
        $updatedSubject = [
            'code'     => strtoupper(trim($data['code'])),
            'name'     => trim($data['name']),
            'teacher'  => trim($data['teacher']),
            'units'    => (int) $data['units'],
            'schedule' => trim($data['schedule']),
        ];

        if (!$this->db->table($this->table)->update($updatedSubject, $id)) {
            return false;
        }

        $updatedSubject['id'] = $id;
        return $updatedSubject;
    }

    public function updateForUser($id, $userId, array $data)
    {
        if (!$this->findByIdAndUserId($id, $userId)) {
            return false;
        }

        return $this->update($id, $data);
    }

    // Delete subject
    public function deleteForUser($id, $userId)
    {
        if (!$this->findByIdAndUserId($id, $userId)) {
            return false;
        }

        return $this->delete($id);
    }

    // Count subjects
    public function count($userId = null)
    {
        return count($this->getAll($userId));
    }

    // Total units
    public function totalUnits($userId = null)
    {
        return array_sum(array_column($this->getAll($userId), 'units'));
    }
}