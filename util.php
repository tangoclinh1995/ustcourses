<?php
class DatabaseConnector {
	private $db = null;
	
	public function __construct() {
		$options = [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::MYSQL_ATTR_DIRECT_QUERY => false
		];
		
		$this->db = new PDO("mysql:dbname=hkust_courses; host=localhost; charset=utf8","tangoclinh1995", "230395", $options);
	}

	public function Query($query, $fetch_all = false) {
		$ps = $this->db->query($query);
		$ps->setFetchMode(PDO::FETCH_ASSOC);
		
		if ($fetch_all) 
			return $ps->fetchAll();
		else return $ps;
	}
}
?>
