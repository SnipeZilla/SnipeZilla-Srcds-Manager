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
if ( !preg_match('/a|b/',$_SESSION['level']) ) die('Error 403 - Forbidden: Access is denied.');

$n = $_POST["a"];
//File
$file = __DIR__ . '..\..\..\include\msg.log.xml';

//File not found
if (!file_exists($file)) {

        echo '<p>msg.log.xml not found...</p>';
        exit();

}

//simplexml
$xml = @simplexml_load_file($file);
$msg = array();

//Parse error
if ( !$xml ) {

        echo '<p>Error found in msg.log.xml...</p>';
        exit();

}

foreach($xml->children() as $child) {
   
        $role = $child->attributes();
        $role = (int)trim(strtolower((string)$role));

        if ( $role && is_numeric($role) && preg_match("/[0-9]+/", substr($role,0,1) ) ) {

                $msg[$role] = $child;
           
        }

}

foreach ($msg as $k=>$v) {
    if ($k>=20 && $k<99) {
        echo $k.'<input type="checkbox" name="msg" value="'.$k.'" '.(preg_match('/(\b'.$k.'\b)/', $n)?' checked':'').'>"'.$v.'<br/>';
    }
}
?>