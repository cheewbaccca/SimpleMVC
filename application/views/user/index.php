<?php 
use ItForFree\SimpleMVC\Config;

$User = Config::getObject('core.user.class');
?>
<?php include('includes/admin-users-nav.php'); ?>

<div class="row">
    <div class="col-md-12">
        <h2>Список пользователей</h2>
    </div>
</div>
<br>

<?php if (!empty($users)): ?>
<div class="table-responsive">
    <table class="table table-striped">
        <thead class="thead-dark">
        <tr>
          <th scope="col">id</th>
          <th scope="col">Логин</th>
          <th scope="col">Email</th>
          <th scope="col">Зарегистрирован</th>
          <th scope="col">Неудачных попыток входа</th>
          <th scope="col">Действия</th>
        </tr>
         </thead>
        <tbody>
        <?php foreach($users as $user): ?>
        <tr>
            <td> <?= $user->id ?> </td>
            <td> <?= "<a href=" . \ItForFree\SimpleMVC\Router\WebRouter::link('admin/adminusers/index&id='
            . $user->id . ">{$user->login}</a>" ) ?> </td>
            <td>  <?= $user->email ?> </td>
            <td>  <?= $user->timestamp ?> </td>
            <td> <?= $user->login_attempts ?> </td>
            <td>
                <div class="btn-group" role="group">
                <?= $User->returnIfAllowed("admin/adminusers/edit",
                    "<a href=" . \ItForFree\SimpleMVC\Router\WebRouter::link("admin/adminusers/edit&id=". $user->id)
                    . " class='btn btn-sm btn-outline-primary'>Редактировать</a>");?>
                <?= $User->returnIfAllowed("admin/adminusers/delete",
                    "<a href=" . \ItForFree\SimpleMVC\Router\WebRouter::link("admin/adminusers/delete&id=". $user->id)
                    . " class='btn btn-sm btn-outline-danger ml-1'>Удалить</a>");?>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        
        </tbody>
    </table>
</div>
<br>
<?php else:?>
    <div class="alert alert-info" role="alert">
        Список пользователей пуст.
    </div>
<?php endif; ?>