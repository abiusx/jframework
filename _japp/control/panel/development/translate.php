<?php
class PanelDevelopmentTranslateController extends BaseControllerClass
{
    function Start ()
    {
    	$Target=$_GET['target'];
    	if (!array_key_exists($Target, j::$i18n->Languages))
			$Target=reset(j::$i18n->Languages);
    	
		if (count($_POST))
		{
			$Data=$_POST;
			$Sum=0;
			foreach ($Data['pid1'] AS $k=>$PID1)
			{
				if ($Data['pid2'][$k]==null and $Data['translation'][$k]=="") continue;
				if ($Data['pid2'][$k]) //edit translation
				{
//					j::SQL("UPDATE jfp_i18n SET Phrase=? WHERE ID=? LIMIT 1",$Data['translation'][$k],$Data['pid2'][$k]);
					$Sum+=j::$i18n->EditPhrase($Data['pid2'][$k], $Data['translation'][$k]);
				}
				else //insert translation 
				{
					$IID=j::$i18n->AddPhrase($Data['translation'][$k], $Target);
//					$IID=j::SQL("INSERT INTO jfp_i18n (Language,Phrase) VALUES (?,?)",$Target,$Data['translation'][$k]);
					if ($IID)
					{
						$Sum++;
//						j::SQL("INSERT INTO jfp_i18n_graph (ID1,ID2) VALUES (?,?)",$Data['pid1'][$k],$IID);
						j::$i18n->Link($Data['pid1'][$k], $IID);
					}
				}
			}
			$this->Affected=$Sum;
		}
    	
    	
    	if (array_key_exists($Target, j::$i18n->Languages))
    	{
    		if (isset($_GET['pid']))
    			$PID=$_GET['pid']*1;
    		if ($PID)
    			$x="AND P1.ID=?";
    		else
    			$x="";
    		$Query="SELECT 
    			P1.Phrase AS Phrase,
    			P1.Language AS Pivot,
    			P1.ID AS PID1,
    			P2.Phrase AS Translation,
    			P2.Language AS Target,
    			P2.ID AS PID2
    			 
    			FROM 
    		jfp_i18n AS P1
			LEFT JOIN 
			(
				jfp_i18n AS P2 JOIN 
				(
					SELECT ID1, ID2 FROM jfp_i18n_graph
					UNION ALL
					SELECT ID2, ID1 FROM jfp_i18n_graph
				) AS G
				ON (P2.ID=G.ID2 AND P2.Language=?)
			)
			ON (G.ID1=P1.ID)
			WHERE P1.Language!=?
			{$x}
			ORDER BY (P2.ID IS NULL) DESC,P2.Phrase=''
			
			";
    		if ($PID)
    			$Res=j::SQL($Query,$Target,$Target,$PID);
    		else
    			$Res=j::SQL($Query,$Target,$Target);
			$this->Phrases=$Res;    		
	    	$this->Target=$Target;
    	}
    	$this->Languages=j::$i18n->Languages;
    	
    	return $this->Present();
    }
}
?>