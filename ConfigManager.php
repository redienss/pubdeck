<?php

/**
 * Class loading configs from .ini files
 * 
 * @author Tomasz Szneider
 */
class ConfigManager
{
	/**
	 * Load config from .ini file
	 * 
	 * @param string $file - name of .ini file to load
	 * 
	 * @return void
	 */	
	public static function load($file)
	{
		// Load .ini file	
	    $config = parse_ini_file(__DIR__."\\".$file);
		
		// Define consts   
	    foreach($config as $key => $value){
	        define(strtoupper($key), $value);
	    }
	}
}
