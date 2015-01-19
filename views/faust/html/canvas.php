<?php echo HTML::style('media/faust/css/canvas.cropper.css', array(), TRUE);?>

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
            <canvas id="preview180" width="180" height="180" class="preview"></canvas>
            <p>大尺寸图片，180x180像素</p>
            </div>
            <div>
            <canvas id="preview100" width="100" height="100"></canvas>
            <p>中尺寸图片 100x100像素</p>
            </div>
            <div>
            <canvas id="preview50" width="50" height="50"></canvas>
            <p>小尺寸图片 50x50像素</p>
            </div>
        </td>
        </tr>
    </tbody>
</table>

<?php echo HTML::script('media/faust/js/canvas.cropper.js', array(), TRUE);?>