<?php
// ============================================================
//  classes/Grade.php
//
//  Handles database operations related to the grades table.
//  Keeps grade records scoped to the logged-in user.
// ============================================================

class Grade extends BaseModel {
    protected $table = 'grades';

    public function getByUserId($userId) {
        return $this->db
                    ->table($this->table)
                    ->select()
                    ->where('user_id', $userId)
                    ->orderBy('id', 'ASC')
                    ->get();
    }

    public function findByIdAndUserId($id, $userId) {
        return $this->db
                    ->table($this->table)
                    ->select()
                    ->where('id', $id)
                    ->where('user_id', $userId)
                    ->first();
    }

    public function create(array $data) {
        return $this->db->table($this->table)->insert($data);
    }

    public function updateForUser($id, $userId, array $data) {
        if (!$this->findByIdAndUserId($id, $userId)) {
            return false;
        }

        return $this->db->table($this->table)->update($data, $id);
    }

    public function deleteForUser($id, $userId) {
        if (!$this->findByIdAndUserId($id, $userId)) {
            return false;
        }

        return $this->delete($id);
    }
}