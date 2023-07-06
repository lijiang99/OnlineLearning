<?php
/**
 * file: student.class.php
 * 本类的实例对象为学生可进行选课等......操作
 */

include_once("conn.inc.php"); /* 包含数据库连接文件 */

class Student{
		/* 学生用户的信息 */
		private $id, $users_profile_id;

		/**********************************************************************************************************************/
		/**
		 * 构造函数用于实例化一个学生对象
		 * @param int    $id                  学生表中的唯一id
		 * @param int    $users_profile_id    用户个人详细信息表中的唯一id
		 */
		function __construct($id, $users_profile_id){ $this->id = $id; $this->users_profile_id = $users_profile_id; }
		/**********************************************************************************************************************/





		/**********************************************************************************************************************/
		/**
		 * __set魔术方法，设置学生类的成员属性值
		 * @param string    $properName     成员属性名
		 * @param mixed     $properValue    成员属性值
		 */
		function __set($properName, $properValue){
				/* 根据参数决定为哪个属性赋值，传入不同的成员属性名，赋予传入相应的值 */
				$this->$properName = $properValue;
		}
		/**********************************************************************************************************************/





		/**********************************************************************************************************************/
		/**
		 * __get魔术方法，在直接获取属性值时会自动调用，以属性名作为参数传入并处理
		 * @param string    $properName    成员属性名
		 * @param mixed                    返回属性值
		 */
		function __get($properName) { return $this->$properName; }
		/**********************************************************************************************************************/





		/*********************************************************************************************************************************************/
		/**
		 * 调用该方法可以进行学生选课，并将选课信息添加到选课结果表courses_select中
		 * @param     PDO    $pdo           PDO对象用于进行数据库操作
		 * @param     int    $courses_id    课程在课程表courses中的唯一id
		 * @return    true                  选课成功
		 * @return    false                 选课失败
		 */
		function SelectCourses($pdo, $courses_id, $classes_id){
				try{
						/* 准备数据库查询语句以传入参数courses_id为过滤条件，从课程表courses中提取课程创建人的teachers_id */
						$sql = "SELECT teachers_id, students_count FROM courses WHERE id=?";
						$result = $pdo->prepare($sql); $result->bindParam(1, $courses_id); /* 绑定参数 */
						$result->execute(); /* 执行准备好的语句 */
						/* 如果能从courses表中提取到数据，则表示课程存在，并将teachers_id赋给对应的关联数组$row */
						if($result->rowCount() > 0){ $row = $result->fetch(PDO::FETCH_ASSOC); }
						/* 否则表示courses表中不存在该课程，输出提示信息并直接返回 */
						else{ echo '<br/>该课程不存在，无法进行选课操作'; return false; }

						/* 准备数据库插入语句，将选课信息插入到选课结果表courses_select中 */
						$sql = "INSERT INTO courses_select(courses_id, students_id, teachers_id, classes_id, created_at) VALUES(?,?,?,?,?)";
						$stmt = $pdo->prepare($sql);
						$stmt->bindParam(1, $courses_id); /* 将传入的参数courses_id绑定到第一个?问号占位符 */
						$stmt->bindParam(2, $this->id); /* 将成员属性$this->id，即学生表students中的唯一id，绑定到第二个?问号占位符 */
						$stmt->bindParam(3, $row['teachers_id']); /* 将上一条查询语句筛选出的关联数组$row中的teachers_id绑定到第三个?问号占位符 */
						$stmt->bindParam(4, $classes_id);
						/* 获取当前系统时间并将其转换为mysql的timestamp格式，绑定到第四个?问号占位符 */
						$mysqltime = date('Y-m-d H:i:s', strtotime("now")); $stmt->bindParam(5, $mysqltime);
						$stmt->execute(); /* 执行准备好的语句 */

						/* 由于学生选课，所以需要更新courses课程表中已选课程的学生人数 */
						$update = "UPDATE courses SET students_count=?, updated_at=? WHERE id=?";
						$stmt1 = $pdo->prepare($update);
						$students_count = $row['students_count'] + 1; $stmt1->bindParam(1, $students_count); $stmt1->bindParam(2, $mysqltime); $stmt1->bindParam(3, $courses_id);
						$stmt1->execute();
						if($stmt1->rowCount() > 0){ return true; } else { return false; }

				}catch(PDOException $e){ /* 异常处理 */
						echo '<br/>Database insertion or updation in student.class.php failed: '.$e->getMessage();
						exit;
				}
		}
		/*********************************************************************************************************************************************/
}
?>
