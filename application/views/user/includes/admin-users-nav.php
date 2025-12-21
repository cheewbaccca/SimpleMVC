<?php 
use ItForFree\SimpleMVC\Config;
use ItForFree\SimpleMVC\Router\WebRouter;

$User = Config::getObject('core.user.class');


//vpre($User->explainAccess("admin/adminusers/index"));
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1">Управление пользователями</span>
        <div class="navbar-nav ml-auto">
            <?php  if ($User->isAllowed("admin/adminusers/index")): ?>
            <a class="nav-link" href="<?= WebRouter::link("admin/adminusers/index") ?>">Список пользователей</a>
            <?php endif; ?>
            
            <?php  if ($User->isAllowed("admin/adminusers/add")): ?>
            <a class="nav-link" href="<?= WebRouter::link("admin/adminusers/add") ?>">+ Добавить пользователя</a>
            <?php endif; ?>
        </div>
    </div>
</nav>