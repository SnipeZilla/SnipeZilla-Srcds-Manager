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
if ( !preg_match('/a|f/',$_SESSION['level']) ) die('Error 403 - Forbidden: Access is denied.');

require 'config.php';
$total = sizeof($server);
if (!$server[1]['ip']) exit();

$id   = $_POST['id'];
$ip   = $_POST['ip'];
$port = $_POST['port'];
$cmd  = preg_replace('/\s+/', ' ',trim($_POST['cmd']));
$dir  = $server[$id]['installdir'].'\\'.$server[$id]['game'].'\\cfg';
$cmdFile = $dir.'\sz.cmd.cfg';

if ( !preg_match('/a/',$_SESSION['level']) && 
   ( !in_array($server[$id]['ip'].':'.$server[$id]['port'], $users[$_SESSION['login']]['svr']) || $server[$id]['ip'].':'.$server[$id]['port'] != $_POST['ip'].':'.$_POST['port'] ) ) die('Error 403 - Forbidden: Access is denied.');

function makeDir($dirName, $rights=0777, $check=false) {

    if (!$dirName) return $check;

    $dirs = explode('\\', $dirName);
    $dir='';

    foreach ($dirs as $part) {

        $dir.=$part.'\\';

        if (!is_dir($dir) && strlen($dir)>0) {

            if ($check) return true;
            mkdir($dir);
            chmod($dir, $rights);

        }

    }

}

if ( !$cmd ) {

        //Delete File
        if ( file_exists($cmdFile) ) @unlink($cmdFile);
        if ( file_exists($cmdFile) ) {

            echo 'Error: CMD File could not be deleted.';
            exit;

        }
        echo '';
        exit;

} else {

        //default cmd
         $def = array( 
                      '-game'       => $server[$id]['game'],
                      '-port'       => $server[$id]['port'], 
                      '-ip'         => $server[$id]['ip']
                     );
        $cmd = preg_replace('/\s+/', ' ',trim($cmd));
        $ln = strlen($cmd);
        $start   = 0;
        $cmdLine = array();
        for ($i = 0; $i<$ln; $i++) {

            if ( $cmd[$i] == "-" || $cmd[$i] == "+" ) {
                    if ($start) {
                        $word              = explode(" ", $word);
                        $cmdLine[$word[0]] = @$word[1];
                    }
                    $start             = 1;
                    $word              = $cmd[$i];

            } elseif ($start) {
                    
                 $word .= $cmd[$i]; 

            }

        }
        if (isset($word)) {
            $word      = explode(" ", $word);
            $cmdLine[$word[0]] = @$word[1];
        }
        $cmd = '';
        foreach ( $cmdLine as $key => $value ) {
            
            if ( isset($def[$key]) ) {

                if ( ($key == "-maxplayers" || $key == "-maxplayers_override") && $value) {

                    if (isset($key)) unset($def[$key]);
                    $value = min((int)$value, $server[$id]['maxplayers']);
                    $cmd .= $key." ".$value." ";

                } elseif ($key == "+map" && $value) {

                    unset($def[$key]);
                    $cmd .= $key." ".$value." ";

                } else {

                    unset($cmdLine[$key]);

                }

            } else {

                $cmd .= $key." ".$value." ";

            }
            

        }
        $cmd = preg_replace('/\s+/', ' ',trim($cmd));

    //save file
    if ($cmd) {
        makeDir($dir);
        $file = fopen($cmdFile, "w");
        fwrite($file, $cmd);
        fclose($file);
        echo $cmd;
    } else {
        @unlink($cmdFile);
        echo '';
    }
    exit;

}
?>