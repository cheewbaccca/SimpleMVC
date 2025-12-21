<?php
use ItForFree\SimpleMVC\Config;
use ItForFree\SimpleMVC\Router\WebRouter;

$User = Config::getObject('core.user.class');
?>

<h2><?= $viewNotes->title ?>
    <span>
        <?php if ($User->isAllowed("admin/notes/edit") && strpos($_SERVER['REQUEST_URI'], 'admin/') !== false): ?>
        <?= $User->returnIfAllowed("admin/notes/edit",
            "<a href=" . WebRouter::link("admin/notes/edit&id=". $viewNotes->id)
            . ">[Редактировать]</a>");?>
        
        <?= $User->returnIfAllowed("admin/notes/delete",
                "<a href=" . WebRouter::link("admin/notes/delete&id=". $viewNotes->id)
            .    ">[Удалить]</a>"); ?>
        <?php endif; ?>
    </span>
    
</h2>

<!-- АВТОРЫ -->
<?php if (!empty($viewNotes->authors)): ?>
    <div style="color: #888; font-size: 0.85em; font-style: italic; margin: 3px 0;">
        Автор(ы):
        <?php foreach ($viewNotes->authors as $author): ?>
            <span class="author"><?= htmlspecialchars($author->login) ?></span>
            <?php if ($author !== end($viewNotes->authors)): ?>, <?php endif; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<p>Контент: <?= $viewNotes->content ?></p>
<p>Зарегестрирована: <?= $viewNotes->publicationDate ?></p>
