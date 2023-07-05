<?php
/**
 * file: signPublish.inc.php 提供教师发布签到的表单页面
 */

echo '<html><body>';
echo '<form action="TaskPage.php?action=publishSign" method="post" enctype="multipart/form-data">';
echo '<label>迟到时间设置: </label><select name="time_late"><option>5</option><option>10</option></select><label>分钟后</label><br>';
echo '<label>缺课时间设置: </label><select name="time_end"><option>10</option><option>15</option></select><label>分钟后</label><br>';
echo '<input type="submit" name="sub" value="确认发布">';
echo '</body></html>';
?>
