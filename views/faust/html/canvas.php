<?php echo HTML::style('media/faust/css/canvas.cropper.css', array(), TRUE);?>
<?php 
    $size_arr = explode('|', $config['pSize']);
    $preview_size = array(
        'height' => $size_arr[0],
        'width' => $size_arr[1],
    );
    $large_size = array(
        'height' => $size_arr[2],
        'width' => $size_arr[3],
    );
    $normal_size = array(
        'height' => $size_arr[4],
        'width' => $size_arr[5],
    );
    $small_size = array(
        'height' => $size_arr[6],
        'width' => $size_arr[7],
    );
?>
<table>
    <tbody>
        <tr>
        <td>
        <canvas id="cropper" data-url="<?php echo $avatar_source;?>"></canvas>
        <div>
            <a id="rotateLeftBtn" href="javascript:void(0);" class="rotate rotate-left">向左旋转</a>
            <a id="rotateRightBtn" href="javascript:void(0);" class="rotate rotate-right">向右旋转</a>
        </div>
        </td>
        <td id="previewContainer" class="preview">
            <div>
                <canvas id="preview-large" width="<?php echo $large_size['width'];?>" height="<?php echo $large_size['height'];?>" class="preview"></canvas>
                <p>大尺寸图片，<?php echo $large_size['width'];?>x<?php echo $large_size['height'];?>像素</p>
            </div>
            <div>
                <canvas id="preview-normal" width="<?php echo $normal_size['width'];?>" height="<?php echo $normal_size['height'];?>"></canvas>
                <p>中尺寸图片 <?php echo $normal_size['width'];?>x<?php echo $normal_size['height'];?>像素</p>
            </div>
            <div>
                <canvas id="preview-small" width="<?php echo $small_size['width'];?>" height="<?php echo $small_size['height'];?>"></canvas>
                <p>中尺寸图片 <?php echo $small_size['width'];?>x<?php echo $small_size['height'];?>像素</p>
            </div>
        </td>
        </tr>
    </tbody>
</table>

<?php echo HTML::script('media/faust/js/canvas.cropper.js', array(), TRUE);?>
<script type="text/javascript">
    jQuery(function($){
        var cropper;
        var previewWidth = <?php echo $preview_size['width'];?>;
        var previewHeight = <?php echo $preview_size['height'];?>;
        var largeWidth = <?php echo $large_size['width'];?>;
        var largeHeight = <?php echo $large_size['height'];?>;

        function init()
        {   
            cropper = new ImageCropper(previewWidth, previewHeight, largeWidth, largeHeight);
            cropper.setCanvas("cropper");
            cropper.addPreview("preview-large");
            cropper.addPreview("preview-normal");
            cropper.addPreview("preview-small");

            if(!isFileReaderSupported())
            {
                alert("抱歉, 您的浏览器不支持FileReader, 请使用 Firefox3.6+ 或者 Chrome10+ 浏览器。");
            }
        }

        function selectImage(fileList)
        {
            cropper.loadImage(fileList[0]);
        }

        function rotateImage(e)
        {
            switch(e.target.id)
            {
                case "rotateLeftBtn":
                    cropper.rotate(-90);
                    break;
                case "rotateRightBtn":
                    cropper.rotate(90);
                    break;
            }
        }
        init();

        $('#fileupload').bind({
            change: function(e){
                selectImage(this.files);
            }
        });
        $('#rotateLeftBtn').bind({
            click: function(e){
                cropper.rotate(-90);
            }
        });
        $('#rotateRightBtn').bind({
            click: function(e){
                cropper.rotate(90);
            }
        });
        $('#crop-image').bind({
            click: function(e){
                var imgData = cropper.getCroppedImageData(largeWidth, largeHeight);

                $.post('/faust/crop', {
                    file: cropper.image.src,
                    avatar: imgData
                }, function(json){
                    $('#status').html("<font color='#f00'>上传成功！</font>").trigger('done');
                }, 'json');
            }
        })
    });
</script>