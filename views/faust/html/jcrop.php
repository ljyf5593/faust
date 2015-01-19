<?php echo HTML::style('media/faust/css/jquery.jcrop.css', array(), TRUE);?>

<!-- This is the image we're attaching Jcrop to -->
<table cellpadding="0" cellspacing="0" border="0" style="margin-top:10px;">
    <tr>
        <td rowspan='2'>
            <img data-width="<?php echo $jcrop_size['width']?>" width="<?php echo $jcrop_size['width']?>" data-height="<?php echo $jcrop_size['height']?>" height="<?php echo $jcrop_size['height']?>" src="<?php echo $avatar_source;?>" id="jcrop_target" />
        </td>
        <td align="center">
            <div style="width:<?php echo $avatar_size['big']['width']?>px;height:<?php echo $avatar_size['big']['height']?>px;overflow:hidden;margin-left:5px;">
                <img data-width="<?php echo $avatar_size['big']['width']?>" data-height="<?php echo $avatar_size['big']['height']?>" width="<?php echo $avatar_size['big']['width']?>" height="<?php echo $avatar_size['big']['height']?>" class="jcrop-preview" src="<?php echo $avatar_source;?>" />
            </div>
            <p><?php echo $avatar_size['big']['width']?>*<?php echo $avatar_size['big']['height']?></p>
        </td>
    </tr>
    <tr align="center">
        <td>
            <div style="width:<?php echo $avatar_size['middle']['width']?>px;height:<?php echo $avatar_size['middle']['height']?>px;overflow:hidden;margin-left:5px;">
                <img data-width="<?php echo $avatar_size['middle']['width']?>" data-height="<?php echo $avatar_size['middle']['height']?>" width="<?php echo $avatar_size['middle']['width']?>" height="<?php echo $avatar_size['middle']['height']?>" class="jcrop-preview" src="<?php echo $avatar_source;?>" />
            </div>
            <p><?php echo $avatar_size['middle']['width']?>*<?php echo $avatar_size['middle']['height']?></p>
        </td>
        <td>
            <div style="width:<?php echo $avatar_size['small']['width']?>px;height:<?php echo $avatar_size['small']['height']?>px;overflow:hidden;margin-left:5px;">
                <img data-width="<?php echo $avatar_size['small']['width']?>" data-height="<?php echo $avatar_size['small']['height']?>" width="<?php echo $avatar_size['small']['width']?>" height="<?php echo $avatar_size['small']['height']?>" class="jcrop-preview" src="<?php echo $avatar_source;?>" />
            </div>
            <p><?php echo $avatar_size['small']['width']?>*<?php echo $avatar_size['small']['height']?></p>
        </td>
    </tr>
</table>

<?php echo HTML::script('media/faust/js/jQuery.AjaxFileUpload.js', array(), TRUE);?>
<?php echo HTML::script('media/faust/js/jquery.jcrop.js', array(), TRUE);?>