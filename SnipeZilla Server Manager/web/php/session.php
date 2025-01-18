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
$_SESSION['local']=(($_SERVER['REMOTE_ADDR']=='127.0.0.1' || $_SERVER['REMOTE_ADDR']=='::1') && $_SERVER['SERVER_NAME'] == 'localhost');
if (!isset($_SESSION['login'])) $_SESSION['login']=false;
$error=false;
$users    = false;
require_once 'users.crc32.php';

if (!$_SESSION['login']) {

    if ( isset($_POST['username']) ) {
        $username = strtolower($_POST['username']);
        if ( !empty($username) &&
             isset($_POST['password']) &&
             !empty($_POST['password']) && isset($users[$username]) && !empty($users[$username]['pw']) ) {

            $correct_hash = $users[$username]['pw'];
            $validate = password_verify($_POST['password'], $correct_hash);

            if ( !$validate ) {

                if (!isset($_SESSION['error'])) $_SESSION['error'] = 0;
                $_SESSION['error'] += 1;

            } else {
                $_SESSION['level'] = $users[$username]['lvl'];
                $_SESSION['login'] = $username;
                $crc = $_SERVER ['HTTP_USER_AGENT'];
                $crc .= serialize(array($users[$_SESSION['login']]['pw'],$users[$_SESSION['login']]['svr'],$users[$_SESSION['login']]['lvl'],$users[$_SESSION['login']]['gp']));
                $_SESSION['CRC']   = crc32(md5($crc));
                header( 'HTTP/1.1 303 See Other' );
                header("Location: index.php");

            }

        } else {
            header( 'HTTP/1.1 303 See Other' );
            header("Location: index.php");
            exit;
        }
    
    } elseif ($_SESSION['local'] && !$users) {
    
           $_SESSION['CRC']   = crc32(md5($crc));
           $_SESSION['login'] = 'localhost';
           $_SESSION['level'] = 'a';
    }
        
}

if ( isset($_SESSION['error']) ) {

    if ($_SESSION['error'] >= 3) {
        die('SnipeZilla Srcds Manager - Authentication Failed');
    }

}
include 'main.php';
?>
