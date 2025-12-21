<?php

namespace application\controllers;

use application\models\Note;

class HomepageController extends \ItForFree\SimpleMVC\MVC\Controller
{
    public string $layoutPath = 'main.php';

    public function indexAction()
    {
        $articleModel = new Note();

        // Модифицируем запрос, чтобы получить только активные статьи
        $sql = "SELECT * FROM {$articleModel->tableName} WHERE isActive = 1 ORDER BY {$articleModel->orderBy} LIMIT :numResults";
        $st = $articleModel->pdo->prepare($sql);
        $st->bindValue(':numResults', 10, \PDO::PARAM_INT);
        $st->execute();
        $articles = $st->fetchAll(\PDO::FETCH_OBJ);

        foreach ($articles as $article) {
            // Эти свойства должны быть ОБЪЯВЛЕНЫ в модели (см. ниже)
            $article->categoryName    = $articleModel->getCategoryNameForId($article->categoryId);
            $article->subcategoryName = $articleModel->getSubcategoryNameForId($article->subcategoryId);
            $article->authors         = $articleModel->getAuthorsForArticle($article->id);
        }

        $this->view->addVar('articles', $articles);
        $this->view->render('homepage/index.php');
    }
}
