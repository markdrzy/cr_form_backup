<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Include cr_form_backup Core Mod
 */
require_once PATH_THIRD . 'cr_form_backup/mod.cr_form_backup' . EXT;

class Cr_form_backup_upd {

	var $version = '0.3';
	var $modname = 'Cr_form_backup';
	var $short_modname = 'cr_form_backup';
	var $cr_form_backup;
	
	function __construct()
	{
		$this->EE =& get_instance();
		$this->cr_form_backup = new Cr_form_backup();
	}
	
	function install()
	{
		$data = array(
			'module_name'			=> $this->modname,
			'module_version'		=> $this->version,
			'has_cp_backend'		=> 'y',
			'has_publish_fields'	=> 'n'
		);
		$this->EE->db->insert('modules',$data);
		$mod_id = $this->EE->db->insert_id();
		
		// Fire up the forge
		$this->EE->load->dbforge();
		
		// Check for and Create ModuleMeta Table if Needed
		$r = $this->EE->db->query("SELECT COUNT(*) AS `table_exists` FROM `information_schema`.`tables` 
									WHERE `table_schema` = '{$this->EE->db->database}' 
									&& `table_name` = '{$this->EE->db->dbprefix}cr_module_meta';");
		if ($r->row('table_exists') == '0')
		{
			$modmeta_fields = array(
				'id'			=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => TRUE),
				'mod_id'		=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE),
				'mod_name'		=> array('type' => 'varchar', 'constraint' => '32'),
				'mod_settings'	=> array('type' => 'text')
			);
			$this->EE->dbforge->add_field($modmeta_fields);
			$this->EE->dbforge->add_key('id',TRUE);
			$this->EE->dbforge->create_table('cr_module_meta',TRUE);
		}
		$mm_data = array(
			'id'			=> '',
			'mod_id'		=> $mod_id,
			'mod_name'		=> $this->modname,
			'mod_settings'	=> 'a:0:{}'
		);
		$this->EE->db->insert('cr_module_meta',$mm_data);
		
		// Create Module Actions
		$actions = array('backup_submission');
		foreach( $actions as $a ){ $this->EE->db->insert('actions',array('class'=>$this->modname,'method'=>$a)); }
		
		// Create Backup Table
		$backup_fields = array(
			'submission_id'		=> array('type' => 'int','constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => TRUE),
			'action'			=> array('type' => 'varchar','constraint' => '32'),
			'post_data'			=> array('type' => 'text'),
			'submitted_on'		=> array('type' => 'datetime')
		);
		$this->EE->dbforge->add_field($backup_fields);
		$this->EE->dbforge->add_key('submission_id',TRUE);
		$this->EE->dbforge->create_table($this->short_modname);
		$this->EE->db->query('CREATE INDEX `backup_action_index` 
								ON `'.$this->EE->db->dbprefix.$this->short_modname.'` (`action`);');
		
		return TRUE;
	}
	
	function update($current = '')
	{
		return FALSE;
	}
	
	function uninstall()
	{
		// Delete from Actions Table
		$this->EE->db->where('class',$this->modname);
		$this->EE->db->delete('actions');
		
		// Delete from Modules Table
		$this->EE->db->select('module_id');
		$query = $this->EE->db->get_where('modules',array('module_name'=>$this->modname));
		$this->EE->db->where('module_id',$query->row('module_id'));
		$this->EE->db->delete('module_member_groups');
		$this->EE->db->where('module_name',$this->modname);
		$this->EE->db->delete('modules');
		
		// Fire up the forge
		$this->EE->load->dbforge();
		
		// Drop ModMeta Table (if it's not being used otherwise)
		$this->EE->db->where('mod_name',$this->modname);
		$this->EE->db->delete('cr_module_meta');
		$this->EE->db->select('mod_id');
		$q = $this->EE->db->get('cr_module_meta');
		if ($q->num_rows() == 0)
		{
			$this->EE->dbforge->drop_table('cr_module_meta');
		}
		
		// Drop Backup Table
		$this->EE->dbforge->drop_table($this->short_modname);
		
		return TRUE;
	}

}

// END