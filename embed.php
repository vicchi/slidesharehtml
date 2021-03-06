<?php
/*
  Slideshare HTML Version
  Uses YQL for scraping and conversion
  Homepage: http://github.com/codepo8/slidesharehtml
  Copyright (c) 2010 Christian Heilmann
  Code licensed under the BSD License:
  http://wait-till-i.com/license.txt
*/
if(!ob_start("ob_gzhandler")) ob_start();
error_reporting(0);
$current = intval($_GET['current']) ? intval($_GET['current']) : 1;
$width = intval($_GET['width']) ? intval($_GET['width']) : 320;
$url='http://www.slideshare.net/api/oembed/1?url='.$_GET['url'].'&format=json';
$ch = curl_init(); 
curl_setopt($ch, CURLOPT_URL, $url); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
$images = curl_exec($ch); 
curl_close($ch);
$data = json_decode($images);
$num = $data->total_slides;
$suffix = $data->slide_image_baseurl_suffix;
$slides = $data->slide_image_baseurl;
$yay = ($num && $slides);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">  
  <title>HTML Slideshare embed</title>
  <style type="text/css">
  *{margin:0;padding:0;}
  #slideshare{
    float:left;
    min-width:<?php echo $width;?>px;
    min-height:200px;
    margin:10px;
    position:relative;
    -moz-box-shadow:2px 2px 10px rgba(0,0,0,.6);
    -webkit-box-shadow:2px 2px 10px rgba(0,0,0,.6);
    -o-box-shadow:2px 2px 10px rgba(0,0,0,.6);
    box-shadow:2px 2px 10px rgba(0,0,0,.6);
  }
  .preload{
    position:absolute;
    left:-20000px;
    top:-2000px;
  }
  #fwd{
    position:absolute;
    right:0px;
  }
  #count{
    position:absolute;
    left:42%;
    bottom:5px;
    display:block;
    font-weight:bold;
  }
  input{
    font-weight:bold;
    border:none;
    background:none;
    color:#fff;
    background:#333;
    margin:0 5px;
    padding:2px;
    -moz-border-radius:3px;
    -webkit-border-radius:3px;
    border-radius:3px;
  }
  input.inner{background:#ccc;color:#000;}
  form{
    font-family:helvetica,arial,sans-serif;
    background:#ccc;
    padding-bottom:5px;
    font-size:12px;
    font-weight:bold;
    position:relative;
  }
  #innext,#inprev{
    position:absolute;
    top:<?php echo intval($width/2.7);?>px;
    right:0;
    width:3em;
  }
  #inprev{
    left:0;
  }
  .inner{
    visibility:hidden;
  }
  #img:hover .inner{visibility:visible}
  input:hover{
    background:#393;
  }
  input[disabled]{
    background:#aaa;
  }
  input.inner[disabled]{
    background:#000;
    color:#333;
  }
  input#current{
    width:3em;
    text-align:center;
    background:#fff;
    color:#000;
    font-weight:bold;
  }
  </style>
</head>
<body>
<?php if($yay){ 
 if($current < 1 || $current > $num){
   $current = 1;
 }  
?>

<div id="slideshare"><form id="f"><div id="img">
  <img id="slide" src="<?php echo $slides;?>-slide-<?php echo $current;?><?php echo $suffix;?>" alt="" width="<?php echo $width;?>">
  <input type="button" id="innext" class="inner" value="&#x25B6;">
  <input type="button" id="inprev" class="inner" value="&#x25C0;">
  </div>
  <div>
    <span id="back">
      <input type="button" id="prev" value="&#x25C0;">
    </span>
    <span id="count"><input type="text" id="current" value="1" name="current"> / <?php echo $num;?></span>
    <span id="fwd">
      <input type="button" value="&#x25B6;" id="next">
    </span>
  </div>
</form></div>
<script src="http://yui.yahooapis.com/3.3.0/build/yui/yui-min.js"></script>
<script>
YUI().use('node','event-key', function(Y) {
  var current = <?php echo $current;?>,
      all = <?php echo $num;?>,
      img = Y.one('#slide'),
      url = img.get('src').replace(/\d+\<?php echo $suffix;?>/,'');
  for(var i=2;i<10;i++){
    var cacheimg = document.createElement('img');
    cacheimg.setAttribute('src',url+i+'<?php echo $suffix;?>');
    cacheimg.className = 'preload';
    document.body.appendChild(cacheimg);
  }
  update(current);
  Y.one('#slideshare').delegate('click',function(event){
    var id = this.get('id');
    switch(id){
      case 'next':current++;break;
      case 'prev':current--;break;
      case 'innext':current++;break;
      case 'inprev':current--;break;
      /*case 'last':current = all;break;
      case 'first':current = 1;break;*/
    }
    update(current);
  },'input');
  Y.one('document').on('keydown',function(event){
    var k = event.keyCode;
    if(k === 37 || k === 39){
      if(k===37){current--;}
      if(k===39){current++}
      update(current);
    }
  });

  function update(c){
    if(c <= 1){current = 1;}
    if(c > all){current = all;}
    if(current !== 1 && current !== all){
      current = c;
    }
    Y.one('#current').set('value',current);
    if(current === all){
      Y.one('#next').set('disabled','disabled');
      Y.one('#innext').set('disabled','disabled');
    } else {
      Y.one('#next').removeAttribute('disabled');
      Y.one('#innext').removeAttribute('disabled');
    }
    if(current === 1){
      Y.one('#prev').set('disabled','disabled');
      Y.one('#inprev').set('disabled','disabled');
    } else {
      Y.one('#prev').removeAttribute('disabled');
      Y.one('#inprev').removeAttribute('disabled');
    }
    if(current > 9){
      var cacheimg = document.createElement('img');
      cacheimg.setAttribute('src',url+(current+1)+'<?php echo $suffix;?>');
      cacheimg.className = 'preload';
      document.body.appendChild(cacheimg);
      var cacheimg2 = document.createElement('img');
      cacheimg2.setAttribute('src',url+(current+2)+'<?php echo $suffix;?>');
      cacheimg2.className = 'preload';
      document.body.appendChild(cacheimg2);
    }
    img.set('src',url + current + '<?php echo $suffix;?>');
  };
  Y.on('blur',function(e){
    current = Y.one('#current').get('value');
    update(current);
    e.preventDefault();
    return false;
  },'#current');
  Y.on('submit',function(e){
    current = Y.one('#current').get('value');
    update(current);
    e.preventDefault();
    return false;
  },'#f');
});
</script>
<?php }?>
</body>
</html>