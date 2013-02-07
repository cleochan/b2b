/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
        	
    config.toolbar = 'MyToolbar';
 
    config.toolbar_MyToolbar =
    [
//       ['NewPage','Preview'],
//       ['Cut','Copy','Paste','PasteText','PasteFromWord','-','Scayt'],
//       ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
        ['Bold','Italic','-','OrderedList','UnorderedList','Image','-','Source']
        /*['Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak'],
        '/',
        ['Styles','Format'],
        ['Bold','Italic','Strike'],
        ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
        ['Link','Unlink','Anchor'],
        ['Maximize','-','About']*/
    ];
    config.width  = 900;
    config.hight = 1200;
};
