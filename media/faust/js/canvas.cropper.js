/**
 * ImageCropper by Flashlizi, Copyright (c) 2011 RIAidea.com
 * Homepage: http://www.riaidea.com/blog/
 * 
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 * 
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 */

(function(scope){

/**
 * 对象构造函数
 * width, height  画布高宽
 * cropWidth, cropHeight 初始裁剪区域高宽
 */
var ImageCropper = function(width, height, cropWidth, cropHeight)
{
    this.width = width;
    this.height = height;
    this.cropWidth = cropWidth;
    this.cropHeight = cropHeight;
    
    this.image = null;  
    this.imageCanvas = null;
    this.imageContext = null;   
    this.imageScale = 1;
    this.imageRotation = 0;

    this.imageLeft = 0;
    this.imageTop = 0;
    this.imageViewLeft = 0;
    this.imageViewTop = 0;

    this.canvas = null;
    this.context = null;
    this.previews = [];
    
    this.maskGroup = [];
    this.maskAlpha = 0.4;
    this.maskColor = "#fff";

    this.cropLeft = 0;
    this.cropTop = 0;
    this.cropViewWidth = cropWidth;
    this.cropViewHeight = cropHeight;

    this.dragSize = 7;
    this.dragColor = "#fff";
    this.dragLeft = 0;
    this.dragTop = 0;

    this.mouseX = 0;
    this.mouseY = 0;
    this.inCropper = false;
    this.inDragger = false;
    this.isMoving = false;
    this.isResizing = false;
    
    //move and resize help properties
    this.mouseStartX = 0;
    this.mouseStartY = 0;
    this.cropStartLeft = 0;
    this.cropStartTop = 0;
    this.cropStartWidth = 0;
    this.cropStartHeight = 0;
}
scope.ImageCropper = ImageCropper;

ImageCropper.prototype.setCanvas = function(canvas)
{
    //working canvas
    this.canvas = document.getElementById(canvas) || canvas;
    this.context = this.canvas.getContext("2d");
    this.canvas.width = this.width;
    this.canvas.height = this.height;
    this.canvas.oncontextmenu = this.canvas.onselectstart = function(){return false;};
    
    //caching canvas
    this.imageCanvas = document.createElement("canvas");
    this.imageContext = this.imageCanvas.getContext("2d");
    this.imageCanvas.width = this.width;
    this.imageCanvas.height = this.height;
}

ImageCropper.prototype.addPreview = function(canvas)
{
    var preview = document.getElementById(canvas) || canvas;
    var context = preview.getContext("2d");
    this.previews.push(context);
}

ImageCropper.prototype.loadImage = function(file)
{
    if(!isFileReaderSupported || !this.isImage(file)) return;
    var reader = new FileReader();
    var me = this;
    reader.readAsDataURL(file);
    reader.onload = function(evt)
    {
        if(!me.image) me.image = new Image();
        me.image.onload = function(e){me._init()};
        me.image.src = evt.target.result;
    }
}

ImageCropper.prototype._init = function()
{   
    //åˆå§‹åŒ–å›¾ç‰‡çš„ç¼©æ”¾æ¯”ä¾‹å’Œä½ç½®
    var scale = Math.min(this.width/this.image.width, this.height/this.image.height);
    if (scale > 1) scale = Math.min(this.cropViewWidth/this.image.width, this.cropViewHeight/this.image.height);
    if (this.image.width*scale<this.cropViewWidth) scale = Math.min(scale, this.cropViewWidth/this.image.width);
    if (this.image.height*scale<this.cropViewHeight) scale = Math.min(scale, this.cropViewHeight/this.image.height);

    this.imageViewLeft = this.imageLeft = (this.width - this.image.width*scale)/2;
    this.imageViewTop = this.imageTop = (this.height - this.image.height*scale)/2;
    this.imageScale = scale;
    this.imageRotation = 0;

    //crop view size
    var minSize = Math.min(this.image.width*scale, this.image.height*scale);
    this.cropViewWidth = Math.min(minSize, this.cropWidth);
    this.cropViewHeight = Math.min(minSize, this.cropHeight);
    this.cropLeft = (this.width - this.cropViewWidth)/2;
    this.cropTop = (this.height - this.cropViewHeight)/2;

    //resize rectangle dragger
    this.dragLeft = this.cropLeft + this.cropViewWidth - this.dragSize/2;
    this.dragTop = this.cropTop + this.cropViewHeight - this.dragSize/2;

    this._update();
    
    //register event handlers
    var me = this;
    this.canvas.onmousedown = function(e){me._mouseHandler.call(me, e)};
    this.canvas.onmouseup = function(e){me._mouseHandler.call(me, e)};
    this.canvas.onmousemove = function(e){me._mouseHandler.call(me, e)};
}

ImageCropper.prototype._mouseHandler = function(e)
{
    if(e.type == "mousemove")
    {
        var clientRect = this.canvas.getClientRects()[0];
        this.mouseX = e.pageX - clientRect.left;
        this.mouseY = e.pageY - clientRect.top;
        this._checkMouseBounds();
        this.canvas.style.cursor = (this.inCropper || this.isMoving)  ? "move" : (this.inDragger || this.isResizing) ? "se-resize" : "";
        this.isMoving ? this._move() : this.isResizing ? this._resize() : null;
    }else if(e.type == "mousedown")
    {
        this.mouseStartX = this.mouseX;
        this.mouseStartY = this.mouseY;
        this.cropStartLeft = this.cropLeft;
        this.cropStartTop = this.cropTop;
        this.cropStartWidth = this.cropViewWidth;
        this.cropStartHeight = this.cropViewHeight;
        this.inCropper ? this.isMoving = true : this.inDragger ? this.isResizing = true : null;
    }else if(e.type == "mouseup")
    {
        this.isMoving = this.isResizing = false;
    }
}

/**
 * 检测当前鼠标应该显示的状态
 */
ImageCropper.prototype._checkMouseBounds = function()
{
    var scrollTop = $(document).scrollTop();
    var scrollLeft = $(document).scrollLeft();
    cropTop = this.cropTop + scrollTop;
    cropLeft = this.cropLeft + scrollLeft;
    this.inCropper = ( this.mouseX >= cropLeft && 
                       this.mouseX <= cropLeft + this.cropViewWidth &&
                       this.mouseY >= cropTop && 
                       this.mouseY <= cropTop + this.cropViewHeight);

    var dragTop = this.dragTop + scrollTop;
    var dragLeft = this.dragLeft + scrollLeft;
    this.inDragger = ( this.mouseX >= dragLeft && 
                       this.mouseX <= dragLeft + this.dragSize &&
                       this.mouseY >= dragTop && 
                       this.mouseY <= dragTop + this.dragSize);
    
    this.inCropper = this.inCropper && !this.inDragger;
}

/**
 * 裁剪区域移动
 */
ImageCropper.prototype._move = function()
{
    var deltaX = this.mouseX - this.mouseStartX;
    var deltaY = this.mouseY - this.mouseStartY;

    this.cropLeft = Math.max(this.imageViewLeft, this.cropStartLeft + deltaX);
    this.cropLeft = Math.min(this.cropLeft, this.width-this.imageViewLeft-this.cropViewWidth);
    this.cropTop = Math.max(this.imageViewTop, this.cropStartTop + deltaY);
    this.cropTop = Math.min(this.cropTop, this.height-this.imageViewTop-this.cropViewHeight);

    this.dragLeft = this.cropLeft + this.cropViewWidth - this.dragSize/2;
    this.dragTop = this.cropTop + this.cropViewHeight - this.dragSize/2;
    
    this._update();
}

/**
 * 图片缩放
 */
ImageCropper.prototype._resize = function()
{
    var delta = Math.min(this.mouseX - this.mouseStartX, this.mouseY - this.mouseStartY);   
    
    var cw = Math.max(10, this.cropStartWidth + delta);
    var ch = Math.max(10, this.cropStartHeight + delta);
    var cw = Math.min(cw, this.width-this.cropStartLeft-this.imageViewLeft);
    var ch = Math.min(ch, this.height-this.cropStartTop-this.imageViewTop);
    this.cropViewWidth = this.cropViewHeight = Math.round(Math.min(cw, ch));

    this.dragLeft = this.cropLeft + this.cropViewWidth - this.dragSize/2;
    this.dragTop = this.cropTop + this.cropViewHeight - this.dragSize/2;
    
    this._update();
}

/**
 * 图片旋转
 */
ImageCropper.prototype.rotate = function(angle)
{
    if(!this.image) return;
    this.imageRotation += angle;
    
    //æ ¹æ®æ—‹è½¬è§’åº¦æ¥æ”¹å˜å›¾ç‰‡è§†åŸŸçš„leftå’Œtop
    var rotateVertical = Math.abs(this.imageRotation%180)==90;
    this.imageViewLeft = rotateVertical ? this.imageTop : this.imageLeft;
    this.imageViewTop = rotateVertical ? this.imageLeft : this.imageTop;
    
    //æ›´æ–°è£å‰ªå’Œå˜å½¢çš„ä½ç½®
    this.cropLeft = (this.width - this.cropViewWidth)/2;
    this.cropTop = (this.height - this.cropViewHeight)/2;
    this.dragLeft = this.cropLeft + this.cropViewWidth - this.dragSize/2;
    this.dragTop = this.cropTop + this.cropViewHeight - this.dragSize/2;
    
    this._update();
}

/**
 *更新图像信息
 */
ImageCropper.prototype._update = function()
{
    this.context.clearRect(0, 0, this.width, this.height);
    
    this._drawImage();
    this._drawMask();   
    this._drawDragger();
    this._drawPreview();
}

/**
 * 绘制图像
 */
ImageCropper.prototype._drawImage = function()
{   
    this.imageContext.clearRect(0, 0, this.width, this.height);
    this.imageContext.save();
    var angle = this.imageRotation%360; 
    this.imageContext.translate(this.imageViewLeft, this.imageViewTop); 
    this.imageContext.scale(this.imageScale, this.imageScale);
    this.imageContext.rotate(this.imageRotation*Math.PI/180);

    switch((360-angle)%360)
    {       
        case 90:
            this.imageContext.drawImage(this.image, -this.image.width, 0);
            break;
        case 180:
            this.imageContext.drawImage(this.image, -this.image.width, -this.image.height);
            break;
        case 270:
            this.imageContext.drawImage(this.image, 0, -this.image.height);
            break;
        case 0:
        default:
            this.imageContext.drawImage(this.image, 0, 0);
            break;
    }
    this.imageContext.restore();
    
    this.context.drawImage(this.imageCanvas, 0, 0);
}

/**
 * 绘制预览图
 */
ImageCropper.prototype._drawPreview = function()
{
    for(var i = 0; i < this.previews.length; i++)
    {
        var preview = this.previews[i];
        preview.clearRect(0, 0, preview.canvas.width, preview.canvas.height);
        preview.save();
        preview.drawImage(this.imageCanvas, this.cropLeft, this.cropTop, this.cropViewWidth, this.cropViewHeight, 0, 0, preview.canvas.width, preview.canvas.height);
        preview.restore();
    }   
}

/**
 * 绘制遮罩选择区域
 */
ImageCropper.prototype._drawMask = function()
{
    // 左侧遮罩
    this._drawRect(this.imageViewLeft, this.imageViewTop, this.cropLeft-this.imageViewLeft, this.height, this.maskColor, null, this.maskAlpha);
    
    // 右侧遮罩
    this._drawRect(this.cropLeft+this.cropViewWidth, this.imageViewTop, this.width-this.cropViewWidth-this.cropLeft+this.imageViewLeft, this.height, this.maskColor, null, this.maskAlpha);
   
    // 上部遮罩
    this._drawRect(this.cropLeft, this.imageViewTop, this.cropViewWidth, this.cropTop-this.imageViewTop, this.maskColor, null, this.maskAlpha);

    // 底部遮罩
    this._drawRect(this.cropLeft, this.cropTop+this.cropViewHeight, this.cropViewWidth, this.height-this.cropViewHeight-this.cropTop+this.imageViewTop, this.maskColor, null, this.maskAlpha);
}

ImageCropper.prototype._drawDragger = function()
{
    this._drawRect(this.dragLeft, this.dragTop, this.dragSize, this.dragSize, null, this.dragColor, null);
}

ImageCropper.prototype._drawRect = function(x, y, width, height, color, border, alpha)
{
    this.context.save();
    if(color !== null) this.context.fillStyle = color;
    if(border !== null) this.context.strokeStyle = border;
    if(alpha !== null) this.context.globalAlpha = alpha;
    this.context.beginPath();
    this.context.rect(x, y, width, height);
    this.context.closePath();
    if(color !== null) this.context.fill();
    if(border !== null) this.context.stroke();
    this.context.restore();
}

ImageCropper.prototype.getCroppedImageData = function(width, height, mime)
{
    var output = document.createElement("canvas");
    output.width = width || this.cropWidth;
    output.height = height || this.cropHeight;
    output.getContext("2d").drawImage(this.imageCanvas, this.cropLeft, this.cropTop, this.cropViewWidth, this.cropViewHeight, 0, 0, output.width, output.height);
    return output.toDataURL(mime || "image/jpeg");
}

ImageCropper.prototype.isImage = function(file)
{
    return (/image/i).test(file.type);
}

})(window);