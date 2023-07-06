<?php
/**
 * file: ChapterPage.php 当学生或教师点击进入课堂时，会执行该脚本，显示相关的某一个特定课程的章节列表，并且教师可进行章节的创建和发布
 */

session_start(); /* 开启Session会话 */
include_once("conn.inc.php"); /* 导入数据库连接文件 */

/* 如果是教师点击进入课堂，则显示章节列表，并提供教师创建章节的链接 */
if($_GET['action'] == 'enterCourse' && $_SESSION['users_profile']['role'] == '教师'){
		include_once("func.inc.php"); /* 导入自定义的函数库文件用于显示课程封面和课程名 */
		showCourse($pdo, $_GET['id']); /* 根据url传递的课程id确定课程名和课程封面 */

		echo '<a href="ChapterPage.php?action=createChapter&courses_id='.$_GET['id'].'"><h3>新建章节</h3></a>'; /* 提供教师新建章节的链接 */
		echo '<a href="CoursePage.php?action=viewClass&courses_id='.$_GET['id'].'" target="_blank"><h3>查看班级</h3></a>'; /* 提供教师创建班级的链接 */
		echo '<a href="AssignmentPage.php?action=editAssignment&courses_id='.$_GET['id'].'" target="_blank"><h3>课程作业</h3></a>'; /* 提供教师查看作业的连接 */

		try{
				$sql = "SELECT * FROM chapters WHERE courses_id={$_GET['id']}"; /* 根据GET数组中的课程id，即courses_id从chapters章节表中提取信息 */
				$result = $pdo->prepare($sql); $result->execute();
				if($result->rowCount() > 0){ /* 如果提取到数据，表示该课程已创建了相关的章节 */
						$allRows1 = $result->fetchAll(PDO::FETCH_ASSOC);
						foreach($allRows1 as $row1){ /* 遍历结果集 */
								/* 制作章节列表的表格 */
								echo '<table border="1" align="left" width="90%">';
								echo '<tr bgcolor="#cccccc">';
								echo '<th>序列号</th><th>章节名</th><th>当前小节数</th><th>状态</th><th>操作</th></tr></br>';

								/* 显示表格数据 */
								echo "<td>第".$row1['seq']."章</td>";
								echo "<td>".$row1['title']."</td>";

								/* 若数据项tasks_count为0，则表示0小节 */
								$tasks_count = (!$row1['tasks_count'] ? 0 : $row1['tasks_count']);
								echo "<td>".$tasks_count."小节</td>";

								echo "<td>".$row1['status']."</td>";

								if($row1['status'] == "未开始"){
										/* 若章节尚未发布，则提供发布链接，并且携带章节id和课程id作为参数 */
										echo '<td><a href="ChapterPage.php?action=publishChapter&chapters_id='.$row1['id'].'&courses_id='.$row1['courses_id'].'">发布</a></td></tr>';
								}else{
										/* 若已发布则在状态栏显示已发布，并提供新建小节的链接，在url中携带参数课程id和章节id */
										echo '<td><a href="ChapterPage.php?action=createTask&chapters_id='.$row1['id'].'&courses_id='.$row1['courses_id'].'">新建小节</a></td></tr>';
										/* 显示小节列表 */
										try{
												$sql = "SELECT seq FROM chapters WHERE id={$row1['id']}";
												$stmt = $pdo->prepare($sql); $stmt->execute(); $seq = $stmt->fetch(PDO::FETCH_ASSOC); /* 此时$seq为关联数组，$seq['seq']表示当前章节数 */
												
												/* 根据GET数组中的课程id和章节id，从tasks小节表中提取信息 */
												$sql = "SELECT * FROM tasks WHERE chapters_id={$row1['id']} AND courses_id={$_GET['id']}";
												$result = $pdo->prepare($sql); $result->execute();
												if($result->rowCount() > 0){
														echo '<br><table border="1" align="left" width="60%">';
														echo '<tr bgcolor="#cccccc">';
														echo '<th>序列号</th><th>小节名</th><th>状态</th><th>操作</th></tr></br>';
														$allRows2 = $result->fetchAll(PDO::FETCH_ASSOC);
														foreach($allRows2 as $row2){
																echo "<tr><td>".$seq['seq'].'.'.$row2['seq']."小节</td>";
																echo "<td>".$row2['title']."</td>";

																echo "<td>".$row2['status']."</td>";
																if($row2['status'] == "未开始"){
																		echo '<td><a href="ChapterPage.php?action=publishTask&tasks_id='.$row2['id'].'&chapters_id='.$row1['id'].'&courses_id='.$row1['courses_id'].'">发布</a></td></tr>';
																}else{
																		/* 若已发布，则在状态栏显示已发布，并提供查看课程资源的链接，在url中携带参数，小节id，章节id，课程id */
																		echo '<td><a href="TaskPage.php?tasks_id='.$row2['id'].'&chapters_id='.$row1['id'].'&courses_id='.$row1['courses_id'].'" target="_blank">进入</a></td></tr>';
								}
						}
												}//else{ echo "尚未创建任何小节！"; } /* 提示用户当前小节列表为空 */
										}catch(PDOException $e){
												echo '<br>Database selection failed in TaskPage.php?action=enterTask '.$e->getMessage().'</br>';
												exit;
										}
								}
						}
				}else{ echo "尚未创建任何章节！"; } /* 提示用户当前章节列表为空 */
		}catch(PDOException $e){ /* 异常处理 */
				echo '<br>Database selection failed in ChapterPage.php?action=enterChapter'.$e->getMessage().'</br>';
				exit;
		}
}

/* 如果教师选择新建章节，则执行章节创建流程 */
if($_GET['action'] == "createChapter"){
		/* 提供创建章节的表单，并且携带的参数为课程id */
		echo '<form action="ChapterPage.php?action=createChapter&courses_id='.$_GET['courses_id'].'" method="post" enctype="multipart/form-data">';
		echo '<label>章节名: </label><input type="text" name="chapter_title"><br>';
		echo '<input type="submit" name="sub" value="确认创建">';
		/* 若教师提交，并且章节名不为空，则将数据写入数据库 */
		if(isset($_POST['sub']) && $_POST['sub'] && isset($_POST['chapter_title']) && $_POST['chapter_title']){
				try{
						$sql = "INSERT INTO chapters(courses_id, teachers_id, title, created_at, updated_at) VALUES(?,?,?,?,?)";
						$stmt1 = $pdo->prepare($sql);
						/* 绑定参数，courses_id为url中携带的参数 */
						$stmt1->bindParam(1, $_GET['courses_id']); $stmt1->bindParam(2, $_SESSION['user']['id']); $stmt1->bindParam(3, $_POST['chapter_title']);
						/* 获取当前系统时间并将其转化为mysql的timestamp格式 */
						$mysqltime1 = date('Y-m-d H:i:s', strtotime("now"));
						$stmt1->bindParam(4, $mysqltime1); $stmt1->bindParam(5, $mysqltime1); $stmt1->execute();

						/* 从chapters表中选择相关章节的最大序列号 */
						$sql = "SELECT MAX(seq) AS max_seq FROM chapters WHERE courses_id={$_GET['courses_id']} AND teachers_id={$_SESSION['user']['id']}";
						$stmt2 = $pdo->prepare($sql); $stmt2->execute();
						$row = $stmt2->fetch(PDO::FETCH_ASSOC); /* 此时row为关联数组，其下标为max_seq，即最大章节序列号 */
						$sql = "UPDATE chapters SET seq=?, updated_at=? WHERE created_at=?";
						$stmt3 = $pdo->prepare($sql);
						/* 更新数据表，将刚插入数据表中的数据项的章节改为该课程中已有的最大章节数+1 */
						$max_seq = $row['max_seq']+1; $stmt3->bindParam(1, $max_seq);
						$mysqltime2 = date('Y-m-d H:i:s', strtotime("now")); $stmt3->bindParam(2, $mysqltime2);

						/* 以时间戳为筛选条件修改刚刚插入chapters章节表中的数据项 */
						$stmt3->bindParam(3, $mysqltime1);
						$stmt3->execute();

						/* 更新courses课程表中的章节数chapters_count和更新时间updated_at */
						$sql = "UPDATE courses SET chapters_count=?, updated_at=? WHERE id=?";
						$stmt4 = $pdo->prepare($sql); $stmt4->bindParam(1, $max_seq); $stmt4->bindParam(2, $mysqltime2); $stmt4->bindParam(3, $_GET['courses_id']);
						$stmt4->execute();


						if($stmt3->rowCount() > 0 && $stmt4->rowCount() > 0){ /* 若对表中数据有影响，则表示chapters_count表中章节序列号和courses表中当前章节数修改成功 */
								echo '<script language="JavaScript">;alert("章节创建成功！");location.href="ChapterPage.php?action=enterCourse&id='.$_GET['courses_id'].'"</script>';
						}
						/* 若无影响，则表示章节数修改失败，需要寻找原因 */
						else{ echo '<script>alert("章节创建失败！")</script>'; }

				}catch(PDOException $e){ /* 异常处理 */
						echo '<br>Database insertion failed in ChapterPage.php?action=createChapter '.$e->getMessage().'</br>';
						exit;
				}
		}
		/* 不允许教师用户提交空的章节名 */
		else if(isset($_POST['chapter_title']) && !$_POST['chapter_title']) { echo "<script>alert('章节名不能为空！')</script>"; }
}

/* 如果教师选择发布章节，则执行章节发布流程 */
if($_GET['action'] == "publishChapter"){
		try{
				/* 根据url中所传递的参数，即章节chapters_id来确定发布哪一个章节 */
				$sql = "UPDATE chapters SET status='已开始', updated_at=? WHERE id = {$_GET['chapters_id']}";
				$stmt = $pdo->prepare($sql);
				$mysqltime = date('Y-m-d H:i:s', strtotime("now")); $stmt->bindParam(1, $mysqltime); $stmt->execute();
				/* 如果对chapters章节表中数据项有影响，则表示章节发布成功，且修改了章节的状态 */
				/* 再将页面跳转到ChapterPage.php?action=enterCourse，并携带课程id作为参数 */
				if($stmt->rowCount() > 0) { echo '<script language="JavaScript">;alert("发布成功！");location.href="ChapterPage.php?action=enterCourse&id='.$_GET['courses_id'].'"</script>'; }
				/* 若对数据项无影响，则表示发布失败，停留在当前页面 */
				else { echo "<script>alert('发布失败！')</script>"; }
		}catch(PDOException $e){ /* 异常处理 */
				echo '<br>Database updation failed in ChapterPage.php?action=publishChapter '.$e->getMessage().'</br>';
				exit;
		}
}

/* 如果教师选择新建小节，则执行小节创建流程 */
if($_GET['action'] == "createTask"){
		/* 提供创建小节的表单，并且携带的参数为课程id和章节id */
		echo '<form action="ChapterPage.php?action=createTask&chapters_id='.$_GET['chapters_id'].'&courses_id='.$_GET['courses_id'].'" method="post" enctype="multipart/form-data">';
		echo '<label>小节名: </label><input type="text" name="task_title"><br>';
		echo '<input type="submit" name="sub" value="确认创建">';
		/* 若教师提交，并且小节名不为空，则将数据写入数据库 */
		if(isset($_POST['sub']) && $_POST['sub'] && isset($_POST['task_title']) && $_POST['task_title']){
				try{
						$sql = "INSERT INTO tasks(courses_id, chapters_id, teachers_id, title, created_at, updated_at) VALUES(?,?,?,?,?,?)";
						$stmt1 = $pdo->prepare($sql);
						/* 绑定参数，使用url中携带的课程id和章节id */
						$stmt1->bindParam(1, $_GET['courses_id']); $stmt1->bindParam(2, $_GET['chapters_id']); $stmt1->bindParam(3, $_SESSION['user']['id']);
						$stmt1->bindParam(4, $_POST['task_title']);
						/* 获取当前系统时间并将其转化为mysql的timestamp格式 */
						$mysqltime1 = date('Y-m-d H:i:s', strtotime("now")); $stmt1->bindParam(5, $mysqltime1); $stmt1->bindParam(6, $mysqltime1); $stmt1->execute();

						/* 从tasks表中选择相关小节的最大序列号 */
						$sql = "SELECT Max(seq) AS max_seq FROM tasks WHERE courses_id={$_GET['courses_id']} AND chapters_id={$_GET['chapters_id']}";
						$stmt2 = $pdo->prepare($sql); $stmt2->execute();
						$row = $stmt2->fetch(PDO::FETCH_ASSOC); /* 此时row为关联数组，其下标为max_seq，即最大小节序列号 */

						$sql = "UPDATE tasks SET seq=?, updated_at=? WHERE created_at=?"; $stmt3 = $pdo->prepare($sql);
						/* 更新tasks数据表，将刚插入数据表中的数据项的小节改为该章节已有的最大小节数+1 */
						$max_seq = $row['max_seq']+1; $stmt3->bindParam(1, $max_seq); $mysqltime2 = date('Y-m-d H:i:s', strtotime("now")); $stmt3->bindParam(2, $mysqltime2);

						/* 以时间戳为筛选条件修改刚刚插入tasks小节表中的数据项 */
						$stmt3->bindParam(3, $mysqltime1); $stmt3->execute();

						/* 因为新建了小节，所以章节中的小节数增加了，需要更新章节表chapters中的tasks_count数据项 */
						$sql = "SELECT tasks_count FROM chapters WHERE id={$_GET['chapters_id']}";
						$stmt4 = $pdo->prepare($sql); $stmt4->execute();
						$row4 = $stmt4->fetch(PDO::FETCH_ASSOC); /* 先提取出当前章节表中的小节数 */
						$tasks_count = (!$row4['tasks_count'] ? 1 : $row4['tasks_count']+1); /* 更新小节数，若是0，则为1，否则增1 */

						/* 更新章节表chapters */
						$sql = "UPDATE chapters SET tasks_count=?, updated_at=? WHERE id=?";
						$stmt5 = $pdo->prepare($sql); $stmt5->bindParam(1, $tasks_count); $stmt5->bindParam(2, $mysqltime2); $stmt5->bindParam(3, $_GET['chapters_id']);
						$stmt5->execute();

						if($stmt3->rowCount() > 0 && $stmt5->rowCount() > 0){
								echo '<script language="JavaScript">;alert("小节创建成功！");location.href="ChapterPage.php?action=enterCourse&id='.$_GET['courses_id'].'"</script>';
								//echo '<script language="JavaScript">;alert("小节创建成功！")</script>';
						}
						/* 若无影响，则表示小节数修改失败，需要寻找原因 */
						else{ echo '<script>alert("小节创建失败！")</script>'; }
				}catch(PDOException $e){ /* 异常处理 */
						echo '<br>Database insertion failed in TaskPage.php?action=createTask '.$e->getMessage().'</br>';
				}
		}/* 不允许教师用户提交空的小节名 */
		else if(isset($_POST['task_title']) && !$_POST['task_title']){ echo "<script>alert('小节名不能为空！')</script>"; }
}

/* 如果教师选择发布小节，则执行小节发布流程 */
if($_GET['action'] == "publishTask"){
		try{
				/* 根据url中传递的参数，即小节tasks_id和来确定发布哪一个小节*/
				$sql = "UPDATE tasks SET status='已开始', updated_at=? WHERE id={$_GET['tasks_id']}";
				$update = $pdo->prepare($sql);
				$mysqltime = date('Y-m-d H:i:s', strtotime("now")); $update->bindParam(1, $mysqltime); $update->execute();
				/* 如果对tasks小节表中数据项有影响，则表示小节发布成功，且修改了小节状态 */
				/* 再将页面跳转到ChapterPage.php?action=enterTask，并携带课程id和章节id作为参数 */
				if($update->rowCount() > 0){
						echo '<script language="JavaScript">;alert("发布成功");location.href="ChapterPage.php?action=enterCourse&id='.$_GET['courses_id'].'";</script>';
				}
				/* 若对数据项无影响，则表示发布失败，停留在当前页面 */
				else { echo "<script>alert('发布失败！')</script>"; }

		}catch(PDOException $e){
				echo '<br>Database updation failed in ChapterPage.php?action=publishTask '.$e->getMessage().'</br>';
				exit;
		}
}

/* 如果是学生点击进入课堂，则显示章节列表 */
if($_GET['action'] == 'enterCourse' && $_SESSION['users_profile']['role'] == '学生'){
		include_once("func.inc.php"); /* 导入自定义的函数库文件用于显示课程封面和课程名 */
		showCourse($pdo, $_GET['id']); /* 根据url传递的课程id确定课程名和课程封面 */
		
		echo '<a href="StudentAssignmentPage.php?action=myAssignment&courses_id='.$_GET['id'].'" target="_blank"><h3>我的作业</h3></a>'; /* 提供学生查看作业的连接 */

		try{
				$sql = "SELECT * FROM chapters WHERE courses_id={$_GET['id']}"; /* 根据GET数组中的课程id，即courses_id从chapters章节表中提取信息 */
				$result = $pdo->prepare($sql); $result->execute();
				if($result->rowCount() > 0){ /* 如果提取到数据，表示该课程已创建了相关的章节 */
						$allRows1 = $result->fetchAll(PDO::FETCH_ASSOC);
						foreach($allRows1 as $row1){ /* 遍历结果集 */
								/* 制作章节列表的表格 */
								echo '<br><table border="1" align="left" width="90%">';
								echo '<tr bgcolor="#cccccc">';
								echo '<th>序列号</th><th>章节名</th><th>当前小节数</th><th>状态</th></tr></br>';

								/* 显示表格数据 */
								echo "<tr><td>第".$row1['seq']."章</td>";
								echo "<td>".$row1['title']."</td>";

								/* 若数据项tasks_count为0，则表示0小节 */
								$tasks_count = (!$row1['tasks_count'] ? 0 : $row1['tasks_count']);
								echo "<td>".$tasks_count."小节</td>";

								echo "<td>".$row1['status']."</td>";
								
								/* 显示小节列表 */
								try{
										$sql = "SELECT seq FROM chapters WHERE id={$row1['id']}";
										$stmt = $pdo->prepare($sql); $stmt->execute(); $seq = $stmt->fetch(PDO::FETCH_ASSOC); /* 此时$seq为关联数组，$seq['seq']表示当前章节数 */
												
										$sql = "SELECT * FROM tasks WHERE chapters_id={$row1['id']} AND courses_id={$_GET['id']}"; /* 根据GET数组中的课程id和章节id，从tasks小节表中提取信息 */
										$result = $pdo->prepare($sql); $result->execute();
										if($result->rowCount() > 0){
												echo '<br><table border="1" align="left" width="60%">';
												echo '<tr bgcolor="#cccccc">';
												echo '<th>序列号</th><th>小节名</th><th>状态</th><th>操作</th></tr></br>';
												$allRows2 = $result->fetchAll(PDO::FETCH_ASSOC);
												foreach($allRows2 as $row2){
														echo "<tr><td>".$seq['seq'].'.'.$row2['seq']."小节</td>";
														echo "<td>".$row2['title']."</td>";

														echo "<td>".$row2['status']."</td>";
														if($row2['status'] == "未开始"){ /* 小节未发布，则不提供任何操作 */
																echo '<td></td>';
														}else{
																/* 若已发布，则在状态栏显示已发布，并提供进入小节的链接，在url中携带参数，小节id，章节id，课程id */
																echo '<td><a href="TaskPage.php?tasks_id='.$row2['id'].'&chapters_id='.$row1['id'].'&courses_id='.$row1['courses_id'].'" target="_blank">进入</a></td>';
														}
												}
										}//else{ echo "<br>尚未创建任何小节！</br>"; } /* 提示用户当前小节列表为空 */
								}catch(PDOException $e){ echo '<br>Database selection failed in ChapterPage.php?action=enterChapter '.$e->getMessage().'</br>'; exit; }
						}
				}else{ echo "尚未创建任何章节！"; } /* 提示用户当前章节列表为空 */
		}catch(PDOException $e){ echo '<br>Database selection failed in ChapterPage.php?action=enterChapter'.$e->getMessage().'</br>'; exit; }
}

?>
