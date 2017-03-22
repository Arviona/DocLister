<?php
/**
 * DLcrumbs snippet
 *
 * @license GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @author Agel_Nash <Agel_Nash@xaker.ru>
 *
 * Comments by Aharito http://aharito/ru
 *
 * Параметры DLcrumbs:
 * &id - ID страницы для генерации цепочки. По умолчанию ID текущей страницы.
 * &hideMain - (0|1) скрывать Главную или нет. По умолчанию 0 (не скрывать).
 * &showCurrent - (0|1) показывать текущую страницу в цепочке или нет. По умолчанию 0 (не показывать).
 * &minDocs - с какого уровня глубины начинать отбражение цепочки крошек. По умолчанию выводится всегда.
 *
 * Шаблоны.
 * Задаются по правилам DL, то есть мгут быть и инлайн-шаблонами, и именами чанков, или загружаться из файла, документа MODx, конфига, глобального плейсхолдера.
 * &ownerTPL - шаблон-обёртка цепочки крошек. По умолчанию пустой.
 * &tpl - шаблон элемента крошек. По умолчанию - как в DocLister для контроллера site_content.
 * &tplCurrent - шаблон текущего элемента крошек. Если не задано, то совпадает с шаблоном tpl, как в DL.
 * 
 * Это необходимый минимум "доклистеровских" параметров, но можно задавать и многие другие параметры для DL.
 * Например: &debug, &tplFirst, &display и так далее.
 * 
 * Параметры, передаваемые в DL, можно записать в файл в формате JSON и положить в папку core или custom внутри assets/snippets/DocLister/config/
 * Загрузить такой файл можно через &config=`имя_файла_без_раширения:core` для папки core или &config=`имя_файла_без_раширения` для папки custom. 
 *
 * Кроме того, для произвольных манипуляций с данными можно использовать и &prepare.
*/

if (!defined('MODX_BASE_PATH')) {
    die('HACK???');
}
$_out = '';

$_parents = array();
$hideMain = (!isset($hideMain) || (int)$hideMain == 0);
if ($hideMain) {
    $_parents[] = $modx->config['site_start'];
}
$id = isset($id) ? $id : $modx->documentObject['id'];
$tmp = $modx->getParentIds($id);
$_parents = array_merge($_parents, array_reverse(array_values($tmp)));
foreach ($_parents as $i => $num) {
    if ($num == $modx->config['site_start'] && !$hideMain) {
        unset($_parents[$i]);
    }
}

if (isset($showCurrent) && (int)$showCurrent > 0) {
    $_parents[] = $id;
}
if (!empty($_parents) && count($_parents) >= (empty($minDocs) ? 0 : (int)$minDocs)) {
    $_options = array_merge(
        array(
            'config' => 'crumbs:core'
        ),
        !empty($modx->event->params) ? $modx->event->params : array(),
        array(
            'idType'    => 'documents',
            'sortType'  => 'doclist',
            'documents' => implode(",", $_parents)
        )
    );

    $_out = $modx->runSnippet("DocLister", $_options);
}
return $_out;
