<?php
namespace Frogsystem\Legacy;

use Frogsystem\Metamorphosis\Kernels\WebApplicationKernel;

/**
 * Class Kernel
 * @package frogsystem\metamorphosis
 */
class Kernel extends WebApplicationKernel
{
    /**
     * @var array
     */
    protected $middleware = [
        'Frogsystem\Legacy\UrlMiddleware',
        'Frogsystem\Legacy\AliasMiddleware',
    ];

    /**
     * @var array
     */
    protected $pluggables = [
        'Frogsystem\Legacy\ServiceProvider',
        'Frogsystem\Legacy\GlobalData',
        'Frogsystem\Legacy\Routes',
    ];
}
