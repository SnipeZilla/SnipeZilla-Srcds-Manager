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
$copy = isset($_POST['xcopy'])?$_POST['xcopy']:false;
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

        // Check for php.exe processes
        $raw_output = shell_exec("powershell -Command \"Get-CimInstance Win32_Process -Filter \\\"Name='php.exe'\\\" | Select-Object ProcessId, CommandLine | Out-String -Width 2000\"");

        // Explode lines and analyze
        $lines = explode("\n", trim($raw_output));
		
        // Remove the header and separator lines (first two lines)
        if ( count($lines) > 2 ) {
            for ($i = 2; $i < count($lines); $i++) {
                $line = trim($lines[$i]); // Clean any extra spaces
		
                if (preg_match('/^(\d+)\s+(.+)$/', $line, $matches)) {
		
                    if ( preg_match('/.*bin\\\\xcopy.php"?/i', $matches[2]) && $copy != 'false' ) {

		                echo 'xcopy is already running';
                        exit();
		
                    }
                }
            }
        }

        //Check for xcopy
        if ( !file_exists($pathINF_1) && file_exists($pathINF_0) && $copy != 'false' ) {

            //Path to xcopy.php
            $xcopy = $__DIR.'\bin\php.exe';
            $cmd = '-f \"'.$__DIR.'\bin\xcopy.php\" \"'.$app_dir.'\" \"'.$server[$par]['installdir'].'\" \"'.$server[$par]['game'].'\" \"0\" \"1\"';
		    
            //Run xcopy
            $command = "powershell -Command \"\$process = Start-Process -FilePath '".$xcopy."' -ArgumentList '".$cmd."' -WorkingDirectory '".$__DIR."' -PassThru; \$process.Id\"";
		    
            if ( (int)shell_exec($command) ) {;
		    
                echo "Copying new game: ".$games[$server[$par]['appid']]['server'];;
                exit();
		    
            }
        }
        //Steamcmd Update folder
        $steamcmd = $server[0]['steamcmddir']; 
        $cmd='+force_install_dir "'.$server[$par]['installdir'].'" +login '.$server[$par]['login'].' '.$games[$server[$par]['appid']]['app_set_config'].' +app_update '.$games[$server[$par]['appid']]['server'].' validate +quit';

        $raw_output = shell_exec("powershell -Command \"Get-CimInstance Win32_Process -Filter \\\"Name='steamcmd.exe'\\\" | Select-Object ProcessId \"");
        if ( preg_match('/\d+/', $raw_output, $matches) ) {
            echo 'SteamCMD is already running';
            exit();
        }
 
       //Exec SteamCMD
        $command = "powershell -Command \"\$process = Start-Process -FilePath '".$steamcmd."' -ArgumentList '".$cmd."'  -PassThru; \$process.Id\"";
        if ( (int)shell_exec($command) ) {

            echo "Updating app: ".$games[$server[$par]['appid']]['server'];
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

        $raw_output = shell_exec("powershell -Command \"Get-CimInstance Win32_Process -Filter \\\"Name='steamcmd.exe'\\\" | Select-Object ProcessId \"");
        if ( preg_match('/\d+/', $raw_output, $matches) ) {
            echo 'SteamCMD is already running';
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
        $steamcmd = $server[0]['steamcmddir'].' +force_install_dir \"'.$app_dir.'\" +login '.$login.' '.$games[$par]['app_set_config'].' +app_update '.$games[$par]['server'].' validate +quit';

        //Exec SteamCMD
        $command = "powershell -Command \"\$process = Start-Process -FilePath '".$steamcmd."' -PassThru; \$process.Id\"";
        if ( preg_match('/ProcessId=([0-9]+)/' , preg_replace('/\s+/', '', shell_exec($command))) ) {

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