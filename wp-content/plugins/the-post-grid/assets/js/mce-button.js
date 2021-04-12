(function() {
    tinymce.PluginManager.add('rt_tpg_scg', function( editor, url ) {
        var tlpsc_tag = 'the-post-grid';


        //add popup
        editor.addCommand('rt_tpg_scg_popup', function(ui, v) {
            //setup defaults

            editor.windowManager.open( {
                title: 'The Post Grid ShortCode',
                width: jQuery( window ).width() * 0.3,
                height: (jQuery( window ).height() - 36 - 50) * 0.1,
                id: 'rt-tpg-insert-dialog',
                body: [
                    {
                        type   : 'container',
                        html   : '<span class="rt-loading">Loading...</span>'
                    },
                ],
                onsubmit: function( e ) {

                    var shortcode_str;
                    var id = jQuery("#scid").val();
                    var title = jQuery( "#scid option:selected" ).text();
                    if(id && id != 'undefined'){
                        shortcode_str = '[' + tlpsc_tag;
                            shortcode_str += ' id="'+id+'" title="'+ title +'"';
                        shortcode_str += ']';
                    }
                    if(shortcode_str) {
                        editor.insertContent(shortcode_str);
                    }else{
                        alert('No short code selected');
                    }
                }
            });

            putScList();
        });

        //add button
        editor.addButton('rt_tpg_scg', {
            icon: 'rt_tpg_scg',
            tooltip: 'The Post Grid',
            cmd: 'rt_tpg_scg_popup',
        });

        function putScList(){
                var dialogBody = jQuery( '#rt-tpg-insert-dialog-body' )
                jQuery.post( ajaxurl, {
                    action: 'rtTPGShortCodeList'
                }, function( response ) {

                    dialogBody.html(response);
                    console.log(response);
                });

        }

    });
})();