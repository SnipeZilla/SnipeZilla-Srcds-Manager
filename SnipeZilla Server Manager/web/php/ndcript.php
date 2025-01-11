<?php
/*
───────────────────────────────────────────────────────────────────────────
SnipeZilla Srcds Manager
───────────────────────────────────────────────────────────────────────────
Copyright (C) 2015 SnipeZilla.com

SnipeZilla Srcds Manager is free software:
you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

SnipeZilla Srcds Manager is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with SnipeZilla Srcds Manager.
If not, see <http://www.gnu.org/licenses/>.

───────────────────────────────────────────────────────────────────────────
- contact:                   https://steamcommunity.com/profiles/76561197960637077
- Forum:                     https://www.snipezilla.com
- Steam Group:               https://steamcommunity.com/groups/snipezilla
- Installation Guide & Help: https://www.snipezilla.com/snipezilla-srcds-manager
───────────────────────────────────────────────────────────────────────────
*/
function encrypt($string) {
    $key = 'Sz-}o j=t06{@=,k9o@mqs ^8z9-m@3no~(0l# j^ _4c:- ~e~8z.@(_3`_(l@3{`+M8';
    $result = '';
    for($i=0; $i<strlen($string); $i++) {
        $char = substr($string, $i, 1);
        $keychar = substr($key, ($i % strlen($key))-1, 1);
        $ordChar = ord($char);
        $ordKeychar = ord($keychar);
        $sum = $ordChar + $ordKeychar;
        $char = chr($sum);
        $result.=$char;
    }
    return base64_encode($result);
}

function decrypt($string) {
    $key = 'Sz-}o j=t06{@=,k9o@mqs ^8z9-m@3no~(0l# j^ _4c:- ~e~8z.@(_3`_(l@3{`+M8';
    $result = '';
    $string = base64_decode($string);
    for($i=0; $i<strlen($string); $i++) {
        $char = substr($string, $i, 1);
        $keychar = substr($key, ($i % strlen($key))-1, 1);
        $ordChar = ord($char);
        $ordKeychar = ord($keychar);
        $sum = $ordChar - $ordKeychar;
        $char = chr($sum);
        $result.=$char;
    }
    return $result;
}