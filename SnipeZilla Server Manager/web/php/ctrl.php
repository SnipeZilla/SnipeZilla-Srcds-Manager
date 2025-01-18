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


$ctrl = $_POST['ctrl'];
$id   = $_POST['id'];
$ip   = $_POST['ip'];
$port = $_POST['port'];
$dir  = $server[$id]['installdir'].'\\'.$server[$id]['game'];
if ( !preg_match('/a/',$_SESSION['level']) && 
   ( !in_array($server[$id]['ip'].':'.$server[$id]['port'], $users[$_SESSION['login']]['svr']) || $server[$id]['ip'].':'.$server[$id]['port'] != $_POST['ip'].':'.$_POST['port'] ) ) die('Error 403 - Forbidden: Access is denied.');
if ( !preg_match('/a|i/',$_SESSION['level']) ) die('Error 403 - Forbidden: Access is denied.');

$stopFile = 'sz.stop.txt';

function Proc($pid)
{

    //No process
    if (!$pid) return 0;

    //LookUp process
    $run = strpos( shell_exec("tasklist /fi \"PID eq ".$pid."\"" ), (string)$pid );

    //Return pid if running
    if ($run) return (int)$pid;

    //Not running
    return 0;

}

switch ($ctrl) {
    case 'stop':

        $pid = Proc($server[$id]['pid']);
        //file stop
        $file = @fopen($dir.'\\'.$stopFile, "w");
        @fclose($file);

        if ($pid) $se = shell_exec("taskkill /F /PID ".$pid."");
        echo !file_exists($dir.'\\'.$stopFile);
        echo "Server ".$id." [".$server[$id]['fname']."] stopped.";
    break;

    case 'start':
        require_once 'proc.php';

        if (file_exists($dir.'\\'.$stopFile)) {
            unlink($dir.'\\'.$stopFile);
        }

        echo file_exists($dir.'\\'.$stopFile);

        //Run SzMgr
        $root = exec('chdir',$o,$r);
        $root = preg_replace('/\\\\web.*+$/','\bin\\',$root);
        $ExecFile = 'php.exe';
        $ExecPath = 'CD /d "'.$root.'" && START "SnipeZilla Srcds Manager" "'.$root.$ExecFile.'" -f "'.$root.'SzMgr.php"';
        $SzMgr = "SnipeZilla Srcds Manager";
        if ( preg_match('/'.$SzMgr.'/i', shell_exec('schtasks /Query')) ) {
        
            if ( $task_status == 'Ready' ) {

                echo "SnipeZilla Srcds Manager is installed but not running. Server ".$id." [".$server[$id]['fname']."] cannot start.";
                break;

            }
        } else {
            $app_status = TaskMgr($ExecFile) ? 'Running' : 'Ready'; 
            if ( $app_status == 'Running' ) {

                echo "SnipeZilla Srcds Manager is not installed but it is running.";
                break;

            }
       }

       echo 'Starting: SnipeZilla Srcds Manager';
       pclose(popen($ExecPath, "r"));

    break;

    default:
        echo "error";
    break;

}
?>
