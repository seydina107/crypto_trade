<?php
namespace App\Models;

use PDO;
use PDOException;
use DateTime;
use App\Config\Config;

class Alert
{
    private int $id;
    private int $user_id;
    private int $crypto_id;
    private float $threshold;
    private string $direction; 
    private bool $auto_sell;
    private DateTime $created_at;

    private PDO $db;

    public function __construct()
    {
        try {
            $this->db = new PDO(
                "mysql:host=" . Config::DB_HOST . ";dbname=" . Config::DB_NAME,
                Config::DB_USER,
                Config::DB_PASS
            );
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Connexion à la base de données échouée : " . $e->getMessage());
        }
    }

    /********** Setters **********/
    public function setUserId(int $user_id): void { $this->user_id = $user_id; }
    public function setCryptoId(int $crypto_id): void { $this->crypto_id = $crypto_id; }
    public function setThreshold(float $threshold): void { $this->threshold = $threshold; }
    public function setDirection(string $direction): void { $this->direction = $direction; }
    public function setAutoSell(bool $auto_sell): void { $this->auto_sell = $auto_sell; }

    /********** Méthode : Enregistrer une alerte **********/
    public function save(): bool
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO alerts (user_id, crypto_id, threshold, direction, auto_sell, created_at)
                VALUES (:user_id, :crypto_id, :threshold, :direction, :auto_sell, NOW())
            ");
            return $stmt->execute([
                ':user_id' => $this->user_id,
                ':crypto_id' => $this->crypto_id,
                ':threshold' => $this->threshold,
                ':direction' => $this->direction,
                ':auto_sell' => $this->auto_sell ? 1 : 0,
            ]);
        } catch (PDOException $e) {
            error_log("Erreur alerte save() : " . $e->getMessage());
            return false;
        }
    }

    /********** Méthode : Récupérer les alertes actives pour un utilisateur **********/
    public function getByUser(int $user_id): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM alerts WHERE user_id = :user_id");
            $stmt->execute([':user_id' => $user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur getByUser() : " . $e->getMessage());
            return [];
        }
    }

    /********** Méthode statique : Vérifier si une alerte doit être déclenchée **********/
    public static function checkAlerts(PDO $db, int $crypto_id, float $current_price): array
    {
        try {
            $stmt = $db->prepare("SELECT * FROM alerts WHERE crypto_id = :crypto_id");
            $stmt->execute([':crypto_id' => $crypto_id]);
            $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $triggered = [];

            foreach ($alerts as $alert) {
                $match = false;

                if ($alert['direction'] === 'above' && $current_price >= $alert['threshold']) {
                    $match = true;
                } elseif ($alert['direction'] === 'below' && $current_price <= $alert['threshold']) {
                    $match = true;
                }

                if ($match) {
                    $triggered[] = $alert;

                    if ($alert['auto_sell']) {
                       
                    }
                }
            }

            return $triggered;

        } catch (PDOException $e) {
            error_log("Erreur checkAlerts() : " . $e->getMessage());
            return [];
        }
    }
}
