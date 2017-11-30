<?php
function list_themes()
{
    $list = [];
    $dir  = opendir(INSTALL_PATH.FOLDER_CSS);
    while (($fileCSS = readdir($dir)) !== false) {
        if ($fileCSS != '.' && $fileCSS != '..' && $fileCSS != 'ossature.css' && $fileCSS != 'fileuploader.css' && $fileCSS != 'ossature_print.css')
        $list[] = $fileCSS;
    }
    sort($list, SORT_STRING);
    return $list;
}
