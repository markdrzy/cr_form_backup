<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cr_form_backup {

	var $return_data = '';
	var $modname = 'Cr_form_backup';
	var $short_modname = 'cr_form_backup';
	var $settings = array();
	
	function __construct()
	{
		$this->EE =& get_instance();
		
		// Load the OmniLogger class.
		if (file_exists(PATH_THIRD .'omnilog/classes/omnilogger' .EXT))
		{
			include_once PATH_THIRD .'omnilog/classes/omnilogger' .EXT;
		}
		
		/*
		// Grab settings from the MetaMod Table
		$r = $this->EE->db->query("SELECT `mod_settings` FROM `{$this->EE->db->dbprefix}cr_module_meta` 
									WHERE `mod_name` = '{$this->modname}';");
		if ($r->num_rows() > 0)
		{
			foreach ($r->result_array() as $row)
			{
				$this->settings = unserialize($row['mod_settings']);
			}
		}
		*/
	}
	
	/**
	* Logs form data in backup table.
	*
	* @access  public
	* @return  void
	*/
	public function backup_submission()
	{
		$action = mysql_real_escape_string($this->EE->input->post('action',TRUE),$this->EE->db->conn_id);
		$post = mysql_real_escape_string($this->EE->input->post('post',TRUE),$this->EE->db->conn_id);
		
		$this->EE->db->query("INSERT INTO `{$this->EE->db->dbprefix}{$this->short_modname}` 
								(`action`,`post_data`,`submitted_on`) 
								VALUES ('{$action}','{$post}',NOW());");
	}
	
	/**
	* Retrieves data from the backup table.
	*
	* @access  public
	* @param   string      $action        The action of the requested form data.
	* @param   string      $start_date    The start date of the result set.
	* @param   string      $end_date      The end date of the result set.
	* @return  array
	*/
	public function download_data($action,$start_date='',$end_date='')
	{
		if ($start_date != '' && $end_date != '')
		{
			$sm = substr($start_date,4,2);
			$sy = substr($start_date,0,4);
			$sd = '01';
			$start_date = $sy.'-'.$sm.'-'.$sd;
			$em = substr($end_date,4,2);
			$ey = substr($end_date,0,4);
			$ed = cal_days_in_month(CAL_GREGORIAN, $em, $ey);
			$end_date = $ey.'-'.$em.'-'.$ed;
		}
		$q = "SELECT * FROM `{$this->EE->db->dbprefix}{$this->short_modname}` WHERE `action` = '{$action}'";
		if ($start_date != '' && $end_date != '') $q .= " && `submitted_on` BETWEEN DATE('{$start_date}') AND DATE('{$end_date}')";
		$q .= " ORDER BY `submitted_on` ASC;";
		
		$r = $this->EE->db->query($q);
		if ($r->num_rows() > 0) {
			$ts = time();
			header( 'Content-Type: text/csv' );
			header( 'Content-Disposition: attachment;filename='.$action.'-'.$ts.'.csv');
			$fp = fopen('php://output', 'w');
			foreach ($r->result_array() as $row)
			{
				$line = unserialize($row['post_data']);
				array_unshift($line,$action);
				array_push($line,$row['submitted_on']);
				fputcsv($fp, $line);
			}
			fclose($fp);
		}
	}
	
	
	
	/**
	* Logs a message to OmniLog.
	*
	* @access  public
	* @param   string      $message        The log entry message.
	* @param   int         $severity       The log entry 'level'.
	* @param   array       $emails         An array of "admin" email addresses.
	* @param   string      $extended_data  Additional data.
	* @return  void
	*/
	public function log_message(
		$message,
		$severity = 1,
		Array $emails = array(),
		$extended_data = ''
	)
	{
		if (class_exists('Omnilog_entry') && class_exists('Omnilogger'))
		{
			switch ($severity)
			{
				case 3:
					$notify = TRUE;
					$type   = Omnilog_entry::ERROR;
				break;
	
				case 2:
					$notify = FALSE;
					$type   = Omnilog_entry::WARNING;
				break;
	
				case 1:
				default:
					$notify = FALSE;
					$type   = Omnilog_entry::NOTICE;
				break;
			}
	
			$omnilog_entry = new Omnilog_entry(array(
				'addon_name'    => 'Example Add-on',
				'admin_emails'  => $emails,
				'date'          => time(),
				'extended_data' => $extended_data,
				'message'       => $message,
				'notify_admin'  => $notify,
				'type'          => $type
			));
		
			Omnilogger::log($omnilog_entry);
		}
	}

}

// END