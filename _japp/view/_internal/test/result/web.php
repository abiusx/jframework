<?php
/**
 * need to supply me with $result and $profiler
 * first one being test result object of PHPUnit and second an instance of profiler
 */
function DumpResultRows($ResultArray,$BackgroundColor,$Color,$Text,$Odd=false)
{
	if (count($ResultArray)):?>
	<tr style='background-color:<?php exho ($BackgroundColor);?>;color:<?php exho ($Color)?>;font-weight:bold;'>
	<td colspan='4' >
	<?php exho (count($ResultArray)); 
	echo " {$Text}";
	?>
	</td>
	<?php 
		$n=0;
		foreach ($ResultArray as $test)
		{
			echo "<tr>";
			echo "<td width='50' align='center'>";
			echo ++$n;
			echo "</td>";
			$t=$test->failedTest();
			echo "<td>";
			echo get_class($t);
			echo " :: ";
			echo $t->getName();
			echo "</td>";
			echo "<td>";
			$e=new Exception();
 			echo $test->getExceptionAsString();
			echo "</td>";
			echo "<td>";
			$trace=($test->thrownException()->getTrace());
			if ($Odd)
			{
				$file=$trace[0]['file'];
				$line=$trace[0]['line'];
			}
			else
			{
				$file=$trace[3]['file'];
				$line=$trace[3]['line'];
			}
			$dir=substr($file, 0,strlen(jf::root()));
			$dir=substr($file,0,strpos($file,DIRECTORY_SEPARATOR,strlen($dir)+1));
			$dir=substr($file,0,strpos($file,DIRECTORY_SEPARATOR,strlen($dir)+1));
			$filename=substr($file,strlen($dir)+1);
			echo $dir."/<strong>{$filename}</strong> :{$line}";
			echo "</td>";
			echo "</tr>";
		}
		?>
	</tr>
	<?php 
	endif;	
}
?>
		<h1>Test Results</h1>
		<table border='1' cellpadding='5' cellspacing='0' width='100%' >
		<?php if (count ($result->passed())):?>
		<tr style='background-color:green;color:white;font-weight:bold;'>
		<td colspan='4' >
		<?php exho (count($result->passed())); ?> Tests Passed
		</td>
		</tr>
		<?php
		endif;
		DumpResultRows($result->failures(), "red", "white", "Tests Failed");
		DumpResultRows($result->errors(), "#FF7700", "white", "Tests Have Errors");
		DumpResultRows($result->notImplemented(), "yellow", "blue", "Tests Not Implemented",true);
		DumpResultRows($result->skipped(), "gray", "white", "Tests Skipped",true);
		?>

		<tr style='background-color:black;color:white;text-align:right;'>
		<td colspan='4'>
		<span style='float:left;'>
		Time: <?php echo $profiler->Timer()?> seconds
		</span> 
		Total: <?php echo ($result->count());?> Tests 
		in
		<?php echo count(\jf\TestLauncher::$TestFiles);?> Files
		</td>
		</tr>
		
		
		
		
</table>

<?php 
?>