<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1, maximum-scale=1"
    />
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>UME友觅交友后台管理系统</title>
    <link rel="stylesheet" href="/layui/css/layui.css" />
    <link
      rel="stylesheet"
      href="{{ asset('/backend/css/bootstrap.min.css') }}"
    />
    <link rel="stylesheet" href="{{ asset('/x-admin/css/xadmin.css') }}" />
    <link rel="stylesheet" href="{{ asset('/x-admin/css/font.css') }}" />
    @yield('page_css')
  </head>
  <body class="login-bg">
    <!-- <div
      class="layui-card layui-bg-green"
      style="text-align: center;height: 100px;vertical-align: middle"
    >
      <span style="font-size: 50px;line-height: 100px"
        >UME友觅交友后台管理系统</span
      >
    </div> -->
    <div class="login">
      <div class="message">欢迎！请登录</div>
      <div id="darkbannerwrap"></div>

      <form
        method="post"
        action="{{ route('admin/login') }}"
        class="layui-form"
      >
        <input
          type="hidden"
          name="_token"
          value="<?php echo csrf_token(); ?>"
        />
        <input
          name="email"
          placeholder="用户名"
          autocomplete="off"
          type="text"
          class="layui-input"
        />
        <hr class="hr15" />
        <input
          name="password"
          placeholder="密码"
          autocomplete="off"
          type="password"
          class="layui-input"
        />
        <hr class="hr15" />
        <input value="登录" style="width:100%;" type="submit" />
        <hr class="hr20" />
      </form>
    </div>

    <script src="/layui/layui.all.js"></script>
    <script>
      //由于模块都一次性加载，因此不用执行 layui.use() 来加载对应模块，直接使用即可：
      !(function() {
        var layer = layui.layer,
          form = layui.form;
      })();
    </script>
    @yield('page_js')
  </body>
</html>
