<?php
// Get save keyword
$keyword = isset($_REQUEST['keyword']) ? trim($_REQUEST['keyword']) : '';

// Display Mini Search
$template = new template();
$template->setFile('0_search.tpl');
$template->load('APPLET');
$template->tag('keyword', $keyword);
$template = $template->display();

