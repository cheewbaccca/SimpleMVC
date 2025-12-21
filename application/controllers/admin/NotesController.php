<?php
namespace application\controllers\admin;
use application\models\Note;
use ItForFree\SimpleMVC\Config;

/* 
 *   Class-controller notes
 * 
 * 
 */

class NotesController extends \ItForFree\SimpleMVC\MVC\Controller
{
    
    public string $layoutPath = 'admin-main.php';
    
    protected array $rules = [
         ['allow' => true, 'roles' => ['admin']],
         ['allow' => false, 'roles' => ['?', '@']],
    ];
    
    
    public function indexAction()
    {
        $Note = new Note();
        
        $notes = $Note->getList()['results'];
        $this->view->addVar('notes', $notes);
        $this->view->render('note/index.php');
    }
    
    public function viewAction()
    {
        $Note = new Note();
        $noteId = $_GET['id'] ?? null;
        
        if ($noteId) {
            $viewNotes = $Note->getById($_GET['id']);
            $this->view->addVar('viewNotes', $viewNotes);
            $this->view->render('note/view-item.php');
        } else {
            $this->redirect('/admin/notes/index');
        }
    }
    
    /**
     * Выводит на экран форму для создания новой статьи (только для Администратора)
     */
    public function addAction()
    {
        $Url = Config::get('core.router.class');
        if (!empty($_POST)) {
            if (!empty($_POST['saveNewNote'])) {
                $Note = new Note();
                $newNotes = $Note->loadFromArray($_POST);
                
                // Обработка поля isActive
                $newNotes->isActive = !empty($_POST['isActive']);
                
                $newNotes->insert();
                
                // Обработка авторов, если они были выбраны
                if (!empty($_POST['authorIds']) && is_array($_POST['authorIds'])) {
                    foreach ($_POST['authorIds'] as $userId) {
                        $newNotes->addAuthor((int)$userId);
                    }
                }
                
                $this->redirect($Url::link("admin/notes/index"));
            }
            elseif (!empty($_POST['cancel'])) {
                $this->redirect($Url::link("admin/notes/index"));
            }
        }
        else {
            $addNoteTitle = "Добавление новой заметки";
            $this->view->addVar('addNoteTitle', $addNoteTitle);
            
            $this->view->render('note/add.php');
        }
    }
    
    /**
     * Выводит на экран форму для редактирования статьи (только для Администратора)
     */
    public function editAction()
    {
        // Определяем ID из GET или POST параметров
        $id = $_GET['id'] ?? ($_POST['id'] ?? null);
        $Url = Config::get('core.router.class');
        
        if ($id === null) {
            // Если ID не передан, перенаправляем на страницу со списком статей
            $this->redirect($Url::link("admin/notes/index"));
            return;
        }
        
        if (!empty($_POST)) { // это выполняется нормально.
            
            if (!empty($_POST['saveChanges'] )) {
                $Note = new Note();
                
                // Загружаем существующую статью для обновления
                $existingNote = $Note->getById((int)$id);
                
                if ($existingNote) {
                    // Обновляем поля существующей статьи значениями из POST
                    $existingNote->title = $_POST['title'] ?? $existingNote->title;
                    $existingNote->content = $_POST['content'] ?? $existingNote->content;
                    $existingNote->summary = $_POST['summary'] ?? $existingNote->summary;
                    
                    // Обрабатываем categoryId
                    if (isset($_POST['categoryId'])) {
                        $existingNote->categoryId = !empty($_POST['categoryId']) ? (int)$_POST['categoryId'] : null;
                    }
                    
                    // Обрабатываем subcategoryId
                    if (isset($_POST['subcategoryId'])) {
                        $existingNote->subcategoryId = !empty($_POST['subcategoryId']) ? (int)$_POST['subcategoryId'] : null;
                    }
                    
                    // Обрабатываем поле isActive
                    $existingNote->isActive = !empty($_POST['isActive']);
                    
                    // Обновляем статью
                    $existingNote->update();
                }
                
                $this->redirect($Url::link("admin/notes/index"));
            }
            elseif (!empty($_POST['cancel'])) {
                $this->redirect($Url::link("admin/notes/index"));
            }
        }
        else {
            $Note = new Note();
            $viewNotes = $Note->getById($id);
            
            if (!$viewNotes) {
                // Если статья не найдена, перенаправляем на страницу со списком статей
                $this->redirect($Url::link("admin/notes/index"));
                return;
            }
            
            $editNoteTitle = "Редактирование заметки";
            
            $this->view->addVar('viewNotes', $viewNotes);
            $this->view->addVar('editNoteTitle', $editNoteTitle);
            
            $this->view->render('note/edit.php');
        }
        
    }
    
    /**
     * Выводит на экран предупреждение об удалении данных (только для Администратора)
     */
    public function deleteAction()
    {
        $id = $_GET['id'];
        $Url = Config::get('core.router.class');
        
        if (!empty($_POST)) {
            if (!empty($_POST['deleteNote'])) {
                $Note = new Note();
                $newNotes = $Note->loadFromArray($_POST);
                $newNotes->delete();
                
                $this->redirect($Url::link("admin/notes/index"));
              
            }
            elseif (!empty($_POST['cancel'])) {
                $this->redirect($Url::link("admin/notes/edit", ['id' => $id]));
            }
        }
        else {
            
            $Note = new Note();
            $deletedNote = $Note->getById($id);
            $deleteNoteTitle = "Удалить заметку?";
            
            $this->view->addVar('deleteNoteTitle', $deleteNoteTitle);
            $this->view->addVar('deletedNote', $deletedNote);
            
            $this->view->render('note/delete.php');
        }
    }
    
    public function getSubcategoriesAction()
    {
        $categoryId = $_GET['categoryId'] ?? null;
        
        if (!$categoryId) {
            echo json_encode([]);
            return;
        }
        
        $subcategoryModel = new \application\models\Subcategory();
        $sql = "SELECT id, name FROM subcategories WHERE categoryId = :categoryId ORDER BY name";
        $st = $subcategoryModel->pdo->prepare($sql);
        $st->bindValue(":categoryId", $categoryId, \PDO::PARAM_INT);
        $st->execute();
        $subcategories = $st->fetchAll(\PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode($subcategories);
    }

}