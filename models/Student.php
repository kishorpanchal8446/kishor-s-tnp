<?php
/**
 * ASTPMS — Student Model
 */

class Student {
    private PDO $db;

    public function __construct(PDO $dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Get student details by ID
     */
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM students WHERE id = ?");
        $stmt->execute([$id]);
        $student = $stmt->fetch();
        return $student ?: null;
    }

    /**
     * Get student skills list
     */
    public function getSkills(int $studentId): array {
        $stmt = $this->db->prepare("SELECT * FROM student_skills WHERE student_id = ? ORDER BY skill_name ASC");
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    /**
     * Get placement stats summary for a student
     */
    public function getPlacementStats(int $studentId): array {
        $stmtApps = $this->db->prepare("SELECT COUNT(*) FROM applications WHERE student_id = ?");
        $stmtApps->execute([$studentId]);
        $totalApps = (int)$stmtApps->fetchColumn();

        $stmtSelected = $this->db->prepare("SELECT COUNT(*) FROM applications WHERE student_id = ? AND status = 'Selected'");
        $stmtSelected->execute([$studentId]);
        $totalSelected = (int)$stmtSelected->fetchColumn();

        $stmtInt = $this->db->prepare("
            SELECT COUNT(*) FROM interviews i
            JOIN applications a ON i.application_id = a.id
            WHERE a.student_id = ?
        ");
        $stmtInt->execute([$studentId]);
        $totalInterviews = (int)$stmtInt->fetchColumn();

        return [
            'total_applications' => $totalApps,
            'total_selected'     => $totalSelected,
            'total_interviews'   => $totalInterviews,
        ];
    }

    /**
     * Calculate dynamic profile completion percentage
     */
    public function getProfileCompletionPercentage(int $id): int {
        $student = $this->getById($id);
        if (!$student) return 0;

        // Check if student has a resume in student_documents
        $resumeStmt = $this->db->prepare("SELECT COUNT(*) FROM student_documents WHERE student_id = ? AND doc_type = 'resume'");
        $resumeStmt->execute([$id]);
        $hasResume = (int)$resumeStmt->fetchColumn() > 0;

        $checkpoints = [
            !empty($student['student_id']),
            !empty($student['enrollment_number']),
            !empty($student['name']),
            !empty($student['email']),
            !empty($student['phone']),
            !empty($student['department']),
            !empty($student['branch']),
            ((float)$student['cgpa'] > 0),
            !empty($student['profile_pic']) && file_exists(dirname(__DIR__) . '/' . $student['profile_pic']),
            $hasResume,
            count($this->getSkills($id)) > 0,
        ];

        $completed = count(array_filter($checkpoints));
        return (int)round(($completed / count($checkpoints)) * 100);
    }
}
