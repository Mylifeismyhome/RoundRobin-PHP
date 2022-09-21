<?php
include 'robin.php';

function BuildMatchResult($MatchResults, $Robin)
{
    if(!$Robin->is_inited())
    return;

    // Match Result data
    $tmpMatchResult = array();
    for($i = 0; $i<count($MatchResults); $i++)
    {
        $data = new MatchResultData($MatchResults[$i][0], $MatchResults[$i][1]);
        array_push($tmpMatchResult, $data);
    }
    
    $MatchAllResults = array();
    for($i = 0; $i<count($tmpMatchResult); $i+=$Robin->getConcurrentlyRounds())
    {
        $ArrayDay = array();
        for($j = 0; $j<$Robin->getConcurrentlyRounds(); $j++)
        {
            if(!array_key_exists(($i+$j), $tmpMatchResult))
            continue;

            array_push($ArrayDay, $tmpMatchResult[($i+$j)]);
        }
        array_push($MatchAllResults, $ArrayDay);
    }
    
    return $MatchAllResults;
}

$Teams = array();

$data = $_POST['data'];
$jsonObj = json_decode($data);
for($i = 0; $i<count($jsonObj->Teams); $i++)
array_push($Teams, new TeamData($jsonObj->Teams[$i]));

$doubleRobin = $jsonObj->double_robin;

$Robin = new RobinData($Teams, NULL, $doubleRobin);
$Robin->SetMatchResultData(BuildMatchResult($jsonObj->MatchResults, $Robin));
$Robin->ProcessResult();
?>