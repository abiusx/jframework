<?php
class JavascriptLiveDateTime extends BasePluginClass
{
    function InsertTimer ($Year, $Month, $Day, $Hour, $Minute, $Second, $ID = "Timer")
    {
        ?>
<script src="script.persiantimer.js"></script>
<span dir='ltr' id='<?php
        echo $ID; ?>'></span>
<script>
		PersianTimer.set(<?php echo $Year?>,
		<?php echo $Month?>,<?php
        echo $Day; ?>,<?php
        echo $Hour; ?>,<?php
        echo $Minute; ?>,<?php
        echo $Second; ?>);
		PersianTimer.assign('<?php   echo $ID;    ; ?>');
		PersianTimer.play();
		</script>
<?php
    }
}
; ?>