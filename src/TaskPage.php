<?php
/**
 * file: TaskPage.php 当学生或教师点击进入小节时，会执行该脚本，并根据用户的不同身份提供不同的操作链接
 */
session_start(); /* 开启Session会话 */
include_once("conn.inc.php"); /* 包含数据库连接文件 */

/* 提供教师可进行的操作列表: 授课、签到——发布签到/签到记录、作业——发布作业/批阅作业、课件——上传课件/查看课件 */
if($_SESSION['users_profile']['role'] == '教师'){
		echo '<b>授课</b><br>';
		echo '<b>课件</b><br>';
		echo '<a href="TaskPage.php?action=uploadResource&tasks_id='.$_GET['tasks_id'].'&chapters_id='.$_GET['chapters_id'].'&courses_id='.$_GET['courses_id'].'">上传课件<br></a>';
		echo '<a href="TaskPage.php?action=viewResource&tasks_id='.$_GET['tasks_id'].'&chapters_id='.$_GET['chapters_id'].'&courses_id='.$_GET['courses_id'].'">查看课件<br></a>';
}

/* 提供学生可进行的操作列表: 查看作业、查看课件 */
if($_SESSION['users_profile']['role'] == '学生'){
		echo '<b>课件</b><br>';
		echo '<a href="TaskPage.php?action=viewResource&tasks_id='.$_GET['tasks_id'].'&chapters_id='.$_GET['chapters_id'].'&courses_id='.$_GET['courses_id'].'">查看课件<br></a>';
}

/* 如果教师选择上传课件，则执行上传课件的流程 */
if($_GET['action'] == "uploadResource"){
		/* 将课程id，章节id，小节id注册到$_SESSION数组中，从而实现页面传值 */
		/* upload.php脚本会用到$_SESSION中注册的值，用于进行后台合并文件并将文件信息写入数据库 */
		$_SESSION['courses_id'] = $_GET['courses_id'];
		$_SESSION['chapters_id'] = $_GET['chapters_id'];
		$_SESSION['tasks_id'] = $_GET['tasks_id'];
		include("upload.html"); /* 导入前端进行文件分片上传的页面 */
}

/* 如果教师选择删除课件，则执行删除课件的流程 */
else if($_GET['action'] == "deleteResource"){
		try{
				/* 删除数据库中存放该文件信息的数据项 */
				$sql = "DELETE FROM courses_resources WHERE id={$_GET['id']}";
				$stmt = $pdo->prepare($sql); $stmt->execute();

				/* 删除服务器中所保存的对应文件资源 */
				$fileName = end(explode('/', $_GET['fileURL'])); /* 切割字符串，获取对应文件在服务器中所的随机文件名 */
				$extension = end(explode('.', $fileName)); /* 获取文件扩展名，用于判断文件存放在服务器的哪个目录下 */
				/* 根据文件扩展名类型设置要删除的文件的路径 */
				switch($extension){
				case 'pdf': $filePath = './uploads/pdfs/'.$fileName; break;
				case 'ppt':
				case 'pptx': $filePath = './uploads/ppts/'.$fileName; break;
				case 'doc':
				case 'docx': $filePath = './uploads/docs/'.$fileName; break;
				default: $filePath = './uploads/videos/'.$fileName; break;
				}

				/* 若数据库中对应的数据项删除成功，且服务器目录中保存的文件资源移除成功，则文件彻底删除成功 */
				if($stmt->rowCount() > 0 && unlink($filePath)){
						echo '<script language="JavaScript">;alert("课件资源删除成功！");location.href="TaskPage.php?action=viewResource&tasks_id='.$_GET['tasks_id'].'&chapters_id='.$_GET['chapters_id'].'&courses_id='.$_GET['courses_id'].'"</script>';
				}else{
						echo '<script language="JavaScript">;alert("课件资源删除失败！");location.href="TaskPage.php?action=viewResource&tasks_id='.$_GET['tasks_id'].'&chapters_id='.$_GET['chapters_id'].'&courses_id='.$_GET['courses_id'].'"</script>';
				}
		}catch(PDOException $e){
				echo '<br>Database deletion failed in TaskPage.php?action=deleteResource: '.$e->getMessage().'<br>';
				exit;
		}
}

/* 如果学生或教师选择查看课件，则显示已上传的课件列表 */
else if($_GET['action'] == "viewResource"){
		try{
				$sql = "SELECT id, title, media_url, size, created_at FROM courses_resources WHERE teachers_id=? AND courses_id=? AND chapters_id=? AND tasks_id=?";
				$stmt = $pdo->prepare($sql);
				$stmt->bindParam(1, $_SESSION['user']['id']); $stmt->bindParam(2, $_GET['courses_id']); $stmt->bindParam(3, $_GET['chapters_id']); $stmt->bindParam(4, $_GET['tasks_id']);
				$stmt->execute();
				if($stmt->rowCount() > 0){
						/* 制作显示课件资源的表格 */
						echo '<table border="1" align="left" width=90%>';
						echo '<h2>课件资源</h2>';
						echo '<tr bgcolor="#cccccc">';
						echo '<th>文件名</th><th>操作</th><th>文件大小</th><th>创建时间</th></tr>';
						$allRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
						/* 遍历结果集，输出课件资源列表 */
						foreach($allRows as $row){
								echo '<tr><td>'.$row['title'].'</td><td><a href="'.$row['media_url'].'">下载/查看</a>';
								if($_SESSION['users_profile']['role'] == '教师'){ /* 如果是教师用户，额外增加一个用于删除课件资源的链接 */
										echo '||<a onclick="return confirm(\'确定要删除课件资源: '.$row['title'].'?\')" href="TaskPage.php?action=deleteResource&id='.$row['id'].'&tasks_id='.$_GET['tasks_id'].'&chapters_id='.$_GET['chapters_id'].'&courses_id='.$_GET['courses_id'].'&fileURL='.$row['media_url'].'">删除</a></td>';
								}else{ echo '</td>'; }
								echo '<td>'.$row['size'].'</td><td>'.$row['created_at'].'</td></tr>';
						}
						echo '</table>'; /* 输出表格结束标记 */
				}else{ /* 若从数据库中提取不到数据，则表示教师还未上传任何资源 */
						echo '尚未上传任何课件资源！<br>';
				}
		}catch(PDOException $e){ /* 异常处理 */
				echo '<br>Database selection failed in TaskPage.php?action=viewResource: '.$e->getMessage().'<br>';
				exit;
		}
}

?>
