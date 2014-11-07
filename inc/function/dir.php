<?php

function rm_dir($dir)
{
    $files = array_diff(scandir($dir), array('.','..'));
    foreach ($files as $file) {
        (is_dir($dir.'/'.$file)) ? rm_dir($dir.'/'.$file) : unlink($dir.'/'.$file);
    }
    return rmdir($dir);
}

function human_readable_filesize($size)
{
    $mod = 1024;
 
    $units = explode(' ','B KB MB GB TB PB');
    for ($i = 0; $size > $mod; $i++) {
        $size /= $mod;
    }
 
    return round($size, 2) . ' ' . $units[$i];
}