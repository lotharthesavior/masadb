<?php

namespace Helpers;

class AppHelper
{
    /**
     * Split the string by lines
     *
     * @param string $string
     *
     * @return array
     */
    public static function splitByLine(string $string): array
    {
        $vector = preg_split('/$\R?^/m', $string);

        return $vector;
    }
}
