<div id="altContent"></div>
<?php echo HTML::script('media/faust/js/swfobject.js', array(), TRUE);?>
<script type="text/javascript">

    function uploadevent(status){
        try {
            object = eval ("(" + status + ")");
            status = object.status;
        } catch(err) {
            status += '';
        }
         switch(status){

            case 1: // 如果上传成功
                window.location.reload();
            break;

            case 2: // 通过js调用上传
                if(confirm('js call upload')){
                    return 1;
                }else{
                    return 0;
                }
            break;
            case -1: // 取消上传
                alert('cancel!');
            break;
            case -2: // 上传失败
                alert('upload failed!');
            break;

            default:
                alert(typeof(status) + ' ' + status);
        }
    }

    var flashvars = <?php echo json_encode($config);?>

    var params = {
        menu: "false",
        scale: "noScale",
        allowFullscreen: "true",
        allowScriptAccess: "always",
        wmode:"transparent",
        bgcolor: "#FFFFFF"
    };

    var attributes = {
        id:"FaustCplus"
    };

    swfobject.embedSWF("<?php echo URL::site("/media/faust/FaustCplus.swf")?>", "altContent", "650", "500", "9.0.0", "expressInstall.swf", flashvars, params, attributes);

</script>