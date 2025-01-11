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

require 'games.php';
require 'proc.php';

function Priority($prio)
{
    switch ($prio) {

        case 'real time'    :
        case '256'          :
        case 256            : return 256; //real time

        case 'high priority':
        case '128'          :
        case 128            : return 128; //high priority

        case 'above normal' :
        case '32768'        :
        case 32768          : return 32768; //above normal

        case 'normal'       :
        case '32'           :
        case 32             : return 16384; //normal

        case 'below normal' :
        case '16384'        :
        case 16384          : return 16384; //below normal

        case 'idle'         :
        case '64'           :
        case 64             : return 64; //idle

        default             : return 32768; //above normal

    }
}

//Attr steamcmd
$steamcmd = array('steamcmddir',
                  'xcopy',
                  'updatefile',
                  'delay',
                  'email',
                  'priority',
                  'cleanlog',
                  'restart',
                  'empty',
                  'pingable',
                  'plugin');

$email    = array('smtp',
                  'smtp_port',
                  'smtp_ssl',
                  'auth_username',
                  'auth_password',
                  'sendmail_to',
                  'sendmail_from',
                  'alert');

$delay    = array('steam',
                  'ping',
                  'start',
                  'failure',
                  'quit',
                  'say');
//Attr server
$attr     = array('fname',
                  'installdir',
                  'srcds',
                  'ip',
                  'port',
                  'login',
                  'priority',
                  'map',
                  'maxplayers',
                  'cmd',
                  'rcon_say',
                  'xcopy',
                  'updatefile',
                  'pingable',
                  'plugin');

//var
$server = [];
$default=0;
$common=0;
//File
$file = __DIR__ . '..\..\..\config.xml';

//simplexml
$xml = @simplexml_load_file($file);

//Parse error or no file
if ( !$xml ) {

    $server[0] = $steamcmd;
        $server[0]['steamcmddir'] = "";
        $server[0]['priority'] = 32768;
        $server[0]['pingable'] = true;
        $server[0]['alert'] = "";
        $server[0]['plugin'] = false;
        $server[0]['xcopy'] = true;
        $server[0]['updatefile'] = "sz.update.txt";
        $server[0]['cleanlog'] = 7;
        $server[0]['restart'] = 'auto';
        $server[0]['empty'] = true;
        $server[0]['delay']['steam']=120;
        $server[0]['delay']['ping']=120;
        $server[0]['delay']['start']=120;
        $server[0]['delay']['failure']=600;
        $server[0]['delay']['quit']=120;
        $server[0]['delay']['say']=20;
        $server[0]['email']['smtp'] = '';
        $server[0]['email']['smtp_port'] = '';
        $server[0]['email']['smtp_ssl'] = '';
        $server[0]['email']['auth_username'] = '';
        $server[0]['email']['auth_password'] = '';
        $server[0]['email']['sendmail_to'] = '';
        $server[0]['email']['sendmail_from'] = '';
        $server[0]['email']['alert'] = '';
        $server[1]= $attr;
        foreach ($attr as $value) {
            $server[1][$value]='';
        }
        $server[1]['fname'] = '';
        $server[1]['priority'] = 32768;
        $server[1]['maxplayers'] = 24;
        $server[1]['pingable'] = true;
        $server[1]['pid'] = false;
        $default=1;
        $common=1;
    return;
}


//STEAMCMD
foreach($xml->children() as $child) {

    //!steamcmd
    if (strtolower( $child->getName() ) != 'steamcmd') continue;

    foreach($child as $k => $value) {

        $key = strtolower((string)$k);

        //Required Attribute
        if ( !in_array($key, $steamcmd) ) continue;

        if ($key == 'delay') {
            foreach($child->delay->children() as $d => $v) {

                $d = strtolower((string)$d);
                if ( !in_array($d, $delay) && is_numeric($v) ) continue;
                $server[0]['delay'][$d] = trim((int)$v);

            }

        } elseif ($key == 'email') {
            foreach($child->email->children() as $d => $v) {

                $d = strtolower((string)$d);
                if ( !in_array($d, $email) ) continue;
                $server[0]['email'][$d] = trim($v);

            }

        } else {

            $server[0][$key] = trim((string)$value);

        }

    }

    break;

}

//No steamcmd
if ( !isset($server[0]) ) {

    foreach ($steamcmd as $value) {
        $server[0][$value]='';
    }

}

//Missing Dir
if ( !isset($server[0]['steamcmddir']) ) {

    $server[0]['steamcmddir']="";

}

$server[0]['steamcmddir'] = preg_replace('/\/$|\\$/', '', str_replace("/","\\",$server[0]['steamcmddir']));

if ( !file_exists($server[0]['steamcmddir']) ) {

    $server[0]['steamcmddir']="";

}

//xcopy:
if (isset($server[0]['xcopy'])) {

    $server[0]['xcopy'] = 'true' == $server[0]['xcopy'];

} else {

    $server[0]['xcopy'] = 0;

}

//updatefile
if ( !isset($server[0]['updatefile']) || !$server[0]['updatefile'] ) {

        $server[0]['updatefile'] = 'sz.update.txt';

}

//priority
if ( !isset($server[0]['priority']) || !$server[0]['priority']) {

    $server[0]['priority'] = 32768;

} else {

    $server[0]['priority'] = Priority($server[0]['priority']);

}

//Email alert
if ( !isset($server[0]['email']['smtp']) ) {
    $server[0]['email']['smtp'] = '';
}
if ( !isset($server[0]['email']['smtp_port']) ) {
    $server[0]['email']['smtp_port'] = '';
}
if ( !isset($server[0]['email']['smtp_ssl']) ) {
    $server[0]['email']['smtp_ssl'] = '';
}
if ( !isset($server[0]['email']['auth_username']) ) {
    $server[0]['email']['auth_username'] = '';
}
if ( !isset($server[0]['email']['auth_password']) ) {
    $server[0]['email']['auth_password'] = '';
}
if ( !isset($server[0]['email']['sendmail_to']) ) {
    $server[0]['email']['sendmail_to'] = '';
}
if ( !isset($server[0]['email']['sendmail_from']) ) {
    $server[0]['email']['sendmail_from'] = '';
}
if ( !isset($server[0]['email']['alert']) ) {
    $server[0]['email']['alert'] ='';
}

//Clean Log
if ( !isset($server[0]['cleanlog']) || !is_numeric($server[0]['cleanlog']) ) {
    $server[0]['cleanlog'] = 7;

}

//Restart
if ( !isset($server[0]['restart']) ) {
    $server[0]['restart'] = 'auto';
}
if (isset($server[0]['empty'])) {

    $server[0]['empty'] = 'true' == $server[0]['empty'];

} else {

    $server[0]['empty'] = 1;

}

//Pingable
if ( !isset($server[0]['pingable']) ) {

    $server[0]['pingable'] = 'true';

}
//Plugin
if ( isset($server[0]['plugin']) ) {
    $server[0]['plugin']  = 'true' == $server[0]['plugin'];

} else {
       
    $server[0]['plugin'] = false;

}

//Overwrite timers
if ( !isset($server[0]['delay']) ) {
     $default=1;
    $server[0]['delay'] = array();
}
if ( !isset($server[0]['delay']['steam']) || $server[0]['delay']['steam'] < 30 ) {
    $server[0]['delay']['steam'] = 120;
}
if ( !isset($server[0]['delay']['ping']) || $server[0]['delay']['ping'] < 30 ) {
    $server[0]['delay']['ping'] = 120;
}
if ( !isset($server[0]['delay']['start']) || $server[0]['delay']['start'] < 30 ) {
    $server[0]['delay']['start'] = 120;
}
if ( !isset($server[0]['delay']['failure']) || $server[0]['delay']['failure'] < $server[0]['delay']['start'] ) {
    $server[0]['delay']['failure'] = 600;
}
if ( !isset($server[0]['delay']['quit']) || $server[0]['delay']['quit'] < 0 ) {
    $server[0]['delay']['quit'] = 120;
}
if( !isset($server[0]['delay']['say']) || $server[0]['delay']['say'] < 15 ) {
    $server[0]['delay']['say'] = 20;
}

//SERVER

$id = 0;

foreach($xml->children() as $child) {

    //!steamcmd
    if (strtolower( $child->getName() ) == 'steamcmd') continue;

    //AppId
    $role = trim(strtolower((string)$child->attributes()));
    if ( $role && preg_match("/[A-Za-z0-9]+/", $role) ) {
    
        //create new server
        $id++;
        $server[$id]['appid'] = $role;
        $server[$id]['pid'] = '';
        foreach($child as $k => $value) {
        
            $key = strtolower((string)$k);
        
            if ( in_array($key, $attr) ) {
        
                $server[$id][$key] = trim((string)$value);
        
            }
        
        }

        //Check Config:
        
        //not a game
        if ( !isset($games[$role]) ) {
        
            $id--;//server dropped
            continue;
        
        }
        //Game
        $server[$id]['game'] = $games[$role]['game'];

        //Install dir
        if ( !isset($server[$id]['installdir']) ) {
        
            $id--;//server dropped
            continue;
        
        }

        $server[$id]['installdir'] = preg_replace('/\/$|\\$/', '', str_replace("/","\\",$server[$id]['installdir']));
        if ( !$server[$id]['installdir'] || $server[$id]['installdir'] == $server[0]['steamcmddir'] ) {

            $id--;//server dropped
            continue;

        }

        //IP
        if ( !isset($server[$id]['ip']) || !$server[$id]['ip'] ) {

            $id--;//server dropped
            continue;

        }

        //Port
        if ( !isset($server[$id]['port']) ) {

            $id--;//server dropped
            continue;

        }

        // app
        if ( !isset($server[$id]['srcds']) ) {
            $server[$id]['srcds']=$games[$role]['srcds'];
        }

        //Optionnal
        if ( !isset($server[$id]['fname']) ) {

            $server[$id]['fname'] = '';

        }
        //Priority
        if ( !isset($server[$id]['priority']) ) {
            $common=1;
            $server[$id]['priority'] = $server[0]['priority'];

        } else {

            $server[$id]['priority'] = Priority($server[$id]['priority']);

        }

        //Map
        if ( !isset($server[$id]['map']) ) {
        
            $server[$id]['map'] = '';
        
        }

        //maxplayers              
        if ( !isset($server[$id]['maxplayers']) ) {
        
            $server[$id]['maxplayers'] = '';
        
        }
       
        //cmd            
        if ( !isset($server[$id]['cmd']) ) {//-usercon' if $server[$id]['rcon_password']
        
            $server[$id]['cmd'] = '';

        }

        //rcon_say           
        if ( !isset($server[$id]['rcon_say']) || !$server[$id]['rcon_say']) {
        
            $server[$id]['rcon_say'] = 'say';
        
        }

        //xcopy
        if ( isset($server[$id]['xcopy']) ) {

            $server[$id]['xcopy']  = 'true' == $server[$id]['xcopy'];
        
        } else {
      
            $server[$id]['xcopy'] = $server[0]['xcopy'];

        }

        //Plugin
        if ( isset($server[$id]['plugin']) ) {

            $server[$id]['plugin']  = 'true' == $server[$id]['plugin'];
        
        } else {
      
            $server[$id]['plugin'] = $server[0]['plugin'];

        }

        //updatefile
        if ( !isset($server[$id]['updatefile']) ) {
   
                $server[$id]['updatefile'] = $server[0]['updatefile'];
       
        }
        //login
        if ( !isset($server[$id]['login']) ) {
   
                $server[$id]['login'] = '';
       
        }

        //Pingable
        if ( isset($server[$id]['pingable'] ) ) {
        
            $server[$id]['pingable'] = 'true' == $server[$id]['pingable'];
        
        } else {

            $server[$id]['pingable'] = $server[0]['pingable'];
        
        }

        //Running?
        $server[$id]['pid'] = processID($server[$id]['srcds'], $server[$id]['installdir'].'\\'.$server[$id]['srcds']);

    }//if role

}//foreach

if (sizeof($server)<2) {
    foreach ($attr as $value) {
        $server[1][$value]='';
    }
        $server[1]['fname'] = '';
        $server[1]['priority'] = 32768;
        $server[1]['maxplayers'] = 24;
        $server[1]['pingable'] = true;
        $server[1]['pid'] = false;
        $common=1;
}
?>