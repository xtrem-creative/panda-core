<?php

if (!function_exists('str_ends_with')) {
    function str_ends_with($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }
        return (substr($haystack, -$length) === $needle);
    }
}

if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle)
    {
        return (substr($haystack, 0, strlen($needle)) === $needle);
    }
}

if (!function_exists('truncate')) {
    function truncate($str, $length)
    {
        $length = abs((int)$length);
        if(strlen($str) > $length) {
            $str = preg_replace("/^(.{1,$length})(\s.*|$)/s", '\\1...', $str);
        }
        return $str;
    }
}

if (!function_exists('json_readable_encode')) {
    function json_readable_encode($var)
    {
        if (phpversion() >= 5.4) {
            return json_encode($var, JSON_PRETTY_PRINT);
        } else {
            //JSON_PRETTY_PRINT emulation
            $result = '';
            $level = 0;
            $prevChar = '';
            $inQuotes = false;
            $endsLineLevel = null;
            $json = json_encode($var);
            $jsonLength = strlen($json);

            for ($i = 0; $i < $jsonLength; ++$i) {
                $char = $json[$i];
                $newLineLevel = null;
                $post = '';
                if ($endsLineLevel !== null) {
                    $newLineLevel = $endsLineLevel;
                    $endsLineLevel = null;
                }
                if ($char === '"' && $prevChar != '\\') {
                    $inQuotes = !$inQuotes;
                } else if (!$inQuotes) {
                    switch ($char) {
                        case '}':
                        case ']':
                            --$level;
                            $endsLineLevel = null;
                            $newLineLevel = $level;
                            break;

                        case '{':
                        case '[':
                            ++$level;
                        case ',':
                            $endsLineLevel = $level;
                            break;

                        case ':':
                            $post = " ";
                            break;

                        case " ":
                        case "\t":
                        case "\n":
                        case "\r":
                            $char = "";
                            $endsLineLevel = $newLineLevel;
                            $newLineLevel = null;
                            break;
                    }
                }
                if ($newLineLevel !== null) {
                    $result .= "\n" . str_repeat("\t", $newLineLevel);
                }
                $result .= $char . $post;
                $prevChar = $char;
            }

            return $result;
        }
    }
}