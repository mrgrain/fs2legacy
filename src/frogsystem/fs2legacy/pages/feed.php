<?php
require_once(FS2SOURCE . '/lib/class_Feed.php');
$feed = FS2SOURCE . '/feeds/' . $_GET['xml'] . '.php';
if (file_exists($feed)) {
    header('Content-type: application/xml');
    include($feed);
    exit;
} else {
    $template = sys_message($FD->text('frontend', 'systemmessage'), $FD->text('frontend', 'file_not_found'), 404);
}
?>
