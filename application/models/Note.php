<?php

namespace application\models;

class Note extends BaseExampleModel
{
    public string $tableName = "articles";

    public string $orderBy = 'publicationDate DESC';

    public ?int $id = null;

    public ?string $title = null;
    public ?string $summary = null;
    public ?string $content = null;

    public $publicationDate = null;

    public ?int $categoryId = null;
    public ?int $subcategoryId = null;

    public ?bool $isActive = true; // По умолчанию статья активна

    public ?string $categoryName = null;
    public ?string $subcategoryName = null;
    public array $authors = [];

    public function insert()
    {
        if (empty($this->categoryId)) {
            $this->categoryId = 1;
        }

        // Проверяем, существует ли поле isActive в таблице
        $columnsSql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
                      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :tableName AND COLUMN_NAME = 'isActive'";
        $st = $this->pdo->prepare($columnsSql);
        $st->bindValue(':tableName', $this->tableName, \PDO::PARAM_STR);
        $st->execute();
        $isActiveExists = $st->fetch();

        if ($isActiveExists) {
            $sql = "INSERT INTO {$this->tableName}
                    (publicationDate, categoryId, subcategoryId, title, summary, content, isActive)
                    VALUES
                    (:publicationDate, :categoryId, :subcategoryId, :title, :summary, :content, :isActive)";
        } else {
            $sql = "INSERT INTO {$this->tableName}
                    (publicationDate, categoryId, subcategoryId, title, summary, content)
                    VALUES
                    (:publicationDate, :categoryId, :subcategoryId, :title, :summary, :content)";
        }

        $st = $this->pdo->prepare($sql);

        // DATE: YYYY-MM-DD
        $st->bindValue(":publicationDate", (new \DateTime('NOW'))->format('Y-m-d'), \PDO::PARAM_STR);

        $st->bindValue(":categoryId", $this->categoryId, \PDO::PARAM_INT);

        if ($this->subcategoryId === null || $this->subcategoryId === 0) {
            $st->bindValue(":subcategoryId", null, \PDO::PARAM_NULL);
        } else {
            $st->bindValue(":subcategoryId", $this->subcategoryId, \PDO::PARAM_INT);
        }

        $st->bindValue(":title", $this->title, \PDO::PARAM_STR);
        $st->bindValue(":summary", $this->summary ?? '', \PDO::PARAM_STR);
        $st->bindValue(":content", $this->content ?? '', \PDO::PARAM_STR);

        if ($isActiveExists) {
            $st->bindValue(":isActive", $this->isActive ? 1 : 0, \PDO::PARAM_INT);
        }

        $st->execute();
        $this->id = (int)$this->pdo->lastInsertId();
    }

    public function update()
    {
        // Проверяем, существует ли поле isActive в таблице
        $columnsSql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
                      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :tableName AND COLUMN_NAME = 'isActive'";
        $st = $this->pdo->prepare($columnsSql);
        $st->bindValue(':tableName', $this->tableName, \PDO::PARAM_STR);
        $st->execute();
        $isActiveExists = $st->fetch();

        if ($isActiveExists) {
            $sql = "UPDATE {$this->tableName} SET
                        publicationDate = :publicationDate,
                        categoryId = :categoryId,
                        subcategoryId = :subcategoryId,
                        title = :title,
                        summary = :summary,
                        content = :content,
                        isActive = :isActive
                    WHERE id = :id";
        } else {
            $sql = "UPDATE {$this->tableName} SET
                        publicationDate = :publicationDate,
                        categoryId = :categoryId,
                        subcategoryId = :subcategoryId,
                        title = :title,
                        summary = :summary,
                        content = :content
                    WHERE id = :id";
        }

        $st = $this->pdo->prepare($sql);

        $st->bindValue(":publicationDate", (new \DateTime('NOW'))->format('Y-m-d'), \PDO::PARAM_STR);
        $st->bindValue(":categoryId", $this->categoryId, \PDO::PARAM_INT);

        if ($this->subcategoryId === null || $this->subcategoryId === 0) {
            $st->bindValue(":subcategoryId", null, \PDO::PARAM_NULL);
        } else {
            $st->bindValue(":subcategoryId", $this->subcategoryId, \PDO::PARAM_INT);
        }

        $st->bindValue(":title", $this->title, \PDO::PARAM_STR);
        $st->bindValue(":summary", $this->summary ?? '', \PDO::PARAM_STR);
        $st->bindValue(":content", $this->content ?? '', \PDO::PARAM_STR);
        
        if ($isActiveExists) {
            $st->bindValue(":isActive", $this->isActive ? 1 : 0, \PDO::PARAM_INT);
        }
        
        $st->bindValue(":id", (int)$this->id, \PDO::PARAM_INT);

        $st->execute();
    }

    public function getList(int $numRows = 1000): array
    {
        // Проверяем, существует ли поле isActive в таблице
        $columnsSql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
                      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :tableName";
        $st = $this->pdo->prepare($columnsSql);
        $st->bindValue(':tableName', $this->tableName, \PDO::PARAM_STR);
        $st->execute();
        $columns = $st->fetchAll(\PDO::FETCH_COLUMN);
        
        if (in_array('isActive', $columns)) {
            $sql = "SELECT *, isActive FROM {$this->tableName}";
        } else {
            $sql = "SELECT * FROM {$this->tableName}";
        }
        
        if (!empty($this->orderBy)) {
            $sql .= " ORDER BY " . $this->orderBy;
        }
        
        $sql .= " LIMIT :numRows";
        
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':numRows', $numRows, \PDO::PARAM_INT);
        $st->execute();
        
        $result = $st->fetchAll(\PDO::FETCH_OBJ);
        
        return ['results' => $result];
    }

    public function getCategoryNameForId($categoryId): ?string
    {
        if (!$categoryId) return null;

        $sql = "SELECT name FROM categories WHERE id = :id";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(":id", (int)$categoryId, \PDO::PARAM_INT);
        $st->execute();
        $row = $st->fetch(\PDO::FETCH_ASSOC);

        return $row ? $row['name'] : null;
    }

    public function getSubcategoryNameForId($subcategoryId): ?string
    {
        if (!$subcategoryId) return null;

        $sql = "SELECT name FROM subcategories WHERE id = :id";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(":id", (int)$subcategoryId, \PDO::PARAM_INT);
        $st->execute();
        $row = $st->fetch(\PDO::FETCH_ASSOC);

        return $row ? $row['name'] : null;
    }

    public function getAuthorsForArticle(int $articleId): array
    {
        $sql = "SELECT u.*
                FROM users u
                JOIN article_users au ON u.id = au.user_id
                WHERE au.article_id = :article_id
                ORDER BY u.login";

        $st = $this->pdo->prepare($sql);
        $st->bindValue(":article_id", $articleId, \PDO::PARAM_INT);
        $st->execute();

        return $st->fetchAll(\PDO::FETCH_OBJ);
    }

    public function addAuthor(int $userId): bool
    {
        $sql = "INSERT IGNORE INTO article_users (article_id, user_id)
                VALUES (:article_id, :user_id)";

        $st = $this->pdo->prepare($sql);
        $st->bindValue(":article_id", (int)$this->id, \PDO::PARAM_INT);
        $st->bindValue(":user_id", $userId, \PDO::PARAM_INT);

        return $st->execute();
    }

    public function removeAuthor(int $userId): bool
    {
        $sql = "DELETE FROM article_users
                WHERE article_id = :article_id AND user_id = :user_id";

        $st = $this->pdo->prepare($sql);
        $st->bindValue(":article_id", (int)$this->id, \PDO::PARAM_INT);
        $st->bindValue(":user_id", $userId, \PDO::PARAM_INT);

        return $st->execute();
    }
    
    /**
     * Получает из БД все поля одной строки таблицы, с соответствующим Id
     * Возвращает объект класса модели.
     *
     * @param int    $id         id строки (кортежа)
     * @param string $tableName  имя таблицы (необязатлеьный параметр)
     */
    public function getById(int $id, string $tableName = ''): ?\ItForFree\SimpleMVC\MVC\Model
    {
        $tableName = !empty($tableName) ? $tableName : $this->tableName;
        
        // Проверяем, существует ли поле isActive в таблице
        $columnsSql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
                      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :tableName AND COLUMN_NAME = 'isActive'";
        $st = $this->pdo->prepare($columnsSql);
        $st->bindValue(':tableName', $tableName, \PDO::PARAM_STR);
        $st->execute();
        $isActiveExists = $st->fetch();
        
        if ($isActiveExists) {
            $sql = "SELECT *, isActive FROM $tableName where id = :id";
        } else {
            $sql = "SELECT * FROM $tableName where id = :id";
        }
        
        $modelClassName = static::class;
        
        $st = $this->pdo->prepare($sql);
        
        $st->bindValue(":id", $id, \PDO::PARAM_INT);
        $st->execute();
        $row = $st->fetch();
        
        if ($row) {
            return new $modelClassName($row);
        } else {
            return null;
        }
    }
}
