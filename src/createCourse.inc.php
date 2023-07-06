<?php
/**
 * file: createCourse.inc.php 创建课程所要使用的表单文件
 */
?>

<html>
<body>
<form action="ManageCoursePage.php?action=createCourse" method="post" enctype="multipart/form-data">
*课程名: <input type="text" name="course_title"><br>
课程封面: <input type="file" name="course_cover"><br>
课程简介: <input type="text" name="course_summary"><br>
<aside><p>注: *号为必填项</p></aside>
<input type="submit" name="sub" value="确认创建">
</form>
</body>
</html>
