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
class SzMgr
{
    private $version = '1.3.0';              // Sz srcds version
    private $server;                         // Srcds server
    private $games;                          // Games list
    private $msg;                            // Log messages
    private $id;                             // Server id
    private $total;                          // Total server(s)
    private $__DIR;                          // Root Folder

    public function __construct()
    {
        $this->Init();
    }

//-------------------------------------------
//  Get server A2S_INFO
//-------------------------------------------
    private function A2S_INFO()
    {
        //request format
        $a_Header = 0x54;
        $r_Header = 0x49;
        $Payload = "Source Engine Query\0";
        $r_challenge = 0x41;

        //Command
        $req  = pack( 'ccccc', 0xFF, 0xFF, 0xFF, 0xFF, $a_Header). $Payload;
        $resp = pack( 'c', $r_Header);
        $challenge = pack( 'c', $r_challenge);
        $ln   = strlen( $req );

        //Data
        $data = '';

        //opensocket
        $fp = @fsockopen('udp://' . $this->server[$this->id]['ip'], $this->server[$this->id]['port'], $errno, $errstr, 2);

        //connected
        if ( !$fp || empty($fp) ) {
            //error connection
            $this->Log(61, $errno, $errstr);

        } else {

            //Request AS_2INFO
            @fwrite($fp, $req, $ln);

            //Response
            stream_set_timeout($fp, 5);
            $data = @fread ($fp, 1400) ;
            $info['response_type']   = substr($data, 4, 1);
            if ( $info['response_type'] == $challenge) {
                $challenge = substr($data, 5, strlen($data));
                $req = pack( 'ccccc', 0xFF, 0xFF, 0xFF, 0xFF, $a_Header).$Payload.$challenge;
                $ln  = strlen( $req );
                @fwrite($fp, $req, $ln);
                //Response
                stream_set_timeout($fp, 2);
                $data = @fread ($fp, 1400) ;
            }

        }

        //close socket
        @fclose($fp);
        $info = array();
        $info['header']          = substr($data, 0, 4);
        $info['response_type']   = substr($data, 4, 1);
        if ( $info['response_type'] != $resp ) return false;

        //Actual Players
        $data_array              = explode("\x00", substr($data, 6), 5);
        $data                    = $data_array[4];
        $this->server[$this->id]['players'] = ord(substr($data, 2, 1));
        $this->server[$this->id]['bots']    = ord(substr($data, 4, 1));

        if ( empty($this->server[$this->id]['players']) || $this->server[$this->id]['players'] == $this->server[$this->id]['bots'] ) {

            $this->server[$this->id]['empty_since'] += $this->server[0]['delay']['ping'];

        } else {

            $this->server[$this->id]['empty_since'] = 0;

        }

        //status
        return true;
    }

//-------------------------------------------
//  Clear old logs
//-------------------------------------------
    private function ClearLog()
    {
        $days = $this->server[0]['cleanlog'];
        //time out?
        if ( time() - $this->server[0]['task']['log'] < $days*24*3600 || $days == 0 ) return false;

        $filetime = time();
        $path1 = $this->__DIR.'\logs\\';
        if ($handle = opendir($path1)) {

            while (false !== ($file = readdir($handle))) {
                $filelastmodified = filemtime($path1 . $file);

                //24 hours in a day * 3600 seconds per hour
                if( (time() - $filelastmodified) > $days*24*3600 && ($file != '.' || $file != '..') )
                {

                   @unlink($path1 . $file);

                } elseif ( $filelastmodified < $filetime && ($file != '.' || $file != '..') ) {

                    $filetime = $filelastmodified;

                }

            }

            closedir($handle);

        }

        //save time for next cron
        $this->server[0]['task']['log'] = $filetime;
    }

//-------------------------------------------
//  Server Crashed?
//-------------------------------------------
    private function Crash()
    {
        //Crashed?
        if ( $this->Proc($this->server[$this->id]['pid']) ) return false;

        //Remove pid & proc
        $this->server[$this->id]['pid'] = 0;

        //Server was stopped
        if ( file_exists($this->server[$this->id]['installdir'].'\\'.$this->server[$this->id]['game'].'\sz.stop.txt') ) return true;

        //Server was restarted
        if ( $this->server[$this->id]['status'] == 'restart' ) return true;

        //Set status
        $this->Status('crash',1);

        //Reset timer
        $this->server[$this->id]['timer']  = time();

        //Log crash
        $this->Log(52);
        return true;
    }

//-------------------------------------------
//  Server Crash Status
//-------------------------------------------
    private function CrashRecover()
    {
        //No crash recorded
        if ( !$this->server[$this->id]['crash'] ) return true;

        //Uptime > Safe Start
        if ( time()- $this->server[$this->id]['time'] > $this->server[0]['delay']['start'] ) {

            //Ping
            if ( !$this->Ping() ) return false;

            //reset
            $this->Status('',0);

            //server is stable
            $this->server[$this->id]['timer'] = time();
            $this->Log(54);

            //OK for monitoring
            return true;

        }

        //Restart not completed
        return false;
    }

//-------------------------------------------
//  Server Crash Status
//-------------------------------------------
    private function CrashStatus()
    {
        if ( $this->server[$this->id]['status'] == 'crash' ) {

            //Crashed:
            if ( $this->server[$this->id]['crash'] >= 3 ) {

                //More than 3x, extend interval to 10mn
                if ( time() - $this->server[$this->id]['timer'] > $this->server[0]['delay']['failure'] ) {

                    //reset timer
                    $this->server[$this->id]['timer'] = time();

                    //Gave Up
                    if ( $this->server[$this->id]['crash'] > 8 ) {

                        $this->Log(56);
                        $file = @fopen($this->server[$this->id]['installdir'].'\\'.$this->server[$this->id]['game'].'\\sz.stop.txt', "w");
                        @fclose($file);
                        return true;

                    }

                    //update every 3
                    if ( $this->server[$this->id]['crash'] % 3 == 0 ) {

                        $this->Log(37);
                        $this->Update();
                        return true;

                    }

                    if ( $this->server[$this->id]['crash'] % 3 == 1 ) {

                        $this->Log(37);
                        $this->Update();
                        return true;

                    }

                } else {

                    //too many crashes. something wrong.
                    if ($this->server[$this->id]['crash'] == 3) {

                        $this->Log(53);
                        $this->Log(37);
                        $this->Update(); //repair?

                    }

                    return true;

                }

            }

        }

        return false;
    }

//-------------------------------------------
//  Del File
//-------------------------------------------
    private function DelFile($path)
    {
        //File?
        if (!file_exists($path)) return false;

        //Delete File
        unlink($path);
    }

//-------------------------------------------
//  New Folder (1)
//-------------------------------------------
    private function Dir($dirName, $rights=0777, $check=false)
    {
        if (!$dirName) return $check;

        $dirs = explode('\\', $dirName);
        $dir='';

        foreach ($dirs as $part) {

            $dir.=$part.'\\';

            if (!is_dir($dir) && strlen($dir)>0) {

                if ($check) return true;
                mkdir($dir);
                chmod($dir, $rights);

            }

        }
    }

//-------------------------------------------
//  Email alert
//-------------------------------------------
    private function Email()
    {

        if ( !isset($this->server[0]['email']['mail']) || empty($this->server[0]['email']['mail']) ) return false;

        //smtp-crash.txt
        if ( $this->server[0]['email']['postpone'][2] == 1 ) {
            $crash = $this->__DIR.'\bin\smtp-crash.txt';
            if ( $error = $this->ReadFile($crash, '', true) ) {
            
                $this->Log(70, $error);
                @unlink($crash);
            
            }
            $this->server[0]['email']['postpone'][2] = 0;
        }

        //nothing to send
        if ( !$this->server[0]['email']['message'] ) return false;

        //30s between email
        if ( time() - $this->server[0]['email']['postpone'][0] < $this->server[0]['email']['postpone'][1] ) return false;

        //SMTP running
        $proc = preg_split('/\w+=/', shell_exec('wmic process where name="php.exe" get ProcessId, Commandline /FORMAT:LIST'),-1, PREG_SPLIT_NO_EMPTY);
        $numb = sizeof($proc);
        for ($i=1; $i<$numb; $i++) {
        
            if ( preg_match('/.*bin\\\\sendmail.php"?/i', $proc[$i]) ) {

                if ( (time() - $this->server[0]['email']['postpone'][0]) < 120 ) { //2 mn postpone

                    if (time() - $this->server[0]['email']['postpone'][0] > $this->server[0]['email']['postpone'][2]) {
                        $this->Log(69, time() - $this->server[0]['email']['postpone'][0]);
                        $this->server[0]['email']['postpone'][2] += 30;
                    }

                    return false; 

                } else {

                    $pid = (int)$proc[$i+2];
                    if ($pid) shell_exec("taskkill /F /PID ".$pid.""); //kill sendmail
                    break;

                }

            }
        
        }

        //Message
        $message = "SnipeZilla Srcds Manager ".$this->version."\r\n\r\n";
        foreach ( $this->server[0]['email']['message'] as $k => $msg ) {
            foreach ( $msg as $key => $value ) {
                if ($key == 'alert')   $message .= "Alert: ".$value."\r\n";            
                if ($key == 'time')    $message .= "Time: ".$value."\r\n"; 
                if ($key == 'server')  $message .= "Server: ".$value."\r\n";            
                if ($key == 'message') $message .= "Message: ".$value."\r\n\r\n";
            }
        }

        //Wrap
        $message = wordwrap($message, 70, "\r\n");

        //Sendmail path
        $sendmail = $this->__DIR.'\bin\php.exe -f \"'.$this->__DIR.'\bin\sendmail.php\" \"'.$this->server[0]['email']['mail'].'\" \"'.base64_encode($message).'\" ';

        //Run sendmail
        shell_exec('wmic process call create "'.$sendmail.'","'.($this->__DIR.'\bin').'"');
        $this->server[0]['email']['postpone'][0] = time();
        $this->server[0]['email']['postpone'][2] = 1;
        $this->server[0]['email']['message']     = array();
    }

//-------------------------------------------
//  Config
//-------------------------------------------
    private function GetConfig()
    {
        //File
        $file = $this->__DIR.'\config.xml';

        //File not found
        if (!file_exists($file)) {

                $this->Log(0, $file);
                return false;

        }

        //simplexml
        $xml = @simplexml_load_file($file);

        //Parse error
        if ( !$xml ) {

            $this->Log(1, $file);
            return false;

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
        $attr     = array('installdir',
                          'srcds',
                          'fname',
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
        $this->server = [];
        $xcopy        = true;

        //STEAMCMD
        foreach($xml->children() as $child) {

            //!steamcmd
            if (strtolower( $child->getName() ) != 'steamcmd') continue;

            foreach($child as $k => $value) {

                $key = strtolower((string)$k);

                //Required Attribute
                if ( !in_array($key, $steamcmd) ) continue;

                if ($key == 'delay' ) { //delay

                    foreach($child->delay->children() as $d => $v) {

                        $d = strtolower((string)$d);
                        if ( !in_array($d, $delay) && is_numeric($v) ) continue;
                        $this->server[0]['delay'][$d] = trim((int)$v);

                    }

                } elseif ($key == 'email') { //email

                    foreach($child->email->children() as $d => $v) {

                        $d = strtolower((string)$d);
                        if ( !in_array($d, $email) ) continue;
                        $this->server[0]['email'][$d] = trim($v);

                    }

                } else {

                    $this->server[0][$key] = trim((string)$value);

                }

            }

            break;

        }

        //No steamcmd
        if ( !isset($this->server[0]) ) {
            $this->Log(2, 'steamcmd', 'config.xml');
            return false;
        }

        //Missing Dir
        if ( !isset($this->server[0]['steamcmddir']) ) {
            $this->Log(3, 'steamcmd', 'config.xml');
            return false;
        }

        $this->server[0]['steamcmddir'] = preg_replace('/\/$|\\$/', '', str_replace("/","\\",$this->server[0]['steamcmddir']));

        if ( !file_exists($this->server[0]['steamcmddir']) ) {
            $this->Log(3, 'steamcmd', 'config.xml');
            return false;
        }

        //xcopy:
        if (isset($this->server[0]['xcopy'])) {
            $this->server[0]['xcopy'] = 'true' == $this->server[0]['xcopy'];
        } else {
            $this->server[0]['xcopy'] = false;
        }

        //Plugin:
        if (isset($this->server[0]['plugin'])) {
            $this->server[0]['plugin'] = 'true' == $this->server[0]['plugin'];
        } else {
            $this->server[0]['plugin'] = false;
        }

        //updatefile
        if ( !isset($this->server[0]['updatefile']) || !$this->server[0]['updatefile'] ) {
                $this->server[0]['updatefile'] = 'sz.update.txt';
        }

        //priority
        if ( isset($this->server[0]['priority']) ) {
            $this->server[0]['priority'] = $this->Priority($this->server[0]['priority']);
        } else {
            $this->server[0]['priority'] = $this->Priority('above normal');
        }

        //Email alert
        if (isset($this->server[0]['email']['sendmail_to']) && !empty($this->server[0]['email']['sendmail_to']) ) {

            //ini_set smtp
            if ( !isset($this->server[0]['email']['smtp']) || empty($this->server[0]['email']['smtp']) ) {
                $this->server[0]['email']['smtp']='localhost';
            }
            if ( !isset($this->server[0]['email']['smtp_port']) || empty($this->server[0]['email']['smtp_port']) ) {
                $this->server[0]['email']['smtp_port']=25;
            }
            if ( isset($this->server[0]['email']['smtp_ssl']) ) {
                $this->server[0]['email']['smtp_ssl'] = 'true' == $this->server[0]['email']['smtp_ssl'];
            } else {
                $this->server[0]['email']['smtp_ssl'] = false;
            }
            if ( !isset($this->server[0]['email']['auth_username']) || empty($this->server[0]['email']['auth_username']) ) {
                $this->server[0]['email']['auth_username']='';
            }
            if ( !isset($this->server[0]['email']['auth_password']) || empty($this->server[0]['email']['auth_password']) ) {
                $this->server[0]['email']['auth_password']='';
            }
            if ( !isset($this->server[0]['email']['sendmail_from']) || empty($this->server[0]['email']['sendmail_from']) ) {
                $this->server[0]['email']['sendmail_from']='no-reply@snipezilla.com';
            }
            if ( !isset($this->server[0]['email']['alert']) ) {
                $this->server[0]['email']['alert'] ='';
            }
            $this->server[0]['email'] = array('mail' => base64_encode(serialize(array('smtp'  => $this->server[0]['email']['smtp'],
                                                                                      'smtp_port' => $this->server[0]['email']['smtp_port'],
                                                                                      'smtp_ssl' => $this->server[0]['email']['smtp_ssl'],
                                                                                      'auth_username' => $this->server[0]['email']['auth_username'],
                                                                                      'auth_password' => $this->server[0]['email']['auth_password'],
                                                                                      'sendmail_to' => $this->server[0]['email']['sendmail_to'],
                                                                                      'sendmail_from' => $this->server[0]['email']['sendmail_from']))),
                                                                                      'alert' => $this->server[0]['email']['alert'],
                                                                                      'postpone' => array(time(),30,0),
                                                                                      'message' => array());

        } else {

            //No email
            $this->server[0]['email'] = array('mail' => '', 'alert' => '', 'postpone' => array(time(),30,0),'message' => array());

        }


        //Clean Log
        if ( isset($this->server[0]['cleanlog']) && is_numeric($this->server[0]['cleanlog']) ) {
            $this->server[0]['cleanlog'] = (int)$this->server[0]['cleanlog'];
        } else {
            $this->server[0]['cleanlog'] = 7;
        }

        //Pingable
        if ( isset($this->server[0]['pingable']) ) {
            $this->server[0]['pingable'] = 'true' == $this->server[0]['pingable'];
        } else {
            $this->server[0]['pingable'] = true;
        }

        //Restart
        if ( !isset($this->server[0]['restart']) ) {
            $this->server[0]['restart'] = 'auto';
        }

        if ( isset($server[0]['empty']) && $this->server[0]['pingable'] ) {
            $this->server[0]['empty'] = 'true' == $this->server[0]['empty'];
        } else {
            $this->server[0]['empty'] = $this->server[0]['pingable'];
        }

        //Overwrite timers
        if ( !isset($this->server[0]['delay']) ) {
            $this->server[0]['delay'] = array();
        }
        if ( !isset($this->server[0]['delay']['steam']) || $this->server[0]['delay']['steam'] < 30 ) {
            $this->server[0]['delay']['steam'] = 120;
        }
        if ( !isset($this->server[0]['delay']['ping']) || $this->server[0]['delay']['ping'] < 30 ) {
            $this->server[0]['delay']['ping'] = 120;
        }
        if ( !isset($this->server[0]['delay']['start']) || $this->server[0]['delay']['start'] < 30 ) {
            $this->server[0]['delay']['start'] = 120;
        }
        if ( !isset($this->server[0]['delay']['failure']) || $this->server[0]['delay']['failure'] < $this->server[0]['delay']['start'] ) {
            $this->server[0]['delay']['failure'] = 600;
        }
        if ( !isset($this->server[0]['delay']['quit']) || $this->server[0]['delay']['quit'] < 0 ) {
            $this->server[0]['delay']['quit'] = 120;
        }
        if ( !isset($this->server[0]['delay']['say']) || $this->server[0]['delay']['say'] < 15 ) {
            $this->server[0]['delay']['say'] = 15;
        }

        //Internal Var
        $this->server[0]['time'] = '';
        $this->server[0]['task']['log'] = time()-($this->server[0]['cleanlog']*24*3600)-1;
        $this->server[0]['status'] = 'off';

        //SERVER
        $this->id = 0;

        foreach($xml->children() as $child) {

            //!steamcmd
            if (strtolower( $child->getName() ) == 'steamcmd') continue;

            //AppId
            $role = trim(strtolower((string)$child->attributes()));
            if ( $role && preg_match("/[A-Za-z0-9]+/", $role) ) {

                //create new server
                $this->id++;
                $this->server[$this->id]['appid'] = $role;

                foreach($child as $k => $value) {

                    $key = strtolower((string)$k);

                    if ( in_array($key, $attr) ) {
                        $this->server[$this->id][$key] = trim((string)$value);
                    }

                }

                //Check Config:

                //not a game
                if ( !isset($this->games[$role]) ) {
                    $this->Log(5, $role);
                    return false;
                }
                // app
                if ( !isset($this->server[$this->id]['srcds']) ) {
                    $this->server[$this->id]['srcds']=$this->server[$this->id]['installdir'].'\\'.$this->games[$this->server[$this->id]['appid']]['srcds'];
                }
                $this->server[$this->id]['app'] = substr($this->server[$this->id]['srcds'],strrpos($this->server[$this->id]['srcds'], '\\')+1);

                //Game
                $this->server[$this->id]['game'] = $this->games[$role]['game'];

                //Install dir
                if ( !isset($this->server[$this->id]['installdir']) ) {
                    $this->Log(6);
                    return false;
                }

                $this->server[$this->id]['installdir'] = preg_replace('/\/$|\\$/', '', str_replace("/","\\",$this->server[$this->id]['installdir']));

                if ( $this->server[$this->id]['installdir'] && $this->server[$this->id]['installdir'] != str_replace('\steamcmd.exe','',$this->server[0]['steamcmddir']) ) {
                    $this->Dir($this->server[$this->id]['installdir']) ;
                } else {
                    $this->Log(6);
                    return false;
                }

                //IP
                if ( !isset($this->server[$this->id]['ip']) || !$this->server[$this->id]['ip'] ) {
                        $this->Log(2, 'ip', 'server '.$this->id);
                        return false;
                }

                //Port
                if ( !isset($this->server[$this->id]['port']) ) {
                        $this->Log(2, 'port', 'server '.$this->id);
                        return false;
                }

                //install dir or Port defined?
                for ($ii=1; $ii<$this->id; $ii++) {

                    if ( $this->server[$this->id]['installdir'] == $this->server[$ii]['installdir'] && $this->server[$this->id]['ip'].':'.$this->server[$this->id]['port'] == $this->server[$ii]['ip'].':'.$this->server[$ii]['port'] ) {
                        $this->Log(9, $ii);
                        return false;
                    }

                    if ( $this->server[$this->id]['ip'].':'.$this->server[$this->id]['port'] == $this->server[$ii]['ip'].':'.$this->server[$ii]['port'] ) {
                        $this->Log(10, $ii);
                        return false;
                    }

                }

                //Optional

                //Login
                if ( isset($this->server[$this->id]['login']) && $this->server[$this->id]['login']) {
                    $this->server[$this->id]['login'] = preg_replace('/\s+/', ' ',trim($this->server[$this->id]['login']));
                } else {
                    $this->server[$this->id]['login'] = 'anonymous';
                }

                //Priority
                if ( isset($this->server[$this->id]['priority']) ) {
                    $this->server[$this->id]['priority'] = $this->Priority($this->server[$this->id]['priority']);
                } else {
                    $this->server[$this->id]['priority'] = $this->server[0]['priority'];
                }

                //Map
                if ( !isset($this->server[$this->id]['map']) ) {
                    $this->server[$this->id]['map'] = '';
                }

                //maxplayers              
                if ( !isset($this->server[$this->id]['maxplayers']) ) {
                    $this->server[$this->id]['maxplayers'] = '';
                }

                //Internal var | rcon_password
                $this->server[$this->id]['rcon_password'] = $this->ReadFile($this->server[$this->id]['installdir'].'\\'.$this->server[$this->id]['game'].'\\'.$this->games[$this->server[$this->id]['appid']]['config'],'rcon_password');

                if (!$this->server[$this->id]['rcon_password']) $this->Log(68);

                //cmd            
                if ( !isset($this->server[$this->id]['cmd']) ) {
                    $this->server[$this->id]['cmd'] = $this->Preg();
                } else {
                    $this->server[$this->id]['cmd'] = $this->Preg($this->server[$this->id]['cmd']);
                }

                //rcon_say           
                if ( !isset($this->server[$this->id]['rcon_say']) || !$this->server[$this->id]['rcon_say']) {
                    $this->server[$this->id]['rcon_say'] = 'say';
                }

                //xcopy
                if ( isset($this->server[$this->id]['xcopy']) ) {
                    $this->server[$this->id]['xcopy']  = 'true' == $this->server[$this->id]['xcopy'];
                } else {
                    $this->server[$this->id]['xcopy'] = $this->server[0]['xcopy'];
                }

                //Plugin:
                if ( isset($this->server[$this->id]['plugin']) ) {
                    $this->server[$this->id]['plugin'] = 'true' == $this->server[$this->id]['plugin'];
                } else {
                    $this->server[$this->id]['plugin'] = $this->server[0]['plugin'];
                }

                //updatefile
                if ( !isset($this->server[$this->id]['updatefile']) || !$this->server[$this->id]['updatefile'] ) {
                        $this->server[$this->id]['updatefile'] = $this->server[0]['updatefile'];
                }

                //Pingable
                if ( isset($this->server[$this->id]['pingable']) ) {
                    $this->server[$this->id]['pingable'] = 'true' == $this->server[$this->id]['pingable'];
                } else {
                    $this->server[$this->id]['pingable'] = $this->server[0]['pingable'];
                }

                //Internal var          
                $this->server[$this->id]['players'] = 0;
                $this->server[$this->id]['empty_since'] = 0;
                $this->server[$this->id]['pid'] = $this->ProcessId('chk', $this->id);
                $this->server[$this->id]['update'] = file_exists($this->server[$this->id]['installdir'].'\\'.$this->server[$this->id]['game'].'\\'.$this->server[$this->id]['updatefile']);
                if ($this->server[$this->id]['pid']) {
                    //Server is online
                    $this->server[$this->id]['time'] = time();
                    $this->Log(55);
                } else {
                    $this->server[$this->id]['time'] = 0;
                }
                $this->server[$this->id]['timer']   = 0;
                $this->server[$this->id]['quit']    = 0;
                $this->server[$this->id]['status']  = ($this->server[$this->id]['pid']?'on':'off');
                $this->server[$this->id]['crash']   = 0;
                $this->server[$this->id]['ping']    = ($this->server[$this->id]['pid']?1:0);
                $this->server[$this->id]['version'] = $this->ServerVersion();

                //internal var steamcmd
                $this->server[0][$this->server[$this->id]['appid']]['version'] = 0;
                $this->server[0][$this->server[$this->id]['appid']]['cache']   = $this->ServerVersion(1);
                $this->server[0][$this->server[$this->id]['appid']]['time']    = time();

                //Create Cache apps
                $this->Dir($this->__DIR.'\apps\\'.$this->games[$this->server[$this->id]['appid']]['server']);

            }//if role

        }//foreach

        return true;
    } 

//-------------------------------------------
//  Games List
//-------------------------------------------
    private function GetGamesList()
    {
        //File
        $file = $this->__DIR.'\include\games.list.xml';

        //File not found
        if (!file_exists($file)) {

                $this->Log(0, $file);
                return false;

        }

        //simplexml
        $xml = @simplexml_load_file($file);

        //Parse error
        if ( !$xml ) {

            $this->Log(1, $file);
            return false;

        }

        //Require Attr
        $attr = array('name','game','server','cmd','app_set_config','config','srcds');
        $games = [];

        foreach($xml->children() as $child) {

            //AppID defined
            $role = $child->attributes();
            $role = trim(strtolower((string)$role));

            //Valid AppID
            if ( $role && preg_match("/[A-Za-z0-9]+/", substr( $role ,0,1) ) ) {

                if ( isset($game[$role]) ) {

                    //Game already declared
                    $this->Log(2, $role, $file);
                    return false;

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

                    if (!isset($games[$role]['cmd']) || $games[$role]['cmd'] == '') {
                        $games[$role]['cmd'] = '';
                    }

                    if (!isset($games[$role]['app_set_config']) || $games[$role]['app_set_config'] == '') {
                        $games[$role]['app_set_config'] = '';
                    } else {
                        $games[$role]['app_set_config'] = ' +app_set_config '.$games[$role]['app_set_config'];
                    }

                    if (!isset($games[$role]['config']) || $games[$role]['config'] == '') {
                        $games[$role]['config'] = 'cfg\server.cfg';
                    } else {
                        $games[$role]['config'] = preg_replace('/^\//','',str_replace("/","\\", $games[$role]['config']));
                    }

                    if (!isset($games[$role]['srcds']) || $games[$role]['srcds'] == '') {
                        $games[$role]['srcds'] = 'srcds.exe';
                    }

                } else {

                    //error missing attributes
                    $this->Log(2, '(name, game, server)', $file);
                    return false;

                }

                if ( !($game[$role]['game'] )) {

                    //error missing attributes
                    $this->Log(2, 'name', $file);
                    return false;

                }

                if ( !($game[$role]['server'] )) {

                    //error missing attributes
                    $this->Log(2, 'server', $file);
                    return false;

                }

            }

        }

        return $games;
    }

//-------------------------------------------
//  Messages List
//-------------------------------------------
    private function GetMsg()
    {
        //File
        $file = $this->__DIR.'\include\msg.log.xml';

        //File not found
        if (!file_exists($file)) {

                $this->Log(-2, $file);
                return false;

        }

        //simplexml
        $xml = @simplexml_load_file($file);
        $msg = array();

        //Parse error
        if ( !$xml ) {

            $this->Log(-3, 'msg.log.xml');
            return false;

        }

        foreach($xml->children() as $child) {

                $role = $child->attributes();
                $role = (int)trim(strtolower((string)$role));

                if ( $role && is_numeric($role) && preg_match("/[0-9]+/", substr($role,0,1) ) ) {

                        $msg[$role] = $child;

                }

        }

        return $msg;
    }

//-------------------------------------------
//  Init Server(s)
//-------------------------------------------
    private function Init()
    {
        //Root
        $root        = exec('chdir',$o,$r);
        $this->__DIR = preg_replace('/\\\\bin$/','',$root);

        //-Folders:
        $this->Dir($this->__DIR.'\apps');
        $this->Dir($this->__DIR.'\logs');

        //-Reset id
        $this->Log(-1);
        $this->id = 0;

        //-Load messages
        $this->msg   = $this->GetMsg();     //Config $msg;

        //-Avalaible Games
        $this->games = $this->GetGamesList(); //Config  $games; 
        if (!$this->games || sizeof($this->games)< 1) {

            $this->Log(7);
            die(); // No games

        }

        //Load & Error in config
        if ( !$this->GetConfig() ) {

            $this->Log(8);
            die(); // No server

        }

        //Number of servers
        $this->total = sizeof($this->server);
        if ( !$this->server || $this->total < 2 ) {

            $this->Log(8);
            die(); // No server

        }

        //Install new game?
        for ($this->id=1; $this->id < $this->total; $this->id++) {

            //New?
            if ( (!$this->server[$this->id]['version'] = $this->ServerVersion()) && !$this->server[$this->id]['update'] ) {

                //stop steamcmd?
                $this->ProcessId('kill', 0);

                //Update needs to be run 2x with xcopy; app 90 tends to crash
                for ($i=0; $i<7; $i++) {

                    //Install new game
                    $this->Log(20, $this->games[$this->server[$this->id]['appid']]['name']);
                    $this->Update();

                    //Wait for
                    $this->Wait($this->server[$this->id]['update']);

                    //Update complete
                    if ( $this->server[$this->id]['version'] = $this->ServerVersion() ) break;

                }

                //6x failed!
                if ( !$this->server[$this->id]['version'] = $this->ServerVersion() ) {

                        //installation failed!
                        $this->Log(21);
                        die();

                }

            } elseif ($this->server[$this->id]['update'] = $this->ProcessId('chk',0)) {

                $this->Log(22,'xcopy ['.$this->server[$this->id]['update'].']');

            }

        }

        //-Start Monitoring [loop]
        $this->Run();
    }

//-------------------------------------------
//  Log
//-------------------------------------------
    private function Log($n = 0, $text1 = '', $text2 = '')
    {
        //No logs
        if ( ($n > -1) && (!isset($this->msg[$n]) || !$this->msg[$n]) ) return false;

        //Text to replace
        $a = ''; $b = ''; $c = ''; $d = ''; $e = '';
        $a = $this->id; //%1 server id
        if ( $this->id > 0 && isset($this->server[$this->id]) && isset($this->games[$this->server[$this->id]['appid']]) ) {

            if (isset($this->server[$this->id]['fname']) && $this->server[$this->id]['fname']) {

                $b = $this->server[$this->id]['fname'];   //%2 friendly name

            } else {

                $b = $this->games[$this->server[$this->id]['appid']]['name'];   //%2 game name

            }
            $c = $this->games[$this->server[$this->id]['appid']]['server']; //%3 game app

        }
        $d = $text1; //%4 extra
        $e = $text2; //%5 extra

        //Current time
        $local = exec('wmic os get localdatetime',$o,$r);
        $date  = substr($o[1], 0, 4).'-'.substr($o[1], 4, 2).'-'.substr($o[1], 6, 2);
        $time  = substr($o[1], 8, 2).':'.substr($o[1], 10, 2).':'.substr($o[1], 12, 2);

        //Daily File Path
        $filePath = $this->__DIR.'\logs\\'.$date.'.txt';

        //text
        $header = "SnipeZilla Srcds Manager ".$this->version."\r\n";
        $msg = '';
        if ($n > -1) {

            $msg     = str_replace(array('%1','%2','%3','%4','%5'), array($a,$b,$c,$d,$e), $this->msg[$n]);
            $message = $n." - ".$time." - ".$a." - ".$msg."\r\n";

        } else {

            //Sz_srcds started
            $message = "" ;
            if ($n == -2) $message = $n." - ".$time." - 'msg.log.xml' couldn't be loaded. File not found.";
            if ($n == -3) $message = $n." - ".$time." - 'msg.log.xml' couldn't be loaded. Syntax error.";

        }

        //Daily new file?
        if (!file_exists($filePath)){

            $file = fopen($filePath, "w");
            fwrite($file, $header);
            fwrite($file, $message);
            fclose($file);

        } else {

            $contents  = file_get_contents($filePath);
            if ($n == -1)
            $contents .= "\r\n".$header;
            $contents .= $message;
            file_put_contents($filePath, $contents);

        }

        //Email alert?
        if ( !empty($this->server[0]['email']['mail']) && !empty($this->server[0]['email']['alert']) && preg_match('/(\b'.$n.'\b)/', $this->server[0]['email']['alert']) && $msg != '' && $n >= 20) {

            $this->server[0]['email']['message'][] = array( 'alert' => $n, 'time' => $time, 'server' => '('.$this->id.') '.$b, 'message' => $msg);
		
        }
    }

//-------------------------------------------
//  Check Master Server for current version
//-------------------------------------------
    private function MasterServerVersion()
    {
        if ( !$this->server[0][$this->server[$this->id]['appid']]['version'] ||
             ( (time() - $this->server[0][$this->server[$this->id]['appid']]['time'] > $this->server[0]['delay']['steam']) &&
               ($this->server[$this->id]['version'] >= $this->server[0][$this->server[$this->id]['appid']]['version']) ) ) {

            //Url to check:
            $json = @file_get_contents('https://api.steampowered.com/ISteamApps/UpToDateCheck/v1?appid='.$this->server[$this->id]['appid'].'&version=1&format=json');

            //Server response
            $data = @json_decode($json,true);

            //Version checked in case of multi servers
            if ( isset($data['response']['required_version']) ) {
                $version = preg_replace('/[^0-9]/', '', $data['response']['required_version']);
            } else { $version=false; }

            //Save current version
            if ($version) {

                if ( $this->server[0][$this->server[$this->id]['appid']]['version'] && $this->server[0][$this->server[$this->id]['appid']]['version'] < $version) {

                    //new version available
                    $this->Log(23, $this->server[$this->id]['version'], $version);

                }

                //Save version from Steam
                $this->server[0][$this->server[$this->id]['appid']]['version'] = $version;

            } else { //no answer

                //Saving as actual version for the timer in case of 1st check
                $this->Log(24);
                $this->server[0][$this->server[$this->id]['appid']]['version'] = $this->server[$this->id]['version'];

            }

            //Last ping
            $this->server[0][$this->server[$this->id]['appid']]['time'] = time();

        }

        //server is out of date
        if ( $this->server[0][$this->server[$this->id]['appid']]['version'] > $this->server[$this->id]['version'] ) {

            //Update required
            $this->Update();
            return true;

        }

        //No new version
        return false;
    }

//-------------------------------------------
//  Ping server
//-------------------------------------------
    private function Ping()
    {
        //Not pingable?
        if ( !$this->server[$this->id]['pingable'] || !$this->server[$this->id]['pid'] ) return false;

        //Update coming
        if ( $this->server[$this->id]['update'] || $this->server[$this->id]['quit'] ) return false;

        //Frequency not reached
        if ( time() - $this->server[$this->id]['timer'] < $this->server[0]['delay']['ping'] ) return false;

        //Server Just Started
        if ( time() - $this->server[$this->id]['time'] < $this->server[0]['delay']['start'] ) return false;

        //Set timer for next ping
        $this->server[$this->id]['timer'] = time();

        //Ping server
        if ( $this->A2S_INFO() ) {

            //Online server
            $this->server[$this->id]['ping'] = 1;
            return true;

        }

        //No response
        $this->server[$this->id]['ping'] -= 1;

        //Log Server not pingable
        $this->Log(62, abs($this->server[$this->id]['ping'])+1);

        //Unresponsive server x3
        if ( $this->server[$this->id]['ping'] == -2 ) {

            //Stop Server:
            $this->ProcessId('kill', $this->id, $this->server[$this->id]['pid']);

            //Marker crash!
            $this->Status('crash',1);

            //Log
            $this->Log(63);

        }

        //Can't ping
        return false;
    }

//-------------------------------------------
//  Preg Command Line Parameters
//-------------------------------------------
    private function Preg($cmd = '')
    {
        //default cmd
         $def = array( 
                      '-game'       => $this->server[$this->id]['game'],
                      '-usercon'    => '',
                      '-console'    => '', 
                      '-ip'         => $this->server[$this->id]['ip'],
                      '-port'       => $this->server[$this->id]['port'], 
                      '-maxplayers' => $this->server[$this->id]['maxplayers'],
                      '+map'        => $this->server[$this->id]['map']
                     );

        $cmd = explode(" ",preg_replace('/\s+/', ' ',trim($cmd.' '.$this->games[$this->server[$this->id]['appid']]['cmd'])));
        $cmdLine = array();
        $ln = sizeof($cmd);

        for ($i = 0; $i<$ln; $i++) {

            if ( isset($cmd[$i+1]) && ( $cmd[$i+1][0] != "-" || $cmd[$i+1][0] != "+" ) && ( $cmd[$i][0] == "-" || $cmd[$i][0] == "+" ) ) {

                $cmdLine[$cmd[$i]] = $cmd[$i+1];
                $i++;               

            } else {

               $cmdLine[$cmd[$i]] = '';

            }

        }
        $cmd = '';
        foreach ( $cmdLine as $key => $value ) {
            if ( $key == "-dedicated" ) {

                if (isset($def['-game'])) unset($def['-game']);
                $cmd .= $key." ".$value." ";
   
            } elseif ( isset($def[$key]) ) {

               if ( ($key == "-maxplayers" || $key == "-maxplayers_override") && $value) {

                    if (isset($def[$key])) unset($def[$key]);
                    $cmd .= $key." ".$value." ";

                } elseif ($key == "+map" && $value) {

                    $def["+map"] = $value;

                } else {

                    unset($cmdLine[$key]);

                }

            } else {

                $cmd .= $key." ".$value." ";

            }
            

        }

        foreach ( $def as $key => $value ) {

            if ($key == '-maxplayers' && empty($value)) continue;
            if ($key == '+map' && empty($value))        continue;
            $cmd .= " ".$key." ".$value;

        }

        return preg_replace('/\s+/', ' ',trim($cmd));
    }

//-------------------------------------------
//  Process Priority code
//-------------------------------------------
    private function Priority($prio)
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

//-------------------------------------------
//  Process running
//-------------------------------------------
    private function Proc($pid)
    {
        //No process
        if (!$pid) return 0;

        //LookUp process
        $run = strpos( shell_exec("tasklist /fi \"PID eq ".$pid."\"" ), (string)$pid );

        //Return pid if running
        if ($run) return (int)$pid;

        //Not running
        return 0;
    }

//-------------------------------------------
//  TaskList
//-------------------------------------------
    private function ProcessId($mode, $id=0, $pid=0)
    {
        switch ($mode) {

            //Check if process is running
            case 'chk':
                //steamcmd or xcopy
                if (!$id) {

                    $proc = preg_split('/\w+=/', shell_exec('wmic process where name="steamcmd.exe" get ProcessId /FORMAT:LIST'),-1, PREG_SPLIT_NO_EMPTY);
                    $numb = sizeof($proc)-1;
                    if ($numb<1) {

                        $proc = preg_split('/\w+=/', shell_exec('wmic process where name="php.exe" get ProcessId, Commandline /FORMAT:LIST'),-1, PREG_SPLIT_NO_EMPTY);
                        
                        $numb = sizeof($proc);
                        for ($i=1; $i<$numb; $i++) {
                        
                            if ( preg_match('/.*bin\\\\xcopy.php"?/i', $proc[$i]) ) {
                        
                                $proc[1]=$proc[$i+1];
                                break;

                            }
                        
                        }

                    }
                    if (isset($proc[1])) return (int)$proc[1];

                } else {

                    $ExecPath = '/'.str_replace('\\','\\\\',$this->server[$id]['srcds']).'/i';

                    $proc = preg_split('/\w+=/', shell_exec('wmic process where name="'.$this->server[$id]['app'].'" get ProcessId, CommandLine /FORMAT:LIST'),-1, PREG_SPLIT_NO_EMPTY);

                    $numb = sizeof($proc)-1;

                    for ($i = 1; $i<$numb; $i++) {

                        if ( preg_match($ExecPath, $proc[$i]) && preg_match('/-port '.$this->server[$id]['port'].'/i', $proc[$i]) ) {

                            return (int)$proc[$i+1];

                        }

                    }

                }

               //not running
               return 0;

            break;

            //Kill process
            case 'kill':
                //No pid!?
                if (!$pid) {
                    $pid = $this->ProcessId('chk',$id);
                }

                //Kill ProcessId
                $pid = $this->Proc($pid);
                if ($pid) $se = shell_exec("taskkill /F /PID ".$pid."");

                //Remove the server
                $this->server[$id]['pid'] = 0;
                if ($pid)
                    $this->Log(60, $pid);

                //return kill
                sleep(1);
                return true;
            break;

            default: return 0;

        }
    }

//-------------------------------------------
// Quit or delay Update
//-------------------------------------------
    private function Quit( $xcopy, $ready )
    {
        //Cannot send RCON quit or say
        if ( !$this->server[$this->id]['version'] ||
             !$this->server[$this->id]['rcon_password'] ||
             !$this->server[$this->id]['pid'] ) return true;

        //Reset timing
        if ( $this->server[$this->id]['quit'] == 0 ) {

            $this->server[$this->id]['quit']  = time() ;
            $this->server[$this->id]['timer'] = time();
            $this->RCON($this->server[$this->id]['rcon_say'], $this->msg[40]);

        }

        //Send RCON say ready to update
        if ( time() - $this->server[$this->id]['timer'] >= $this->server[0]['delay']['say'] ) {

            $this->RCON($this->server[$this->id]['rcon_say'],$this->msg[40]);
            $this->server[$this->id]['timer'] = time();

        }

        //update cache for xcopy
        if ( $xcopy && !$ready ) return true;

        //Wait for an update to end or give time
        if ( $this->server[0]['status'] != 'off' || time() - $this->server[$this->id]['quit'] < $this->server[0]['delay']['quit'] ) return false;

        //xcopy ready
        if ( $xcopy ) {

            if ( time() - $this->server[$this->id]['quit'] >= $this->server[0]['delay']['quit'] ) {

                $this->Log(25);
                if ( $this->RCON('quit') ) $this->Wait($this->server[$this->id]['pid'], 30);

                //Ready to Update
                return true;

            }

                //Not Ready 
                return false;

        }

        //Steamcmd is running
        if ( $this->ProcessId('chk',0) ) return false;

        if ( time() - $this->server[$this->id]['quit'] >= $this->server[0]['delay']['quit'] ) {

            $this->Log(25);
            if ( $this->RCON('quit') ) $this->Wait($this->server[$this->id]['pid'], 30);

            //Ready to Update
            return true;

        }

        //not yet
        return false;
    }

//-------------------------------------------
//  RCON
//-------------------------------------------
    private function RCON($cmd, $msg = '')
    {
        //pingable?
        if (  $this->server[$this->id]['ping'] < 0 || 
              $this->server[$this->id]['rcon_say'] == 'none') return false;

        //No password?
        if ( !$this->server[$this->id]['rcon_password'] ) return false;

        //opensocket
        $fp = @fsockopen('tcp://' . $this->server[$this->id]['ip'], $this->server[$this->id]['port'], $errno, $errstr, 1);

        //connected
        if ( !$fp || empty($fp) ) {

            //error connection
            $this->Log(61, $errno, $errstr);
            return false;

        } else {

            //Data
            $data = pack("VV", 1, 3).$this->server[$this->id]['rcon_password']."\x00\x00\x00";
            $data = pack("V",strlen($data)).$data;
            $ln   = strlen( $data );

            //Request AS_2INFO
            @fwrite($fp, $data, $ln);

            //Verify Password
            stream_set_timeout($fp, 5);
            $r = @fread ($fp, 1400) ; //empty

            //Size get Long string
            $size = substr($r, 0, 4);
            $r    = substr($r, 4);//trunc $r
            $size = @unpack('Vdata', $size);

            //ID get Long string
            $id   = substr($r, 0, 4);
            $r    = substr($r, 4);//trunc $r
            $id   = @unpack('Vdata', $id);

            //Bad password
            if ( ( $id['data'] != 1 ) ) {

                //Wrong rcon_password
                $this->Log(64);

                //Reset ping & frequency
                $this->server[$this->id]['ping'] -= 1;
                $this->server[$this->id]['timer'] = time();

                //return
                return false;

            }

            //msg
            $msg = trim($msg);
            if ($msg)
            $msg = " ".$msg;

            //Send rcon say
            $data = pack ("VV", 2, 2).$cmd.$msg."\x00\x00\x00" ;
            $data = pack ("V", strlen ($data)).$data ;

            fwrite ($fp, $data, strlen($data)) ;

            //close socket
            @fclose($fp);

            //Reset ping & frequency
            $this->server[$this->id]['ping']  = 1;
            $this->server[$this->id]['timer'] = time();

            //status
            return true;

        }
    }

//-------------------------------------------
//  Read File
//-------------------------------------------
    private function ReadFile($path = '', $word = "", $com = false, $rn=" ")
    {
        if ( !file_exists($path) ) return false;

        //Open File
        $file = fopen($path, "r");

        //Read all lines
        $lines = '';
        while ( ($line = fgets($file)) !== false ) {

            //!comments
            if (!$com) { $line = preg_replace('/^\s+|\n|\r|\/\/.*|\s+$/m', '', $line); } else { $line = preg_replace('/^\s+|\n|\r|\s+$/m', '', $line); }

            //appends po search word
            if ( $line ) {
                //XXX_yyyy || XXXyyyy ?
                if ( $word && preg_match('/('.$word.'|'.str_replace('_','',$word).')\s+"?([^"]+)/i', $line, $matches) ) {
 
                    //variable found
                    return trim($matches[2]); 

                } else {

                    //appends lines
                    $lines .= $line.$rn;

                }

            }

        }

        //Close file
        fclose($file);

        //No word found
        if ($word) return false;


        //Return 1st line
        return trim($lines);
    }

//-------------------------------------------
//  Restart
//-------------------------------------------
    private function Restart()
    {
        //Never restart
        if ($this->server[0]['restart'] == 'never') return false;

        //Up less than 12h
        if ( time() - $this->server[$this->id]['time'] < 12*3600 ) return false;

        //Auto restart
        if ( $this->server[0]['restart'] == 'auto' ) {

            if ( time() - $this->server[$this->id]['time'] > 24*3600 && $this->server[$this->id]['empty_since'] > $this->server[0]['delay']['ping'] ) {

                //Log
                $this->Log(66);

                //Kill server
                $this->Status('restart','');
                if ( $this->RCON('quit') ) $this->Wait($this->server[$this->id]['pid'], 30);
                $this->ProcessId('kill', $this->id);
                return true;

            }

            return false;

        }

        //Schedule
        $local = exec('wmic os get localdatetime',$o,$r);
        if ( substr($o[1], 8, 2) == $this->server[0]['restart'] || time() - $this->server[$this->id]['time'] > 24*3600 ) {

            //Restart only if empty
            if ( $this->server[$this->id]['pingable'] && $this->server[0]['empty'] ) {

                if ($this->server[$this->id]['empty_since'] > $this->server[0]['delay']['ping'] ) {

                    //Log
                    $this->Log(66);

                    //Kill server
                    $this->Status('restart','');
                    $this->ProcessId('kill', $this->id);
                    return true;

                 }

                 //Postpone until empty
                 if ( time() - $this->server[$this->id]['time'] < 24*3600 ) return false;

            }

            // Not empty: Info Restart
            if ( $this->server[$this->id]['rcon_password'] ) {

                //Not empty Restart with Quit
                if ( $this->server[$this->id]['quit'] == 0 ) {

                    $this->server[$this->id]['quit']  = time();
                    $this->server[$this->id]['timer'] = time();
                    $this->RCON($this->server[$this->id]['rcon_say'], $this->msg[67]);

                    //delay
                    return true;

                }

                //Quit
                if ( time() - $this->server[$this->id]['timer'] < $this->server[0]['delay']['say'] ) return true;

                //Quit
                if ( $this->RCON('quit') ) $this->Wait($this->server[$this->id]['pid'], 30);

            }

            //Kill server
            $this->Log(66);
            $this->Status('restart','');
            $this->ProcessId('kill', $this->id);
            return true;

        }

        //No restart
        return false;
    }

//-------------------------------------------
//  Run [loop]
//-------------------------------------------
    private function Run()
    {
        while (true) {

            for ( $this->id = 1; $this->id < $this->total; $this->id++ ) {

                //Start server:
                if ( !$this->Start() ) continue;

                //Check process:
                $this->State();

            } sleep(1);

            //Task
            $this->Task();

        }
    }

//-------------------------------------------
//  Check current version
//-------------------------------------------
    private function ServerVersion( $cache = false)
    {
        //Path steam.inf
        if ( $cache ) {

            //apps folder
            $pathINF = $this->__DIR.'\apps\\'.$this->games[$this->server[$this->id]['appid']]['server'].'\\'.$this->server[$this->id]['game'].'\steam.inf';

        } else {

            //game folder
            $pathINF = $this->server[$this->id]['installdir'].'\\'.$this->server[$this->id]['game'].'\steam.inf';

        }

        //File/Server Not Present
        if ( !file_exists($pathINF) ) return 0;

        // Read Current version:
        $lines_array = file($pathINF);

        //Version
        $find_version = "/^PatchVersion=\d|^ServerVersion=\d/i";

        foreach($lines_array as $line) {

            if ( preg_match($find_version,$line) ) {
                //Read actual version
                list(, $version) = explode("=", $line);
                $version = preg_replace('/[^0-9]/', '', $version);
                return $version;
                break;
            }

        }

        //Nothing found
        return 0;
    }

//-------------------------------------------
//  Monitor Crash & UpToDate
//-------------------------------------------
    private function State()
    {
        //Crashed?
        if ( $this->Crash() ) return false;

        //Server Recovered from previous crash?
        if ( !$this->CrashRecover() ) return false;

        //New Version?
        if ( $this->MasterServerVersion() ) return false;

        //Ping if no Restart?
        if ( !$this->Restart() ) $this->Ping();
    }

//-------------------------------------------
//  Server state
//-------------------------------------------
    private function Status($status='', $crash = 0)
    {
        $act_status = $this->server[$this->id]['status'];
        $act_crash  = $this->server[$this->id]['crash'];

        //State
        if ( $status )
            $this->server[$this->id]['status'] = $status;

        //Crash Counter
        if ( $crash ) { $this->server[$this->id]['crash'] += $crash;

        } elseif ($crash===0) { $this->server[$this->id]['crash'] = 0; }

        //Log changed
        if ( $act_status != $status || $crash != $status )
            $this->Log(57,$this->server[$this->id]['status'],$this->server[$this->id]['crash']);
        return false;
    }

//-------------------------------------------
//  Start Game Server
//-------------------------------------------
    private function Start()
    {
        //Already running?
        if ( $this->server[$this->id]['pid'] ) return true;

        //Server on hold?
        if ( file_exists($this->server[$this->id]['installdir'].'\\'.$this->server[$this->id]['game'].'\sz.stop.txt') ) {

            if ( $this->server[$this->id]['status'] != 'stop' ) {

                //Log status paused
                $this->Status('stop',0);
                $this->Log(65);

            }

            return false;

        }

        //Updating or crashing? no need to start
        if ( $this->UpdateStatus() ) return false;

        //Always crashed?
        if ( $this->CrashStatus() ) return false;

        //Command Line Parameters
        $cmd = $this->ReadFile($this->server[$this->id]['installdir'].'\\'.$this->server[$this->id]['game'].'\cfg\sz.cmd.cfg');
        if ($cmd) {

            //From cfg file
            $this->server[$this->id]['cmd'] = $this->Preg($cmd);

        }

        //refresh version
        $this->server[$this->id]['version'] = $this->ServerVersion();
        $this->server[0][$this->server[$this->id]['appid']]['cache'] = $this->ServerVersion(1);

        //Start SRCDS.EXE
        $srcds = $this->server[$this->id]['srcds'].' '.$this->server[$this->id]['cmd'];
        preg_match('/ProcessId=([0-9]+)/' , preg_replace('/\s+/', '', shell_exec('wmic process call create "'.$srcds.'","'.$this->server[$this->id]['installdir'].'"')), $matches);
        $this->server[$this->id]['pid'] = @$matches[1];

        //Verify Launch
        if (!$this->server[$this->id]['pid']) { //Failed to launch.

           $this->Status('crash',1);
           $this->server[$this->id]['timer']  = time();
           $this->Log(51);
           return false;

        }

        //Set process priority
        shell_exec('wmic process where ProcessId="'.$this->server[$this->id]['pid'].'" CALL setpriority '.$this->server[$this->id]['priority']);

        //Startup time
        $this->server[$this->id]['time']  = time(); //time server was launched

        //set status
        $this->Status('on','');
        $this->server[$this->id]['timer']  = time();
        $this->server[$this->id]['ping']   = 0;
        $this->server[$this->id]['quit'] = 0;

        //Log launch start
        $this->Log(50, str_replace('\"','"', str_replace($this->server[$this->id]['installdir'].'\\','',$srcds)));

        //Update rcon_password
        $this->server[$this->id]['rcon_password'] = $this->ReadFile($this->server[$this->id]['installdir'].'\\'.$this->server[$this->id]['game'].'\\'.$this->games[$this->server[$this->id]['appid']]['config'],'rcon_password');
        if (!$this->server[$this->id]['rcon_password']) $this->Log(68);

        //Pause
        sleep(1);
        return true;
    }

//-------------------------------------------
//  Task
//-------------------------------------------
    private function Task()
    {
        //Send Email Alert
        $this->Email();

        //-Delete older logs
        $this->ClearLog();
    }

//-------------------------------------------
//  Update
//-------------------------------------------
    private function Update()
    {
        //cache status
        $ready = false;
        $xcopy = $this->server[$this->id]['xcopy'];

        //Create Flag File
        if ( $this->server[$this->id]['updatefile'] &&
             $this->server[$this->id]['version']    &&
            !file_exists($this->server[$this->id]['installdir'].'\\'.$this->server[$this->id]['game'].'\\'.$this->server[$this->id]['updatefile']) ) {

            $file = @fopen($this->server[$this->id]['installdir'].'\\'.$this->server[$this->id]['game'].'\\'.$this->server[$this->id]['updatefile'], "w");
            @fclose($file);

        }

        //status update
        if ( $this->server[$this->id]['update'] ) {

            if ( !$this->Proc($this->server[$this->id]['update']) ) {

                //reset status
                $this->server[$this->id]['update'] = 0;
                $this->server[0]['status'] = 'off';
                $this->server[0][$this->server[$this->id]['appid']]['cache'] = $this->ServerVersion(1);

                if ( !$this->server[0][$this->server[$this->id]['appid']]['cache'] || $this->server[0][$this->server[$this->id]['appid']]['cache'] < $this->server[0][$this->server[$this->id]['appid']]['version'] ) {

                    $this->Log(34);//cache steamcmd,crash

                }

            }

        }

        //Use files in cache to update
        if ( $xcopy ) {

            //cache up to date or install new game?
            $this->server[0][$this->server[$this->id]['appid']]['cache'] = $this->ServerVersion(1);
            if ( $this->server[0][$this->server[$this->id]['appid']]['cache'] && $this->server[0][$this->server[$this->id]['appid']]['version'] <= $this->server[0][$this->server[$this->id]['appid']]['cache'] ) {

                $ready = true;

            }

        }

        //Server will stop with a plugin
        if ( $this->server[$this->id]['plugin'] && $this->server[$this->id]['pid'] ) return false;

        //RCON quit or delay the update
        if ( !$this->Quit( $xcopy, $ready ) ) return false;

        //Steamcmd is running
        if ( $this->ProcessId('chk',0) ) return false;

        //crashed
        if ( $this->server[$this->id]['status'] == 'crash'  && $this->server[$this->id]['crash'] == 4 ) $ready = false;
        if ( $this->server[$this->id]['crash'] >= 5 ) $xcopy = false;

        //xcopy ready to copy the files
        if ( $xcopy && $ready ) {

            //Stop Server:
            $this->ProcessId('kill', $this->id, $this->server[$this->id]['pid']);

            //Start xcopy && get pid
            $this->server[$this->id]['update'] = $this->xcopy();

        } else {

            //xcopy: Update cache, after crash
            if ( $xcopy  ) {

                //Delete current version
                if ($this->server[$this->id]['status'] == 'off') {

                    $pathINF = $this->server[$this->id]['installdir'].'\\'.$this->server[$this->id]['game'].'\steam.inf';
                    if (file_exists($pathINF)) @unlink($pathINF);

                }

                //Update the Cache first
                $path = $this->__DIR.'\apps\\'.$this->games[$this->server[$this->id]['appid']]['server'] ;

            //Direct
            } else {

                //Stop Server:
                $this->ProcessId('kill', $this->id, $this->server[$this->id]['pid']);

                //Game Dir
                $path = $this->server[$this->id]['installdir'];

            }

            //Steamcmd Update folder
            $steamcmd = $this->server[0]['steamcmddir'].' +force_install_dir \"'.$path.'\"'.' +login '.$this->server[$this->id]['login'].$this->games[$this->server[$this->id]['appid']]['app_set_config'].' +app_update '.$this->games[$this->server[$this->id]['appid']]['server'].' validate +quit';

            //Exec SteamCMD
            preg_match('/ProcessId=([0-9]+)/' , preg_replace('/\s+/', '', shell_exec('wmic process call create "'.$steamcmd.'"')), $matches);
            $this->server[$this->id]['update'] = @$matches[1];

        }

        //SteamCMD or xcopy crashed?
        if ( !$this->server[$this->id]['update'] ) {

            if ( !$xcopy )           $this->Log(33); //steamcmd crashed, app
            if ( $xcopy && !$ready ) $this->Log(34); //steamcmd crashed, cache
            if ( $xcopy && $ready )  $this->Log(35); //xcopy crashed
            return false;

        }

        //Save Server Updating
        $this->server[0]['status'] = $this->id;

        //Update is active.
        if ( !$xcopy )           $this->Log(30); // update app
        if ( $xcopy && !$ready ) $this->Log(31); // update cache
        if ( $xcopy && $ready )  $this->Log(32); // xcopy
        return true;
    }

//-------------------------------------------
//  Update status
//-------------------------------------------
    private function UpdateStatus()
    {
        if ( $this->server[$this->id]['update'] ) {

            //SteamCMD stopped
            if ( !$this->Proc($this->server[$this->id]['update']) ) {

                $this->server[$this->id]['update'] = 0;
                $this->server[0]['status'] = 'off';

                //New Game Version
                $this->server[$this->id]['version'] = $this->ServerVersion();

                //Update Failed
                if ( !$this->server[$this->id]['version'] || $this->server[$this->id]['version'] < $this->server[0][$this->server[$this->id]['appid']]['version'] ) {
                 
                    //error status
                    if ($this->server[$this->id]['crash'] < 5) {

                        $this->Status('update',1);
                        $this->Update();

                    }

                    //Failed
                    return true;

                }

                //del flag file
                $this->DelFile($this->server[$this->id]['installdir'].'\\'.$this->server[$this->id]['game'].'\\'.$this->server[$this->id]['updatefile']);

                //Update completed
                $this->Log(39);

                //the cache was repair
                if ( $this->server[$this->id]['xcopy'] && $this->server[$this->id]['status'] == 'crash'  && $this->server[$this->id]['crash'] == 4 ) {

                    $this->Status('off','');
                    $this->Update();
                    return true;

                }
                //Reset states
                $this->Status('off','');

                //Restart
                return false;

            }

            //steamcmd still running
            return true;

        }

        //Update queued [error]?
        if ( $this->server[$this->id]['status'] == 'update' ) {

            //Crashed:
            if ( $this->server[$this->id]['crash'] > 2 ) {

                //More than 3x, extend interval to 15mn
                if ( time() - $this->server[$this->id]['timer'] > $this->server[0]['delay']['failure'] ) {

                    $this->server[$this->id]['timer']   = time();
                    $this->Log(36, $this->server[$this->id]['crash']);
                    $this->Update();
                    $this->Status('',1);

                }

            } else {

                    $this->server[$this->id]['timer']   = time();
                    $this->Log(36, $this->server[$this->id]['crash']);
                    $this->Update();
                    $this->Status('',1);

            }

            return true;

        }

        //Force Update at startup? [flag]
        if ( file_exists($this->server[$this->id]['installdir'].'\\'.$this->server[$this->id]['game'].'\\'.$this->server[$this->id]['updatefile']) &&
             !$this->server[$this->id]['pid'] && !$this->server[$this->id]['crash']) {

            $this->Log(22,$this->server[$this->id]['updatefile']);
            $this->Update();
            return true;

        }

        //No updates
        return false;
    }

//-------------------------------------------
//  Wait for process to finish
//-------------------------------------------
    private function Wait($pid, $timeout = 0)
    {
        $wait = 1;
        $time = 0;
        $wd   = $timeout > 0;

        while ($this->Proc($pid)>0)
        {

            sleep($wait);

            if ($time > 300 || !$time) {
 
                $this->Log(99);
                $time = $wait;

            }

            $time += $wait;

            //watchdog
            if ($wd) {

               $timeout -= $wait;
               if ( !($timeout > 0) ) return false;

            }

        }
        return true;
    }

//-------------------------------------------
//  xcopy.exe
//-------------------------------------------
    private function xcopy()
    {
        $source      = $this->__DIR.'\apps\\'.$this->games[$this->server[$this->id]['appid']]['server'];
        $destination = $this->server[$this->id]['installdir'];
        $game        = $this->server[$this->id]['game'];
        $safe        = $this->server[$this->id]['crash'] > 0;

        //Safe mode
        if ($safe) $this->Log(38);

        //Path to xcopy.php
        $xcopy = $this->__DIR.'\bin\php.exe -f \"'.$this->__DIR.'\bin\xcopy.php\" \"'.$source.'\" \"'.$destination.'\" \"'.$game.'\" \"'.$safe.'\" \"1\"';

        //Run xcopy
        preg_match('/ProcessId=([0-9]+)/' , preg_replace('/\s+/', '', shell_exec('wmic process call create "'.$xcopy.'","'.$this->__DIR.'"')), $matches);

        return @$matches[1];
    }
} new SzMgr();