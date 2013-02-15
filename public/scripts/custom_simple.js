function setupFCK() {
	if(document.getElementById('contents')) {
        var oFCKeditor = new FCKeditor('contents') ;
        oFCKeditor.BasePath = "/scripts/fckeditor/" ;
        oFCKeditor.Height = 500;
        oFCKeditor.Width = 900;
        oFCKeditor.ToolbarSet = 'Basic';
        oFCKeditor.ReplaceTextarea() ;
    }

}
