<?php
namespace App\Models;
/**
 * Développeur assigné(s) : Seydina
 * Entité : Classe 'Log' de la couche Models
 */


use PDO;
use PDOException;
use DateTime;
use App\Config\Config; // Importer la classe de configuration 

class Log 
{
    /*************** Propriétés ***************/
    private int $id;
    private int $user_id;
    private string $action;
    private DateTime $timestamp;
    private string $ip_address;
    private string $user_agent;

    private PDO $db; // connexion de la base de données

    /*************** Constructeur ***************/
    public function __construct()
    {
        try {
            $this->db = new PDO("mysql:host=" . Config::DB_HOST . ";dbname=" . Config::DB_NAME, Config::DB_USER, Config::DB_PASS);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    /*************** Getters ***************/
public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getAction(): string
    {
        return $this->action;
    }
    
    public function getTimestamp(): DateTime
    {
        return $this->timestamp;
    }

    public function getIpAddress(): string
    {
        return $this->ip_address;
    }

    public function getUserAgent(): string
    {
        return $this->user_agent;
    }

    /*************** Setters ***************/
public function setUserId(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    public function setIpAddress(string $ip_address): void
    {
        $this->ip_address = $ip_address;
    }
    
    public function setUserAgent(string $user_agent): void
    {
        $this->user_agent = $user_agent;
    }

    /*************** Méthodes ***************/

   /*************** Méthode : Enregistrer un log ***************/
    public function create(): bool
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO logs (user_id, action, timestamp, ip_address, user_agent) 
                VALUES (:user_id, :action, NOW(), :ip_address, :user_agent)");

            return $stmt->execute([
                ':user_id' => $this->user_id,
                ':action' => $this->action,
                ':ip_address' => $this->ip_address,
                ':user_agent' => $this->user_agent
            ]);
        } catch (PDOException $e) {
            error_log("Erreur lors de l'insertion du log : " . $e->getMessage());
            return false;
        }
    }

    /*************** Méthode : Récupérer tous les logs ***************/
    public function getAll(): array
    {
        try {
            $stmt = $this->db->query("SELECT * FROM logs ORDER BY timestamp DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des logs : " . $e->getMessage());
            return [];
        }
    }
}




?>
