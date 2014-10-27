<?php
/*
	PufferPanel - A Minecraft Server Management Panel
	Copyright (c) 2013 Dane Everitt

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see http://www.gnu.org/licenses/.
*/
namespace PufferPanel\Core\Components;

/**
 * PufferPanel Core Database Trait
 */
trait Database {

	/**
	* @param mixed $db
	* @static
	*/
	protected static $db;

	/**
	* @param string $salt
	* @static
	*/
	public static $salt;

	/**
	* Builds the database connection and allows it to be called multiple times.
	*
	* @return void
	* @static
	*/
	public static function buildConnection(){

		require('../core/configuration.php');
		try {

			/*
			* Connect to SQL Server over SSL
			*/
			if(array_key_exists('sql_ssl', $_INFO) && $_INFO['sql_ssl'] === true){

				self::$db = new \PufferPanel\Core\Database_Initiator('mysql:host='.$_INFO['sql_h'].';dbname='.$_INFO['sql_db'], $_INFO['sql_u'], $_INFO['sql_p'],
					array(
						\PDO::MYSQL_ATTR_SSL_KEY => $_INFO['sql_ssl_client-key'],
						\PDO::MYSQL_ATTR_SSL_CERT => $_INFO['sql_ssl_client-cert'],
						\PDO::MYSQL_ATTR_SSL_CA => $_INFO['sql_ssl_ca-cert'],
						\PDO::ATTR_PERSISTENT => true,
						\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
					)
				);

			}else{

				self::$db = new \PufferPanel\Core\Database_Initiator('mysql:host='.$_INFO['sql_h'].';dbname='.$_INFO['sql_db'], $_INFO['sql_u'], $_INFO['sql_p'], array(
					\PDO::ATTR_PERSISTENT => true,
					\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
				));

			}

			self::$db->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );

		}catch (PDOException $e) {

			echo "MySQL Connection Error: " . $e->getMessage();

		}

	}

	/**
	* Connects to the database by calling the builder function.
	*
	* @return object
	* @static
	*/
	public static function connect() {

		if (!self::$db) {

			self::buildConnection();

		}

		return self::$db;

	}

}