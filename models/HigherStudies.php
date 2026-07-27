<?php
/**
 * ASTPMS — Higher Studies Model
 * Manages GATE, GRE, CAT, MS, M.Tech higher studies resources & registrations
 */

class HigherStudies {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getAll(): array {
        return $this->db->query("SELECT * FROM higher_studies ORDER BY created_at DESC")->fetchAll();
    }

    public function isEnrolled(int $studentId, int $entry_id): bool {
        $stmt = $this->db->prepare("SELECT id FROM higher_studies_enrollments WHERE student_id = ? AND higher_study_id = ?");
        $stmt->execute([$studentId, $entry_id]);
        return (bool)$stmt->fetch();
    }

    public function enroll(int $studentId, int $entry_id): bool|string {
        if ($this->isEnrolled($studentId, $entry_id)) return 'Already registered for this exam/program.';
        try {
            $stmt = $this->db->prepare("INSERT INTO higher_studies_enrollments (student_id, higher_study_id) VALUES (?, ?)");
            $stmt->execute([$studentId, $entry_id]);
            return true;
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }
}
