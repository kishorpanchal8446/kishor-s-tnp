<?php
/**
 * ASTPMS — Training Model
 * Manages skill training programs, enrollments, and attendance
 */

class Training {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Get upcoming training programs
     */
    public function getUpcoming(int $limit = 6): array {
        $stmt = $this->db->prepare("
            SELECT *, name AS title, trainer AS trainer_name
            FROM training_programs
            WHERE status IN ('Upcoming', 'Ongoing')
            ORDER BY start_date ASC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get all training programs
     */
    public function getAll(): array {
        return $this->db->query("
            SELECT *, name AS title, trainer AS trainer_name
            FROM training_programs
            ORDER BY start_date DESC
        ")->fetchAll();
    }

    /**
     * Get single training program by ID
     */
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT *, name AS title, trainer AS trainer_name FROM training_programs WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Check if a student is enrolled in a training program
     */
    public function isEnrolled(int $studentId, int $trainingId): bool {
        $stmt = $this->db->prepare("SELECT id FROM training_registrations WHERE student_id = ? AND training_id = ?");
        $stmt->execute([$studentId, $trainingId]);
        return (bool)$stmt->fetch();
    }

    /**
     * Enroll a student in a training program
     */
    public function enroll(int $studentId, int $trainingId): bool|string {
        if ($this->isEnrolled($studentId, $trainingId)) {
            return 'You are already enrolled in this training program.';
        }
        try {
            $stmt = $this->db->prepare("
                INSERT INTO training_registrations (student_id, training_id, status)
                VALUES (?, ?, 'Registered')
            ");
            $stmt->execute([$studentId, $trainingId]);
            return true;
        } catch (PDOException $e) {
            return 'Failed to enroll: ' . $e->getMessage();
        }
    }

    /**
     * Get registrations for a student
     */
    public function getByStudent(int $studentId): array {
        $stmt = $this->db->prepare("
            SELECT tr.*, tp.name AS title, tp.trainer AS trainer_name, tp.start_date, tp.end_date, tp.venue, tp.status AS program_status
            FROM training_registrations tr
            JOIN training_programs tp ON tr.training_id = tp.id
            WHERE tr.student_id = ?
            ORDER BY tp.start_date ASC
        ");
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }
}
