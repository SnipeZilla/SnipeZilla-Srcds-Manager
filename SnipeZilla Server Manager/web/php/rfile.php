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
function rFile($path = '', $word = "", $com = false) {

    if ( !file_exists($path) ) return false;

    //Open File
    $file = fopen($path, "r");

    //Read all lines
    $lines = '';
    while ( ($line = fgets($file)) !== false ) {

        //!comments
        if (!$com) {

            $line = preg_replace('/^\s+|\n|\r|\/\/.*|\s+$/m', '', $line);

        } else {

            $line = preg_replace('/^\s+|\n|\r|\s+$/m', '', $line);

        }
        //appends po search word
        if ( $line ) {
            //XXX_yyyy || XXXyyyy ?
            if ( $word && preg_match('/('.$word.'|'.str_replace('_','',$word).')\s+"?([^"]+)/i', $line, $matches) ) {

                //variable found
                return trim($matches[2]); 

            } else {

                //appends lines
                $lines .= $line." ";

            }

        }

    }

    //Close file
    fclose($file);

    //No word found
    if ($word) return false;


    //Return 1st line
    return trim($lines);

}