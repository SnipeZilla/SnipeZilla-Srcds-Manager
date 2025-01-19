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
$fct = $_POST['fct'];
$sid = $_POST['sid'];
$dir = trim(str_replace(array('../','./','..\\','.\\'),array('','','',''),$_POST['dir']));
//j
if ( !preg_match('/a|j/',$_SESSION['level']) ) die('Error: Access is denied.');

if (!preg_match('/^ftp-(\d+)$/',$sid,$m)) {

    if ( isset($server[$sid]) && (( $users != false && is_array($users[$_SESSION['login']]['svr']) && in_array($server[$sid]['ip'].':'.$server[$sid]['port'], $users[$_SESSION['login']]['svr']) ) || preg_match('/a/',$_SESSION['level'])) ) {
    
            $dir = trim(implode('\\', explode( '\\', str_replace('/','\\',$dir) ) ),"\\");
            $directory = trim(implode('\\', explode( '\\', str_replace('/','\\',$server[$sid]['installdir'].'\\'.$dir) ) ),"\\");
    
    } else {
    
        die('Error: Access is denied.');
    
    }
    //triple check
    if ($directory != realpath($directory) || !$directory) die('Error: Access is denied.');

} else {
    //ftp
    $sid = $m[1]-1;

}

switch ($fct) {

    case 'newFolder':
        $nf  = trim($_POST['nf']);
        $directory = $directory.'\\'.$nf;

        if ( !$nf ) {

            echo 'Error: Directory has no name';
            exit;
        }
        if (preg_match('/[\\/:\*?"<>|]/',$nf)) {

            echo 'Error: Invalid Character found';
            exit;
        }
        if ( file_exists($directory) ) {

            echo 'Error: Directory exists';
            exit;

        }

        @mkdir($directory);
        @chmod($directory, 0777);
        if (is_dir($directory)) {

            exit;

        }
        echo 'Error: Directory couldn\'t be created';

    break;
    case 'rmFiles':
        if ( !$dir || !file_exists($directory) ) {

            echo 'Error: Access is denied.';
            exit;

        }

        $errFiles     = 0;
        $countFiles   = 0;

        if(is_dir($directory)) {

            rmFiles($directory, $errFiles, $countFiles);

        } else {

            $countFiles++;
            $errFiles += true !== @unlink($directory); // File

        }

        $error ='';
        if ($errFiles) $error='Error: ';
        if ($countFiles>1) {

            echo $error.($countFiles-$errFiles).' out of '.$countFiles.' files were deleted.';

        } else {

            echo $error.($countFiles-$errFiles).' out of '.$countFiles.' file was deleted.';

        }
 
    break;

    case 'copy':
        set_time_limit (300);
        $id2  = $_POST['id2'];
        $sid2 = $_POST['sid2'];
        $dir2 = trim(str_replace(array('../','./','..\\','.\\'),array('','','',''),$_POST['dir2']));
        //j
        if ( isset($server[$sid2]) && (( $users != false && is_array($users[$_SESSION['login']]['svr']) && in_array($server[$sid2]['ip'].':'.$server[$sid2]['port'], $users[$_SESSION['login']]['svr']) ) || preg_match('/a/',$_SESSION['level'])) ) {
        
                $dir2       = trim(implode('\\', explode( '\\', str_replace('/','\\',$dir2) ) ),"\\");
                $directory2 = trim(implode('\\', explode( '\\', str_replace('/','\\',$server[$sid2]['installdir'].'\\'.$dir2) ) ),"\\");
        
        } else {
        
            die('Error: Access is denied.');
        
        }
        if (!file_exists($directory2)) {echo 'Error: Unknown Destination';exit;};

        $files2copy = json_decode($_POST['files'], true);
        $errFiles     = 0;
        $countFiles   = 0;

        $files_count=sizeof($files2copy);

        for ($i=0;$i<$files_count;$i++) {

            $dir       = trim(implode('\\', explode( '\\', str_replace('/','\\',$files2copy[$i]['file']) ) ),"\\");
            $directory = trim(implode('\\', explode( '\\', str_replace('/','\\',$server[$sid]['installdir'].'\\'.$dir) ) ),"\\");
            if (!file_exists($directory)) continue;

            if( is_dir($directory) ) {

                $dir=substr(strrchr(rtrim($directory, '\\'), '\\'), 1);
                copyTo( $directory, $directory2.'\\'.basename($dir), $errFiles, $countFiles);

            } else {

                $countFiles++;
                $errFiles+= true !== copy( $directory, $directory2.'\\'.basename($dir));

            }

        }

        $error ='';
        if ($errFiles) $error='Error: ';
        if ($countFiles>1) {

            echo $error.($countFiles-$errFiles).' out of '.$countFiles.' files were copied.';

        } else {

            echo $error.($countFiles-$errFiles).' out of '.$countFiles.' file was copied.';

        }
 
    break;

    case 'unzip':
        $root = trim(implode('\\', explode( '\\', str_replace('/','\\',trim(str_replace(array('../','./','..\\','.\\'),array('','','',''),$_POST['root']))) ) ),"\\");
        $root = trim(implode('\\', explode( '\\', str_replace('/','\\',$server[$sid]['installdir'].'\\'.$root) ) ),"\\");
        //triple check
        if ($root != realpath($root) || !$root) die('Error: Access is denied.');
        if ( !$dir || !file_exists($directory) || !file_exists($root) ) {

            echo 'Error: Access is denied.';
            exit;

        }
        
        $zip = new ZipArchive;
        $res = $zip->open($directory);
        if ($res === true) {

            $zip->extractTo($root);
            $zip->close();
        
        } else {

            echo 'Error: Could not open archive.';
            exit;

        }

        switch( (int) $zip->status )
        {
        case ZipArchive::ER_OK           : echo 'Files have been extracted.';break;
        case ZipArchive::ER_MULTIDISK    : echo 'Error: Multi-disk zip archives not supported';break;
        case ZipArchive::ER_RENAME       : echo 'Error: Renaming temporary file failed';break;
        case ZipArchive::ER_CLOSE        : echo 'Error: Closing zip archive failed';break;
        case ZipArchive::ER_SEEK         : echo 'Error: Seek error';break;
        case ZipArchive::ER_READ         : echo 'Error: Read error';break;
        case ZipArchive::ER_WRITE        : echo 'Error: Write error';break;
        case ZipArchive::ER_CRC          : echo 'Error: CRC error';break;
        case ZipArchive::ER_ZIPCLOSED    : echo 'Error: Containing zip archive was closed';break;
        case ZipArchive::ER_NOENT        : echo 'Error: No such file';break;
        case ZipArchive::ER_EXISTS       : echo 'Error: File already exists';break;
        case ZipArchive::ER_OPEN         : echo 'Error: Can\'t open file';break;
        case ZipArchive::ER_TMPOPEN      : echo 'Error: Failure to create temporary file';break;
        case ZipArchive::ER_ZLIB         : echo 'Error: Zlib error';break;
        case ZipArchive::ER_MEMORY       : echo 'Error: Malloc failure';break;
        case ZipArchive::ER_CHANGED      : echo 'Error: Entry has been changed';break;
        case ZipArchive::ER_COMPNOTSUPP  : echo 'Error: Compression method not supported';break;
        case ZipArchive::ER_EOF          : echo 'Error: Premature EOF';break;
        case ZipArchive::ER_INVAL        : echo 'Error: Invalid argument';break;
        case ZipArchive::ER_NOZIP        : echo 'Error: Not a zip archive';break;
        case ZipArchive::ER_INTERNAL     : echo 'Error: Internal error';break;
        case ZipArchive::ER_INCONS       : echo 'Error: Zip archive inconsistent';break;
        case ZipArchive::ER_REMOVE       : echo 'Error: Can\'t remove file';break;
        case ZipArchive::ER_DELETED      : echo 'Error: Entry has been deleted';break;
       
        default: echo ('Unknown status ');
        }
    break;

    case 'edit':
        
        if ( !$dir || !file_exists($directory) ) {

            echo 'Error: Access is denied.';
            exit;

        }
        $extension=substr($dir, strrpos($dir, '.')+1);
        if (!in_array($extension,$edit)) {
            echo 'Error: File extension not recognized';
            exit;
        }
        $filesize = filesize($directory);
        if ($filesize>1024*1024*8) {
            echo 'Error: file is too big';
            exit;
        }
        ini_set('default_charset', 'ISO-8859-1');
        header("content-type:text/plain;charset=ISO-8859-1");
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        $content = @file_get_contents($directory);
        if ($filesize && !$content) {
            echo 'Error: Could not open file';
            exit;
        }
        echo $content;
    break;
    case 'save':
        $content=iconv("UTF-8", "CP1252", json_decode($_POST['content']));
        if ( !$dir || !file_exists($directory) ) {

            echo 'Error: Access is denied.';
            exit;

        }
        $fp = fopen($directory, 'w');
        if (!$fp) {
            echo 'Error: Could not save file';
            exit;
        }
        fwrite($fp, $content);
        fclose($fp);
        echo 'File saved';
    break;

/* FTP */
    case 'ftpnewFolder':
        $nf  = trim($_POST['nf']);
        $directory = $directory.'\\'.$nf;
        if ( !$nf ) {

            echo 'Error: Directory has no name';
            exit;
        }
        if (preg_match('/[\\/:\*?"<>|]/',$nf)) {

            echo 'Error: Invalid Character found';
            exit;
        }
        $ftp=ftp_conn($sid);
        $directory = str_replace('//','/',trim($users[$_SESSION['login']]['ftp'][$sid]['prefix'].'/'.$dir,'/'));

        if (@ftp_chdir($ftp, $directory.'/'.$nf)) {

            echo 'Error: Directory exists';
            exit;

        }
        ftp_chdir($ftp, $directory);
        if (ftp_mkdir($ftp, $nf)) {

            @ftp_chmod($ftp, 0777,$nf);
            exit;

        }
        echo 'Error: Directory couldn\'t be created ';

    break;

    case 'ftprmFiles':
        $ftp=ftp_conn($sid);
        $directory = str_replace('//','/',trim($users[$_SESSION['login']]['ftp'][$sid]['prefix'].'/'.$dir,'/'));

        //ftp_chdir($ftp, $directory);

        $errFiles     = 0;
        $countFiles   = 0;

        ftp_rdel($ftp,$directory, $errFiles,  $countFiles);
        
        $error ='';
        if ($errFiles) $error='Error: ';
        if ($countFiles>1) {

            echo $error.($countFiles-$errFiles).' out of '.$countFiles.' files were deleted.';

        } else {

            echo $error.($countFiles-$errFiles).' out of '.$countFiles.' file was deleted.';

        }
 
    break;

    case 'ftpcopy':
        set_time_limit (300);
        $id2  = $_POST['id2'];
        $sid2 = $_POST['sid2'];
        $dir2 = trim(str_replace(array('../','./','..\\','.\\'),array('','','',''),$_POST['dir2']));
        //j

        $files2copy = json_decode($_POST['files'], true);
        $errFiles     = 0;
        $countFiles   = 0;

        if (!preg_match('/^ftp-(\d+)$/',$sid2,$m)) { echo 'Error: Function'; exit;}
        //ftp;
        $sid2 = $m[1]-1;
        $ftp=ftp_conn($sid2);
        $directory2 = trim($users[$_SESSION['login']]['ftp'][$sid2]['prefix'].'/'.$dir2,'/');
        $files_count=sizeof($files2copy);


        if (function_exists('bzopen') && $files_count == 1 && $_POST['bz2']=='true') {
            $dir = trim(implode('\\', explode( '\\', str_replace('/','\\',$files2copy[0]['file']) ) ),"\\");
            $dir = trim(implode('\\', explode( '\\', str_replace('/','\\',$directory.'\\'.$dir) ) ),"\\");
            $bz2_content = file_get_contents($dir);
            $bz2_file = $directory.'\\'.$files2copy[0]['file'].'.bz2';
            $bz = bzopen($bz2_file , "w");
            bzwrite($bz, $bz2_content);
            bzclose($bz);
            $files2copy[0]['file'].='.bz2';
        } else {$bz2_file='';}

       
        for ($i=0;$i<$files_count;$i++) {
        
            $dir       = trim(implode('\\', explode( '\\', str_replace('/','\\',$files2copy[$i]['file']) ) ),"\\");
            $directory = trim(implode('\\', explode( '\\', str_replace('/','\\',$server[$sid]['installdir'].'\\'.$dir) ) ),"\\");
            $dir2  = basename($dir);
            if (!file_exists($directory)) continue;
            if ($files2copy[$i]['isFolder']) {
                @ftp_mkdir($ftp, $directory2.'/'.$dir2);
                ftp_putAll($ftp, $directory, $directory2.'/'.$dir2, $errFiles, $countFiles);
            } else {
                $countFiles++;
                $errFiles+= true !==  ftp_put($ftp, $directory2.'/'.$dir2, $directory, FTP_BINARY);
            }
        }
        
        if ($bz2_file and file_exists($bz2_file)) {
            unlink($bz2_file);
        }

        $error ='';
        if ($errFiles) $error='Error: ';
        if ($countFiles>1) {

            echo $error.($countFiles-$errFiles).' out of '.$countFiles.' files were copied.';

        } else {

            echo $error.($countFiles-$errFiles).' out of '.$countFiles.' file was copied.';

        }
 
    break;

    case 'ftpcopy2':
        set_time_limit (300);
        $id2  = $_POST['id2'];
        $sid2 = $_POST['sid2'];
        $dir2 = trim(str_replace(array('../','./','..\\','.\\'),array('','','',''),$_POST['dir2']));
        //j
        if ( isset($server[$sid2]) && (( is_array($users[$_SESSION['login']]['svr']) && in_array($server[$sid2]['ip'].':'.$server[$sid2]['port'], $users[$_SESSION['login']]['svr']) ) || preg_match('/a/',$_SESSION['level'])) ) {
        
                $dir2       = trim(implode('\\', explode( '\\', str_replace('/','\\',$dir2) ) ),"\\");
                $directory2 = trim(implode('\\', explode( '\\', str_replace('/','\\',$server[$sid2]['installdir'].'\\'.$dir2) ) ),"\\");
        
        } else {
        
            die('Error: Access is denied.');
        
        }
        if (!file_exists($directory2)) {echo 'Error: Unknown Destination';exit;};
        $files2copy = json_decode($_POST['files'], true);
        $errFiles     = 0;
        $countFiles   = 0;

        $ftp=ftp_conn($sid);
        $files_count=sizeof($files2copy);
        
        for ($i=0;$i<$files_count;$i++) {
        
            $dir       = trim(implode('/', explode( '/', str_replace('\\','/',$files2copy[$i]['file']) ) ),"\\");
            $directory = str_replace('//','/',trim($users[$_SESSION['login']]['ftp'][$sid]['prefix'].'/'.$dir,'/'));
            $dir2  = basename($dir);
            //if (!file_exists($directory)) continue;
            if ($files2copy[$i]['isFolder']) {
                if (!file_exists($directory2.'\\'.$dir2)) @mkdir($directory2.'\\'.$dir2,0777); 
                ftp_getAll($ftp, $directory, $directory2.'\\'.$dir2, $errFiles, $countFiles);
            } else {
                $countFiles++;
                $errFiles+= true !==  @ftp_get($ftp, $directory2.'\\'.$dir2, $directory, FTP_ASCII);
            }
        }

        $error ='';
        if ($errFiles) $error='Error: ';
        if ($countFiles>1) {

            echo $error.($countFiles-$errFiles).' out of '.$countFiles.' files were copied.';

        } else {

            echo $error.($countFiles-$errFiles).' out of '.$countFiles.' file was copied.';

        }

    break;

    default:
        echo 'Error: Unknow command';
    break;

}

?>