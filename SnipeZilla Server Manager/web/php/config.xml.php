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
if ( !preg_match('/a|b/',$_SESSION['level']) ) die('Error 403 - Forbidden: Access is denied.');

require 'Array2XML.php';

function Error($msg) {

    echo $msg;
    exit();

}
$path = __DIR__ . '..\..\..\config.xml';
$str = json_decode($_POST["s"],true);
$str = $str[0];
if (!$str || empty($str)) Error('No data receive. Browser error');
$xml = Array2XML::createXML('config', $str);
$cfg = $xml->saveXML();

//Basic validation:
if ( !file_exists($str['steamcmd']['steamcmddir']) ||
     !preg_match('/(steamcmd.exe)$/i', $str['steamcmd']['steamcmddir']) ) {

        Error('Error: SteamCMD Location (steamcmd.exe) is missing.');

}
$total = (isset($str['server'])?sizeof($str['server']):0);
for ($i=0; $i<$total; $i++) {

    //game selection
    if ( $str['server'][$i]['@attributes']['appid']=='none') {
    
            Error('Error->server #'.($i+1).': No game selected!');
    
    }

    //Install dir with steamcmd.exe?
    if ( $str['server'][$i]['installdir'] == str_replace('\steamcmd.exe','',$str['steamcmd']['steamcmddir']) ) {

        Error('Error->server #'.($i+1).': Install Dir should not containt steamcmd.exe!');

    }

    //Install Dir
    if ( !is_dir($str['server'][$i]['installdir']) ) {
    
            Error('Error->server #'.($i+1).': Install Dir Missing');
    
    }

    //IP
    if ( !$str['server'][$i]['ip'] ) {
    
            Error('Error->server #'.($i+1).': IP Missing');
    
    }

    //PORT
    if ( !$str['server'][$i]['port'] ) {
    
            Error('Error->server #'.($i+1).': PORT Missing');
    
    }

    //install dir or Port defined?
    for ($ii=0; $ii<$i; $ii++) {

        if ( $str['server'][$i]['installdir'] == $str['server'][$ii]['installdir'] ) {

            Error('Error->server #'.($i+1).': Install Dir already selected in server #'.($ii+1));

        }

        if ( $str['server'][$i]['ip'].':'.$str['server'][$i]['port'] == $str['server'][$ii]['ip'].':'.$str['server'][$ii]['port'] ) {

            Error('Error->server #'.($i+1).': Port already defined in server #'.($ii+1));

        }        

    }

}

//Save
$file = fopen($path, "w");
fwrite($file, $cfg);
fclose($file);
echo 'Success';

?> 
