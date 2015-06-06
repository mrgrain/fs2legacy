<?php
namespace Frogsystem\Legacy\Controllers;

use Frogsystem\Metamorphosis\Controller;

class AdminController extends Controller
{
    public function index()
    {
        global $FD;
        include(__DIR__ . '/../admin/admin.php');
    }
}
