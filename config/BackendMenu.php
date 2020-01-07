<?php
/**
 * Created by PhpStorm.
 * User: brucewei
 * Date: 2018/4/26
 * Time: 上午10:00
 */

return
array(
    array(
        'name' => '用户审核',
        'icon' => 'glyphicon glyphicon-briefcase',
        'url' => '/admin/new-order',
        'href' => 'collapseOne',
        'pattern' => array(
            '/admin/new-order',
            '/admin/un-order',
            '/admin/in-order',
            '/admin/complete-order',
            '/admin/type-order',
        ),
        'permission' => '*',
        'submenu' => array(

            array(
                'name' => '待审核',
                'url' => '/admin/un-order',
                'icon' => 'glyphicon glyphicon-edit',
                'permission' => 'un-order',
            ),
            array(
                'name' => '已审核',
                'url' => '/admin/in-order',
                'icon' => 'glyphicon glyphicon-share',
                'permission' => 'in-order',
            ),
            array(
                'name' => '重新上传资料',
                'url' => '/admin/complete-order',
                'icon' => 'glyphicon glyphicon-check',
                'permission' => 'complete-order',
            ),
        ),
    ),
);
