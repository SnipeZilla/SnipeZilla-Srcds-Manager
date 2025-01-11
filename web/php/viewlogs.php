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
$id   = $_POST['id'];
$file = $_POST['file'];
$path = __DIR__ . '..\..\..\logs';

if ( !file_exists($path) || ( !@is_array($users[$_SESSION['login']]['svr']) && !preg_match('/a/',$_SESSION['level']) ) ) {

    echo 'No Logs.';
    exit;

}

if (!$file) {

    $dots = array('.', '..');
    if ($files = array_diff(scandir($path), $dots)) {
    
        echo '<h4>Available Logs:</h4>';
        echo '<ul class="link">';
        
            foreach($files as $file)
            {
               if(!is_dir($path.'\\'.$file))
               {
                  echo '<li><span data-name="'.$file.'">'.substr($file, 0, strrpos($file, ".")).'</span></li>';
               }
            }
        
        echo '</ul>';
        
    } else {
        
        echo 'No Logs.';
        exit;
        
    }

} else if (file_exists($path.'\\'.$file)) {

    //Open File
    $ln = fopen($path.'\\'.$file, "r");
    $lines=[];
    $lns=[];
    //Read all lines
    while ( ($line = fgets($ln)) !== false ) {
        if (!trim($line)) continue;
        $lns[]= $line;
        $valid = preg_match('/^(\d+)/', $line, $m);
        if ( !$valid ) {

            $n = sizeof($lns)-2;
            if ( $n >= 0 ) $valid = preg_match('/^(\d+)/', $lns[$n], $m);

            if (preg_match('/SnipeZilla/i',$line)) {

                $lines[]= '<h3>'.$line.'</h3>';
                continue;

            }

            if ( !$valid || ($m[1] != $id && $id>0) ) continue;

            if ($m[1]==50) { $lines[]= '<span class="cmd" title="Launch Command Line">'.$line.'</span>';
            } else { $lines[]= $line;}
           

        } elseif ( $id==0 || $m[1] == $id ) {
            $lines[]= preg_replace('/(\\d{2}\\:\\d{2}\\:\\d{2})( - \\d+ - )?(.*)/',"<span class='time'>$1</span>$2<span class='text'>$3</span>",$line);
        }
    }
    echo implode('<br/>',$lines);
}





?>