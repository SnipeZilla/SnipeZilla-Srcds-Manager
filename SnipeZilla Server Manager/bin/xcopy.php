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
function xcopy($source, $dest, $exclude, $safe = false, $print = false )
{
    $source = str_replace('/', '\\', $source);
    $dest   = str_replace('/', '\\', $dest);
    // Check for symlinks
    if (is_link($source)) {
        return symlink(readlink($source), $dest);
    }

    // Simple copy for a file
    if (is_file($source)) {

        $arr =  explode('\\',  strtolower($source));
        foreach ($exclude as $skip)
        {
            if (in_array($skip, $arr)) return false;
        }
        if (!$safe && file_exists($dest) && (filemtime($dest) > filemtime($source))) return false;
		if ($print) echo($source."\n");
        return copy($source, $dest);
    }

    // Make destination directory
    if (!is_dir($dest)) {
		echo 'mkdir: '.$dest."\n";
        mkdir($dest, 0755);
    }

    // Loop through the folder
    $dir = dir($source);
    while (false !== $entry = $dir->read()) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        // Deep copy directories
        xcopy($source.'\\'.$entry, $dest.'\\'.$entry, $exclude, $safe, $print);
    }

    // Clean up
    $dir->close();
    return true;
}
//Get vars
$source = $argv[1];
$dest   = $argv[2];
$game   = $argv[3];
$safe   = $argv[4] == '1';
$print  = $argv[5] == '1';

//Root
$root = exec('chdir',$o,$r);
$root = preg_replace('/\\\\bin$/','',$root);
$excludePath = 'include\xcopy.exclude.txt';

//Exclude file/folder
if ( !file_exists($root.'\\'.$excludePath) ) {

    $file = fopen($root.'\\'.$excludePath, "w");
    fwrite($file, "motd.txt\r\nserver.cfg");
    fclose($file);

}

$exclude=array('steam.inf');
$excludeFile = fopen($root.'\\'.$excludePath, "r");
if ($excludeFile) {
    while (($line = fgets($excludeFile)) !== false) {
        //!comments
        $line = preg_replace('/^\s+|\n|\r|\/\/.*|\s+$/m', '', $line);
        if ($line) {
             $exclude[]= strtolower($line);
        }
    }
    fclose($excludeFile);
}
//copy
if (file_exists($dest.'\\'.$game.'\steam.inf')) @unlink($dest.'\\'.$game.'\steam.inf');
xcopy($source, $dest, $exclude, $argv[4], $argv[5]);
copy($source.'\\'.$game.'\steam.inf', $dest.'\\'.$game.'\steam.inf');
