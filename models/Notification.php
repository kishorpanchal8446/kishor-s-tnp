<?php
/**
 * ASTPMS — Notification Model
 * Manages student and system notifications
 */

class Notification {
    private PDO $db;

    public function __construct(PDO $dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Retrieve notifications for a student
     */
    public function getByStudent(int $studentId): array {
        $stmt = $this->db->prepare("
            SELECT * FROM notifications 
            WHERE (recipient_id = ? AND recipient_role = 'student') OR (recipient_id IS NULL AND recipient_role = 'all')
            ORDER BY created_at DESC
        ");
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    /**
     * Count unread notifications
     */
    public function getUnreadCount(int $studentId): int {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM notifications 
            WHERE ((recipient_id = ? AND recipient_role = 'student') OR (recipient_id IS NULL AND recipient_role = 'all')) AND is_read = 0
        ");
        $stmt->execute([$studentId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Mark a specific notification as read
     */
    public function markAsRead(int $notificationId): bool {
        $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
        return $stmt->execute([$notificationId]);
    }

    /**
     * Mark all notifications as read for a student
     */
    public function markAllAsRead(int $studentId): bool {
        $stmt = $this->db->prepare("
            UPDATE notifications SET is_read = 1 
            WHERE (recipient_id = ? AND recipient_role = 'student') OR (recipient_id IS NULL AND recipient_role = 'all')
        ");
        return $stmt->execute([$studentId]);
    }

    /**
     * Add new notification
     */
    public function add(?int $recipientId, string $title, string $message, string $type = 'General', string $recipientRole = 'student', string $senderRole = 'system'): bool {
        $stmt = $this->db->prepare("
            INSERT INTO notifications (recipient_id, recipient_role, sender_role, title, message, type)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$recipientId, $recipientRole, $senderRole, $title, $message, $type]);
    }
}
