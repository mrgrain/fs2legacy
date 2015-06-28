<?php



if (POPUP !== true) {

} else {
######################################
### START OF DISPLAY POPUP CONTENT ###
######################################
    $JUST_CONTENT = false;

    ob_start();
    require(FS2ADMIN . '/' . $PAGE_DATA_ARR['file']);
    $popup = ob_get_clean();

    if ($JUST_CONTENT !== true) {

        echo $head;
        echo '
<body id="find_body">
    <div id="find_head">
        &nbsp;<img border="0" src="?images=pointer.png" alt="->" class="middle">&nbsp;
        <strong>' . $PAGE_DATA_ARR['title'] . '</strong>
    </div>
    <div align="left">
';

        echo $popup;

        echo '
    </div>
</body>
</html>
';
    } else {
        echo $popup;
    }

####################################
### END OF DISPLAY POPUP CONTENT ###
####################################
}

?>
