jQuery(document).ready(function($) {

    tinymce.create('tinymce.plugins.birthdays_plugin', {
        init : function(ed, url) {
                // Register command for when button is clicked
                ed.addCommand('birthdays_insert_shortcode', function() {
                    selected = tinyMCE.activeEditor.selection.getContent();

                    if( selected ){
                        //If text is selected when button is clicked
                        //Wrap shortcode around it.
                        content =  '[birthdays class=""]'+selected+'[/birthdays]';
                    }else{
                        content =  '[birthdays class=""]';
                    }

                    tinymce.execCommand('mceInsertContent', false, content);
                });

            // Register buttons - trigger above command when clicked
            ed.addButton('birthdays_button', {
                title : 'Insert birthdays shortcode, [birthdays class="" img_width="" template=""]',
                cmd : 'birthdays_insert_shortcode',
                image: url + '/../images/birthday_cake_icon.png'
            });
        },   
    });

    // Register our TinyMCE plugin
    // first parameter is the button ID1
    // second parameter must match the first parameter of the tinymce.create() function above
    tinymce.PluginManager.add('birthdays_button', tinymce.plugins.birthdays_plugin);
});