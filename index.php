<?php

/***
 * 简易文件浏览程序
 * @方淞 WeChat:wnfangsong E-mail:wnfangsong@163.com
 * @LINK:http://www.xpzfs.com
 * @TIME:2018-03-06
 */

header("Content-Type: text/html;charset=utf-8");
set_time_limit(20);
date_default_timezone_set("prc");
define('URL', 'http://www.cdnurl.fs');

/**
 * 文件容量转换
 * @param int $byte
 * @return string
 */
function toByte(int $byte) : string
{
    $type = ['B', 'KB', 'MB', 'GB'];
    for ($i = 0; $byte >= 1024; $i++) {
        $byte /= 1024;
    }
    $string = (floor($byte * 100) / 100) . $type[$i];
    return $string;
}

/**
 * 过滤字符
 * @param string $string
 * @return string
 */
function checkString(string $string) : string
{
    $string = preg_replace('/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]/', '', $string);
    $string = str_replace(array("\0", "%00", "\r"), '', $string);
    return $string;
}

/**
 * 获取路径下的文件及文件夹
 * @param string $path
 * @return array
 */
function scanFile(string $path) : array
{
    if (!is_dir(__DIR__ . $path)) {
        return [];
    }
    $new_file_list = [];
    $list = scandir(__DIR__ . $path);
    foreach ($list as $k => $v) {
        if (!in_array($v, ['.', '..'])) {
            if ($path === '/' && $v === 'index.php' || $v === 'robot.txt') {
                continue;
            }
            $file = __DIR__ . $path . $v;
            $file_type = '';
            $file_size = '-';
            $file_url = '-';
            if (is_file($file)) {
                $file_type = strtolower(substr(strrchr($file, '.'), 1));
                if (in_array($file_type, ['jpg', 'jpeg', 'gif', 'png'])) {
                    $file_type = 'img';
                }
                $file_size = toByte(filesize($file));
                $file_url = URL . $path . $v;
            }
            if (is_dir($file)) {
                $file_type = 'dir';
            }
            if ($file_type !== '') {
                $new_file_list[] = [
                    'file_name' => $v,
                    'file_url' => $file_url,
                    'file_type' => $file_type,
                    'update_time' => date('Y-m-d H:i', filemtime($file)),
                    'file_size' => $file_size,
                    'link' => $file_type === 'dir' ? URL . '/?path=' . urlencode(base64_encode($path . $v . '/')) : ''
                ];
            }
        }
    }
    return $new_file_list;
}

//获取PATH参数并过滤
$path = isset($_GET['path']) ? checkString(urldecode(base64_decode($_GET['path']))) : '/';
$path = str_replace('../', '', $path);
$path = str_replace('./', '', $path);
if ($path === '') {
    $path = '/';
} else {
    if (!is_dir(__DIR__ . $path)) {
        $path = '/';
    } else {
        $tmp_path = realpath(__DIR__ . $path);
        if (false === $tmp_path || $tmp_path === __DIR__) {
            $path = '/';
        }
    }
}
//其他参数
$back = dirname($path);
if (in_array($back, ['', '.', '\\'])) {
    $back = '/';
}
if (substr($back, 0, 1) !== '/') {
    $back = '/' . $back;
}
if (substr($back, -1) !== '/') {
    $back .= '/';
}
if (empty(array_filter(explode('/', $path))) && $back === '/') {
    $back_url = 'javascript:;';
} else {
    $back_url = URL . '/?path=' . urlencode(base64_encode($back));
}
$file_list = scanFile($path);

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>简易文件浏览程序 - 方淞（XPZFS.COM）</title>
    <link rel="shortcut icon" href="http://www.xpzfs.com/favicon.ico" />
    <style type="text/css">
        html, body, div, span, ul, li, p {margin: 0; padding: 0; font-size: 100%; vertical-align: baseline; background: transparent;}
        ul, li {list-style: none;}
        a {margin: 0; padding: 0; font-size: 100%; vertical-align: baseline; -webkit-tap-highlight-color:rgba(0,0,0,0); background: transparent; color: #000;}
        a:focus, input, textarea {outline: none;}
        a:link, a:visited, a:active {text-decoration: none; color: #000;}
        a:hover {text-decoration: none; color: #f00;}
        body {background-color: #fff; overflow: hidden; overflow-y: scroll; font-size: 16px; color: #000; font-family: "Microsoft Yahei", "SimHei"; line-height: 26px;}
        div, ul, li, p, a, span {float: left;}
        #path {width: calc(100% - 40px); margin: 10px 20px 20px 20px; padding-bottom: 10px; border-bottom: 1px solid #e0e0e0;}
        #path p {width: 100%; height: 40px;}
        #path p.txt {height: auto; line-height: 42px; padding: 4px 0; font-size: 38px; font-weight: bold;}
        #path p.link {height: auto;}
        #path p.link a {height: 36px; line-height: 36px; font-size: 18px; margin-right: 20px; display: block; text-decoration: underline;}
        #file {width: calc(100% - 40px); margin: 0 20px 20px 20px; border-bottom: 1px solid #e3e8ea;}
        #file ul {width: 100%; height: 30px; padding: 5px 0; overflow: hidden;}
        #file ul:nth-child(odd) {background-color: #f1f4f7;}
        #file ul.title {height: auto; background-color: #e3e8ea;}
        #file ul.title li {font-weight: bold; font-size: 18px; height: 42px; line-height: 42px;}
        #file ul.title li.ico {display: none;}
        #file ul.title li.name {margin-left: 43px;}
        #file ul li {width: auto; height: 30px; line-height: 30px; padding: 0 15px;}
        #file ul li.ico {width: 22px; height: 22px; padding: 0; margin: 4px 7px 4px 14px; border-radius: 3px; background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEIAAAAWCAYAAAB0S0oJAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6OEM0MTUxRTIyMTE4MTFFODhEOEFGQzgzNTE4NUVBNDEiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6OEM0MTUxRTMyMTE4MTFFODhEOEFGQzgzNTE4NUVBNDEiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo4QzQxNTFFMDIxMTgxMUU4OEQ4QUZDODM1MTg1RUE0MSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo4QzQxNTFFMTIxMTgxMUU4OEQ4QUZDODM1MTg1RUE0MSIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PimLI44AAAScSURBVHja7Fh9TBtlGP9d7649oNBSmIaCcyKOMZ2KTjeG25Ilfm0kfsz4j8bEELMgSuJHdIkxi5l/OHUat5hF0YmbTqeJi2YuRo0uMYgMccPRTRIKKNgyV9qu9Ot6X7537Si4ld4VBv7Be3lzX8897/v+3uf3fBylKAoWGmBagCDZmLkcbMTjHahwltkv9u7zL76E63QfGjbegW3bd0AQBDAMEzxy6NPK6XQGh8ID9iVWe6b3n913FDc9dg2ivjh+ft0FxkKDMlHBxs6NlfMGBAGhlJwKM71XF7+y9kZsfaYFL+94A5IkZZ0fAWFanSr1ycJx/SNXQ5EU/PLmadBmEzPf1BD1CK2tr8PjWxoJMKI4GzoVOekHb3i0CmueuxayKIsZqRH9eld/3p1NDophpwhInj6c290IvqcdpkJu6teUCiWdutCa//ID41W5IKTuHKHCxP09m+6ChTXPGHk1FjB59MT9iocrwTnMmX1E/qYW1cRs/xWgndUoam5F6L0WiP1doPKKJlBQZAFy2E9MjwxEmWZkYSoIJ12n8O7efRMY08zMmavq6f1kEGdOBCYemBhqWmeZ0cSYihoUv3AYCh8hilJKUufYkbcRPvQqMR1BtQ4x1wmvvnUl3IN/wusdJaqnjjGTdnPTUgx+70Xo72ganIuopSblET7SS4wOJIfG4N+6CpJvmCBmHiPUKJ1G3E96sQH16jY6sshoOqM+Hp1vnUI8IOCWJ6pRusxmSC+TwbtAiYagiPx5k8+QhdAEiLNEjliDJJF7SfcKz/rGEIlE0ruf8hNWqxWlJQ7DO9/T1o/ejwe1CCEJEhreqZtZHiH9M4RQ27OQhl1JR2jKQvuURzY5yqAkYroH3nfgIH5q7wBnSTvgOB/Huvo1eLqlyTAQ+Ys4UDSlhciCRZxxXzKZGnI4UBJ87QHiIJei8MFtBCZWs46s7ohO4ikHvWNMeY0uasTjPPgET76eZBHksJgt4DiLYWrIgoy+r4bBnxOw7P7F4OzpyHBs9x9g8xnUNlbpoIYkIvZjG1mNBNuWPTk5JsH9KwgQumRbP9iPjs5jsHDp3ePjcdTXrUIzySEM1wqsCTWbr7zgecdOF46/359cLEdjxUNXTU8NaWwEse9aYWvem1u8JhFl/MPnYdnZrUt+870N2LD+NsK8NPVkWYbdbss5QpzpCWDcE0XV3eWqeaH9lV78/tEAzFYGiYiE0eP+7ECED74E8/J1YKtX5zSJyOFdEIdO6pbv7OrWaguzOZ3AJRICrlteg3JnmeHxvb/58e1TXYj5ecSDCYT+ihAQ3GALWC1cqp2k1tmdpeDuZkq2/5ATCNKoG7Fv9oBiTLozIDU6FJPdZ1l2Sq1hLcjPaQ49bW74+8dhKWJx9MUTSddF6CJEkqlNIixAjEvZgaAdTh+sDtnoBOSAh2SdT0IKegkQFr/e727fsF7rs9UWr70M5kJGqy5xPgBooTl5LfIyymoduqLGXLRLllAZnMcFeue6+mQugTwzG/OY6x8zvgpnme56ZHjEE7yiwoksP2Z89iVWQzVOYCAcLK60zis1Fv5Z/t/bvwIMANHrsGTxJEq1AAAAAElFTkSuQmCC") no-repeat -16px 2px #495057; background-size: 54px 18px;}
        #file ul li.dir {background-color: #e8590c; background-position: 3px 2px;}
        #file ul li.img {background-color: #9c36b5; background-position: -34px 2px;}
        #file ul li.name {width: 300px; padding-left: 5px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;}
        #file ul li.name a {text-decoration: underline;}
        #file ul li.update_time {width: 150px; text-align: center;}
        #file ul li.file_size {width: 90px; text-align: center;}
        #file ul li.file_url {width: calc(100% - 693px); overflow: hidden; white-space: nowrap; text-overflow: ellipsis;}
        #file ul.null {height: 300px; text-align: center; padding-top: 30px; font-size: 15px; color: #c5c5c5;}
        #footer {width: calc(100% - 40px); margin: 5px 20px 50px 20px;}
        #footer span {margin-left: 20px; color: #c1c1c1; float: right; font-size: 15px;}
        #footer span a {color: #c1c1c1; font-size: 15px;}
        #footer span a:hover {text-decoration: underline;}
    </style>
</head>
<body>

<div id="path">
    <p class="txt">Index of&nbsp;<?php echo $path; ?></p>
    <p class="link">
        <a href="<?php echo URL . '/?path=' . urlencode(base64_encode('/')); ?>">Root Directory</a>
        <a href="<?php echo $back_url; ?>">Parent Directory</a>
    </p>
</div>
<div id="file">
    <ul class="title">
        <li class="ico"></li>
        <li class="name">Name</li>
        <li class="update_time">Last modified</li>
        <li class="file_size">Size</li>
        <li class="file_url">File Url</li>
    </ul>
    <?php if (empty($file_list)) {
        echo '<ul class="null">该文件夹为空。</ul>';
    } else { foreach ($file_list as $k => $v) { ?>
    <ul class="list">
        <li class="ico <?php echo $v['file_type']; ?>"></li>
        <li class="name">
            <?php if ($v['link'] === '') {
                echo $v['file_name'];
            } else {
                echo '<a href="' . $v['link'] . '">' . $v['file_name'] . '</a>';
            } ?>
        </li>
        <li class="update_time"><?php echo $v['update_time']; ?></li>
        <li class="file_size"><?php echo $v['file_size']; ?></li>
        <li class="file_url"><?php echo $v['file_url']; ?></li>
    </ul>
    <?php }} ?>
</div>
<div id="footer">
    <span class="icp"><a href="http://www.miitbeian.gov.cn/" target="_blank">浙ICP备15016441号-2</a></span>
    <span class="tse"><a href="http://www.xpzfs.com/">方淞（XPZFS.COM）&nbsp;技术支持</a></span>
</div>

</body>
</html>
