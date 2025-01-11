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

//File
$file = __DIR__ . '..\..\..\include\games.list.xml';

//File not found
if (!file_exists($file)) {

        die('Missing games.list.xml');

}

//simplexml
$xml = @simplexml_load_file($file);

//Parse error
if ( !$xml ) {

    die('no games in games.list.xml');

}

//Require Attr
$attr  = array('name','game','server','login','app_set_config','config','srcds');
$games = [];
$game  = [];
foreach($xml->children() as $child) {
   
    //AppID defined
    $role = $child->attributes();
    $role = trim(strtolower((string)$role));

    //Valid AppID
    if ( $role && preg_match("/[A-Za-z0-9]+/", substr( $role ,0,1) ) ) {

        if ( isset($game[$role]) ) {

            //Game already declared
            die('Game already declared in games.list.xml');

        }

        foreach($child as $k => $v) {

            $key = (string)$k;

            //Required Attribute
            if (in_array($key, $attr))
            $game[$role][(string)$key] = trim((string)$v);

        }

        //All Attr
        if (  isset($game[$role]['name']) && isset($game[$role]['game']) && isset($game[$role]['server']) ) {
        
            $games[$role] = $game[$role];

            if (!isset($games[$role]['login']) || $games[$role]['login'] != 'true') {
                $games[$role]['login'] = false;
            } else {
                $games[$role]['login'] = true;
            }

            if (!isset($games[$role]['app_set_config']) || !$games[$role]['app_set_config']) {
                $games[$role]['app_set_config'] = '';
            } else {
                $games[$role]['app_set_config'] = ' +app_set_config '.$games[$role]['app_set_config'];
            }
        
            if (!isset($games[$role]['config']) || !$games[$role]['config']) {
                $games[$role]['config'] = 'cfg\server.cfg';
            } else {
                $games[$role]['config'] = preg_replace('/^\//','',str_replace("/","\\", $games[$role]['config']));
            }
        
            if (!isset($games[$role]['srcds']) || !$games[$role]['srcds']) {
                $games[$role]['srcds'] = 'srcds_win64.exe';
            } else {
                $games[$role]['srcds'] = preg_replace('/^\//','',str_replace("/","\\", $games[$role]['srcds']));
            }

        } else {

            //error missing attributes
            die('Missing attributes in games.list.xml');

        }

        if ( !($game[$role]['game'] )) {

            //error missing attributes
            die('Missing attributes in games.list.xml');

        }

        if ( !($game[$role]['server'] )) {

            //error missing attributes
            die('Missing attributes in games.list.xml');

        }
 
    }

}



?>
