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
require_once 'users.crc32.php';

function processID( $ExecFile = 'srcds.exe', $ExecPath = '', $port = '') {

    if (!$ExecPath) return 0;

    $ExecPath = '/'.str_replace('\\','\\\\',$ExecPath).'/i'; 
    $proc = preg_split('/\w+=/', shell_exec('wmic process where name="'.$ExecFile.'" get ProcessId, commandLine /FORMAT:LIST'),-1, PREG_SPLIT_NO_EMPTY);
    $numb = sizeof($proc)-1;
    for ($i = 1; $i<$numb; $i++) {

        if ( preg_match($ExecPath, $proc[$i]) && preg_match('/-port '.$port.'/i', $proc[$i]) ) {

            return (int)$proc[$i+1];

        }

    }

    //not running
    return 0;

}

function TaskMgr( $ExecFile = 'php.exe' ) {

    $cmd = preg_split('/commandLine=/i', shell_exec('wmic process where name="'.$ExecFile.'" get commandLine /FORMAT:LIST'),-1, PREG_SPLIT_NO_EMPTY);

    $numb = sizeof($cmd);
    for ($i=1; $i<$numb; $i++) {
        if ( preg_match('/.*\\\\bin\\\\SzMgr.php"?/i', $cmd[$i]) ) {

            return true;

        }

    }
    return false;

}
