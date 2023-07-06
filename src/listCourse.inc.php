<?php
/**
 * file: listCourse.inc.php 课程列表显示脚本，可根据教师、学生不同的身份显示不同列表
 */

session_start(); /* 开启Session会话 */

if($_SESSION['users_profile']['role'] == '学生'){
		include_once("conn.inc.php"); /* 包含数据库连接文件 */
		try{
				/* 以学生表students中唯一的id作为过滤条件从选课结果表courses_select中提取学生已选课程的相关数据 */
				$query = "SELECT id, courses_id, teachers_id FROM courses_select WHERE students_id= {$_SESSION['user']['id']}";
				$stmt = $pdo->prepare($query);
				$stmt->execute(); /* 执行所准备的语句 */
				/* 如果从courses_select表中提取到了数据，则表示有该学生的选课记录，显示该学生所选择的所有课程 */
				if($stmt->rowCount() > 0){
						/* 制作显示学生选择的所有课程的表格 */
						echo '<table border="1" align="left" width=90%>';
						echo '<h2>已选课程</h2>';
						echo '<tr bgcolor="#cccccc">';
						echo '<th>课程名</th><th>课程封面</th><th>任课老师</th><th>课程简介</th><th>当前章节数</th><th>操作</th></tr>';

						$allRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
						/* 遍历结果集，输出学生已选课程的相关信息 */
						foreach($allRows as $row){
								/* 以课程表courses唯一的id作为过滤条件从课程表中提取课程的相关信息 */
								$sql = "SELECT title, cover, type, summary, chapters_count FROM courses WHERE id = {$row['courses_id']}";
								$courses_result = $pdo->prepare($sql); $courses_result->execute();
								$courses_rows = $courses_result->fetch(PDO::FETCH_ASSOC); /* 获得结果集关联数组courses_rows */

								/* 以教师表teachers唯一的id作为过滤条件从教师表中获取该教师在个人详细信息表users_profile中对应唯一的users_profile_id */
								$sql = "SELECT users_profile_id FROM teachers WHERE id = {$row['teachers_id']}";
								$teachers_result = $pdo->prepare($sql); $teachers_result->execute();
								$teachers_rows = $teachers_result->fetch(PDO::FETCH_ASSOC); /* 获得结果集关联数组teachers_rows */

								/* 以users_profile_id作为过滤条件从个人详细信息表users_profile中获取教师的姓名 */
								$sql = "SELECT name FROM users_profile WHERE id = {$teachers_rows['users_profile_id']}";
								$users_profile_result = $pdo->prepare($sql); $users_profile_result->execute();
								$users_profile_rows = $users_profile_result->fetch(PDO::FETCH_ASSOC); /* 获取结果集关联数组users_profile_rows */

								/* 输出学生已选的所有课程的信息 */
								echo '<tr><td>'.$courses_rows['title'].'</td><td>'.'<img height="100" src="'.$courses_rows['cover'].'"></td>';
								echo '<td>'.$users_profile_rows['name'].'</td><td>'.$courses_rows['summary'].'</td><td>'.$courses_rows['chapters_count'].'</td>';

								/* 为学生提供进入课堂的链接，并在url中携带该课程的唯一id */
								echo '<td><a href="ChapterPage.php?action=enterCourse&id='.$row['courses_id'].'">进入课堂</a>/';
								echo '<a onclick="return confirm(\'确定要退出课程: '.$courses_rows['title'].'？\')" href="StudentCoursePage.php?action=dropCourse&id='.$row['courses_id'].'">退课</a></td></tr>';
								//echo '<td>进入课堂/退课</td></tr>'; /* 提供进入课堂和退课的链接，以后再进行完善 */
						}
						echo '</table>'; /* 输出表格结束标记 */
				}
		}catch(PDOException $e){ /* 异常处理 */
				echo '<br/>Database selection failed: '.$e->getMessage();
				exit;
		}
}else if($_SESSION['users_profile']['role'] == '教师'){
		include_once("conn.inc.php"); /* 包含数据库连接文件 */
		try{
				$query = "SELECT id,title,cover,summary,status,chapters_count,students_count,created_at FROM courses WHERE teachers_id = {$_SESSION['user']['id']}";
				$stmt = $pdo->prepare($query);
				$stmt->execute();
				/* 如果从courses数据表中提取到了数据，则表示教师创建过课程，显示教师创建的所有课程的相关信息 */
				if($stmt->rowCount() > 0){
						/* 制作显示课程信息的表格 */
						echo '<table border="1" align="left" width=90%>';
						echo '<h2>课程表</h2>';
						echo '<tr bgcolor="#cccccc">';
						echo '<th>课程名</th><th>课程封面</th><th>课程简介</th><th>课程状态</th><th>当前章节数</th><th>创建时间</th><th>操作</th></tr>';

						$allRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
						/* 遍历结果集，输出课程的相关信息 */
						foreach($allRows as $row){
								/* 输出从数据库中教师创建的所有课程的相关信息 */
								echo "<tr><td>".$row['title']."</td>";
								echo "<td><img height='100' src='{$row['cover']}'></td>";
								echo '<td>'.$row['summary'].'</td><td>'.$row['status'].'</td>';
								echo '<td>'.$row['chapters_count'].'</td><td>'.$row['created_at'].'</td>';

								/* 如果课程尚未发布，提供发布课程操作链接，若选择发布课程，则进入课程管理页面ManageCoursePage执行课程发布流程 */
								if($row['status'] == '未开始'){ echo '<td><a href="ManageCoursePage.php?action=publishCourse&id='.$row['id'].'">发布</a>/'; }
								else{ /* 课程已发布，则显示课程状态，并提供教师开始授课链接 */
										echo '<td>已发布/';
										
										/* 如果教师点击进入链接，进入CoursePage.php脚本执行 */
										echo '<a href="ChapterPage.php?action=enterCourse&id='.$row['id'].'">进入课堂</a>/';
								}

								/* 提供删除课程的操作链接，若选择删除课程，则弹出窗口提示用户是否确认删除，若确认则进入课程管理页面ManageCoursePage执行课程删除流程 */
								echo '<a onclick="return confirm(\'确定要删除课程: '.$row['title'].'？\')" href="ManageCoursePage.php?action=deleteCourse&id='.$row['id'].'">删除</a>';
								echo '</td></tr>';
						}
						echo '</table>'; /* 输出表格结束标记 */
				}else { echo '尚未创建任何课程'; }
		}catch(PDOException $e){ /* 异常处理 */
				echo '<br/>Database selection failed: '.$e->getMessage();
				exit;
		}
}
?>
