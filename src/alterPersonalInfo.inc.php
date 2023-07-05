<?php
/**
 * file: alterPersonalInfo.inc.php 用户可以进行个人信息修改，提供修改个人信息的表单
 */

echo '<html><body>';
echo '<form action="PersonalHomePage.php?action=alter" method="post" enctype="multipart/form-data">';
echo "<label>头像: </label><input type='file' name='avatar' /><br>";
echo "<label>性别: </label><input type='text' name='gender' placeholder='{$_SESSION['users_profile']['gender']}' /><br>";
echo "<label>签名: </label><input type='text' name='signature' placeholder='{$_SESSION['users_profile']['signature']}' /><br>";
echo "<label>密码: </label><input type='password' name='password' /><br>";
echo "<label>邮箱: </label><input type='text' name='email' placeholder='{$_SESSION['user']['email']}' /><br>";
echo "<label>手机号: </label><input type='text' name='phone' placeholder='{$_SESSION['user']['phone']}' /><br>";
echo "<input type='submit' name='sub' value='确认修改'/><br>";
echo "</body></html>";
?>

