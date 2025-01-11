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
var Sz = {
    author: 'SnipeZilla',
    version: 'Version: 1.0.0',
    token: '',
    page: '',
    pid: 0,
    tab: {id:0, txt:''},
    timer: '',
    ini:
        function() {
            $('link[rel="shortcut icon"]').attr('href','css/images/Sz.png');
            /* version */
            $('#version .version').html(this.version);
            //
            $( '#container' ).tooltip({ track: true,
                                       position: {
                                                    my: "left top+25"
                                       },
                                       show:{ easing: "easeInExpo", duration: 500 },
                                       open: function() {
                                                if ( $('.ui-tooltip').length>1 ) $('.ui-tooltip:eq(0)').remove();
                                            }
                                    });
            $(document).on('focus click', '*',  function(e){
                $('.ui-tooltip:eq(0)').remove();
            });
            /*load Script*/
            $(".nav-2 a").click(function(event) {
                $('#version').addClass('load');
                $(".ui-dialog").remove();
                $('#Alert,#Dialog_Drive,#Dialog_Login').remove();
                Sz.page = event.target.id;
                Sz.Load();
            });
            function Hash(match){
                if (match){
                    $('#nav-1').find('.nav-2 a').each(function() {
                        if ($(this).attr('href') == '#'+match) {
                            Sz.page=match; 
                            Sz.Load();
                            return false;
                        }
                    });
                }
                return false;
            };

            $(window).on('hashchange', function() {
                var match = location.hash.match(/^#?(.*)$/)[1];
                if (match != Sz.page) Hash(match);
            });
            if (window.location.hash) {
                var match = location.hash.match(/^#?(.*)$/)[1];
                Hash(match);
            }
            //Load 1st page
            if (Sz.page) return false;
            $('#nav-1').find('.nav-2 a').each(function() {
                if ($(this).attr('href')) {
                    Sz.page=$(this).attr('href').replace('#','');
                    window.location.hash = Sz.page;
                    Sz.Load();
                    return false;
                }
            });
        },

    Load: function() {
        $('#content').load('php/'+Sz.page+'.php?' + new Date().getTime()+'&token='+Sz.token, function( response, status, xhr ) {
            if ( status == "success" ) { 
                $('.nav-2 a').removeClass('active');
                $('#'+Sz.page).addClass('active');
                $('#version').removeClass('load');
            }
        }); //load
    },

    Ajax: function(Req_URL, This_Data, This_ID, This_Fct) {
        if ( $('#version').hasClass('load') ) return false;
        $('#version').addClass('load');
        $.ajax({
        type: 'POST',
        url: Req_URL+'?'+(new Date()).getTime(),
        data: This_Data,
        cache: false,
        headers: {
            'Cache-Control':"no-cache, must-revalidate",
            'Expires':"Sat, 26 Jul 1997 05:00:00 GMT"
        },
        }).done(function(data) {
            $('#version').removeClass('load');
            try{

               if (This_ID && This_Fct != 1) {
                   Sz.Response(data);
               }
               if (This_Fct == 1) {
                    query = JSON.parse(/({.*})/.exec(data)[1]);
                    Sz.TaskStatus(query,This_ID);
               }
               if (This_Fct == 2) {
                   if (data.trim() == 'Success') {
                       $('.ui-dialog-content').remove();
                       Sz.tab.txt = 'config.xml saved. SnipeZilla Srcds Manager should be restarted...';
                       Sz.Load();
                   } else {
                       //setTimeout(function() {
                           // $( "#saved" ).text('');
                       //}, 5000 );
                   }
               }
               if (This_Fct == 3) {

                    if (data.trim()) {
                        var id = Sz.tab.id-1;
                        $('.suggest-map:eq('+(id)+') span').click(function(){
                            $('[name="map"]:eq('+id+')').val($(this).text());
                            $('.suggest-map:eq('+id+')').slideUp(250,'swing');
                        });
                        $('.suggest-map:eq('+id+')').slideDown(250);
                    }

               }

            }catch(err){
                console.log(err.message);
            }
        });
    },

    getPath: function (a) {
        var path = $(a).text(),
        $parent = $(a).parents("li").eq(1).find("a:first");
        if ($parent.length == 1) {
            path = Sz.getPath($parent) + "\\" + path;
        }
        return path;
    },

    Result: function (id,data) {
        if (!data) return false;
        Sz.timer = clearTimeout(Sz.timer);
        if ( data.slice(0,5) == 'Error' ) {
            $(id).addClass('error');
        } else { $(id).removeClass('error'); }
        $(id).show().html(data);
        if (id != '#saved' && id != '#result') return false;
        Sz.timer = setTimeout(function() {
                        $( id).text('').hide();
                   }, 5000 );
    },

    Response: function (data,err) {
        if (!data) return false;
        Sz.timer = clearTimeout(Sz.timer);
        if ($('#response').length) {
            $('#response').stop().html(data);
            if (data.slice(0,5) == 'Error' || err) {
                $('#response').addClass('error');
            } else {
                $('#response').removeClass('error');
            }
        } else {
            $('#container').append('<div id="response"'+((data.slice(0,5) == 'Error' || err)?' class="error"':'')+'>'+data+'</div>');
        }
        $('#response').fadeIn(250);
        Sz.timer = setTimeout(function() {
            $('#response').fadeOut(250, function(){$('#response').remove()})}, 10000 );
    },
    SteamCMD: function (path) {
        if ( $('#version').hasClass('load') ) return false;
        $('#version').addClass('load');
        $.ajax({
            type: "POST",
            url: 'php/setup.php?'+(new Date()).getTime(),
            data: {fct:'steamcmd',par: path, token: Sz.token},
            cache: false
        }).done(function(data) {

            try{
                if (data == 'Success') {
                    $('.validateTips').text( 'Success: steamcmd is installed' ).addClass( "ui-state-highlight" );
                    setTimeout(function() {
                        $('.validateTips').removeClass( "ui-state-highlight", 250 ).text('');
                    }, 5000 );
                    $('#drive').fileTree({ root: $('#drives').val()+':/', script: '../php/jqueryFileTree.php' }, function(file) {
                        if (Sz.tab.id == 0) {
                            $('.ui-dialog-title').text( file.replace(/\//g,'\\'));
                        }
                    });
                } else {
                    $('.validateTips').text( data ).addClass( "ui-state-highlight" );
                    setTimeout(function() {
                        $('.validateTips').removeClass( "ui-state-highlight", 250 ).text('');
                    }, 5000 );
                }
            }catch(err){
                console.log(err.message);
            }

            $('#version').removeClass('load');

        });

    },

    TaskStatus: function (query,id) {
        s = query[0].trim();
        if ( s == 'Create' ) {
            $( "#create,.create" ).show();
            $( "#delete" ).hide();
            $( "#run" ).hide();
            $( "#end" ).hide();
        } else {
            $( "#create,.create" ).hide();
            $( "#delete" ).show();
            if (s == 'Running') {
                $( "#run" ).addClass('disable').prop( "disabled", true).show();
                $( "#end" ).removeClass('disable').prop( "disabled",false).show();
            } else {
                $( "#run" ).removeClass('disable').prop( "disabled",false).show();
                $( "#end" ).addClass('disable').prop( "disabled", true).show();
            }
        }
        $(id).html(query[0]+query[1]);

    },

    popup: {
        data:'',
        open: function (txt,data,fct,o) {
            var p='<div id="popup" style="display: none">'+
            	       '<div class="popup-container">'+
            		        '<div class="popup-header">'+
                                '<span class="popup-title"></span>'+
                                '<span class="fa fa-times popup-close" onclick="Sz.popup.close(0);"></span>'+
                            '</div>'+
             		        '<div class="popup-content"></div>'+
              		        '<div class="popup-footer">'+
                                '<input type="button" class="popup-confirm" value="Confirm" />'+
                                '<input type="button" onclick="Sz.popup.close(0);" class="popup-cancel" value="Cancel" />'+
                            '</div>'+
                           '</div>'+
                        '</div>';
        
            $("#content").append(p);
            if (txt.css) {$("#popup .popup-container").addClass(txt.css)};
            $("#popup").fadeIn(125);
            $("#popup .popup-header .popup-title").html(txt.header);
            $("#popup .popup-content").html(txt.content);
            if (txt.css == 'large') {
                var h0 = 0;//$('#header').outerHeight( true );
                var h1 = $('#popup .popup-header').outerHeight( true );
                var h2 = $('#popup .popup-footer').outerHeight( true );
                var h = $(window).innerHeight();
                var w = $('#container').outerWidth( true );
                $('#popup .popup-content').css({'height':(h-(h0+h1+h2+15))+'px'});
                $('#popup .popup-container').css({'width':w+'px','margin-left':-(w/2)});
            }
            $("#popup .popup-confirm").click(function(){fct(data);if (!o) {Sz.popup.close(125)}});
        },
        close: function (t) {
            $('#popup,#popup .popup-container').fadeOut(t,function(){$('#popup').remove()});
        },
        resize: function () {
        
        }
    }

}


$.ajaxSetup ({
    // Disable caching of AJAX responses
    cache: false
});
$( document ).ajaxError(function(){
    $('#version').removeClass('load');
});
