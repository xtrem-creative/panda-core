<?php

function _hash($string, $saltPrefix = '', $saltSuffix = '', $hashAlgo = 'whirlpool') {
    if (in_array($hashAlgo, hash_algos())) {
        return hash($hashAlgo, $saltPrefix . $string . $saltSuffix);
    }
    else {
        throw new InvalidArgumentException('This hash algorithm doesn\'t exists.');
    }
}