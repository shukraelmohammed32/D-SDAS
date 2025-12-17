<?php
// src/Admin.php
require_once 'Database.php';

class Admin {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

   
    public function getAllRounds() {
        $stmt = $this->db->query("SELECT * FROM admission_rounds ORDER BY start_time DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
 
    public function createRound($name, $start, $end) {
        $sql = "INSERT INTO admission_rounds (round_name, start_time, end_time) 
                VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$name, $start, $end]);
    }


    // Changes the status of an admission round (e.g., scheduled -> active).

    public function updateRoundStatus($roundId, $status) {
        $sql = "UPDATE admission_rounds SET status = ? WHERE round_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $roundId]);
    }


    // Finalizes the allocations for a SEALED round.
    // This is the CORE ALLOCATION STEP: sets is_finalized=TRUE.

    public function finalizeAllocations($roundId) {
        // 1. Mark all allocations for this round as FINALIZED
        $sql = "UPDATE allocations 
                SET is_finalized = TRUE 
                WHERE round_id = ? AND is_finalized = FALSE";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([$roundId]);

        if ($result) {
            // 2. Change the round status to 'completed'
            $this->updateRoundStatus($roundId, 'completed');
            return ['success' => true, 'msg' => 'Allocations finalized and round completed.'];
        }
        return ['success' => false, 'msg' => 'Failed to finalize allocations.'];
    }


    //Gets a summary of current allocation stats by department.

    public function getAllocationSummary() {
        $sql = "SELECT d.dept_name, d.base_capacity, d.current_locked,
                       p.historical_cutoff_rank
                FROM departments d
                LEFT JOIN prediction_stats p ON d.dept_id = p.dept_id
                ORDER BY d.dept_name";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}