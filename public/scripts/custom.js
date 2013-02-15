function setupFCK() {
	if(document.getElementById('contents')) {
        var oFCKeditor = new FCKeditor('contents') ;
        oFCKeditor.BasePath = "/scripts/fckeditor/" ;
        oFCKeditor.Height = 400;
        oFCKeditor.Width = 700;
        oFCKeditor.ReplaceTextarea() ;
    }

}
