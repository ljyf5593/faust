<div class="clearfix">
    <!-- The fileinput-button span is used to style the file input field as button -->
    <a href="javascript:void(0);" class="btn btn-success btn-xlarge fileinput-button">
    选择图片
    <input id="fileupload" type="file" name="file">
    </a>
    <a id="crop-image" href="javascript:;" class="btn btn-warning btn-xlarge">裁剪上传</a>
</div>
<h4 id="status">滑触照片可以选择上传区域。</h4>
<div id="avatar-container"></div>
<script type="text/javascript">
	function isFileReaderSupported() {
	  return typeof(FileReader) !== "undefined";
	}

	window.addEventListener('load', function(){
		$('#status').bind({
          done: function(e){
            setTimeout(function(e){
              window.location.reload();
            }, 500);
          }
        })

        if(isFileReaderSupported()){
          $('#avatar-container').load('/faust/canvas');
        } else {
          $('#avatar-container').load('/faust/jcrop');
        }
	}, false);
</script>