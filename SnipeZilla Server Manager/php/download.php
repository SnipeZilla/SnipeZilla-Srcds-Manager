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
require_once 'session.start.php';
$_TOKEN=$_GET['token'];
require_once 'users.crc32.php';
require 'config.php';
$sid = $_GET['sid'];
$dir = trim(str_replace(array('../','./','..\\','.\\',"'"),array('','','','',''),$_GET['dir']));
//j
if ( !preg_match('/a|j/',$_SESSION['level']) ) die('Error: Access is denied.');

if (!preg_match('/^ftp-(\d+)$/',$sid,$m)) {
    if ( isset($server[$sid]) && (( is_array($users[$_SESSION['login']]['svr']) && in_array($server[$sid]['ip'].':'.$server[$sid]['port'], $users[$_SESSION['login']]['svr']) ) || preg_match('/a/',$_SESSION['level'])) ) {
    
            $dir = trim(implode('\\', explode( '\\', str_replace('/','\\',$dir) ) ),"\\");
            $directory = trim(implode('\\', explode( '\\', str_replace('/','\\',$server[$sid]['installdir'].'\\'.$dir) ) ),"\\");
    
    } else {
    
        die('Error: Access is denied.');
    
    }
    //triple check
    if ($directory != realpath($directory) || !$directory || !file_exists($directory)) die('Error: Access is denied.');
    header("Content-Type: application/octet-stream");
    header('Content-Length: '.filesize($directory));
    header('Content-Disposition: attachment; filename="'.substr($dir, strrpos($dir, '\\')+0).'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    readfile($directory);
} else {
    $sid=$m[1]-1;
    header("Content-Type: application/octet-stream");
    //header('Content-Length: '.filesize($directory));
    header('Content-Disposition: attachment; filename="'.substr($dir, strrpos($dir, '/')+1).'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    readfile('ftp://'.$users[$_SESSION['login']]['ftp'][$sid]['login'].':'.$_SESSION['ftp'][$sid].'@'.$dir);
}
?>
