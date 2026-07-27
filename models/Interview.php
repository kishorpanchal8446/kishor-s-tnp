<?php
/**
 * ASTPMS — Interview Model
 * Handles interview schedules for students
 */

class Interview {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Get all interviews for a student
     */
    public function getByStudent(int $studentId): array {
        $stmt = $this->db->prepare("
            SELECT i.*, i.round_type AS round_name, COALESCE(i.join_url, i.venue) AS location_link, j.role, c.name AS company_name
            FROM interviews i
            JOIN applications a ON i.application_id = a.id
            JOIN jobs j ON a.job_id = j.id
            JOIN companies c ON j.company_id = c.id
            WHERE a.student_id = ?
            ORDER BY i.scheduled_date ASC
        ");
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }
}
