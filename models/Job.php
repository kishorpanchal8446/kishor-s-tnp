<?php
/**
 * ASTPMS — Job Model
 * Manages placement jobs, eligibility filtering, and CRUD operations
 */

class Job {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Get all active jobs with optional filters
     */
    public function getAll(array $filters = []): array {
        $sql = "
            SELECT j.*, j.role AS title, c.name AS company_name, NULL AS company_logo, c.website AS company_website
            FROM jobs j
            JOIN companies c ON j.company_id = c.id
            WHERE j.status = 'Open'
        ";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (j.role LIKE ? OR c.name LIKE ? OR j.location LIKE ? OR j.skills_required LIKE ?)";
            $s = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$s, $s, $s, $s]);
        }
        if (!empty($filters['job_type'])) {
            $sql .= " AND j.job_type = ?";
            $params[] = $filters['job_type'];
        }
        if (!empty($filters['min_cgpa'])) {
            $sql .= " AND j.min_cgpa <= ?";
            $params[] = (float)$filters['min_cgpa'];
        }
        if (!empty($filters['branch'])) {
            $sql .= " AND (j.eligible_branches IS NULL OR j.eligible_branches = '' OR FIND_IN_SET(?, j.eligible_branches) OR j.eligible_branches LIKE ?)";
            $params[] = $filters['branch'];
            $params[] = '%' . $filters['branch'] . '%';
        }

        $sql .= " ORDER BY j.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get featured jobs for student home dashboard
     */
    public function getFeatured(int $limit = 6): array {
        $stmt = $this->db->prepare("
            SELECT j.*, j.role AS title, c.name AS company_name
            FROM jobs j
            JOIN companies c ON j.company_id = c.id
            WHERE j.status = 'Open' AND j.deadline >= CURDATE()
            ORDER BY j.created_at DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get job details by ID
     */
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT j.*, j.role AS title, c.name AS company_name, NULL AS company_logo, c.email AS company_email, c.website AS company_website
            FROM jobs j
            JOIN companies c ON j.company_id = c.id
            WHERE j.id = ?
        ");
        $stmt->execute([$id]);
        $job = $stmt->fetch();
        return $job ?: null;
    }

    /**
     * Check if a student is eligible for a job
     */
    public function isStudentEligible(int $jobId, array $student): bool {
        $job = $this->getById($jobId);
        if (!$job) return false;

        // CGPA check
        if ((float)$student['cgpa'] < (float)$job['min_cgpa']) {
            return false;
        }

        // Branch check
        if (!empty($job['eligible_branches'])) {
            $branches = array_map('trim', explode(',', $job['eligible_branches']));
            if (!in_array($student['branch'], $branches)) {
                return false;
            }
        }

        return true;
    }
}
