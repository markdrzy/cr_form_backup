<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');



class Cr_form_backup_acc {

	var $name			= 'CR Form Backup Accessory';
	var $id				= 'cr_form_backup';
	var $version		= '1.0';
	var $description	= 'The Click Rain Form Backup Accessory.';
	var $sections		= array();

	/**
	 * Constructor
	 */
	function __construct()
	{
		$this->EE =& get_instance();
	}
	
	function set_sections()
	{
		return TRUE;
	}
	
	function update()
	{
		return TRUE;
	}
	
}
// END CLASS