<?php

if (!function_exists('rm_dir')) {
    function rm_dir($dir)
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir($dir . '/' . $file)) ? rm_dir($dir . '/' . $file) : unlink($dir . '/' . $file);
        }
        return rmdir($dir);
    }
}