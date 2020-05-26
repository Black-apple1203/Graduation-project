<?php if (!defined('THINK_PATH')) exit();?><div id="line_box"></div>
<script type="text/javascript">
	FusionCharts.ready(function(){
		var revenueChart = new FusionCharts({
			"type": "msline",
			"height":"250px",
			"renderAt": "line_box",
			"width":"99%",
			"dataFormat": "xml",
			"showLegend":"1",
			"dataSource": '<?php echo ($line_xml); ?>',
		});
		revenueChart.render();
	});
</script>