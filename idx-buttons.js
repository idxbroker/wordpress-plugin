(function() {
	tinymce.create('tinymce.plugins.shortcodePlugin', {
		init : function(ed, url) {
			// Register commands
			ed.addCommand('mcebutton', function() {
				ed.windowManager.open({
					file : url + '/button-popup.php', // file that contains HTML for our modal window
					width : 620 + parseInt(ed.getLang('button.delta_width', 0)), // size of our window
					height : 340 + parseInt(ed.getLang('button.delta_height', 0)), // size of our window
					inline : 1
				}, {
					plugin_url : url
				});
			});
 
			// Register buttons
			ed.addButton('idx_button', {title : 'IDX', cmd : 'mcebutton', image: url + 'images/icon.png' });
		},
 
		getInfo : function() {
			return {
				longname : 'IDX Shortcode',
				author : 'Pramod Sivadas',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}
	});
 
	// Register plugin
	// first parameter is the button ID and must match ID elsewhere
	// second parameter must match the first parameter of the tinymce.create() function above
	tinymce.PluginManager.add('idx_button', tinymce.plugins.shortcodePlugin);
 
})();
