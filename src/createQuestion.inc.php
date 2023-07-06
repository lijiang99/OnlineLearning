<?php
/**
 * file: createQuestion.inc.php 教师新建选择题所要用到的表单文件
 */
session_start();
?>
<html>
<body>
<form action="TaskPage.php?action=createQuestion" method="post" enctype="multipart/form-data">
<label>题目设置: </label><input type="text" name="title"><br>
<label>选项A: </label><input type="text" name="option_A"><br>
<label>选项B: </label><input type="text" name="option_B"><br>
<label>选项C: </label><input type="text" name="option_C"><br>
<label>选项D: </label><input type="text" name="option_D"><br>
<label>答案设置: <select name="answers"><option>A</option><option>B</option><option>C</option><option>D</option></select><br>
<label>解析设置: </label><input type="text" name="explain"><br>
<label>分数设置: </label><input onkeyup="this.value=this.value.replace(/\D/g,'')" onafterpaste="this.value=this.value.replace(/\D/g, '')" name="scores"><br>
<input type="submit" name="sub" value="确认新建">
</body>
</html>


