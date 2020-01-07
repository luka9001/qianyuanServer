<template>
  <div>
    <el-container style=" border: 1px solid #eee" :gutter="10">
      <el-aside width="200px" style="background-color: rgb(238, 241, 246)">
        <el-menu :default-openeds="['1']">
          <el-submenu index="1">
            <template slot="title"
              ><i class="el-icon-message"></i>用户审核</template
            >
            <el-menu-item-group>
              <el-menu-item index="1-1" @onClick="detail">待审核</el-menu-item>
              <el-menu-item index="1-2">已审核</el-menu-item>
              <el-menu-item index="1-2">重新上传资料</el-menu-item>
            </el-menu-item-group>
          </el-submenu>
        </el-menu>
      </el-aside>

      <el-container>
        <el-header style="text-align: right; font-size: 12px"> </el-header>
        <el-main>
          <el-table :data="tableData">
            <el-table-column prop="date" label="日期" width="140">
            </el-table-column>
            <el-table-column prop="name" label="姓名" width="120">
            </el-table-column>
            <el-table-column prop="address" label="地址"> </el-table-column>
          </el-table>
        </el-main>
      </el-container>
    </el-container>
  </div>
</template>

<script>
export default {
  mounted() {
    console.log("Component mounted.");
  },
  methods: {
    detail() {
      alert("123123");
    }
  }
};
</script>

<!-- <script>
export default {
  data() {
    const item = {
      date: "2016-05-02",
      name: "王小虎",
      address: "上海市普陀区金沙江路 1518 弄"
    };
    return {
      tableData: Array(20).fill(item)
    };
  },
  mounted() {
    console.log("Component mounted.");
  },
  methods: {
    detail() {
      alert("123123");
    }
  }
};
</script> -->
