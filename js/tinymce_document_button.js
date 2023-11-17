(function() {
    tinymce.PluginManager.add('comodocButton', function( editor, url ) {
        editor.addButton( 'comodocButton', {
            text: tinyMCE_document.button_name,
            icon: false,
			onclick: function() {
				
				var templateOptions = jQuery.parseJSON(tinyMCE_document.document_template_select_options);
				var categoryOptions = jQuery.parseJSON(tinyMCE_document.doc_category_options);
				editor.windowManager.open( {
					title: tinyMCE_document.button_title,
					body: [
                        {
                            type: 'textbox',
                            name: 'id',
                            label: 'Document ID (if single document)',
                            value: ''
                        },
						{
                            type   : 'listbox',
                            name   : 'docCat',
                            label  : 'Category (if document category)',
                            values : categoryOptions
                        },
						{
                            type   : 'checkbox',
                            name   : 'featured',
                            label  : 'Featured',
                            text   : 'Featured',
                            checked : false
                        },
						{
                            type   : 'combobox',
                            name   : 'orderby',
                            label  : 'Order By',
                            values : [
                                { text: 'Date', value: 'date' },
                                { text: 'Title', value: 'title' },
								{ text: 'Menu Order', value: 'menu_order' }
                            ]
                        },
						{
                            type   : 'combobox',
                            name   : 'order',
                            label  : 'Order',
                            values : [
                                { text: 'Ascending', value: 'ASC' },
                                { text: 'Descending', value: 'DESC' }
                            ]
                        },
						{
                            type   : 'listbox',
                            name   : 'template',
                            label  : 'Template',
                            values : templateOptions
                        },
                    ],
					
					//[comodocs featured=TRUE/FALSE template=TEMPLATE NAME document-cat=MEMBER_TYPE orderby=DATE/TITLE/MENU_ORDER order=ASC/DESC limit=#]  
					
                    onsubmit: function( e ) {
						
						var docID = (e.data.id ? ' id='+e.data.id : '');
						var docCat = (e.data.docCat ? ' document-cat='+e.data.docCat : '');
						var featured = (e.data.featured ? ' featured=true' : ' featured=false');
						var orderby = (e.data.orderby ? ' orderby='+e.data.orderby : ' orderby=date');
						var order = (e.data.order ? ' order='+e.data.order : ' order=DESC');
						var template = (e.data.template ? ' template=' + e.data.template : ' template=default');
						
                        editor.insertContent( '[comodocs '+ docID + docCat + featured + orderby + order + template +']');
                    }
                });
            },
        });
    });
})();