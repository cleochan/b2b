function setupFCK() {
	if(document.getElementById('comments')) {
        var oFCKeditor = new FCKeditor('comments') ;
        oFCKeditor.BasePath = "/scripts/fckeditor/" ;
        oFCKeditor.Height = 300;
        oFCKeditor.Width = 900;
        oFCKeditor.ToolbarSet = 'Basic';
        oFCKeditor.ReplaceTextarea() ;
    }

}
