<?php
/**
 * ASTPMS — Application Model
 * Manages job applications submitted by students
 */

class Application {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Submit a job application
     */
    public function apply(int $studentId, int $jobId, ?string $resumePath = null, ?string $coverLetter = null): bool|string {
        if ($this->hasApplied($studentId, $jobId)) {
            return 'You have already applied for this job.';
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO applications (student_id, job_id, status)
                VALUES (?, ?, 'Applied')
            ");
            $stmt->execute([$studentId, $jobId]);
            return true;
        } catch (PDOException $e) {
            error_log('Apply error: ' . $e->getMessage());
            return 'Database error while submitting application.';
        }
    }

    /**
     * Check if a student has already applied for a job
     */
    public function hasApplied(int $studentId, int $jobId): bool {
        $stmt = $this->db->prepare("SELECT id FROM applications WHERE student_id = ? AND job_id = ?");
        $stmt->execute([$studentId, $jobId]);
        return (bool)$stmt->fetch();
    }

    /**
     * Get all applications for a student
     */
    public function getByStudent(int $studentId): array {
        $stmt = $this->db->prepare("
            SELECT a.*, j.role, j.job_type, j.location, j.package_lpa, c.name AS company_name
            FROM applications a
            JOIN jobs j ON a.job_id = j.id
            JOIN companies c ON j.company_id = c.id
            WHERE a.student_id = ?
            ORDER BY a.applied_at DESC
        ");
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    /**
     * Get application details by ID
     */
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT a.*, j.role, c.name AS company_name, s.name AS student_name, s.email AS student_email
            FROM applications a
            JOIN jobs j ON a.job_id = j.id
            JOIN companies c ON j.company_id = c.id
            JOIN students s ON a.student_id = s.id
            WHERE a.id = ?
        ");
        $stmt->execute([$id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }
}
