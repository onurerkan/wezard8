<?php
require('WezardError.php');
/*
 * Fire Starter! -- Basic Bridge for SQL Connection
 * @author Onur Erkan
 * @url http://weapp.in
 * @version 8-build.10.0.13
 * @version 8.1-build.03.0.14
 * -Developed for We Application Development
 */

class Wezard{
	public $dbc;
	public $model;
	/**
	 * Construct Method For Wezard
	 * Connect SQL via PDO else throws an error
	 */
	public function __construct() {
		
			$this -> dbc = new PDO('mysql:host=localhost;dbname=levis;port=8889', 'root', 'root', array(PDO::ATTR_PERSISTENT => true));		
			$this -> dbc -> setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
			$this -> dbc -> exec('SET NAMES utf8');

			//$this->Onur = new Onur();
			//throw new WezardError(array("title" =>"PDO_EXCEPTION_ERROR","message"=>"Wrong Username/ Password / Host Combination"));

	}
	/* Test Case for SubMethods */
	public function erkan(){
		$this->Onur->debug('NABER');
	}

	/**
	 * Set Model Variable For Database
	 *
	 */
	public function setModel($model) {
		$this -> model = $model;
	}


	/**
	 * PDO Quote Method For Bastards
	 * Similar to mysql_real_escape_string or mysqli
	 *
	 */
	protected function escapeString($arr) {

		foreach ($arr as $key => $value) {
			$value = $this -> dbc -> quote($value);
		}
		return $arr;
	}

	/**
	 * Clean Possible XSS Attacks
	 * @url https://gist.github.com/1098477
	 * @description : It was tested against *most* exploits here: http://ha.ckers.org/xss.html
	 * Added version 1.2 26 Nov 2012
	 */
	protected function xss_clean($data) {
		// Fix &entity\n;
		$data = str_replace(array('&amp;', '&lt;', '&gt;'), array('&amp;amp;', '&amp;lt;', '&amp;gt;'), $data);
		$data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
		$data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
		$data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

		// Remove any attribute starting with "on" or xmlns
		$data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

		// Remove javascript: and vbscript: protocols
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

		// Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

		// Remove namespaced elements (we do not need them)
		$data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

		do {
			// Remove really unwanted tags
			$old_data = $data;
			$data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
		} while ($old_data !== $data);

		// we are done...
		return $data;
	}
	/**
	 * Select One Row from given table
	 * @var $param (* = statement)
	 * @var $statement (WHERE condition)
	 */
	public function getOne($param,$statement){
		
		if($statement != '')
		$query = $this -> dbc -> prepare('SELECT '.$param.' FROM ' . $this -> model . ' WHERE ' . $statement);
		else
		throw new WezardError(array("title" => "Statement Error","message" => "WHERE 'den sonra koşul bekleniyor."));
		
		$query -> execute();
		$result = $query -> fetchAll(PDO::FETCH_ASSOC);
		
		return $result;
	}
	/**
	 * SELECT QUERY
	 * @param VALUE : ALL or Specific Coloumn
	 * @param PARAMS : WHERE Condition
	 * @return result
	 */
	public function get($value = '*', $params = '') {

		$nice_data = $this -> escapeString($data);

		($params != '') ? $query = $this -> dbc -> prepare('SELECT ' . $value . ' FROM ' . $this -> model . ' WHERE ' . $params) : $query = $this -> dbc -> prepare('SELECT ' . $value . ' FROM ' . $this -> model . ' ');
		
		$query -> execute();

		$result = $query -> fetchAll(PDO::FETCH_ASSOC);
		return $result;
	}

	/**
	 * INSERT QUERY
	 * @param VALUES : Coloumn Name
	 * @param PARAMS : VALUE FOR NEW ITEM
	 * @return Affected Row
	 */
	public function add($values, $params) {

		if(!is_array($params) || is_null($params))
		throw new WezardError(array("title" => "Obje türü hatası" ,"message" => "Eklenecek öğeler array() içerisinde gönderilmeli"));

		$updatedAt = date('Y-m-d H:i:s');
		$createdAt = date('Y-m-d H:i:s');

		$values .= ',updatedAt,createdAt';
		array_push($params,$updatedAt);
		array_push($params,$createdAt);

		$nice_params = $this -> escapeString($params);
		$qmark = str_repeat(' ?,', sizeof($params));
		$qmark = rtrim($qmark, ',');
		$query = $this -> dbc -> prepare('INSERT INTO ' . $this -> model . '(' . $values . ') VALUES(' . $qmark . ')');
		$result = $query -> execute($nice_params);
		return $this->dbc->lastInsertId();

	}

	/**
	 * UPDATE QUERY
	 * @param ID : Row Id
	 * @param VALUES : COLOUMN NAME
	 * @param PARAMS : VALUE FOR NEW ITEM
	 * @return Affected Row
	 */
	public function update($id, $values, $params = array()) {
			
		if(!is_array($params) || is_null($params))
		throw new WezardError(array("title" => "Obje türü hatası" ,"message" => "Eklenecek öğeler array() içerisinde gönderilmeli"));
			
		$updatedAt = date('Y-m-d H:i:s');
		
		$values .= ',updatedAt';
		array_push($params,$updatedAt);
				
		$nice_params = $this -> escapeString($params);
		
		/* Change Values */
		
		$val = explode(',', $values);
		$new_values = '';
		for ($i=0; $i < sizeof($val); $i++) {
			
			$i != (sizeof($val) -1) ? $new_values .= $val[$i].'=?,' : $new_values .= $val[$i].'=?';
		}
		
		
		$query = $this -> dbc -> prepare('UPDATE ' . $this -> model . ' SET ' . $new_values . ' WHERE id =' . $id);
		$result = $query -> execute($nice_params);
		return 1;
	}

	/**
	 * DELETE QUERY
	 * Set isActive 1 to 0
	 * @param ID : Row Id
	 * @return Affected Row
	 */
	public function delete($id) {
		$this -> dbc -> exec('UPDATE ' . $this -> model . ' SET isActive =0 WHERE id=' . $id);
		return 1; // Affected rows ? 

	}

	/**
	 * BUILD QUERY
	 * To build a native SQL Query
	 * @param SQL : Query
	 * @return Array
	 */
	public function buildQuery($sql) {
		$arr = array();
		$result = $this -> dbc -> query($sql);
		foreach ($result as $row) {
			array_push($arr, $row);
		}
		return $arr;
	}

	/**
	 * BUILD APP
	 * It builds Apps' View Template
	 * @param template : A Mustache File
	 * @param data : To be rendered [Array]
	 * @return Nothing
	 */


} 
?>