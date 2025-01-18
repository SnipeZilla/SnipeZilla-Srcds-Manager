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
require 'filemanager.func.php';
$id     = $_POST['id'];
$host   = $_POST['host'];
$prefix = $_POST['prefix'];
$name   = $_POST['name'];
$login  = $_POST['login'];
$pw     = $_POST['pw'];
$dir    = $_POST['dir'];
$sid    = '';

$dir = trim(implode('/', explode( '/', str_replace('\\','/',$dir) ) ),"/");

if (!$pw && isset($_SESSION['ftp'][$id])) {
    $pw = $_SESSION['ftp'][$id];
} else {
    $_SESSION['ftp'][$id] = $pw;
}
if (!$host || !$ftp = ftp_connect($host)) {
    echo 'Error: Host not available';
    exit;
}
// login with username and password
if (!$login || !$pw || !@ftp_login($ftp, $login, $pw)) {
    unset($_SESSION['ftp'][$id]);
    echo 'Error: Wrong credentials';
    exit;
}

ftp_pasv($ftp, true);
//
//-------------------
ftp_chdir($ftp, $prefix.'/'.$dir);
//
// get contents of the current directory
$contents = ftp_nlist($ftp, ".");
//-

$files   = '';
$root         = substr($dir, 0, strrpos( $dir, '/'));
$foldername   = '../'.$dir ;//substr($dir, strlen($dir),strrpos( '\\'.$dir, '\\') );
if ( $foldername =='../') {

    $foldername = trim($host.'/'.$prefix,'/');

}

$folders = '<tr class="folders root">
                <td><span class="fa fa-desktop refresh" title="Home"></td>
                <td colspan="3" data-name="'.$dir.'" class="root-name data-file" title="Back"><span class="file-name" data-name="'.$root.'">'.($foldername).'</span></td>
            </tr>';
foreach ($contents as $f) {

    if ($f == '.' || $f == '..') continue;
    $fsize = ftp_size($ftp, $f);
    if($fsize == "-1") { //Is a file or directory?
    

                // Folder
                $folders.='<tr class="folders">
                            <td class="chk"><input type="checkbox" value="1" title="Select to copy"></td>
                            <td class="file data-file"><span class="fa fa-folder"></span><span class="file-name realdir" data-name="'.($dir.'/'.$f).'">'.$f.'</span></td>
                            <td class="file-ctrl">
                                <div class="edit-file disable"><span class="fa fa-pencil-square-o"></span></div>
                                <div class="unzip-file disable"><span class="fa fa-cube"></span></div>
                                <div class="download-file disable"><span class="fa fa-download"></span></div>
                            </td>
                            <td class="del-file">
                                <div><span class="fa fa-times" title="Delete"></span></div>
                            </td>
                        </tr>';


    } else {

                $extention=substr($f, strrpos($f, '.')+1);
                $files.='<tr class="files">
                            <td class="chk"><input type="checkbox" value="1" title="Select to copy"></td>
                            <td class="file"><span class="fa '.Icon($f).'"></span><span class="file-name-e realdir" data-name="'.($dir.'/'.$f).'">'.$f.'</span></td>
                            <td class="file-ctrl">
                                <div class="edit-file disable"><span class="fa fa-pencil-square-o"></span></div>
                                <div class="unzip-file disable"><span class="fa fa-cube"></span></div>
                                <div class="download-file"><a href="php/download.php?sid=ftp-'.$id.'&dir='.str_replace('//','/',$host.'/'.$prefix.'/'.$dir.'/'.$f).'&token='.$_TOKEN.'"  target="_blank"><span class="fa fa-download" title="Download"></span></a></div>
                            </td>
                            <td class="del-file">
                                <div><span class="fa fa-times" title="Delete"></span></div>
                            </td>
                        </tr>';

    }

}


echo $folders.$files;
//-------------------
ftp_close($ftp);
//-------------------
?>
