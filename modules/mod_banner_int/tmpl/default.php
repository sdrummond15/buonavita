<?php

$active = JFactory::getApplication()->getMenu()->getActive();

$app = JFactory::getApplication();
$title = $app->getCfg('sitename');

if(isset($active) && isset($active->title) && !empty($active->title)){
    $title = $active->title;
}

?>
<div id="banner-int">
    <div class="banner-img banner-<?= JFilterOutput::stringURLSafe($title) ?>">
    </div>
    <div class="banner-fix"></div>
    <div class="banner-int">
        <h1><?= $title ?></h1>
    </div>
</div>