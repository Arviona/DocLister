<?php
require_once(MODX_BASE_PATH . 'assets/cache/dl_autoload.php');

/**
 * Class modPlugin
 */
class modPlugin extends autoTable
{
    /**
     * @var string
     */
    protected $table = "site_plugins";
}
