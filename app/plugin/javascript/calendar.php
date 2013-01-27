<?php
class JavascriptJalaliCalendar extends BasePluginClass
{
    function InsertHeaders ()
    {
        ?>


<link rel="stylesheet" type="text/css" media="all"
	href="script.calendar.skins.aqua.theme.css" title="Aqua" />

<!-- import the Jalali Date Class script -->
<script type="text/javascript" src="script.calendar.jalali.js"></script>


<!-- import the calendar script -->
<script type="text/javascript" src="script.calendar.calendar.js"></script>

<!-- import the calendar script -->
<script type="text/javascript" src="script.calendar.calendar-setup.js"></script>

<!-- import the language module -->
<script type="text/javascript" src="script.calendar.lang.calendar-fa.js"></script>
<?php
    }
    function InsertCalendar ($Name = "", $ID = "")
    {
        
        if ($ID != '')
            $IDstr = "id='$ID'";
        if ($Name != '')
            $Namestr = "name='$Name'";
        $n = rand();
        ?>
<input dir='ltr' <?
        echo $IDstr?> type="text" <?php
        echo $Namestr?>
	readonly='readonly' size="18" />
<img id="date_btn_<?php echo $n?>" src="script.calendar.cal.png" title="انتخاب تاریخ"
	style="vertical-align: top;" />

<script type="text/javascript">
				Calendar.setup({
					inputField     :    "<?=$ID?>",  
					button         :    "date_btn_<?php echo $n?>",  
		       		ifFormat       :    "%Y-%m-%d %H:%M:%S",       
		       		showsTime      :    true,
        			dateType	   :	'jalali',
        			timeFormat     :    "24",
					weekNumbers    : false
				});
				
			</script>
<?php
    }
}
?>