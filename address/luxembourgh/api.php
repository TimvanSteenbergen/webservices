<?php
/**
 * API class
 * @author Tim van Steenbergen
 * @version 2017-12-19
 */

class api
{
	private $db;

	/**
	 * Constructor - open DB connection
	 *
	 * @param none
	 * @return database
	 */
	function __construct()
	{
		$conf = json_decode(file_get_contents('configuration.json'), TRUE);
		$this->db = new mysqli($conf["host"], $conf["user"], $conf["password"], $conf["database"]);
	}

	/**
	 * Destructor - close DB connection
	 *
	 * @param none
	 * @return none
	 */
	function __destruct()
	{
		$this->db->close();
	}

	/**
	 * Get the list of users.
	 *
	 * @param none or user id
	 * @return list of data on JSON format
	 */
	function get($params)
	{
		if (empty($params['postcode'])) {
			return 'No postalcode given to search for.';
		}
		$postcode = $this->db->real_escape_string($params['postcode']);
		var_dump($postcode);
		if (!preg_match('/^[a-zA-Z0-9]*$/', $postcode)){
			return 'Wrong postalcode given to search for.';
		}
		$housenumber = $this->db->real_escape_string($params['number']);
		if (!empty($housenumber)){
			if (!preg_match('/^[a-zA-Z0-9]*$/', $housenumber)){
			return 'Wrong format Housenumber given to search for.';
			}
		}
		$query = 'SELECT'
		. ' a.postcode AS postcode'
		. ', a.number AS number'
		. ', a.city AS city'
		. ', a.street AS street'
		. ', a.lon AS longitude'
		. ', a.lat AS latitude'
		. ' FROM address AS a'
		. ' WHERE a.postcode = \'' . $postcode . '\''
		. ($params['number'] == ''? '' : ' AND a.number = \'' . $housenumber . '\'')
		. ' ORDER BY a.lon'
		;

		$result = $this->db->query($query);
		$list = array();
		while ($row = $result->fetch_assoc())
		{
			$row['city'] = utf8_encode($row['city']);
			$row['street'] = utf8_encode($row['street']);
			$list[] = $row;
		}
		return $list;
	}
}
