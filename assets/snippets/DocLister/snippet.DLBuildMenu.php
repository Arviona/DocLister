<?php
/**
 * Comments by Aharito http://aharito.ru
 * 
 * Параметры:
 
 * parents - родительская (начальная) папка
 * maxDepth - макс. глубина
 * addWhereList - Условия выборки документов для всех уровней. По умолчанию c.hidemenu = 0
 * addWhereListN - Условия выборки документов N уровня
 * orderBy - Условия сортировки документов всех уровней
 * orderByN - Условия сортировки документов N уровня
 
 * Также, можно использовать и prepare, но для prepare уже имеется обязательный вызов DLFixedPrepare::buildMenu.
 * Поэтому используются параметры BeforePrepare и AfterPrepare - получение prepare сниппетов из параметров BeforePrepare и AfterPrepare
 * для совмещения с обязательным вызовом DLFixedPrepare::buildMenu метода.

 * Шаблоны.
 * Задаются по правилам DL, то есть могут быть и инлайн-шаблонами, и именами чанков, или загружаться из файла, документа MODx, конфига, глобального плейсхолдера.
 *
 * TplMainOwner  Основной шаблон обертка
 * TplSubOwner   Шаблон обертка для вложенных уровней
 * TplOwnerN     Шаблон обертка для N уровня вложенности
 
 * TplOneItem              Основной шаблон для каждого пункта меню всех уровней
 * noChildrenRowTPL        Общий шаблон пункта меню без дочерних элементов
 
 * TplDepthN               Шаблон пункта меню вложенности N
 * TplNoChildrenDepthN     Шаблон пункта меню вложенности N без дочерних элементов
 
 * TplCurrent              шаблон текущего пункта меню.
 * TplCurrentN             шаблон текущего пункта меню имеющего вложенные пункты. Где N это номер уровня вложенности.
 * TplCurrentNoChildrenN   шаблон текущего пункта меню, не имеющего вложенные пункты. Где N это номер уровня вложенности.
 
 * Плейсхолдеры.
 * Доступны плейсхолдеры dl.wrap, dl.submenu, dl.currentDepth. Назначение понятно из названий.
 * Доступны и остальные плейсхолдеры DocLister, например title и url, поля документа типа pagetitle и longtitle, а также dl.class, dl.active и т.д.
 *
 * Следует иметь в виду, что плейсхолдер dl.class переопределён в методе DLFixedPrepare::buildMenu.
 * Поэтому в DLBuildMenu, помимо "обычных" для этого плейсхолдера классов first, last, odd, even и current, данный плейсхолдет добавляет ещё и класс active для родительских элементов всех уровней текущего элемента меню.
 *
 * Кстати, вот здесь http://modx.im/blog/addons/1299.html#comment24544 есть упоминание и про параметры DL для переопределения классов: lastClass, currentClass, firstClass, oddClass
 *
*/

if ( ! defined('MODX_BASE_PATH')) {
    die('HACK???');
}

include_once(MODX_BASE_PATH . 'assets/lib/APIHelpers.class.php');
include_once(MODX_BASE_PATH . 'assets/snippets/DocLister/lib/DLFixedPrepare.class.php');

$p = &$modx->event->params;
if ( ! is_array($p)) {
    $p = array();
}

/** Текущий уровень вложенности */
$p['currentDepth'] = $currentDepth = \APIhelpers::getkey($p, 'currentDepth', 1);


/** Основной шаблон обертка */
$p['TplMainOwner'] = \APIhelpers::getkey($p, 'TplMainOwner',
    '@CODE:<ul id="nav" class="menu level-1">[+dl.wrap+]</ul>'
);

/** Шаблон обертка для вложенных уровней */
$p['TplSubOwner'] = \APIhelpers::getkey($p, 'TplSubOwner',
    '@CODE:<ul class="sub-menu level-[+dl.currentDepth+]">[+dl.wrap+]</ul>'
);

/**
 * TplOwnerN    Шаблон обертка для N уровня вложенности
 * TplMainOwner Основной шабон обертка
 * TplSubOwner  Шаблон обертка для вложенных уровней
 */
$currentOwnerTpl = \APIhelpers::getkey($p, 'TplOwner' . $currentDepth);
if (empty($currentOwnerTpl)) {
    $currentOwnerTpl = \APIhelpers::getkey($p, (($currentDepth == 1) ? 'TplMainOwner' : 'TplSubOwner'));
}


/** Основной шаблон для каждого пункта меню всех уровней */
$p['TplOneItem'] = $currentTpl = \APIhelpers::getkey($p, 'TplOneItem',
    '@CODE:<li id="menu-item-[+id+]" class="menu-item [+dl.class+]">
        <a href="[+url+]" title="[+e.title+]">[+title+]</a>
        [+dl.submenu+]
    </li>'
);

/**
 *   TplDepthN               Шаблон пункта меню вложенности N
 *   TplNoChildrenDepthN     Шаблон пункта меню вложенности N без дочерних элементов
 *   noChildrenRowTPL        Общий шаблон пункта меню без дочерних элементов
 */
$currentTpl = \APIhelpers::getkey($p, 'TplDepth' . $currentDepth, $currentTpl);
$currentNoChildrenTpl = \APIhelpers::getkey($p, 'TplNoChildrenDepth' . $currentDepth);
if (empty($currentNoChildrenTpl)) {
    $currentNoChildrenTpl = \APIhelpers::getkey($p, 'noChildrenRowTPL', $currentTpl);
}


/** Условия выборки документов для всех уровней */
$p['addWhereList'] = $currentWhere = \APIhelpers::getkey($p, 'addWhereList', 'c.hidemenu = 0');
/** addWhereListN   Условия выборки документов N уровня */
$currentWhere = \APIhelpers::getkey($p, 'addWhereList' . $currentDepth, $currentWhere);

$p['orderBy'] = $currentOrderBy = \APIhelpers::getkey($p, 'orderBy', 'menuindex ASC, id ASC');
/** orderByN   Условия сортировки документов N уровня */
$currentOrderBy = \APIhelpers::getkey($p, 'orderBy' . $currentDepth, $currentOrderBy);


$p['tvList'] = $currentTvList = \APIhelpers::getkey($p, 'tvList');
$currentTvList  = \APIhelpers::getkey($p, 'tvList' . $currentDepth, $currentTvList);
/**
 * Получение prepare сниппетов из параметров BeforePrepare и AfterPrepare
 * для совмещения с обязательным вызовом DLFixedPrepare::buildMenu метода
 */
$prepare = \APIhelpers::getkey($p, 'BeforePrepare', '');
$prepare = explode(",", $prepare);
$prepare[] = 'DLFixedPrepare::buildMenu';
$prepare[] = \APIhelpers::getkey($p, 'AfterPrepare', '');
$p['prepare'] = trim(implode(",", $prepare), ',');

return $modx->runSnippet('DocLister', array_merge(array(
        'idType'  => 'parents',
        'parents' => \APIhelpers::getkey($p, 'parents', 0),
    ), $p, array(
        'params'           => $p,
        'tpl'              => $currentTpl,
        'ownerTPL'         => $currentOwnerTpl,
        'mainRowTpl'       => $currentTpl,
        'noChildrenRowTPL' => $currentNoChildrenTpl,
        'noneWrapOuter'    => '0',
        'addWhereList'     => $currentWhere,
        'orderBy'          => $currentOrderBy
    ))
);
