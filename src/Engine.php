<?php
// src/Engine.php
require_once 'Database.php';

class Engine {
    private $db;
    const COOLDOWN_MINUTES = 30;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ============================================================
    // 1. THE TRI-FACTOR PREDICTION ALGORITHM
    // ============================================================
    public function getPrediction($userId, $deptId) {
        // A. GET STUDENT RANK
        $stmt = $this->db->prepare("SELECT admission_rank FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        $rank = $stmt->fetchColumn();

        if (!$rank) return ['color' => 'gray', 'msg' => 'Rank not found.'];

        // B. GET DEPARTMENT FACTORS (Historical & Capacity)
        $sql = "SELECT d.base_capacity, d.current_locked, 
                       p.historical_cutoff_rank, p.implicit_demand_factor
                FROM departments d
                LEFT JOIN prediction_stats p ON d.dept_id = p.dept_id
                WHERE d.dept_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$deptId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return ['color' => 'gray', 'msg' => 'Department data missing.'];

        // --- THE MATH ---
        
        // Factor 1: Effective Cutoff (Historical * Demand)
        // If demand is 1.1 (10% higher), the cutoff rank gets TIGHTER (lower number)
        // Actually, usually higher demand means harder to get in (lower rank number needed).
        // Let's assume a simpler logic: Estimated Rank Needed = Historical / Demand Factor.
        $estimatedCutoff = $data['historical_cutoff_rank'] / $data['implicit_demand_factor'];

        // Factor 2: Live Capacity Pressure
        $percentFull = ($data['current_locked'] / $data['base_capacity']) * 100;

        // --- THE DECISION TREE ---

        // RED CONDITION: Seat overflow OR Rank is way bad
        if ($percentFull >= 100) {
            return ['color' => 'red', 'msg' => 'High Risk: Department Full.'];
        }
        if ($rank > ($estimatedCutoff * 1.15)) { // 15% buffer
            return ['color' => 'red', 'msg' => 'High Risk: Rank is too low based on history.'];
        }

        // YELLOW CONDITION: Getting full OR Borderline rank
        if ($percentFull >= 90) {
            return ['color' => 'yellow', 'msg' => 'Caution: Seats filling up fast (90%).'];
        }
        if ($rank > $estimatedCutoff) {
            return ['color' => 'yellow', 'msg' => 'Moderate Risk: You are slightly above predicted cutoff.'];
        }

        // GREEN CONDITION
        return ['color' => 'green', 'msg' => 'Safe: Good chance of admission.'];
    }

    // ============================================================
    // 2. THE ALLOCATION & COOLDOWN LOGIC
    // ============================================================
    public function lockChoice($userId, $deptId, $roundId) {
        // A. Check if user already has an allocation
        $sql = "SELECT allocation_id, dept_id, locked_at FROM allocations WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        // B. COOLDOWN CHECK
        if ($existing) {
            $lastLocked = new DateTime($existing['locked_at']);
            $now = new DateTime();
            $diffMinutes = ($now->getTimestamp() - $lastLocked->getTimestamp()) / 60;

            if ($diffMinutes < self::COOLDOWN_MINUTES) {
                $wait = ceil(self::COOLDOWN_MINUTES - $diffMinutes);
                return ['success' => false, 'msg' => "Cooldown Active! Wait $wait minutes."];
            }
            
            // If cooldown passed, UPDATE choice
            // 1. Decrement old dept count
            $this->updateDeptCount($existing['dept_id'], -1);
            
            // 2. Update allocation
            $upd = $this->db->prepare("UPDATE allocations SET dept_id = ?, locked_at = NOW() WHERE user_id = ?");
            $upd->execute([$deptId, $userId]);

            // 3. Increment new dept count
            $this->updateDeptCount($deptId, 1);
            
            return ['success' => true, 'msg' => 'Choice Updated & Locked!'];
        }

        // C. FIRST TIME LOCK (Insert)
        $ins = $this->db->prepare("INSERT INTO allocations (user_id, dept_id, round_id) VALUES (?, ?, ?)");
        if ($ins->execute([$userId, $deptId, $roundId])) {
            $this->updateDeptCount($deptId, 1);
            return ['success' => true, 'msg' => 'Choice Locked Successfully!'];
        }

        return ['success' => false, 'msg' => 'Database Error.'];
    }

    // Helper to keep the 'current_locked' column fast and accurate
    private function updateDeptCount($deptId, $change) {
        $sql = "UPDATE departments SET current_locked = current_locked + ? WHERE dept_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$change, $deptId]);
    }
}