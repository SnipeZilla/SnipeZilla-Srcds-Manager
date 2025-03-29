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
if ( !preg_match('/a|d/',$_SESSION['level']) && ($_POST['fct'] != 'update' || $_POST['username'] != $_SESSION['login']) ) die('Error 403 - Forbidden: Access is denied.');

$header="<?php if ( !isset(\$_SESSION['token']) || empty(\$_SESSION['token']) || \$_SESSION['token'] != \$_TOKEN) die('403 - Forbidden: Access is denied.'); ?>";
$level = '';
$first    = false;
if (!$users) {

    $first = true;
    $level = 'a';

}
function Perm($username) {

        global $users;
        if (!$username) return false;
        foreach ($users as $name => $data) {

            if ( $users[$name]['gp'] == $username ) {

                $lvllen = strlen( $users[$name]['lvl'] );
                for( $i = 0; $i <= $lvllen; $i++ ) {
                    $char = substr( $users[$name]['lvl'], $i, 1 );

                    if ( !preg_match('/'.$char.'/', $users[$username]['lvl']) ) {

                        $users[$name]['lvl'] = str_replace($char, '', $users[$name]['lvl']);

                    }

                }

                $svrlen = sizeof($users[$name]['svr']);
                $svr = '';
                for( $i = 0; $i <= $svrlen; $i++ ) {

                    if ( is_array($users[$username]['svr']) && in_array($users[$name]['svr'][$i], $users[$username]['svr']) ) {

                        $svr[] = $users[$name]['svr'][$i];

                    }

                }
                $users[$name]['svr'] = $svr;

                Perm($name);

            }

        }

}

function Orphan($username) {

        global $users;
        $parent = '';
        if ($users[$username]['gp']) $parent = $users[$username]['gp'];
        foreach ($users as $name => $data) {

            if ( $users[$name]['gp'] == $username ) {
                
                $users[$name]['gp'] = $parent;
                if (!$parent) {

                    $parent = $name;
                    $users[$name]['svr'] = $users[$username]['svr'];
                    $users[$name]['lvl'] = $users[$username]['lvl'];

                }
            }

        }
        Perm($parent);

}

switch ($_POST['fct']) {
    case 'new':
        $username = strtolower($_POST['username']); 
        $password = $_POST['password'];
        $password2= $_POST['password2'];

        if (!trim($username) || strlen($username) < 2){

            echo 'Error: username too short. Minimum 2 chars.';
            exit();

        }
        if (isset($users[$username])){

            echo 'Error: Username: "'.$username.'" is reserved or unavailable.';
            exit(); 

        }
        if (!preg_match('/^[a-z0-9_]+$/i', $username)) {

            echo 'Error: invalid character in "'.$username.'". Only letters, numbers and _ permissible.';
            exit(); 

        }
        if (!trim($password) || strlen($password) < 6 ){

            echo 'Error: password too short. Minimun 6 chars';
            exit();

        }
        if ( $password != $password2 ) {

            echo 'Error: password does not match';
            exit();

        }
        if ( !preg_match('/[a-z-A-Z]+/',$password) || !preg_match('/[0-9]+/',$password) ) {

            echo 'Error: password should have letters and numbers.';
            exit();

        }
        if ( preg_match('/a/',$_SESSION['level']) ) {
           $gp = '';
        } else {
            $gp = $_SESSION['login']; 
        }
        $users[$username] = array('email' => '', 'pw' => password_hash($password, PASSWORD_DEFAULT), 'svr' => '', 'lvl' => $level, 'gp' => $gp, 'rcon' => '' );
        $file = fopen($db_users, "w");
        fwrite($file, $header.serialize($users));
        fclose($file);
        if ($first) {
            session_unset();
            session_destroy();
            session_regenerate_id(true);
            echo 'Error: refresh';
            exit;
        }
        include 'users.list.php';
    break;

    case 'delete':
        $username = $_POST['username']; 
        if (isset($users[$username]) ){
            Orphan($username);
            unset($users[$username]);            
            $file = fopen($db_users, "w");
            fwrite($file, $header.serialize($users));
            fclose($file);
            include 'users.list.php';
        } else {
            echo 'Error: '.$username.' not found. Refresh page for latest users list.';
        }

    break;

    case 'update':
        $username = $_POST['username']; 
        $password = $_POST['password'];
        $password2= $_POST['password2'];
        $svr      = $_POST['svr'];
        $lvl      = $_POST['lvl'];
        if (!trim($username)){

            echo 'Error: no username received.';
            exit();

        }
        if (!isset($users[$username])) {

            echo 'Error: '.$username.' doesn\'t exist.';
            exit(); 

        }
        if ($password) {
            if ( !trim($password) || strlen($password) < 6 ){
            
                echo 'Error: password too short';
                exit();
            
            }
            if ( $password != $password2 ){
            
                echo 'Error: password does not match';
                exit();
            
            }
            if ( !preg_match('/[a-zA-Z]+/', $password) || preg_match('/\[0-9]+/', $password) ) {

                echo 'Error: password must contain letters and numbers';
                exit();

            }
            $users[$username]['pw']=password_hash($password, PASSWORD_DEFAULT);
        }
        $users[$username]['svr']=$svr;
        if ($lvl) {
            if (preg_match('/a/',$lvl)) $lvl='abcdefgh';
            $chk_lvl = str_split($lvl);
            $lvl='';
            $n=sizeof($chk_lvl);
            for ($i=0; $i<$n; $i++) {
                if ( preg_match('/a|'.$chk_lvl[$i].'/',$_SESSION['level'])  ) $lvl.=$chk_lvl[$i];
            }
            $users[$username]['lvl']=$lvl;
        } else {
            $users[$username]['lvl']='';
        }
        if ( $username == $_SESSION['login'] ) $users[$username]['lvl'] = $_SESSION['level'];
        Perm($username);
        $file = fopen($db_users, "w");
        fwrite($file, $header.serialize($users));
        fclose($file);
        include 'users.list.php';

    break;

    case 'ftp':
        $host     = $_POST['host']; 
        $prefix   = $_POST['prefix']; 
        $name     = substr($_POST['name'], 0, 20); 
        $login    = $_POST['login']; 
        $username = $_SESSION['login'];


        if (!trim($host)){

            echo 'Error: no host received.';
            exit();

        }

        if (!trim($name)){

            echo 'Error: FTP are not saved without a name.';
            exit();

        }
        if (!trim($login)){

            echo 'Error: no login received.';
            exit();

        }
        if (!isset($users[$username])) {

            echo 'Error: No Login Session.';
            exit(); 

        }
        if (isset($users[$username]['ftp'])) {
            $total=sizeof($users[$username]['ftp']);
            if ($total>7) {

                echo 'Error: FTP not saved. Max 8.';
                exit();

            }
            for ($i=0; $i<$total; $i++) {

                if ( $users[$username]['ftp'][$i]['name'] == $name ) {
                    
                    $users[$username]['ftp'][$i]=array('name' => $name, 'host' => $host, 'prefix' => $prefix, 'login' => $login, 'pw' => '');
                    $name='';
                    break;

                }

            }
            if ($name) {
                $users[$username]['ftp'][$total]=array('name' => $name, 'host' => $host, 'prefix' => $prefix, 'login' => $login, 'pw' => '');
            }
        } else{
            $users[$username]['ftp'][0]=array('name' => $name, 'host' => $host, 'prefix' => $prefix, 'login' => $login, 'pw' => '');
        }

        $file = fopen($db_users, "w");
        fwrite($file, $header.serialize($users));
        fclose($file);
        if ($name) {
            echo 'New FTP saved.';
        } else {
            echo 'FTP updated.';
        }

    break;
    case 'ftp-del':
        $id = $_POST['id'];
        $username = $_SESSION['login'];

        if (isset($users[$username]['ftp'])) {
            $total=sizeof($users[$username]['ftp']);
            if (!isset($users[$username]['ftp'][$id])) {

                echo 'Error: FTP not found.';
                exit();

            }
            if (isset($_SESSION['ftp'][$id])) {

                unset($_SESSION['ftp'][$id]);
                $_SESSION['ftp'] = array_values($_SESSION['ftp']);

            }
            $name = $users[$username]['ftp'][$id]['name'];
            unset($users[$username]['ftp'][$id]);
            $users[$username]['ftp'] = array_values($users[$username]['ftp']);
            $file = fopen($db_users, "w");
            fwrite($file, $header.serialize($users));
            fclose($file);
            echo 'FTP "'.$name.'" removed.';

        } else {

            echo 'Error: FTP not found.';
            exit();

        }

    break;

    default: break;
}
?>