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
require 'config.php';
$total = sizeof($server);
if (!$server[1]['ip']) exit();
$sid = $_POST['sid'];
$dir = trim(str_replace(array('../','./','..\\','.\\'),array('','','',''),$_POST['dir']));
$valid=preg_match('/a/',$_SESSION['level']);
if ( isset($users) && !$valid ) {
	if ( in_array($server[$sid]['ip'].':'.$server[$sid]['port'], $users[$_SESSION['login']]['svr']) ) {
		$valid=true;
	}
}

if ( isset($server[$sid]) && $valid ) {

        $dir = trim(implode('\\', explode( '\\', str_replace('/','\\',$dir) ) ),"\\");
        $directory = trim(implode('\\', explode( '\\', str_replace('/','\\',$server[$sid]['installdir'].'\\'.$dir) ) ),"\\");

} else {

    die('Error: Access is denied.');

}
//triple check
if ($directory != realpath($directory)  || !is_dir($directory) ) die('Error: Access is denied.');

$files   = '';
$root         = substr($dir, 0, strrpos( $dir, '\\'));
$foldername   = '..\\'.$dir ;
if ( $foldername =='..\\') {

    $foldername = $server[$sid]['fname']? $server[$sid]['fname'] : 'Server '.$sid;

}

$folders = '<tr class="folders root">
                <td><span class="fa fa-folder-open refresh" title="Home"></td>
                <td colspan="3" data-name="'.$dir.'" class="root-name data-file" title="Back"><span class="file-name" data-name="'.$root.'">'.($foldername).'</span></td>
            </tr>';
if(file_exists($directory)){

    if ($handle = opendir($directory)) {

        while (false !== ($f = readdir($handle))) {
    
            if ($f == '.' || $f == '..') continue;

            if(is_dir($directory . '\\' . $f)) {

                // Folder
                $folders.='<tr class="folders">
                            <td class="chk"><input type="checkbox" value="1" title="Select to copy"></td>
                            <td class="file data-file"><span class="fa fa-folder"></span><span class="file-name realdir" data-name="'.($dir.'\\'.$f).'">'.$f.'</span></td>
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
                // File
                $fsize=@filesize($directory . '\\' . $f);
                //if (!$fsize) continue;
                $extention=substr($f, strrpos($f, '.')+1);
                $files.='<tr class="files">
                            <td class="chk"><input type="checkbox" value="1" title="Select to copy"></td>
                            <td class="file"><span class="fa '.Icon($f).'"></span><span class="file-name-e realdir" data-name="'.($dir.'\\'.$f).'">'.$f.'</span></td>
                            <td class="file-ctrl">
                                <div class="edit-file'.(in_array($extention,$edit)?'':' disable').'"><span class="fa fa-pencil-square-o"'.(in_array($extention,$edit)?' title="Edit"':'').'></span></div>
                                <div class="unzip-file'.(in_array($extention,$zip)?'':' disable').'"><span class="fa fa-cube"'.(in_array($extention,$zip)?' title="Extract Files..."':'').'></span></div>
                                <div class="download-file"><a href="php/download.php?sid='.$sid.'&dir=\''.($dir.'\\'.$f).'\'&token='.$_TOKEN.'" target="_blank"><span class="fa fa-download" title="Download"></span></a></div>
                            </td>
                            <td class="del-file">
                                <div><span class="fa fa-times" title="Delete"></span></div>
                            </td>
                        </tr>';

            }

        }

    }

}

echo $folders.$files;
?>