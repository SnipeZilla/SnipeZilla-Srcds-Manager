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
require_once 'users.crc32.php';
include 'config.php';
$total = sizeof($server);

    foreach ($users as $name => $data) {

        if ( !preg_match('/a/',$_SESSION['level']) && $_SESSION['login'] != $name && ($_SESSION['login'] != $users[$name]['gp']) ) {
            if (!$users[$name]['gp']) continue;
            $parent = $users[$name]['gp'];
            while ( isset($users[$parent]) ) {

                if ($_SESSION['login'] == $users[$parent]['gp']) break;
                $parent = $users[$parent]['gp'];

            }
            if (!isset($users[$parent])) continue;
        }

        echo '<tr'.($_SESSION['login'] == $name && sizeof($users)>1 ?' class="user"':'').'>';
		echo '<td>'.($_SESSION['login'] == $name ?'':'<span class="fa fa-times del-user" title="Delete user"></span>').'</span><span class="username">'.$name.'</span>'.($users[$name]['gp']?'<br/><span class="parent" title="'.$users[$name]['gp'].'\' group"><span class="fa fa-level-down group"></span>'.$users[$name]['gp']:'').'</span></td>';

		echo '<td>';
                echo '<input type="password" name="password" maxlength="50" />New<br/><input type="password" name="password2" maxlength="50" />Confirm<br/>';
        echo '</td>';

        $d=[];
        if ($data) {
            foreach ($data as $data => $values) {
                $d[$data] = $values;
            }
        } 
        if (!isset($d['lvl']))$d['lvl']='';
        if (!isset($d['svr']))$d['svr']=array();

		echo '<td>';

                for ($i=1; $i<$total; $i++) {
                    if ( !isset($server[$i]['appid']) ) continue;
                    if ( ( is_array($users[$name]['svr']) && in_array($server[$i]['ip'].':'.$server[$i]['port'], $users[$name]['svr'])) ||
                         ( preg_match('/a/',$_SESSION['level']) && !$users[$name]['gp'] ) ||
                         ( $users[$name]['gp'] && is_array($users[$users[$name]['gp']]['svr']) && in_array($server[$i]['ip'].':'.$server[$i]['port'], $users[$users[$name]['gp']]['svr']) &&
                            (is_array($users[$_SESSION['login']]['svr']) && in_array($server[$i]['ip'].':'.$server[$i]['port'], $users[$_SESSION['login']]['svr']) || preg_match('/a/',$_SESSION['level'])) )
                       ) {

                        echo '<input type="checkbox" name="empty" value="'.($server[$i]['ip'].':'.$server[$i]['port']).'"';
                        if ($_SESSION['login'] == $name) echo ' onclick="return false;" onkeydown="return false;"';
                         if ( (is_array($d['svr']) && in_array($server[$i]['ip'].':'.$server[$i]['port'], $d['svr'])) || preg_match('/a/',$d['lvl']) )  echo ' checked';
                        if ($server[$i]['fname']) {
                            echo '><span title="Server '.$i.' ['.$games[$server[$i]['appid']]['name'].']">'.$server[$i]['fname'].'</span><br/>';
                        } else {
                            echo '>Server '.$i.' ['.$games[$server[$i]['appid']]['name'].']<br/>';
                        }

                    }
                }

		echo '</td>';
		echo '<td>';

                if ( preg_match('/a/',$_SESSION['level']) && !$users[$name]['gp'] )
				    echo '<span title="No restriction"><input type="checkbox" name="a" value="a" '.($_SESSION['login'] == $name?' onclick="return false;" onkeydown="return false;"':'').(preg_match('/a/',$d['lvl'])?' checked':'').'>SuperAdmin</span><br/>';
                    if ( ( preg_match('/b/',$users[$name]['lvl'])) ||
                         ( preg_match('/a/',$_SESSION['level']) && !$users[$name]['gp'] ) ||
                         ( $users[$name]['gp'] && preg_match('/b/', $users[$users[$name]['gp']]['lvl']) && $users[$name]['gp'] && preg_match('/a|b/', $users[$_SESSION['login']]['lvl']) )
                       ) 
				    echo '<span title="Allow to access Configuration page"><input type="checkbox" name="b" value="b" '.($_SESSION['login'] == $name?' onclick="return false;" onkeydown="return false;"':'').(preg_match('/a|b/',$d['lvl'])?' checked':'').'>Configuration</span><br/>';
                    if ( ( preg_match('/c/',$users[$name]['lvl'])) ||
                         ( preg_match('/a/',$_SESSION['level']) && !$users[$name]['gp'] ) ||
                         ( $users[$name]['gp'] && preg_match('/c/', $users[$users[$name]['gp']]['lvl']) && $users[$name]['gp'] && preg_match('/a|c/', $users[$_SESSION['login']]['lvl']) )
                       ) 
				    echo '<span title="Allow to access Installation page"><input type="checkbox" name="c" value="c" '.($_SESSION['login'] == $name?' onclick="return false;" onkeydown="return false;"':'').(preg_match('/a|c/',$d['lvl'])?' checked':'').'>Installation</span><br/>';    
                    if ( ( preg_match('/d/',$users[$name]['lvl'])) ||
                         ( preg_match('/a/',$_SESSION['level']) && !$users[$name]['gp'] ) ||
                         ( $users[$name]['gp'] && preg_match('/d/', $users[$users[$name]['gp']]['lvl']) && $users[$name]['gp'] && preg_match('/a|d/', $users[$_SESSION['login']]['lvl']) )
                       ) 

                    if ( ( preg_match('/j/',$users[$name]['lvl'])) ||
                         ( preg_match('/a/',$_SESSION['level']) && !$users[$name]['gp'] ) ||
                         ( $users[$name]['gp'] && preg_match('/j/', $users[$users[$name]['gp']]['lvl']) && $users[$name]['gp'] && preg_match('/a|j/', $users[$_SESSION['login']]['lvl']) )
                       ) 
				    echo '<span title="Access to file manager (server access only: upload maps, change cfg etc...)"><input type="checkbox" name="j" value="j" '.($_SESSION['login'] == $name?' onclick="return false;" onkeydown="return false;"':'').(preg_match('/a|j/',$d['lvl'])?' checked':'').'>File Manager</span><br/>'; 

				    echo '<span title="Allow to add users on his own server. Server and Permission are inherited."><input type="checkbox" name="d" value="d" '.($_SESSION['login'] == $name?' onclick="return false;" onkeydown="return false;"':'').(preg_match('/a|d/',$d['lvl'])?' checked':'').'>Add user</span><br/>';          
                    if ( ( preg_match('/e/',$users[$name]['lvl'])) ||
                         ( preg_match('/a/',$_SESSION['level']) && !$users[$name]['gp'] ) ||
                         ( $users[$name]['gp'] && preg_match('/e/', $users[$users[$name]['gp']]['lvl']) && $users[$name]['gp'] && preg_match('/a|e/', $users[$_SESSION['login']]['lvl']) )
                       ) 
				    echo '<span title="Allow to view other server in Status page."><input type="checkbox" name="e" value="e" '.($_SESSION['login'] == $name?' onclick="return false;" onkeydown="return false;"':'').(preg_match('/a|e/',$d['lvl'])?' checked':'').'>View Servers status</span><br/>';          
                    if ( ( preg_match('/f/',$users[$name]['lvl'])) ||
                         ( preg_match('/a/',$_SESSION['level']) && !$users[$name]['gp'] ) ||
                         ( $users[$name]['gp'] && preg_match('/f/', $users[$users[$name]['gp']]['lvl']) && $users[$name]['gp'] && preg_match('/a|f/', $users[$_SESSION['login']]['lvl']) )
                       ) 
				    echo '<span title="Allow to change Command-line parameters"><input type="checkbox" name="f" value="f" '.($_SESSION['login'] == $name?' onclick="return false;" onkeydown="return false;"':'').(preg_match('/a|f/',$d['lvl'])?' checked':'').'>CMD parameters</span><br/>';          

                    if ( ( preg_match('/g/',$users[$name]['lvl'])) ||
                         ( preg_match('/a/',$_SESSION['level']) && !$users[$name]['gp'] ) ||
                         ( $users[$name]['gp'] && preg_match('/g/', $users[$users[$name]['gp']]['lvl']) && $users[$name]['gp'] && preg_match('/a|g/', $users[$_SESSION['login']]['lvl']) )
                       ) 
				    echo '<span title="Allow to send RCON command"><input type="checkbox" name="g" value="g" '.($_SESSION['login'] == $name?' onclick="return false;" onkeydown="return false;"':'').(preg_match('/a|g/',$d['lvl'])?' checked':'').'>RCON command</span><br/>'; 

                    if ( ( preg_match('/h/',$users[$name]['lvl'])) ||
                         ( preg_match('/a/',$_SESSION['level']) && !$users[$name]['gp'] ) ||
                         ( $users[$name]['gp'] && preg_match('/h/', $users[$users[$name]['gp']]['lvl']) && $users[$name]['gp'] && preg_match('/a|h/', $users[$_SESSION['login']]['lvl']) )
                       ) 
				    echo '<span title="Allow to Force Update at Startup"><input type="checkbox" name="h" value="h" '.($_SESSION['login'] == $name?' onclick="return false;" onkeydown="return false;"':'').(preg_match('/a|h/',$d['lvl'])?' checked':'').'>Request Update</span><br/>'; 


                    if ( ( preg_match('/i/',$users[$name]['lvl'])) ||
                         ( preg_match('/a/',$_SESSION['level']) && !$users[$name]['gp'] ) ||
                         ( $users[$name]['gp'] && preg_match('/i/', $users[$users[$name]['gp']]['lvl']) && $users[$name]['gp'] && preg_match('/a|i/', $users[$_SESSION['login']]['lvl']) )
                       ) 
				    echo '<span title="Allow to STOP/START server"><input type="checkbox" name="i" value="i" '.($_SESSION['login'] == $name?' onclick="return false;" onkeydown="return false;"':'').(preg_match('/a|i/',$d['lvl'])?' checked':'').'>STOP server</span>'; 

		echo '</td>';
        echo '<td><span class="fa fa-floppy-o update-user" title="'.($_SESSION['login'] == $name ?'Update your password':'Update user').'"></td>';
        echo '</tr>'; 

    }

?>