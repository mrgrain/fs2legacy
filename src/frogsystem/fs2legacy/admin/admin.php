<?php



if (POPUP !== true) {

##################################
### START OF MENU/NAVI DISPLAY ###
##################################
    echo '


<!-- Main Container -->
<div id="bg"><div id="bg_padding">

    <!-- Left Menu -->
    <div id="navi_container">';

    if ($template_navi == '') {
        $template_navi = '
        <div class="leftmenu top" style="height:43px;">
            <img src="?icons=arrow.gif" alt="->">&nbsp;<b>Hallo Admin!</b>
            <div><br>
               Herzlich<br>Willkommen
               im<br>Admin-CP des<br>Frogsystem 2!
            </div>
        </div>';
    }

    echo $template_navi;
    echo '
    </div>
    <!-- /Left Menu -->

    <!-- Content Container -->
    <div id="content_container">';

    $top = '<h2 class="cb-text">(' . $PAGE_DATA_ARR['title'] . ')</h2>';
    echo get_content_container($top, $content);

    echo '
    </div>
    <!-- /Content Container -->

</div></div>
<!-- /Main Container -->

</body>
</html>
';

##############################
### END OF CONTENT DISPLAY ###
##############################

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
