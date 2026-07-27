<?php
/**
 * ASTPMS — Company Model
 * Handles recruiting companies, profiles, and job listings
 */

class Company {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM companies WHERE id = ?");
        $stmt->execute([$id]);
        $c = $stmt->fetch();
        return $c ?: null;
    }

    public function getAllActive(): array {
        return $this->db->query("SELECT * FROM companies WHERE is_active = 1 AND is_verified = 1 ORDER BY name ASC")->fetchAll();
    }
}
