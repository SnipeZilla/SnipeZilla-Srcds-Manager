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
    $ExecPath = '/' . preg_quote($ExecPath, '/') . '/i';
    $appName = escapeshellarg($ExecFile);
    
    $raw_output = shell_exec("powershell -Command \"Get-CimInstance Win32_Process -Filter \\\"Name='".$appName."'\\\" | Select-Object ProcessId, CommandLine | Out-String -Width 2000\"");
    $lines = explode("\n", trim($raw_output));
    
    if ( count($lines) > 2 ) {
        for ($i = 2; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
    
            if (preg_match('/^(\d+)\s+(.+)$/', $line, $matches)) {
    
                if ( preg_match($ExecPath, $matches[2]) && preg_match('/-port '.$port.'/', $matches[2]) ) {
    
                    return (int)$matches[1];
    
                }
            }
        }
    }

    //not running
    return 0;

}

function TaskMgr( $ExecFile = 'php.exe' ) {

    // Check for php.exe processes
    $raw_output = shell_exec("powershell -Command \"Get-CimInstance Win32_Process -Filter \\\"Name='php.exe'\\\" | Select-Object ProcessId, CommandLine | Out-String -Width 2000\"");

    // Explode lines and analyze
    $lines = explode("\n", trim($raw_output));
    
    // Remove the header and separator lines (first two lines)
    if ( count($lines) > 2 ) {
        for ($i = 2; $i < count($lines); $i++) {
            $line = trim($lines[$i]); // Clean any extra spaces
    
            if (preg_match('/^(\d+)\s+(.+)$/', $line, $matches)) {
    
                if ( preg_match('/.*bin\\\\SzMgr.php"?/i', $matches[2]) ) {
                    return true;

                }
            }
        }
    }

    return false;

}
