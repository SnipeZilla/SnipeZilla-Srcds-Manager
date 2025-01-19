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

require 'config.php';
$total = sizeof($server);
if (!$server[1]['ip']) exit();
$id       = $_POST['id'];
$ip       = $server[$id]['ip'];
$port     = $server[$id]['port'];
$dir      = $server[$id]['srcds'];
$stopFile = 'sz.stop.txt';
$srcds    = substr($dir,strrpos($dir, '\\')+1);

$info          = [];
$info['error'] = false;
$info['stop']  = file_exists($server[$id]['installdir'].'\\'.$server[$id]['game'].'\\'.$stopFile);
$info['pid']   = processID($srcds, $dir, $port);

if (!$ip || !$port || $info['stop']) {
    $info['error']=true;
    echo json_encode($info);
    exit();
}

//request format
$a_Header = 0x54;
$r_Header = 0x49;
$Payload = "Source Engine Query\0";
$r_challenge = 0x41;
//Command
$req       = pack( 'ccccc', 0xFF, 0xFF, 0xFF, 0xFF, $a_Header).$Payload;
$resp      = pack( 'c', $r_Header);
$challenge = pack( 'c', $r_challenge);
$ln        = strlen( $req );

//Data
$data = '';

//opensocket
$fp = fsockopen('udp://'.$ip, $port, $errno, $errstr, 10);

//connected
if ( !$fp || empty($fp) ) {
    //error connection
    $info['error'] = true;
    echo json_encode($info);
    exit();
} else {
    //Request AS_2INFO
    @fwrite($fp, $req, $ln);
    //Response
    stream_set_timeout($fp, 2);
    $data = @fread ($fp, 1400) ;
    $info['response_type']   = substr($data, 4, 1);
    if ( $info['response_type'] == $challenge) {
        $challenge = substr($data, 5, strlen($data));
        $req = pack( 'ccccc', 0xFF, 0xFF, 0xFF, 0xFF, $a_Header).$Payload.$challenge;
        $ln  = strlen( $req );
        @fwrite($fp, $req, $ln);
        //Response
        stream_set_timeout($fp, 2);
        $data = @fread ($fp, 1400) ;
    }
}

//close socket
@fclose($fp);

$info['response_type']   = substr($data, 4, 1);
if ( $info['response_type'] != $resp) {
    $info['error'] = true;
    echo json_encode($info);
    exit();
}
$info['network_version'] = ord(substr($data, 5, 1));
$data_array              = explode("\x00", substr($data, 6), 5);
$info['name']            = $data_array[0];
$info['map']             = $data_array[1];
$info['game']            = $data_array[2];
$info['description']     = $data_array[3];
$data                    = $data_array[4];
$unpack                  = unpack("S", substr($data, 0, 2));
$info['app_id']          = array_pop($unpack);
$info['players']         = ord(substr($data, 2, 1));
$info['playersmax']      = ord(substr($data, 3, 1));
$info['bots']            = ord(substr($data, 4, 1));
$info['status']          = 1;
$info['dedicated']       = substr($data, 5, 1); 
$info['os']              = substr($data, 6, 1); 
$info['password']        = ord(substr($data, 7, 1)); 
$info['vac']             = ord(substr($data, 8, 1)); 
$info['header']          ='';
//status
//$info=array_filter($info);
echo json_encode($info,JSON_PARTIAL_OUTPUT_ON_ERROR);

?>