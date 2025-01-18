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
$edit = array('ajx',
              'am',
              'asa',
              'asc',
              'asp',
              'aspx',
              'awk',
              'bat',
              'c',
              'cdf',
              'cf',
              'cfg',
              'cfm',
              'cgi',
              'cnf',
              'conf',
              'cpp',
              'css',
              'csv',
              'ctl',
              'dat',
              'dhtml',
              'diz',
              'file',
              'forward',
              'grp',
              'h',
              'hpp',
              'hqx',
              'hta',
              'htaccess',
              'htc',
              'htm',
              'html',
              'htpasswd',
              'htt',
              'htx',
              'in',
              'inc',
              'inf',
              'ini',
              'ink',
              'java',
              'js',
              'jsp',
              'log',
              'logfile',
              'm3u',
              'm4',
              'm4a',
              'mak',
              'map',
              'model',
              'msg',
              'nfo',
              'nsi',
              'info',
              'old',
              'pas',
              'patch',
              'perl',
              'php',
              'php2',
              'php3',
              'php4',
              'php5',
              'php6',
              'phtml',
              'pix',
              'pl',
              'pm',
              'po',
              'pwd',
              'py',
              'qmail',
              'rb',
              'rbl',
              'rbw',
              'readme',
              'reg',
              'rss',
              'rtf',
              'ruby',
              'session',
              'setup',
              'sh',
              'shtm',
              'shtml',
              'sql',
              'ssh',
              'stm',
              'style',
              'svg',
              'tcl',
              'text',
              'threads',
              'tmpl',
              'tpl',
              'txt',
              'ubb',
              'vbs',
              'xhtml',
              'xml',
              'xrc',
              'xsl');
$zip=array('zip');
//Icons
function Icon($file) {

    if ($file == 'steamcmd.exe') {
        return 'fa-steam-square';
    }
    if ($file == 'srcds.exe') {
        return 'fa-steam';
    }
    $extension=substr($file, strrpos($file, '.')+1);
    switch ($extension) {
        case 'exe': return 'fa-code';
        case 'zip':
        case 'rar':
        case 'bz2':
        case '7zip': return 'fa-file-archive-o';
        case 'php':
        case 'css':
        case 'html':
        case 'htm': return 'fa-file-code-o';
        case 'avi':
        case 'mp4':
        case 'divx': return 'fa-film';
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'bmp': return 'fa-picture-o';
        case 'mp3':
        case 'wav':
        case 'aac':
        case 'sabl':
        case 'ac3':
        case '3ga': return 'fa-file-audio-o';
        case 'txt':
        case 'cfg':
        case 'ini':
        case 'inf':
        case 'vdf':
        case 'acf':
        case 'vmt': return 'fa-file-text-o';
        case 'pdf': return 'fa-file-pdf-o';
        case 'doc':
        case 'docx': return 'fa-file-word-o';
        case 'xls':
        case 'xlsx': return 'fa-file-excel-o';
        case 'ppt':
        case 'pptx': return 'fa-file-powerpoint-o';
        default:return 'fa-file-o';
    }

}
//copy
function copyTo($src,$dst, &$errFiles, &$countFiles) { 
    $dir = opendir($src); 
    $countFiles++;
    if (!file_exists($dst)) @mkdir($dst,0777); 
    while(false !== ( $file = readdir($dir)) ) { 
        if (( $file != '.' ) && ( $file != '..' )) { 
            if ( is_dir($src . '/' . $file) ) { 
                copyTo($src . '/' . $file,$dst . '/' . $file, $errFiles, $countFiles); 
            } 
            else {
                $countFiles++;
                $errFiles+= true !== copy($src . '/' . $file, $dst . '/' . $file); 
            } 
        } 
    } 
    closedir($dir); 
} 

//rmfiles
function rmFiles($directory, &$errFiles, &$countFiles) {

    if ($handle = opendir($directory)) {

        while (false !== ($f = readdir($handle))) {

            if ($f == '.' || $f == '..') continue;

            $file = $directory . '\\' . $f;

            if(is_dir($file)) {

                rmFiles($file, $errFiles, $countFiles); // Scan Folder

            } else {

                $countFiles++;
                $errFiles += true !== @unlink($file); // File

            }

        }

        $countFiles++;
        @chmod($directory, 0777);
        $errFiles += true !== @rmdir($directory); // Folder
        closedir($handle);

    }

}
//ftp connect
function ftp_conn($id) {

    global $users;

    if (!$ftp = ftp_connect($users[$_SESSION['login']]['ftp'][$id]['host'])) {
        die('Error: Host not available');
    }
    // login with username and password
    if (!@ftp_login($ftp, $users[$_SESSION['login']]['ftp'][$id]['login'], $_SESSION['ftp'][$id])) {
        //unset($_SESSION['ftp'][$sid2]);
        die('Error: Wrong credentials');
    }
    ftp_pasv($ftp, true);

    return $ftp;

}

//ftp
function ftp_rdel ($handle, $path, &$errFiles, &$countFiles) {

  if (@ftp_delete ($handle, $path) === false) {

    if ($children = @ftp_nlist ($handle, $path)) {
        foreach ($children as $p) {
            if ($p == '.' || $p == '..') continue;
            ftp_rdel ($handle,  $p, $errFiles, $countFiles);
        }
    }
    @ftp_chmod($handle, 0777, $path);
    @ftp_rmdir ($handle, $path);
  } else {$countFiles++;}
}

function ftp_putAll($conn_id, $src_dir, $dst_dir, &$errFiles, &$countFiles) {
    global $index;
    $d = dir($src_dir);
    while($file = $d->read()) {
        if ($file != "." && $file != "..") {
            if (is_dir($src_dir."/".$file)) {
                if (!@ftp_chdir($conn_id, $dst_dir."/".$file)) {
                    @ftp_mkdir($conn_id, $dst_dir."/".$file);
                }
                ftp_putAll($conn_id, $src_dir."/".$file, $dst_dir."/".$file, $errFiles, $countFiles);
            } else {
                $countFiles++;
                $errFiles+= true !==  @ftp_put($conn_id, $dst_dir."/".$file, $src_dir."/".$file, (in_array($extension,$edit)?FTP_ASCII:FTP_BINARY));
            }
        }
    }
    $d->close();
}

function ftp_getAll($conn_id, $src_dir, $dst_dir, &$errFiles, &$countFiles) {
    global $index;
    if (ftp_size($conn_id, $src_dir)==-1) {
        if (!file_exists($dst_dir)) @mkdir($dst_dir,0777); 
        if ($files = @ftp_nlist ($conn_id, $src_dir)) {
            foreach ($files as $file) {
                if ($file == '.' || $file == '..') continue;
                ftp_getAll ($conn_id,  $file, $dst_dir.'\\'.basename($file), $errFiles, $countFiles);

            }
        }
    } else {
        $countFiles++;
        $errFiles+= true !==  @ftp_get($conn_id, $dst_dir, $src_dir, (in_array($extension,$edit)?FTP_ASCII:FTP_BINARY)); // put the files
    }

}

?>