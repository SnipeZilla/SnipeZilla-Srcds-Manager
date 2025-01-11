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
if ( !preg_match('/a|h/',$_SESSION['level']) ) die('Error 403 - Forbidden: Access is denied.');

require 'config.php';
$total = sizeof($server);
if (!$server[1]['ip']) exit();

$id   = $_POST['id'];
$ip   = $_POST['ip'];
$port = $_POST['port'];
$update  = $_POST['update'] == 'true';
$updateFile = $server[$id]['installdir'].'\\'.$server[$id]['game'].'\\'.$server[$id]['updatefile'];
echo $updateFile;
if ( !preg_match('/a/',$_SESSION['level']) && 
   ( !in_array($server[$id]['ip'].':'.$server[$id]['port'], $users[$_SESSION['login']]['svr']) || $server[$id]['ip'].':'.$server[$id]['port'] != $_POST['ip'].':'.$_POST['port'] ) ) die('Error 403 - Forbidden: Access is denied.');

if ($update) {
    if (!file_exists($updateFile)) {

        $file = fopen($updateFile, "w");
        fclose($file);
    }
        echo "Update will be executed at the next start";

} else {

    if (file_exists($updateFile))
        unlink($updateFile);
    echo "Update is cancelled";

}

?>