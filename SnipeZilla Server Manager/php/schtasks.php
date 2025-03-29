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
if ( !preg_match('/a|c/',$_SESSION['level']) ) die('Error 403 - Forbidden: Access is denied.');

require 'Array2XML.php';

$task = $_POST['task'];
$sys  = $_POST['sys'] == 'true';
$root = exec('chdir',$o,$r);
$root = preg_replace('/\\\\web.*+$/','\\',$root).'bin';
$SzMgr = "SnipeZilla Srcds Manager";
$query = array('','');

// Domain\Name
if ($task == 'create' || $task == 'createxp') {

    if (!$sys) {

        $user = trim(shell_exec('whoami'));

        if (!$user && $task != 'createxp') {

            echo "Not possible";
            exit();

        }

    } else {

        $user = 'S-1-5-18';

    }

}

//
function QueryTask($SzMgr) {

    if ( preg_match('/'.$SzMgr.'/i', shell_exec('schtasks /Query')) ) {

        // Check for php.exe processes
        $raw_output = shell_exec("powershell -Command \"Get-CimInstance Win32_Process -Filter \\\"Name='php.exe'\\\" | Select-Object ProcessId, CommandLine | Out-String -Width 2000\"");
	    
        // Explode lines and analyze
        $lines = explode("\n", trim($raw_output));
        
        // Remove the header and separator lines (first two lines)
        if ( count($lines) > 2 ) {
            for ($i = 2; $i < count($lines); $i++) {
                $line = trim($lines[$i]); // Clean any extra spaces
        
                if (preg_match('/^(\d+)\s+(.+)$/', $line, $matches)) {
        
                    if ( preg_match('/.*bin\\\\SzMgr.php"?/i', $matches[2]) ) {
                        return 'Running';
	    
                    }
                }
            }
        }

        return 'Ready';

    
    } else {
    
        return 'Create';
    
    }

}

function KScript() {

    // Check for php.exe processes
    $raw_output = shell_exec("powershell -Command \"Get-CimInstance Win32_Process -Filter \\\"Name='php.exe'\\\" | Select-Object ProcessId, CommandLine | Out-String -Width 2000\"");
    
    // Explode lines and analyze
    $lines = explode("\n", trim($raw_output));
    
    // Remove the header and separator lines (first two lines)
    if ( count($lines) > 2 ) {
        for ($i = 2; $i < count($lines); $i++) {
            $line = trim($lines[$i]); // Clean any extra spaces
    
            if (preg_match('/^(\d+)\s+(.+)$/', $line, $matches)) {
    
                if ( preg_match('/.*bin\\\\SzMgr.php"?/i', $matches[2]) ) {
                    shell_exec("taskkill /F /PID ".$matches[1].""); 
                    break;
                }
    
            }
        }
    }

}

switch ($task) {

    case 'create' :
        KScript();
        $Task = array(
            '@attributes' => array(
                'version' => '1.2',
                'xmlns'   => 'http://schemas.microsoft.com/windows/2004/02/mit/task'
            ),
            'RegistrationInfo' => array(
                'Author'      => 'Snipezilla',
                'Description' => 'SnipeZilla Srcds Manager: Update and monitor multiple instances of srcds.'
            ),
            'Triggers' => array(
                'BootTrigger' => array(
                    'StartBoundary' => '2012-07-04T15:00:00',
                    'Enabled'       => 'true'
                )
            ),
            'Principals' => array(
                'Principal' => array(
                    '@attributes' => array(
                        'id' => 'Author'
                    ),
                    'UserId'   => $user,
                    'RunLevel' => 'LeastPrivilege'
               )
            ),
            'Settings' => array(
                'MultipleInstancesPolicy'    => 'IgnoreNew',
                'DisallowStartIfOnBatteries' => 'false',
                'StopIfGoingOnBatteries'     => 'false',
                'AllowHardTerminate'         => 'true',
                'StartWhenAvailable'         => 'false',
                'RunOnlyIfNetworkAvailable'  => 'false',
                'IdleSettings'               => array(
                        'StopOnIdleEnd' => 'true',
                        'RestartOnIdle' => 'false'
                ),
                'AllowStartOnDemand'         => 'true',
                'Enabled'                    => 'true',
                'Hidden'                     => 'false',
                'RunOnlyIfIdle'              => 'false',
                'WakeToRun'                  => 'false',
                'ExecutionTimeLimit'         => 'PT0S',
                'Priority'                   => '7',
            ),
            'Actions' => array(
                '@attributes' => array(
                    'Context' => 'Author'
                ),
                'Exec' => array(
                    'Command'          => $root.'\php.exe',
                    'Arguments'        => '-f "'.$root.'\SzMgr.php"',
                    'WorkingDirectory' => $root
                )
            )
        );
        Array2XML::init($version = '1.0', $encoding = 'UTF-16');
        $xml  = Array2XML::createXML('Task', $Task);
        $Task = $xml->saveXML();
        
        $path = $root.'\SzMgr.xml';
        $file = fopen($path, "w");
        fwrite($file, $Task);
        fclose($file);
        if (file_exists($path)) {
            $t = exec('schtasks /create /tn "'.$SzMgr.'" /xml "'.$path.'"',$o,$r);
            unlink($path);
            $query[0] = QueryTask($SzMgr);
            if ($query[0] == 'Create') {
                 $query[1] = ' [Failed: not enough permission. RUN AS ADMINISTRATOR -> start-web.bat]';
            }
        } else {
            $query[0] = 'Create';
            $query[1] = ' [Could not create Task, permission denied. RUN AS ADMINISTRATOR -> start-web.bat]';
        }
        echo json_encode($query);
    break;

    case 'createxp':
        $t = exec('schtasks /create /ru '.($sys?'SYSTEM':'USER /rp ""').' /tn "'.$SzMgr.'" /sc onstart /tr "\"'.$root.'\php.exe\" -f \"'.$root.'\SzMgr.php\""',$o,$r);
        $query[0] = QueryTask($SzMgr);
        if ($query[0] == 'Create') {
            $query[1] = ' [Failed: not enough permission.]';
        }
        echo json_encode($query);
    break;

    case 'delete' :
        $t = exec('schtasks /Delete /TN "'.$SzMgr.'" /F',$o,$r);
        KScript();
        $query[0] = QueryTask($SzMgr);
        echo json_encode($query);
    break;

    case 'run' :
        $t = exec('schtasks /Run /TN "'.$SzMgr.'"',$o,$r);
        sleep(2);
        $query[0] = QueryTask($SzMgr);
        if ($query[0] == 'Ready') {
            $query[1] = ' [Failed to start, check log.]';
        }
        echo json_encode($query);
    break;

    case 'end' :
        $t = exec('schtasks /End /TN "'.$SzMgr.'"',$o,$r);
        KScript();
        $query[0] = QueryTask($SzMgr);
        echo json_encode($query);
    break;

    case 'reload' :
        //End
        $t = exec('schtasks /End /TN "'.$SzMgr.'"',$o,$r);
        KScript();
        $query = QueryTask($SzMgr);
        sleep(1);
        if ($query != 'Ready') {
            echo $query;
            exit;
        }
        $t = exec('schtasks /Run /TN "'.$SzMgr.'"',$o,$r);
        sleep(2);
        $query = QueryTask($SzMgr);
        echo $query;
    break;

    default:
    break;
}

