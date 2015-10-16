<?php 
$SEMESTER = "2015 Spring";
$BASE_LINK = "https://w5.ab.ust.hk/wcq/cgi-bin/1430/";
$COURSE_PER_DEPARTMENT = 90;

$DEPARTMENTS = [];

$COMMON_CORE = [				//	[Id in Database, Common Core page[, School-Sponsored Page]]
	"Humanities" 				=> [1, "12", "09"],
	"Social Analysis" 			=> [2, "13", "10"],
	"Science & Technology" 		=> [3, "14", "11"],
	"Quantitative Reasoning" 	=> [4, "15"],
	"Arts" 						=> [5, "16"],
	"English Communication" 	=> [6, "17"],
	"Chinese Communication" 	=> [7, "18"],
];

$CLASS_TYPE = [
	"L"		=> "lectures",
	"T"		=> "tutorials",
	"LA"	=> "labs",
];

$WEEK_DAY = ["Mo" => 0, "Tu" => 1, "We" => 2, "Th" => 3, "Fr" => 4, "Sa" => 5, "Su" => 6];



function LoadDepartmentListToDatabase($db) {	
	libxml_use_internal_errors(true);	//Turn off warning while HTML Document is being parsed

	$doc = new DOMDocument();
	$doc->recover = true;				//Try to parse non-well formed HTML Document
	$doc->loadHTML(file_get_contents($GLOBALS["BASE_LINK"]));
	$xpath = new DOMXPath($doc);

	$query = "INSERT INTO departments (Code) VALUES ";
	$first_dept_gone = false;

	foreach($xpath->query("//div[@class='depts']/a") as $dept) {
		$query .= ($first_dept_gone ? "," : "") . "('" . $dept->nodeValue . "')";
		$first_dept_gone = true;
	}

	$db->Query($query);

	libxml_use_internal_errors(false);
}


function StandardizeStringForSQL($s) {
	$s = trim($s);
	$s = str_replace("'", "''", $s);

	return $s;
}


function GetCourseDescription($xpath, $courseDocument) {
	$nodes = $xpath->query(".//div[@class='courseinfo']/div[@class='courseattr popup']//table", $courseDocument);
	if ($nodes->length == 0) return "";

	foreach ($xpath->query(".//tr/th", $nodes[0]) as $title) {
		if (strcmp($title->nodeValue, "DESCRIPTION") == 0 && $title->nextSibling != null)
			return StandardizeStringForSQL($title->nextSibling->nodeValue);
	}

	return "";
}


function GetCourseFundamentalInfo($xpath, $courseDocument, &$info) {
	$nodes = $xpath->query("./h2", $courseDocument);
	if ($nodes->length == 0) return "";

	preg_match_all("/([A-Z]+) (\d+[A-Z]*) - ([a-zA-z0-9 :!-\/’]+)\((\d) unit[s]*\)/", $nodes[0]->nodeValue, $info, PREG_SET_ORDER);

	$info = $info[0];

	//Inside info: [1] = Department Code, [2] = Course Code, [3] = Course Name, [4] = Unit
	$info[3] = StandardizeStringForSQL($info[3]);
	$info[4] = (int)$info[4];
}


function ConvertTimeToInt($h, $m, $am) {
	if ($h == 12 && $am) $h = 0;
	if (!$am && $h != 12) $h += 12;
	return $h*60 + $m;
}


function AddClassTime($courseId, $nbr, $value, $classType, $db, &$classTimeQueries) {
	if (strcmp($value,"TBA") == 0) return false;

	preg_match_all("/([a-zA-z]+) (\d{2}):(\d{2})(AM|PM) - (\d{2}):(\d{2})(AM|PM)/", $value, $matches, PREG_SET_ORDER);

	$start_time = ConvertTimeToInt((int)$matches[0][2], (int)$matches[0][3], strcmp($matches[0][4], "AM") == 0);
	$end_time = ConvertTimeToInt((int)$matches[0][5], (int)$matches[0][6], strcmp($matches[0][7], "AM") == 0);

	//Get Weekday && Add time information to Time database
	$len = strlen($matches[0][1]);

	for ($i = 0; $i < $len; $i += 2) {
		$wd = $GLOBALS["WEEK_DAY"][substr($matches[0][1], $i, 2)];
		$classTimeQueries[$classType][0] .= ($classTimeQueries[$classType][1] ? "," : "")
										. "($courseId,$nbr,$wd,$start_time,$end_time)";

		$classTimeQueries[$classType][1] = true;
	}

	return true;
}


function AddClasses($courseId, $xpath, $courseDocument, $db, &$classQueries, &$classTimeQueries) {
	//Count the number of classes which can be used in preparing Weekly Timetable
	$class_added = 0;

	$table = $xpath->query("./table[@class='sections']", $courseDocument);
	if ($table->length == 0) return ;

	//Loop through each class section in the table
	foreach($xpath->query(".//tr[@class='newsect secteven']|.//tr[@class='newsect sectodd']", $table[0]) as $class_doc) {
		$td = $xpath->query("./td", $class_doc);

		//Identify class type: Lecture, Tutorial, Lab. Otherwise no consider		
		preg_match_all("/([A-Z]+)(\d+)([A-Z]*) \((\d+)\)/", $td[0]->nodeValue, $matches, PREG_SET_ORDER);

		if (array_key_exists($matches[0][1], $GLOBALS["CLASS_TYPE"]))
			$class = $GLOBALS["CLASS_TYPE"][$matches[0][1]];
		else continue;

		//Also get Class No., Suffix & Nbr code
		$no = (int)$matches[0][2];
		$suffix = $matches[0][3];
		$nbr = (int)$matches[0][4];

		//Get Room
		$room = $td[2]->nodeValue;

		//Get Instructor
		$instructors = "";
		foreach ($xpath->query("./a", $td[3]) as $instructor)
			$instructors .= $instructor->nodeValue . "||";
		$instructors = StandardizeStringForSQL($instructors);

		//Get Quota & Avail Number
		$quota_class = $td[4]->attributes->getNamedItem("class");

		if ($quota_class !== null && strcmp($quota_class->nodeValue, "quota") == 0) {
			preg_match_all("/(\d+)\/\d+\/(\d+)/", substr($xpath->query(".//div[@class='quotadetail']", $td[4])[0]->nodeValue, 17), $matches, PREG_SET_ORDER);
			$quota_spec = (int)$matches[0][1];
			$avail_spec = (int)$matches[0][2];

			$quota = (int)$xpath->query("./span", $td[4])[0]->nodeValue - $quota_spec;
			$avail = (int)$td[6]->nodeValue - $avail_spec;
		} else {
			$quota = (int)$td[4]->nodeValue;
			$avail = (int)$td[6]->nodeValue;

			$quota_spec = $avail_spec = 0;
		}

		//Get Wait number
		$wait = (int)$td[7]->nodeValue;

		//Get Instructor consent requirement
		$consent = (int)($xpath->query("./div[@class='popup classnote']", $td[8])->length != 0);

		//Get Class notes
		$notes = $xpath->query("./div[@class='popup classnotes']", $td[8]);
		$notes = StandardizeStringForSQL($notes->length == 0 ? "" : $notes[0]->nodeValue);

		//Get Date & Time, Room, Instructor & Add to "Time" database table
		$has_time = AddClassTime($courseId, $nbr, $td[1]->nodeValue, $class, $db, $classTimeQueries);
		
		if ($class_doc->nextSibling !== null) {
			$check_class = $class_doc->nextSibling->attributes->getNamedItem("class");

			if ($check_class !== null)
				if (strcmp($check_class->nodeValue, "secteven") == 0 || strcmp($check_class->nodeValue, "sectodd") == 0)
					$has_time = $has_time || AddClassTime($courseId, $nbr, $xpath->query("./td", $class_doc->nextSibling)[0]->nodeValue, $class, $db);			
		}

		//Add the class to the summary table, if its time is specified
		if ($has_time) {
			$classQueries[$class][0] .= ($classQueries[$class][1] ? "," : "")
										. "($courseId,$nbr,$no,'$suffix','$room','$instructors',$quota,$avail,$quota_spec,$avail_spec,$wait,$consent,'$notes')";

			$classQueries[$class][1] = true;
			++$class_added;
		}
	}

	return $class_added;
}


function LoadDepartmentCoursesToDatabase($db, $departmentCode, $departmentId = -1) {
	libxml_use_internal_errors(true);	//Turn off warning while HTML Document is being parsed

	$doc = new DOMDocument();
	$doc->recover = true;				//Try to parse non-well formed HTML Document
	$doc->loadHTML(file_get_contents($GLOBALS["BASE_LINK"]."subject/$departmentCode"));
	$xpath = new DOMXPath($doc);

	//Get the beginning of CourseId series of this department
	if ($departmentId == -1)
		$course_id = $course_id_start = (int)$db->Query("SELECT Id FROM departments WHERE Code='$departmentCode'", true)[0]["Id"] * $GLOBALS["COURSE_PER_DEPARTMENT"];
	else $course_id = $course_id_start = $departmentId * $GLOBALS["COURSE_PER_DEPARTMENT"];

	//The SQL Query which will be used to insert all course data at once
	$course_query = "INSERT INTO courses (Id,Dept,Code,Name,Unit,Matching,Description) VALUES ";

	//The SQL Queries which will be used to insert all class data at once
	$class_queries = [];
	$class_time_queries = [];

	foreach($GLOBALS["CLASS_TYPE"] as $val) {
		$class_queries[$val] = ["INSERT INTO $val (CourseId,Nbr,No,Suffix,Room,Instructor,Quota,Avail,QuotaSpec,AvailSpec,Wait,Consent,Notes) VALUES ", false];
		$class_time_queries[$val] = ["INSERT INTO $val" . "_time (CourseId,Nbr,WeekDay,Start,End) VALUES ", false];
	}


	$first_course_gone = false;

	//Loop through each Div elements corresponding to a course
	foreach ($xpath->query(".//div[@class='course']") as $course_doc) {
		$description = GetCourseDescription($xpath, $course_doc);

		//Get Matching condition
		$matching = (int)($xpath->query(".//div[@class='courseinfo']/div[@class='matching']", $course_doc)->length != 0);

		//Get Course Information: [2] = Course code, [3] = Course Name, [4] = Unit
		GetCourseFundamentalInfo($xpath, $course_doc, $info);

		//Add Lectures, Tutorials and Labs to their corresponding database tables		
		//Only add course to database if this course has >= 1 classes which can be used in Weekly Timetable
		if (AddClasses($course_id, $xpath, $course_doc, $db, $class_queries, $class_time_queries) != 0) {			
			$course_query .= ($first_course_gone ? "," : "")
							. "($course_id,'$departmentCode','{$info[2]}','{$info[3]}',{$info[4]},$matching,'$description')";

			++$course_id;
			$first_course_gone = true;
		}
	}

	//Execute SQL Queries (Assume that all data has been cleared beforehand)
	if ($first_course_gone) $db->Query($course_query);

	foreach($GLOBALS["CLASS_TYPE"] as $val) {
		if ($class_queries[$val][1]) $db->Query($class_queries[$val][0]);
		if ($class_time_queries[$val][1]) $db->Query($class_time_queries[$val][0]);
	}
}


function LoadCommonCoreToDatabase($db, $webId, $code, $schoolSponsored) {
	libxml_use_internal_errors(true);	//Turn off warning while HTML Document is being parsed

	$doc = new DOMDocument();
	$doc->recover = true;				//Try to parse non-well formed HTML Document
	$doc->loadHTML(file_get_contents($GLOBALS["BASE_LINK"]."/common_core/4Y/$webId"));
	$xpath = new DOMXPath($doc);

	//Loop through each Div elements corresponding to a course
	foreach ($xpath->query(".//div[@class='course']") as $course_doc) {
		//Get Course Information: [1] = Department Code, [2] = Course code
		GetCourseFundamentalInfo($xpath, $course_doc, $info);

		$db->Query("UPDATE courses SET CommonCore=$code,SchoolSponsored=$schoolSponsored WHERE Dept='{$info[1]}' AND Code='{$info[2]}'");
	}
}


function LoadAllCommonCoreToDatabase($db) {
	$db->Query("UPDATE courses SET CommonCore=0,SchoolSponsored=0");

	foreach($GLOBALS["COMMON_CORE"] as $data) {
		LoadCommonCoreToDatabase($db, $data[1], $data[0], 0);
		if (isset($data[2])) LoadCommonCoreToDatabase($db, $data[2], $data[0], 1);
	}
}


function ClearDatabase($db, $clearDepartment = false) {
	if ($clearDepartment) {
		$db->Query("DELETE FROM departments");
		$db->Query("ALTER TABLE departments auto_increment = 1");		
	}

	$db->Query("DELETE FROM courses");

	foreach($GLOBALS["CLASS_TYPE"] as $val) {
		$db->Query("DELETE FROM $val");
		$db->Query("DELETE FROM $val" . "_time");	
	}	
}
?>