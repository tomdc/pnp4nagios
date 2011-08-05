<?php
#
# Copyright (c) 2006-2010 Joerg Linge (http://www.pnp4nagios.org)
# Template for check_disk
#
# RRDtool Options

foreach ($this->DS as $KEY=>$VAL) {
# set initial values
	$fmt = '%7.3lf';
	$pct = '';
	$upper = "";
	$maximum = "";
	$divis = 1;
	$return = '\n';
	$unit = "B";
	$label = $unit;
	if ($VAL['UNIT'] != "") {
		$unit = $VAL['UNIT'];
		$label = $unit;
		if ($VAL['UNIT'] == "%%") {
			$label = '%';
			$fmt = '%5.1lf';
			$pct = '%';
		}
	}
	if ($VAL['MAX'] != "") {
		# adjust value and unit, details in .../helpers/pnp.php
		$max = pnp::adjust_unit( $VAL['MAX'].$unit,1024,$fmt );
		$upper = "-u $max[1] ";
		$maximum = "of $max[1] $max[2]$pct used";
		$label = $max[2];
		$divis = $max[3];
		$return = '';
	}
	$ds_name[$KEY] = str_replace("_","/",$VAL['NAME']);
	# set graph labels
	$opt[$KEY]     = "--vertical-label $label -l 0 $upper --title \"Filesystem $ds_name[$KEY]\" ";
	# Graph Definitions
	$def[$KEY]     = rrd::def( "var1", $VAL['RRDFILE'], $VAL['DS'], "AVERAGE" ); 
	$def[$KEY]     .= rrd::def( "warning", $VAL['RRDFILE'], 'WARN', "AVERAGE" ); 
	$def[$KEY]     .= rrd::def( "critical", $VAL['RRDFILE'], 'CRIT', "AVERAGE" ); 
	$def[$KEY]     .= rrd::def( "max", $VAL['RRDFILE'], 'MAX', "AVERAGE" ); 
	# "normalize" graph values
	$def[$KEY]    .= rrd::cdef( "v_n","var1,$divis,/");
	$def[$KEY]    .= rrd::cdef( "w_n","warning,$divis,/");
	$def[$KEY]    .= rrd::cdef( "c_n","critical,$divis,/");
	$def[$KEY]    .= rrd::cdef( "m_n","max,$divis,/");
	$def[$KEY]    .= rrd::area( "v_n", "#c6c6c6",  $ds_name[$KEY] );
	# show values in legend
	$def[$KEY]    .= rrd::gprint( "v_n", "LAST", "$fmt $label$pct $maximum ");
	$def[$KEY]    .= rrd::gprint( "v_n", "AVERAGE", "$fmt $label$pct avg used $return\\n");
	# create max line and legend
	if ($VAL['MAX'] != "") {
		$def[$KEY] .= rrd::line1( "m_n", "#000000" , "FS Size "); 
		$def[$KEY] .= rrd::gprint( "m_n", "MAX", "$fmt $label$pct\\n" );
	}
        $def[$KEY]    .= rrd::line1( "w_n", "#009933" , "Warning " );
	$def[$KEY]    .= rrd::gprint( "w_n", "AVERAGE", "$fmt $label\\n");
        $def[$KEY]    .= rrd::line1( "c_n", "#FF0000" , "Critical" );
	$def[$KEY]    .= rrd::gprint( "c_n", "AVERAGE", "$fmt $label\\n");
}
?>
