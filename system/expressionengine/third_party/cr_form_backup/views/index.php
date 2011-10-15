<?php


// Stats Table
$stats_table = '<table id="form-stats">';
foreach ($report_data as $act => $act_data)
{
	$stats_table .= '<tr class="form-head"><th colspan="13">Form: '.$act.'</th></tr>';
	$stats_table .= '<tr class="date-head"><td></td><th>Jan</th><th>Feb</th><th>Mar</th><th>Apr</th>'
					.'<th>May</th><th>Jun</th><th>Jul</th><th>Aug</th>'
					.'<th>Sep</th><th>Oct</th><th>Nov</th><th>Dec</th></tr>';
	foreach ($act_data as $year => $year_data)
	{
		$stats_row = '<tr class="form-stats"><th>'.$year.'</th>'
						.'<td>'.$year_data[0]['January'].'</td><td>'.$year_data[0]['February'].'</td>'
						.'<td>'.$year_data[0]['March'].'</td><td>'.$year_data[0]['April'].'</td>'
						.'<td>'.$year_data[0]['May'].'</td><td>'.$year_data[0]['June'].'</td>'
						.'<td>'.$year_data[0]['July'].'</td><td>'.$year_data[0]['August'].'</td>'
						.'<td>'.$year_data[0]['September'].'</td><td>'.$year_data[0]['October'].'</td>'
						.'<td>'.$year_data[0]['November'].'</td><td>'.$year_data[0]['December'].'</td></tr>';
		$stats_table .= str_replace('<td></td>','<td>0</td>',$stats_row);
	}
}
$stats_table .= '</table>';



// Action Table
$mlist = <<< EOD
	<option value="01">January</option>
	<option value="02">February</option>
	<option value="03">March</option>
	<option value="04">April</option>
	<option value="05">May</option>
	<option value="06">June</option>
	<option value="07">July</option>
	<option value="08">August</option>
	<option value="09">September</option>
	<option value="10">October</option>
	<option value="11">November</option>
	<option value="12">December</option>
EOD;

$ylist = '';
for ($i = $report_start_year;$i <= date('Y');$i++)
{
$ylist .= '	<option value="'.$i.'">'.$i.'</option>';
}

$this->table->set_template(array('table_open'=>'<table id="form-list">'));
$this->table->set_heading(
	'Form','Start Date','End Date',''
);
foreach ($forms as $f)
{
	$this->table->add_row(
		$f,
		'<select name="sm" id="sm-'.$f.'" class="'.$f.'">'.$mlist.'</select> '
			.'<select name="sy" id="sy-'.$f.'" class="'.$f.'">'.$ylist.'</select>',
		'<select name="em" id="em-'.$f.'" class="'.$f.'">'.$mlist.'</select> '
			.'<select name="ey" id="ey-'.$f.'" class="'.$f.'">'.$ylist.'</select>',
		'<button id="dl-'.$f.'" class="'.$f.'">Download</button>',
		'<button id="dla-'.$f.'" class="'.$f.'">Download All</button>'
	);
}

$action_table = $this->table->generate();
$this->table->clear();

?>
<!-- <h1>O HAI <?=strtoupper($screen_name)?></h1> -->
<?=$action_table?>
<?=$stats_table?>