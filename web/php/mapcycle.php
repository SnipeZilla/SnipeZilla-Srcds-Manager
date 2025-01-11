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
$_TOKEN=$_POST['token'];
require_once 'users.crc32.php';

include 'games.php';

$dir= $_POST['dir'];
$app= $_POST['app'];
if (!$app || $app == 'none') return false;
$lists = '';
$root  = exec('chdir',$o,$r);
$__DIR = preg_replace('/\\\\web\\\\php$/','',$root);
$app_dir          = $__DIR.'\apps\\'.$games[$app]['server'].'\\'.$games[$app]['game'].'\\';
$app_cfg_dir      = $app_dir.'cfg\\';
$mapcycle_dir     = $dir.'\\'.$games[$app]['game'].'\\';
$mapcycle_cfg_dir = $mapcycle_dir.'cfg\\';

if ( !file_exists($mapcycle_dir.'steam.inf') &&  file_exists($app_dir.'steam.inf') ) {

    $mapcycle_dir     = $app_dir;
    $mapcycle_cfg_dir = $app_cfg_dir;

}

$mapcycle = 'mapcycle.txt';
$mapcycle_default = 'mapcycle_default.txt';

$path = $mapcycle_dir.$mapcycle;
if ( file_exists($path) ) {

    //Open File
    $file = fopen($path, "r");
    
    //Read all lines
    while ( ($line = fgets($file)) !== false ) {
    
        $line = preg_replace('/^\s+|\n|\r|\/\/.*|\s+$/m', '', $line);
        if ($line)
        $lists .= '<span>'.trim($line).'</span>';
    
    }

}

$path = $mapcycle_dir.$mapcycle_default;
if ( file_exists($path) ) {

    //Open File
    $file = fopen($path, "r");
    
    //Read all lines
    while ( ($line = fgets($file)) !== false ) {
    
        $line = preg_replace('/^\s+|\n|\r|\/\/.*|\s+$/m', '', $line);
        if ($line && !preg_match('/\b'.trim($line).'\b/i',$lists))
        $lists .= '<span>'.trim($line).'</span>';
    
    }

}

$path = $mapcycle_cfg_dir.$mapcycle;
if ( file_exists($path) ) {

    //Open File
    $file = fopen($path, "r");
    
    //Read all lines
    while ( ($line = fgets($file)) !== false ) {
    
        $line = preg_replace('/^\s+|\n|\r|\/\/.*|\s+$/m', '', $line);
        if ($line && !preg_match('/\b'.trim($line).'\b/i',$lists))
        $lists .= '<span>'.trim($line).'</span>';
    
    }

}

$path = $mapcycle_cfg_dir.$mapcycle_default;
if ( file_exists($path) ) {

    //Open File
    $file = fopen($path, "r");
    
    //Read all lines
    while ( ($line = fgets($file)) !== false ) {
    
        $line = preg_replace('/^\s+|\n|\r|\/\/.*|\s+$/m', '', $line);
        if ($line && !preg_match('/\b'.trim($line).'\b/i',$lists))
        $lists .= '<span>'.trim($line).'</span>';
    
    }

}

echo $lists;

?>