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
if ( !isset($_SESSION['token']) || empty($_SESSION['token']) || $_SESSION['token'] != $_TOKEN) die('Error 403 - Forbidden: Access is denied.');
$db_users = __DIR__ . '..\..\..\include\users.db.php';
$crc = $_SERVER ['HTTP_USER_AGENT'];
if (file_exists($db_users)) {

    $users = unserialize(trim(strip_tags(file_get_contents($db_users))));
    if ( isset($users[$_SESSION['login']]) ) {
        $crc .= serialize(array($users[$_SESSION['login']]['pw'],$users[$_SESSION['login']]['svr'],$users[$_SESSION['login']]['lvl'],$users[$_SESSION['login']]['gp']));
    } else {
        $crc .= time();
    }

} else { $users = false;}
if ( !isset($_SESSION['login']) || ($_SESSION['login'] && crc32(md5($crc)) != $_SESSION['CRC']) ) {
    session_unset();
    session_destroy();
    session_regenerate_id(true);
    die('Error 403 - Forbidden: Access is denied.');
}
?>