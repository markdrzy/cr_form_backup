<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Include cr_form_backup Core Mod
 */
require_once PATH_THIRD . 'cr_form_backup/mod.cr_form_backup' . EXT;

class Cr_form_backup_mcp {

	var $cr_noaa;
	var $modname = 'Cr_form_backup';
	var $short_modname = 'cr_form_backup';

	function __construct()
	{
		$this->EE =& get_instance();
		$this->cr_form_backup = new Cr_form_backup();
	}
	
	function index()
	{
		$this->EE->load->library('table');
		
		// Load the custom CSS
		$this->EE->cp->add_to_head('<link rel="stylesheet" href="'.URL_THIRD_THEMES.'cr_form_backup/css/style.css">');
		
		// Get Form Types
		$r = $this->EE->db->query("SELECT `action` FROM `{$this->EE->db->dbprefix}{$this->short_modname}` 
									GROUP BY `action`;");
		$forms = array();
		if ($r->num_rows() > 0)
		{
			foreach ($r->result_array() as $row)
			{
				$forms[] = $row['action'];
			}
		
			// Get Form Results
			$r = $this->EE->db->query("SELECT YEAR(`submitted_on`) as `Y` 
										FROM `{$this->EE->db->dbprefix}{$this->short_modname}` 
										ORDER BY `submitted_on` ASC LIMIT 1;")->result_array(); // get start
			$report_start_year = $r[0]['Y'];
			$report_result_set = array();
			for ($i = $report_start_year;$i <= date("Y"); $i++)
			{
				foreach ($forms as $f)
				{
					$q = <<< EOD
	
	SELECT max(January) as January
	,max(February) as February
	,max(March) as March
	,max(April) as April
	,max(May) as May
	,max(June) as June
	,max(July) as July
	,max(August) as August
	,max(September) as September
	,max(October) as October
	,max(November) as November
	,max(December) as December
	FROM
	(SELECT CASE month
	WHEN "January" THEN total_listings
	END as January
	, CASE month
	WHEN "February" THEN total_listings
	END as February
	, CASE month
	WHEN "March" THEN total_listings
	END as March
	, CASE month
	WHEN "April" THEN total_listings
	END as April
	, CASE month
	WHEN "May" THEN total_listings
	END as May
	, CASE month
	WHEN "June" THEN total_listings
	END as June
	, CASE month
	WHEN "July" THEN total_listings
	END as July
	, CASE month
	WHEN "August" THEN total_listings
	END as August
	, CASE month
	WHEN "September" THEN total_listings
	END as September
	, CASE month
	WHEN "October" THEN total_listings
	END as October
	, CASE month
	WHEN "November" THEN total_listings
	END as November
	, CASE month
	WHEN "December" THEN total_listings
	END as December
	FROM
	(SELECT CASE listed_month
	WHEN "1" THEN "January"
	WHEN "2" THEN "February"
	WHEN "3" THEN "March"
	WHEN "4" THEN "April"
	WHEN "5" THEN "May"
	WHEN "6" THEN "June"
	WHEN "7" THEN "July"
	WHEN "8" THEN "August"
	WHEN "9" THEN "September"
	WHEN "10" THEN "October"
	WHEN "11" THEN "November"
	WHEN "12" THEN "December"
	END as month
	, total_listings
	FROM
	(
	SELECT stuff_by_month.listed_month, count(stuff_by_month.listed_month) total_listings from
	(
	SELECT month(submitted_on) listed_month
	FROM exp_cr_form_backup WHERE year(submitted_on) = {$i} && action = '{$f}' ) stuff_by_month
	GROUP BY listed_month
	) monthly_totals
	) aggregates
	) final_pivot;
	
EOD;
					$report_result_set[$f][$i] = $this->EE->db->query($q)->result_array();
				}
			}
			
			$b = str_replace('&amp;','&',BASE);
			$a = '&'; // or AMP?
			$y = date('Y');
			$m = date('m');
			$js = <<< EOD
			
				function rangeLabel(c) {
					var sm = $('#form-list select.'+c+'[name=sm]').val();
					var sy = $('#form-list select.'+c+'[name=sy]').val().substr(2,2);
					var em = $('#form-list select.'+c+'[name=em]').val();
					var ey = $('#form-list select.'+c+'[name=ey]').val().substr(2,2);
					$('#form-list button#dl-'+c).text('Range: '+sm+'/'+sy+' - '+em+'/'+ey);
				}
			
				$('#form-list select[name=ey] option[value={$y}]').attr('selected','selected');
				$('#form-list select[name=em] option[value={$m}]').attr('selected','selected');
				
				$('#form-list select[name=ey]').each(function(){
					rangeLabel($(this).attr('class'));
				});
				
				$('#form-list select').change(function(){
					rangeLabel($(this).attr('class'));
				});
			
				$('#form-list button').click(function(){
					var act = $(this).attr('id');
					var frm = $(this).attr('class');
					var mth = (act == 'dla-'+frm)? 'all': 'range';
					var sd = '',ed = '';
					if (mth == 'range') {
						sd = $('#sy-'+frm).val() + $('#sm-'+frm).val();
						ed = $('#ey-'+frm).val() + $('#em-'+frm).val();
						if (sd > ed) {
							var sdo = sd,sd = ed,ed = sdo;
						}
					}
					// console.log([act,frm,'dla-'+frm,mth,sd,ed]);
					
					var dl_url = '{$b}{$a}C=addons_modules{$a}M=show_module_cp{$a}'
									+'module={$this->modname}{$a}method=download_data{$a}'
									+'frm=' + frm + '{$a}sd=' + sd + '{$a}ed=' + ed;
					
					if ( ! $('#dl-iframe').length ) {
						$('body').append('<iframe id="dl-iframe" style="visibility:hidden">');
					}
					$('#dl-iframe').attr('src',dl_url);
				});
			
EOD;
			
			$this->EE->javascript->output($js);
			$this->EE->javascript->compile();
			
			$vars = array(
				'screen_name'=>$this->EE->session->userdata['screen_name'],
				'forms'=>$forms,
				'report_start_year'=>$report_start_year,
				'report_data'=>$report_result_set
			);
			return $this->EE->load->view('index', $vars, TRUE);
			
		} else {
		
			$vars = array();
			return $this->EE->load->view('index-empty', $vars, TRUE);
		
		}
	}
	
	function download_data()
	{
		$act = $this->EE->input->get('frm');
		$sd = $this->EE->input->get('sd');
		$ed = $this->EE->input->get('ed');
		$this->cr_form_backup->download_data($act,$sd,$ed);
		die();
	}

}

// END