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
if (!$_SESSION['local']) { echo 'Function not available.'; exit; }

include 'config.php';

$type = $_POST['type'];
$id   = $_POST['id'];

switch($type) {

    case 'folder':

        if ( file_exists($server[$id]['installdir'].'\\'.$server[$id]['game']) ) {

            exec('start /max explorer '.$server[$id]['installdir'].'\\'.$server[$id]['game']);

        } elseif ( file_exists($server[$id]['installdir']) ) {

            exec('start /max explorer '.$server[$id]['installdir']);

        } else { echo 'Directory not found'; }

    break;

    case 'cfg':

        $dirName = $server[$id]['installdir'].'\\'.$server[$id]['game'];
        $cfg     = $server[$id]['installdir'].'\\'.$server[$id]['game'].'\\'.$games[$server[$id]['appid']]['config'];

        if ( file_exists($server[$id]['installdir'] ) && !file_exists($cfg) ) {
            $dirs = explode('\\', preg_replace('/\\\\[^\\\\]+$/','',$cfg));
            $dir='';

            foreach ($dirs as $part) {

                $dir.=$part.'\\';

                if (!is_dir($dir) && strlen($dir)>0) {

                    mkdir($dir);
                    chmod($dir, 0777);

                }

            }
            $file = fopen($cfg, "w");
            fwrite($file, 'rcon_password "Sz!'.mt_rand(0, 9).mt_rand(0, 9).mt_rand(0, 9).mt_rand(0, 9).'" //RCON Password');
            fclose($file);

        }

        if ( file_exists($cfg) ) {

            echo 'Opening: '.$cfg;
            exec('explorer '.$cfg);

        } else { echo 'File not found'; }

    break;

    case 'log':
        $dir = __DIR__ . '..\..\..\..\logs';
        if ( file_exists($dir) ) {

            exec('start /max explorer '.$dir);

        } else { echo 'No logs.'; }

    break;
    case 'apps':
        $dir = __DIR__ . '..\..\..\..\apps';
        if ( !file_exists($dir) ) {
                mkdir($dir);
                chmod($dir, '0777');

        }
        exec('start /max explorer '.$dir);
    break;
    default: echo 'Unknow command';
    break;
}

?>