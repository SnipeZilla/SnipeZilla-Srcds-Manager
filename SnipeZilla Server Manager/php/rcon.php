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
include 'rfile.php';
if ( !preg_match('/a|g/',$_SESSION['level']) ) die('Error 403 - Forbidden: Access is denied.');

require 'config.php';
$total = sizeof($server);
if (!$server[1]['ip']) exit();

$id   = $_POST['id'];
$ip   = $_POST['ip'];
$port = $_POST['port'];
$rcon = preg_replace('/\s+/', ' ',trim($_POST['rcon']));


if ( !preg_match('/a/',$_SESSION['level']) && 
   ( !in_array($server[$id]['ip'].':'.$server[$id]['port'], $users[$_SESSION['login']]['svr']) || $server[$id]['ip'].':'.$server[$id]['port'] != $_POST['ip'].':'.$_POST['port'] ) ) die('Error 403 - Forbidden: Access is denied.');

if ( preg_match('/^quit\s?/i',$rcon) && !preg_match('/a|i/',$_SESSION['level']) ) die('Error 403 - Forbidden: Access is denied.');

$dir = $server[$id]['installdir'].'\\'.$server[$id]['game'];
$rcon_password = rFile($server[$id]['installdir'].'\\'.$server[$id]['game'].'\\'.$games[$server[$id]['appid']]['config'],'rcon_password');
if (!$rcon_password ) {

    if ( empty($server[$id]['rcon_password']) ) {
        echo 'Error: rcon password not defined!';
        exit;
    }

    $rcon_password = $server[$id]['rcon_password'];
}


//opensocket
$fp = fsockopen($server[$id]['ip'], $server[$id]['port'], $errno, $errstr, 1);

//connected
if ( !$fp || empty($fp) ) {

    //error connection
    echo $errno.' '.$errstr;
    exit;

} else {
    //Data
    $data = pack("VV", 1, 3).$rcon_password."\x00\x00\x00";
    $data = pack("V",strlen($data)).$data;
    $ln   = strlen( $data );

    //Request AS_2INFO
    @fwrite($fp, $data, $ln);

    //Verify Password
    stream_set_timeout($fp, 5);
    $r = @fread ($fp, 1400) ; //empty
    $r = @fread ($fp, 1400) ;

    //Size get Long string
    $size = substr($r, 0, 4);
    $r    = substr($r, 4);//trunc $r
    $size = @unpack('Vdata', $size);

    //ID get Long string
    $id   = substr($r, 0, 4);
    $r    = substr($r, 4);//trunc $r
    $id   = @unpack('Vdata', $id);

    //Quit?
    if ( preg_match('/^quit\s?/i',$rcon) ) {

        $file = fopen($dir.'\\sz.stop.txt', "w");
        fclose($file);

    }

    //Send rcon
    $data = pack ("VV", 2, 2).$rcon."\x00\x00\x00" ;
    $data = pack ("V", strlen ($data)).$data ;

    fwrite ($fp, $data, strlen($data)) ;
	$resp='';
    do {

        $fr     = fread ($fp, 4096) ;
        $resp  .= $fr;

    } while ($fr);

    //close socket
    @fclose($fp);
    echo "<pre>";
    print_r(str_replace(array('<','>'),array('&lt;','&gt;'),$resp));
    echo "</pre>";

}


?>