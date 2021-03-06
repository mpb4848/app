// Insert buttons for Main RTE
(function(window){

	var WE = window.WikiaEditor;

	WE.modules.Insert = $.createClass(WE.modules.ButtonsList,{

		modes: true,
		
		headerClass: 'insert',
		headerTextId: 'insert-title',
		
		cuttingPosition: false,
		cuttingText: false,

		items: [
			'InsertImage',
			'InsertGallery',
			'InsertSlideshow',
			'InsertSlider',
			'InsertVideo',
			'InsertMedia',
			'Poll',
			'Table'
		],

		init: function() {
			WE.modules.Insert.superclass.init.call(this);
			this.editor.on('beforemediawikibutton',this.beforeMediawikiButton,this);
			if (this.editor.config.insertCutPosition >= 0 && this.editor.config.insertCutText) {
				this.cuttingPosition = this.editor.config.insertCutPosition;
				this.cuttingText = this.editor.config.insertCutText;
			}
		},

		beforeMediawikiButton: function( editor, button ) {
			var disabled = {
				'mw-editbutton-wmu': 1,
				'mw-editbutton-wpg': 1,
				'mw-editbutton-vet': 1
			};
			return typeof button.imageId == 'undefined' || typeof disabled[button.imageId] == 'undefined';
		},
		
		afterRender: function() {
			WE.modules.Insert.superclass.afterRender.call(this);
			var buttons = this.el.find('.cke_button');
			buttons.addClass('cke_button_big');
			if (this.cuttingPosition !== false) {
				buttons.slice(this.cuttingPosition).hide();
				var link = $('<a>')
					.addClass('cke_more_buttons')
					.html(this.cuttingText)
					.click(function(){
						link.remove();
						buttons.show();
					});
				this.el.append(link);
			}
		}

	});
	
	WE.modules.ToolbarInsert = WE.modules.Insert;
	WE.modules.RailInsert = WE.modules.Insert;

})(this);