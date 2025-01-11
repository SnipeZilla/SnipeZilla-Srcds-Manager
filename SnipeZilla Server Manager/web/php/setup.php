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

$fct   = $_POST['fct'];
$par   = $_POST['par'];
$login     = isset($_POST['login'])?$_POST['login']:'';
$server_id = isset($_POST['server_id'])?$_POST['server_id']:'';
$root  = exec('chdir',$o,$r);
$__DIR = preg_replace('/\\\\web\\\\php$/','',$root);
switch ($fct) {

    case 'folder':

        $dirs = explode('\\', $par);
        $dir='';

        foreach ($dirs as $part) {

            $dir.=$part.'\\';

            if (!is_dir($dir) && strlen($dir)>0) {

                mkdir($dir);
                chmod($dir, '0777');
                if (is_dir($dir)) {
                    echo 'Success';
                    return true;
                }
            }

        }

        echo 'Error: Folder couldn\'t be created';

    break;

    case 'steamcmd':
        $url = 'http://media.steampowered.com/installer/steamcmd.zip';
        $file = $par.'\steamcmd.zip';

        if ( !copy($url,$file) ) {
        
            echo 'ERROR: Unable to download SteamCMD '.$file;
            exit();
        
        }
        
        $zip = new ZipArchive;
        $res = $zip->open($file);
        if ($res == true) {
        
            $zip->extractTo($par);
            $zip->close();
            if (file_exists($par.'\steamcmd.exe')) {
        
                echo 'Success';
        
            } else {
        
                echo 'ERROR: SteamCMD.exe not found';
        
            }
        
        } else {

            echo 'Could not open steamcmd.zip';

        }

    break;

    case 'update':
        include 'config.php';
        //Check Path
        if ( !file_exists( $server[0]['steamcmddir'] ) ||
             !file_exists( $server[$par]['installdir'] ) ||
             !is_dir( $server[$par]['installdir'] ) ) {

            echo "Path not found";
            exit();
        }

        if (!$server[$par]['login']) $server[$par]['login'] = 'anonymous';
        //apps folder
        $app_dir   = $__DIR.'\apps\\'.$games[$server[$par]['appid']]['server'];
        $pathINF_0 = $app_dir.'\\'.$server[$par]['game'].'\steam.inf';
        //game folder
        $pathINF_1 = $server[$par]['installdir'].'\\'.$server[$par]['game'].'\steam.inf';

        $proc = preg_split('/\w+=/', shell_exec('wmic process where name="php.exe" get ProcessId, Commandline /FORMAT:LIST'),-1, PREG_SPLIT_NO_EMPTY);
        
        $numb = sizeof($proc);
        for ($i=1; $i<$numb; $i++) {
        
            if ( preg_match('/.*bin\\\\xcopy.php"?/i', $proc[$i]) ) {
        
		        echo 'xcopy is already running';
                exit();

            }
        
        }

        //Check for xcopy
        if ( !file_exists($pathINF_1) && file_exists($pathINF_0) ) {

            $xcopy = $__DIR.'\bin\php.exe -f \"'.$__DIR.'\bin\xcopy.php\" \"'.$app_dir.'\" \"'.$server[$par]['installdir'].'\" \"'.$server[$par]['game'].'\" \"0\" \"1\"';

            //Run xcopy
            if ( preg_match('/ProcessId=([0-9]+)/' , preg_replace('/\s+/', '', shell_exec('wmic process call create "'.$xcopy.'","'.$__DIR.'"')), $matches) ) {

                echo "Copying new game...";
                exit();

            }

        }

        //Steamcmd Update folder
        $steamcmd = $server[0]['steamcmddir'].' +login '.$server[$par]['login'].' +force_install_dir \"'.$server[$par]['installdir'].'\"'.$games[$server[$par]['appid']]['app_set_config'].' +app_update '.$games[$server[$par]['appid']]['server'].' validate +quit';

        $run = shell_exec('wmic process list brief | find /i "steamcmd.exe"');
        if ($run) {

            echo 'SteamCMD is already running';
            exit();

        }
 
       //Exec SteamCMD
        if ( preg_match('/ProcessId=([0-9]+)/' , preg_replace('/\s+/', '', shell_exec('wmic process call create "'.$steamcmd.'"'))) ) {

            echo "Updating...";
            exit();

        }
        echo "Error: Update crashed.";

    break;

    case 'apps':
        include 'config.php';
        //Check Path
        if ( !file_exists( $server[0]['steamcmddir'] ) ) {

            echo "Error: steamcmd.exe not found";
            exit();

        }

        $run = shell_exec('wmic process list brief | find /i "steamcmd.exe"');
        if ($run) {

            echo 'Error: SteamCMD is already running';
            exit();

        }

        if (!trim($login)) $login = 'anonymous';
        if ( !$server_id || !$par || !isset($game[$par]) ) {
            echo 'not available';
            exit();
        }

        //apps folder
        $app_dir   = $__DIR.'\apps\\'.$server_id;
        if ( !file_exists($__DIR.'\apps') || !is_dir($__DIR.'\apps') ) {
            mkdir($__DIR.'\apps');
            chmod($__DIR.'\apps', 0777);
        }
        if ( !file_exists($app_dir) || !is_dir($app_dir) ) {
            mkdir($app_dir);
            chmod($app_dir, 0777);
        }
        //Steamcmd Update folder
        $steamcmd = $server[0]['steamcmddir'].' +login '.$login.' +force_install_dir \"'.$app_dir.'\"'.$games[$par]['app_set_config'].' +app_update '.$games[$par]['server'].' validate +quit';
 
        //Exec SteamCMD
        if ( preg_match('/ProcessId=([0-9]+)/' , preg_replace('/\s+/', '', shell_exec('wmic process call create "'.$steamcmd.'"'))) ) {

            echo "Updating ".$games[$par]['name'];
            exit();

        }
        echo "Error: steamcmd crashed.";

    break;

    default:
        echo 'ERROR: Unknow command';
    break;

}

?>