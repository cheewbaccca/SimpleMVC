<style> 
    
    textarea{
        height: 200%;
        width: 1110px;
        color: #003300;
    }
   
</style>

<?php include('includes/admin-notes-nav.php'); ?>
<h2><?= $addNoteTitle ?></h2>

<form id="addNote" method="post" action="<?= \ItForFree\SimpleMVC\Router\WebRouter::link("admin/notes/add")?>">
    <div class="form-group">
        <label for="title">Название новой заметки</label>
        <input type="text" class="form-control" name="title" id="title" placeholder="имя заметки">
    </div>
    <div class="form-group">
        <label for="summary">Краткое описание</label>
        <input type="text" class="form-control" name="summary" id="summary" placeholder="Краткое описание статьи">
    </div>
    <div class="form-group">
        <label for="content">Содержание</label><br>
        <textarea type="description" name="content" placeholred="описание заметки"  value=></textarea>
    </div>
    <div class="form-group">
        <label for="categoryId">Категория</label>
        <select name="categoryId" id="categoryId" class="form-control">
            <option value="">-- Без категории --</option>
            <?php
            $categoryModel = new \application\models\Category();
            $categories = $categoryModel->getList(100)['results'];
            foreach ($categories as $category):
            ?>
                <option value="<?= $category->id ?>"><?= htmlspecialchars($category->name) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="subcategoryId">Подкатегория</label>
        <select name="subcategoryId" id="subcategoryId" class="form-control">
            <option value="">-- Без подкатегории --</option>
        </select>
    </div>
    <div class="form-group">
        <label for="authorIds">Авторы</label>
        <select name="authorIds[]" id="authorIds" class="form-control" multiple>
            <?php
            $userModel = new \application\models\UserModel();
            $users = $userModel->getList(100)['results'];
            foreach ($users as $user):
            ?>
                <option value="<?= $user->id ?>"><?= htmlspecialchars($user->login) ?></option>
            <?php endforeach; ?>
        </select>
        <small class="form-text text-muted">Удерживайте Ctrl для выбора нескольких авторов</small>
    </div>
    <div class="form-group form-check">
        <input type="checkbox" class="form-check-input" name="isActive" id="isActive" checked>
        <label class="form-check-label" for="isActive">Активная статья</label>
    </div>
    <input type="submit" class="btn btn-primary" name="saveNewNote" value="Сохранить">
    <input type="submit" class="btn" name="cancel" value="Назад">
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var categorySelect = document.getElementById('categoryId');
    var subcategorySelect = document.getElementById('subcategoryId');
    
    function loadSubcategories(categoryId) {
        // Очищаем и добавляем опцию по умолчанию
        subcategorySelect.innerHTML = '<option value="">-- Без подкатегории --</option>';
        
        if (!categoryId) return;
        
        // Простой запрос к нашей функции
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '<?= \ItForFree\SimpleMVC\Router\WebRouter::link("admin/notes/getSubcategories", ["categoryId" => ""]) ?>' + categoryId, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    var subcategories = JSON.parse(xhr.responseText);
                    subcategories.forEach(function(subcat) {
                        var option = document.createElement('option');
                        option.value = subcat.id;
                        option.textContent = subcat.name;
                        subcategorySelect.appendChild(option);
                    });
                } catch(e) {
                    console.error('Error parsing JSON:', e);
                }
            }
        };
        xhr.send();
    }
    
    // Загружаем при изменении категории
    categorySelect.addEventListener('change', function() {
        loadSubcategories(this.value);
    });
});
</script>
