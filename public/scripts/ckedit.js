function loadCK(taname)
{
CKEDITOR.replace( taname, {
	   
	//turn on CKEitor upload，use CKFinder plugin
   /*filebrowserBrowseUrl : '/scripts/ckfinder/ckfinder.html',

   filebrowserImageBrowseUrl : '/scripts/ckfinder/ckfinder.html?Type=Images',

   filebrowserFlashBrowseUrl : '/scripts/ckfinder/ckfinder.html?Type=Flash',

   filebrowserUploadUrl : '/scripts/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',

   filebrowserImageUploadUrl : '/scripts/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images',
*/
  // filebrowserFlashUploadUrl : '/scripts/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash'
	   filebrowserBrowseUrl :'/filemanager/index.html',
       filebrowserImageBrowseUrl : '/filemanager/index.html?type=Images',
       filebrowserFlashBrowseUrl :'/filemanager/index.html?type=Flash'

   });
}