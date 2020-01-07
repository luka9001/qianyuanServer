<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <!-- 引入样式 -->
    <!-- <link rel="stylesheet" type="text/css"  href="{{ asset('/element-ui/index.css') }}" /> -->
    <!-- <link
      rel="stylesheet"
      href="https://unpkg.com/element-ui/lib/theme-chalk/index.css"
    /> -->
    @yield('page_css')
    <!-- 引入组件库 -->
    <!-- <script src="{{ asset('/element-ui/index.js') }}"></script> -->
    <!-- <script src="https://unpkg.com/element-ui/lib/index.js"></script> -->

    <title>UME友觅交友后台管理系统</title>
  </head>
  <body>
    <div id="app">
    <el-header style="text-align: left; font-size: 50px"> UME友觅交友管理系统</el-header>
      <layout></layout>
    </div>
  </body>
  @yield('page_js')
  <style>
    .el-header {
      background-color: #b3c0d1;
      color: #333;
      line-height: 60px;
    }

    .el-aside {
      color: #333;
    }
  </style>
  <script src="{{ mix('js/app.js') }}"></script>
</html>
