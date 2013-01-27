<?php
class JavascriptDockToolbar extends BasePluginClass
{
	function InsertHeaders()
	{
		?>
<link rel="stylesheet" type="text/css" media="all"
	href="script.docktoolbar.docktoolbar.css" />
<script src="script.instanttooltip.js"></script>
<script src="script.docktoolbar.docktoolbar-1.js"></script>
<?php
	}
	public function InitDock()
	{
		?>
<script>
$(document).ready(function(){

<?php
	}
	public function AddItem($img, $tooltip)
	{
		
		?>
		DockToolbar.add('<?=$img?>','<?=$tooltip?>');
<?php
	}
	public function StartDock($callback,$basewidth=48,$baseheight=48,$zoomfactor=2)
	{
		?>
		DockToolbar.start(<?=$callback?>,<?php echo $basewidth.",".$baseheight.",".$zoomfactor?>);

});
</script>

<?php
	}
}
?>
