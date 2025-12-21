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
     * @var int количество неудачных попыток входа
     */
    public $login_attempts = 0;
    
    /**
     * @var string Критерий сортировки строк таблицы
     */
    public string $orderBy = "login ASC";
    
    /**
     *  @var string название таблицы
     */
    public string $tableName = 'users';
    
    public $salt = null;
    

    public function insert()
    {
        $sql = "INSERT INTO $this->tableName (timestamp, login, salt, pass, role, email) VALUES (:timestamp, :login, :salt, :pass, :role, :email)"; 
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
        $st->execute();
        $this->id = $this->pdo->lastInsertId();
    }
    
    public function update()
    {
        if (!empty($this->pass)){
            $sql = "UPDATE $this->tableName SET timestamp=:timestamp, login=:login, pass=:pass, role=:role, email=:email  WHERE id = :id";  
            $st = $this->pdo->prepare ( $sql );
            $st->bindValue( ":pass", $this->pass, \PDO::PARAM_STR );
        }
        else{
            $sql = "UPDATE $this->tableName SET timestamp=:timestamp, login=:login, role=:role, email=:email  WHERE id = :id";  
            $st = $this->pdo->prepare ( $sql );
        }

        $st->bindValue( ":timestamp", (new \DateTime('NOW'))->format('Y-m-d H:i:s'), \PDO::PARAM_STMT);
        $st->bindValue( ":login", $this->login, \PDO::PARAM_STR );
        
        // Хеширование пароля
        $this->salt = rand(0,1000000);
        //$st->bindValue( ":salt", $this->salt, \PDO::PARAM_STR );
        //$this->pass .= $this->salt;
        //$hashPass = password_hash($this->pass, PASSWORD_BCRYPT);
        //$st->bindValue( ":pass", $this->pass, \PDO::PARAM_STR );
        
        $st->bindValue( ":role", $this->role, \PDO::PARAM_STR );
        $st->bindValue( ":email", $this->email, \PDO::PARAM_STR );
        $st->bindValue( ":id", $this->id, \PDO::PARAM_INT );
        $st->execute();
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
	$sql = "SELECT salt, pass FROM users WHERE login = :login";
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
 $sql = "SELECT role FROM users WHERE login = :login";
 $st = $this->pdo->prepare($sql);
 $st->bindValue(":login", $login, \PDO::PARAM_STR);
 $st->execute();
 return $st->fetch();
    }
    
    /**
     * Увеличивает счетчик неудачных попыток входа для пользователя
     *
     * @param string $login Логин пользователя
     */
    public function incrementLoginAttempts(string $login): void
    {
        $sql = "UPDATE $this->tableName SET login_attempts = login_attempts + 1 WHERE login = :login";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(":login", $login, \PDO::PARAM_STR);
        $st->execute();
    }
    
    /**
     * Сбрасывает счетчик неудачных попыток входа для пользователя
     *
     * @param string $login Логин пользователя
     */
    public function resetLoginAttempts(string $login): void
    {
        $sql = "UPDATE $this->tableName SET login_attempts = 0 WHERE login = :login";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(":login", $login, \PDO::PARAM_STR);
        $st->execute();
    }
    
    /**
     * Извлечет данные и вернет массив моделей из базы данных.
     * Переопределяем метод, чтобы гарантировать получение всех полей, включая login_attempts
     *
     * @param int $numRows ограничение на число строк
     */
    public function getList(int $numRows=1000000): array
    {
        $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM $this->tableName
                ORDER BY  $this->orderBy LIMIT :numRows";
        
        $modelClassName = static::class;
       
        $st = $this->pdo->prepare($sql);
        $st->bindValue( ":numRows", $numRows, \PDO::PARAM_INT );
        $st->execute();
        $list = array();
        
        while ($row = $st->fetch()) {
            $example = new $modelClassName($row);
            $list[] = $example;
        }

        $sql = "SELECT FOUND_ROWS() AS totalRows"; //  получаем число выбранных строк
        $totalRows = $this->pdo->query($sql)->fetch();
    
        return (array("results" => $list, "totalRows" => $totalRows[0]));
    }

    // Получить статьи пользователя
public function getArticles()
{
    $sql = "SELECT n.* FROM notes n
            JOIN article_authors aa ON n.id = aa.article_id
            WHERE aa.user_id = :user_id
            ORDER BY n.publicationDate DESC";
    $st = $this->pdo->prepare($sql);
    $st->bindValue(":user_id", $this->id, \PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll(\PDO::FETCH_OBJ);
}

}