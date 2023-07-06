<?php
/**
 * file: searchCourse.inc.php 搜索课程所要使用的文件，提供课程搜索框
 */
?>

<html>
<body>
<form action="StudentCoursePage.php?action=selectCourse" method="get" enctype="multipart/form-data">
<label for="search">查询课程:    </label>
<input type="search" name="invite_code" placeholder="请输入邀请码" />
<input type="submit" name="sub" value="搜索" />
</form>
</body>
</html>
