<?php
class Logger {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function logActivity($userId, $action, $entityType, $entityId, $details = []) {
        $stmt = $this->pdo->prepare("
            INSERT INTO user_activities (user_id, action, entity_type, entity_id, details, ip_address)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $userId,
            $action,
            $entityType,
            $entityId,
            json_encode($details),
            $_SERVER['REMOTE_ADDR']
        ]);
    }
}
