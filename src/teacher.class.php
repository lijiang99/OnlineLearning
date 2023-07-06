<?php
/**
 * file: teacher.class.php
 * 本类的实例对象为教师可进行课程创建与发布，课程资源上传，签到发布，作业发布，作业批阅，修改个人详细信息
 */

include_once("course.class.php"); /* 导入课程类文件 */
include_once("conn.inc.php"); /* 导入数据库连接文件 */

class Teacher{
		/* 教师的用户信息 */
		private $id, $users_profile_id;
		private $course; /* Course课程类对象 */

		/******************************************************************************************/
		/**
		 * 构造函数用于实例化一个教师对象
		 * @param int    $id                  教师表中的唯一id
		 * @param int    $users_profile_id    用户个人详细信息表中的唯一id
		 */
		function __construct($id, $users_profile_id){
				$this->id = $id; $this->users_profile_id = $users_profile_id;
		}
		/******************************************************************************************/





		/******************************************************************************************/
		/**
		 * __set魔术方法，设置教师类的成员属性值
		 * @param string    $properName     成员属性名
		 * @param mixed     $properValue    成员属性值
		 */
		function __set($properName, $properValue){
				/* 根据参数决定为哪个属性赋值，传入不同的成员属性名，赋予传入相应的值 */
				$this->$properName = $properValue;
		}
		/******************************************************************************************/





		/******************************************************************************************/
		/**
		 * __get魔术方法，在直接获取属性值时会自动调用，以属性名作为参数传入并处理
		 * @param string    $properName    成员属性名
		 * @param mixed                    返回属性值
		 */
		function __get($properName) { return $this->$properName; }
		/******************************************************************************************/





		/************************************************************************************************************************/
		/**
		 * 调用该方法可以创建课程对象，通过关键字clone实现对象的克隆复制
		 * @param Course    $course    课程类Course对象
		 */
		function CreateCourse($course){ $this->course = clone $course; return $this; }
		/************************************************************************************************************************/





		/************************************************************************************************************************/
		/**
		 * Set系列方法，会自动调用Course类中的__set魔术方法来设置课程的相关信息
		 * 调用这些方法前必须确保CreateCourse方法已调用,$this->course对象已创建
		 * @param string    $title             重新设置课程名
		 * @param string    $cover             用户本地图片的存放路径，用于设置课程封面
		 * @param string    $type              课程封面的图片类型(image/jpg, image/png, image/gif, image/jpeg)
		 * @param string    $summary           设置课程简介
		 * @param int       $status            设置课程状态(0-'未开始', 1-'已开始', 2-'已结束')
		 * @param int       $chapters_count    设置课程所含章节数
		 */
		function SetCourseTitle($title) { $this->course->title = $title; return $this; }
		function SetCourseCover($cover, $type) { $this->course->cover = $cover; $this->course->type = $type; return $this; }
		function SetCourseSummary($summary) { $this->course->summary = $summary; return $this; }
		function SetCourseStatus($status) { $this->course->status = $status; return $this; }
		function SetCourseChaptersCount($chapters_count) { $this->course->chapters_count = $chapters_count; return $this; }
		/************************************************************************************************************************/





		/**************************************************************************************************************************************************************************************/
		/**
		 * 调用该方法可以将创建的课程信息存入数据库中的课程表courses
		 * @param PDO    $pdo    PDO对象用于进行数据库操作
		 */
		function InsertIntoCourses($pdo){
				if(!isset($this->course)){ /* 如果调用此方法时，还未实例化课程对象，则表明还未创建课程，需先调用CreateCourse方法 */
						echo '<br/>还未创建任何课程，无法将数据信息存入数据库';
						exit;
				}
				try{
						$sql = "INSERT INTO courses(teachers_id, title, cover, type, summary, status, chapters_count, created_at, updated_at) VALUES(?,?,?,?,?,?,?,?,?)";
						$stmt = $pdo->prepare($sql); /* 准备语句 */

						/* 将参数绑定到对应的问号?占位符 */
						$stmt->bindParam(1, $this->id); /* teachers_id即为Teacher中的成员变量$id */
						/* 自动调用Course对象中的__get魔术方法获取课程对象的成员属性值 */
						$stmt->bindParam(2, $this->course->title); $stmt->bindParam(3, $this->course->cover); $stmt->bindParam(4, $this->course->type);
						$stmt->bindParam(5, $this->course->summary); $status = '未开始'; $stmt->bindParam(6, $status); /* 新创建的课程默认未开始，需要教师发布后才会变为已开始供学生选课 */
						$stmt->bindParam(7, $this->course->chapters_count);

						/* 获取当前系统时间并将其转换为mysql的timestamp格式 */
						$mysqltime = date('Y-m-d H:i:s', strtotime("now"));
						$stmt->bindParam(8, $mysqltime); $stmt->bindParam(9, $mysqltime);

						$stmt->execute(); /* 执行准备好的语句 */
				}catch(PDOException $e){ /* 异常处理 */
						echo '<br/>Database insertion failed: '.$e->getMessage();
						exit;
				}
		}
		/**************************************************************************************************************************************************************************************/





		/*****************************************************************************************************************************************************************/
		/**
		 * 调用该方法可以修改数据库课程表courses中存放的课程信息
		 * @param PDO    $pdo    PDO对象用于进行数据库操作
		 */
		//function UpdateCourses($pdo){
		//		if(!isset($this->course)){ /* 如果调用此方法时，还未实例化课程对象，则表明还未创建课程，需先调用CreateCourse方法 */
		//				echo '<br/>还未创建任何课程，无法将数据信息存入数据库';
		//				exit;
		//		}
		//		try{
		//				$query = "SELECT id FROM courses WHERE teachers_id = ? AND title = ?";
		//				$result = $pdo->prepare($query);
		//				$result->execute($this->id, $this->course->title);
						/* 如果能从courses表中获取数据，则表示课程已存在可以进行课程信息更新 */
		//				if($result->rowCount() > 0){ $id = $result->fetch(PDO::FETCH_ASSOC); }
		//				else{ /* 如果不能获取数据则表示课程不存在，需要先调用CreateCourse方法创建课程 */
		//						echo '<br/>该课程不存在，无法进行课程信息更新';
		//						return;
		//				}

						/* 执行更新数据库操作 */
		/*				$update = "UPDATE courses SET title=?, class_id=?, cover=?, type=?, summary=?, status=?, chapters_count=?, updated_at=? WHERE id=?";
						$stmt = $pdo->prepare($update);
						$stmt->bindParam(1, $this->course->title); $stmt->bindParam(2, $this->course->class_id); $stmt->bindParam(3, $this->course->cover);
						$stmt->bindParam(4, $this->course->type); $stmt->bindParam(5, $this->course->summary);
		 */
		//				switch($this->course->status){ /* 根据枚举值来设置课程的状态 */
		/*				case 0 : $status = '未开始'; break;
						case 1 : $status = '已开始'; break;
						case 2 : $status = '已结束'; break;
						}
						$stmt->bindParam(6, $status);
						$stmt->bindParam(7, $this->course->chapters_count);
		 */
						/* 获取当前系统时间并将其转换为mysql的timestamp格式 */
		//				$mysqltime = date('Y-m-d H:i:s', strtotime("now"));
		//				$stmt->bindParam(8, $mysqltime); $stmt->bindParam(9, $id); /* 此id为query查询结果集result中的id */
		//				$stmt->execute(); /* 执行准备好的语句 */
						/* 如果UPDATE语句执行成功，并对数据表courses有行数影响，则更新数据成功 */
		/*				if($stmt->rowCount() > 0){ echo '<br/>课程信息修改成功！'; }
						else {echo '<br/>课程信息修改失败！';}
				}catch(PDOException $e){
						echo '<br/>Database updation failed: '.$e->getMessage();
						exit;
				}
		}
		 */
		/*****************************************************************************************************************************************************************/
}
?>
