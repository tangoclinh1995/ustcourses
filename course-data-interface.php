<?php 
require "util.php";
require "course-data.php";



function GetDepartmentList() {
	$db = new DatabaseConnector();

	$res = "";

	foreach($db->Query("SELECT * FROM departments") as $row)
		$res .= (strcmp($res, "") != 0 ? "," : "") . "{\"id\":" . $row["Id"] . ", \"code\":\"" . $row["Code"] . "\"}";

	return "[$res]";
}


function UpdateData($department, $departmentId = "") {
	$db = new DatabaseConnector();

	$department = explode(",", $department);

	if (strcmp($departmentId, "") == 0)
		$departmentId = false;
	else $departmentId = explode(",", $departmentId);

	$len = count($department);

	for ($i = 0; $i < $len; ++$i)
		LoadDepartmentCoursesToDatabase($db, $department[$i], $departmentId === false ? -1 : (int)$departmentId[$i]);
}



switch ($_POST["task"]) {
	case 0:
		echo GetDepartmentList();
		break;
	case 1:
		UpdateData($_POST["dept"], isset($_POST["deptId"]) ? $_POST["deptId"] : "");
		echo 1;
		break;		
	case 2:
		ClearDatabase(new DatabaseConnector(), isset($_POST["all"]) && $_POST["all"] == 1);
		echo 1;
		break;
	case 3:
		LoadAllCommonCoreToDatabase(new DatabaseConnector());
		echo 1;
		break;
	case 4:
		$db = new DatabaseConnector();
		ClearDatabase($db, true);
		LoadDepartmentListToDatabase($db);
		echo 1;
		break;
}
?>