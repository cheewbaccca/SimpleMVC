<?php
namespace application\models;

use ItForFree\SimpleMVC\MVC\Model;
/**
 * Класс для обработки пользователей
 */
class UserModel extends Model
{
    // Свойства
    /**
    * @var string логин пользователя
    */
    public $login = null;
    
    public ?int $id = null;

    /**
    * @var string пароль пользователя
    */
    public $pass = null;
    
    /**
     * @var string роль пользователя
     */
    public $role = null;
    
    public $email = null;
    
    public $timestamp = null;
    
    /**
     * @var string Критерий сортировки строк таблицы
     */
    public string $orderBy = "login ASC";
    
    /**
     *  @var string название таблицы
     */
    public string $tableName = 'users';
    
    public $salt = null;
    
    public $failed_login_attempts = 0;

    public function insert()
    {
        $sql = "INSERT INTO $this->tableName (timestamp, login, salt, pass, role, email, failed_login_attempts) VALUES (:timestamp, :login, :salt, :pass, :role, :email, :failed_login_attempts)";
        $st = $this->pdo->prepare ( $sql );
        $st->bindValue( ":timestamp", (new \DateTime('NOW'))->format('Y-m-d H:i:s'), \PDO::PARAM_STMT);
        $st->bindValue( ":login", $this->login, \PDO::PARAM_STR );
        
        //Хеширование пароля
        $this->salt = rand(0,1000000);
        $st->bindValue( ":salt", $this->salt, \PDO::PARAM_STR );
//        \DebugPrinter::debug($this->salt);
        
        $this->pass .= $this->salt;
        $hashPass = password_hash($this->pass, PASSWORD_BCRYPT);
//        \DebugPrinter::debug($hashPass);
        $st->bindValue( ":pass", $hashPass, \PDO::PARAM_STR );
        
        $st->bindValue( ":role", $this->role, \PDO::PARAM_STR );
        $st->bindValue( ":email", $this->email, \PDO::PARAM_STR );
        $st->bindValue( ":failed_login_attempts", $this->failed_login_attempts, \PDO::PARAM_INT );
        $st->execute();
        $this->id = $this->pdo->lastInsertId();
    }
    
    public function update()
    {
        // Если передан пустой пароль, не обновляем его
        if (empty($this->pass)) {
            $sql = "UPDATE $this->tableName SET timestamp=:timestamp, login=:login, email=:email, role=:role, failed_login_attempts=:failed_login_attempts WHERE id = :id";
            $st = $this->pdo->prepare ( $sql );
            
            $st->bindValue( ":timestamp", (new \DateTime('NOW'))->format('Y-m-d H:i:s'), \PDO::PARAM_STMT);
            $st->bindValue( ":login", $this->login, \PDO::PARAM_STR );
            $st->bindValue( ":email", $this->email, \PDO::PARAM_STR );
            $st->bindValue( ":role", $this->role, \PDO::PARAM_STR );
            $st->bindValue( ":failed_login_attempts", $this->failed_login_attempts, \PDO::PARAM_INT );
            $st->bindValue( ":id", $this->id, \PDO::PARAM_INT );
            $st->execute();
        } else {
            // Обновляем пароль, если он был передан
            $sql = "UPDATE $this->tableName SET timestamp=:timestamp, login=:login, pass=:pass, email=:email, role=:role, failed_login_attempts=:failed_login_attempts WHERE id = :id";
            $st = $this->pdo->prepare ( $sql );
            
            $st->bindValue( ":timestamp", (new \DateTime('NOW'))->format('Y-m-d H:i:s'), \PDO::PARAM_STMT);
            $st->bindValue( ":login", $this->login, \PDO::PARAM_STR );
            
            // Хеширование пароля
            $this->salt = rand(0,1000000);
            $this->pass .= $this->salt;
            $hashPass = password_hash($this->pass, PASSWORD_BCRYPT);
            $st->bindValue( ":pass", $hashPass, \PDO::PARAM_STR );
            
            $st->bindValue( ":email", $this->email, \PDO::PARAM_STR );
            $st->bindValue( ":role", $this->role, \PDO::PARAM_STR );
            $st->bindValue( ":failed_login_attempts", $this->failed_login_attempts, \PDO::PARAM_INT );
            $st->bindValue( ":id", $this->id, \PDO::PARAM_INT );
            $st->execute();
        }
    }
    
    /**
     * Вернёт id пользователя
     * 
     * @return ?int
     */
    public function getId()
    {
        if ($this->userName !== 'guest'){
            $sql = "SELECT id FROM users where login = :userName";
            $st = $this->pdo->prepare($sql); 
            $st -> bindValue( ":userName", $this->userName, \PDO::PARAM_STR );
            $st -> execute();
            $row = $st->fetch();
            return $row['id']; 
        } else  {
            return null;
        }  
    }
    
    /**
     * Проверка логина и пароля пользователя.
     */
    public function getAuthData($login): ?array {
 $sql = "SELECT salt, pass, failed_login_attempts FROM users WHERE login = :login";
 $st = $this->pdo->prepare($sql);
 $st->bindValue(":login", $login, \PDO::PARAM_STR);
 $st->execute();
 $authData = $st->fetch();
 return $authData ? $authData : null;
    }
    
    /**
     * Проверяем активность пользователя.
     */
    public function getRole($login): array {
 $sql = "SELECT role, failed_login_attempts FROM users WHERE login = :login";
 $st = $this->pdo->prepare($sql);
 $st->bindValue(":login", $login, \PDO::PARAM_STR);
 $st->execute();
 return $st->fetch();
    }
    public function updateFailedLoginAttempts($login, $attempts): void {
        $sql = "UPDATE $this->tableName SET failed_login_attempts = :failed_login_attempts WHERE login = :login";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(":failed_login_attempts", $attempts, \PDO::PARAM_INT);
        $st->bindValue(":login", $login, \PDO::PARAM_STR);
        $st->execute();
    }

}