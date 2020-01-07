<?php
/**
 * Created by PhpStorm.
 * User: brucewei
 * Date: 2018/5/9
 * Time: 上午11:45
 */

/**
 * 获取文件拓展名
 *
 * @param string $filename
 *
 * @return string
 */
function fileExt($filename)
{
    $ext = pathinfo($filename, PATHINFO_EXTENSION);

    return empty($ext) ? '' : ".$ext";
}

/**
 * 检查当前url是否属于菜单中定义的模式，完成菜单激活状态
 *
 * @param array $pattern
 *            app/config/menu.php中的pattern
 *
 * @return boolean
 */
function is_current_model(array $pattern)
{
    foreach ($pattern as $ptn) {
        if (urldecode(Request::getRequestUri()) == $ptn) {
            return true;
        }
    }

    return false;
}
