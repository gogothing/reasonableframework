<?php
if(!function_exists("get_db_connect")) {
	function get_db_connect() {
		$config = get_config();

		$conn = new PDO(
			sprintf(
				"mysql:host=%s;dbname=%s;charset=utf8",
				$config['db_host'],
				$config['db_name']
			),
			$config['db_username'],
			$config['db_password'],
			array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
		);
		$conn->query("SET NAMES 'utf8'");
		
		return $conn;
	}
}

if(!function_exists("exec_stmt_query")) {
	function exec_stmt_query($sql, $bind=array()) {
		$stmt = get_db_stmt($sql, $bind);
		$stmt->execute();

		return $stmt;
	}
}

// alias sql_query from exec_stmt_query
if(!function_exists("sql_query")) {
	function sql_query($sql, $bind=array()) {
		return exec_stmt_query($sql, $bind);
	}
}

if(!function_exists("get_dbc_object")) {
	function get_dbc_object($renew=false) {
		global $dbc;
		$dbc = $renew ? get_db_connect() : $dbc;
		return $dbc;
	}
}

if(!function_exists("get_db_stmt")) {
	function get_db_stmt($sql, $bind=array()) {
		$stmt = get_dbc_object()->prepare($sql);
		if(count($bind) > 0) {
			foreach($bind as $k=>$v) {
				$stmt->bindParam(':' . $k, $v);
			}
		}
		return $stmt;
	}
}

if(!function_exists("get_db_last_id")) {
	function get_db_last_id() {
		return get_dbc_object()->lastInsertId();
	}
}

if(!function_exists("exec_db_query")) {
	function exec_db_query($sql, $bind=array(), $options=array()) {
		$dbc = get_dbc_object();

		$flag = false;
		$stmt = get_db_stmt($sql, $bind);

		$validOptions = array();
		$optionAvailables = array("is_check_count", "is_commit");
		foreach($optionAvailables as $opt) {
			if(!array_key_empty($opt, $options)) {
				$validOptions[$opt] = $options[$opt];
			} else {
				$validOptions[$opt] = false;
			}
		}
		extract($validOptions);
		
		if($is_commit) {
			$dbc->beginTransaction();
		}

		if($is_check_count == true) {
			if($stmt->execute() && $stmt->rowCount() > 0) {
				$flag = true;
			}
		} else {
			$flag = $stmt->execute();
		}

		if($is_commit) {
			$dbc->commit();
		}

		return $flag;
	}
}

if(!function_exists("exec_db_fetch_all")) {
	function exec_db_fetch_all($sql, $bind=array()) {
		$rows = array();
		$stmt = get_db_stmt($sql, $bind);
		
		if($stmt->execute() && $stmt->rowCount() > 0) {
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		}
		
		return $rows;
	}
}

if(!function_exists("get_page_range")) {
	function get_page_range($page=1, $limit=0) {
		$append_sql = "";
		
		if($limit > 0) {
			$record_start = ($page - 1) * $limit;
			$record_end = $record_start + $limit - 1;
			$append_sql .= " limit $record_start, $record_end";
		}
		
		return $append_sql;
	}
}

// set global db connection variable
$dbc = get_db_connect();
