<?php

class Competitors_t
{
    public $first = NULL; // first team
    public $second = NULL; // opponent team of first team
    public $totalScore = NULL;
}

class WinnerDat_t
{
    public $name = "";
    public $totalScore = NULL;
    
    function __construct($name, $totalScore)
    {
        $this->name = $name;
        $this->totalScore = $totalScore;
    }
}

/*
@ This class contains informations about the matched up teams and the according day
*/
class MatchData
{
    private $day = NULL;
    private $competitors = NULL;
    
    function __construct($day, $competitors)
    {
        $this->day = $day;
        
        $tmpClone = array();
        
        foreach ($competitors as $val => $newVal)
        $tmpClone[$val] = clone $newVal;
        
        $this->competitors = $tmpClone;
    }
    
    public function getDay()
    {
        return $this->day;
    }
    
    public function getCompetitors()
    {
        return $this->competitors;
    }
}

/*
@ This class contains information about the winner teamer
*/
class MatchResultData
{
    private $score_first = NULL;
    private $score_second = NULL;
    
    function __construct($score_first, $score_second)
    {
        $this->score_first = $score_first;
        $this->score_second = $score_second;
    }
    
    public function getScoreFirst()
    {
        return $this->score_first;
    }
    
    public function getScoreSecond()
    {
        return $this->score_second;
    }
}

/*
@ This class contains information about the several teams according by a name
*/
class TeamData
{
    private $name = NULL;
    
    function __construct($name)
    {
        $this->name = $name;
    }
    
    public function getName()
    {
        return $this->name;
    }
}

/*
@ This class is doing all the calculations to find out who matches who, also we uses this class to output our result
*/
class RobinData
{
    private $teams = NULL; // all teams contained in an array
    private $competitors = NULL; // all teams with their included opponent team in an array
    private $matchesResults = NULL; // all match results in an array
    private $matches_data = NULL; // all data of competitors at their according day in an array
    private $totalRounds = 0;
    private $concurrentlyRounds = 0;
    private $totalDays = 0;
    private $odd = false;
    private $doubleRobin = false; // (hin und zurück)
    private $inited = false;
    
    function  __construct($TeamData, $matchesResults = NULL, $doubleRobin = true)
    {
        // Teams data is empty
        if(count($TeamData) <= 0)
        {
            echo "Teams data is empty";
            return;
        }

        // too less teams
        if(count($TeamData) <= 1)
        {
            echo "missing teams, too less teams";
            return;
        }
        
        // check duplicated names
        $duplicated = false;
        for($i = 0; $i<count($TeamData); $i++)
        {
            $name = $TeamData[$i]->GetName();
            for($j = $i + 1; $j<count($TeamData); $j++)
            {
                $name2 = $TeamData[$j]->GetName();
                if(!strcmp($name, $name2))
                {
                    echo "Duplicated name in Team Data: " . $name;
                    $duplicated = true;
                }
            }
        }
        
        if($duplicated)
        return;
        
        $this->doubleRobin = $doubleRobin;
        $this->teams = $TeamData;
        $this->matchesResults = $matchesResults;
        $this->CalculateRounds();
        $this->ConvertTeamsToCompetitors();
        if($this->getCompetitorSize() <= 0)
        {
            echo "Competitors size is <= NULL";
            return;
        }
        
        $this->CalculateDays();
        $this->inited = true;
    }
    
    private function is_odd()
    {
        return $this->odd;
    }
    
    private function is_double_robin()
    {
        return $this->doubleRobin;
    }
    
    /*
    @ This function just imports my team data into the competitors data which is mostly used to process data
    */
    private function ConvertTeamsToCompetitors()
    {
        if($this->is_odd())
        {
            // first slot is empty
            $this->competitors[($this->getTeamSize() / 2)] = new Competitors_t();
            $this->competitors[($this->getTeamSize() / 2)]->first = NULL;
            
            // fill first half
            for($i = 0; $i<($this->getTeamSize() / 2) - 1; $i++)
            {
                $this->competitors[$i] = new Competitors_t();
                $this->competitors[$i]->first = $this->teams[$i];
            }
            
            // fill second half just flip around
            for($i = $this->getTeamSize() - 1, $j = 0; $i>=($this->getTeamSize() / 2) - 1; $i--, $j++)
                $this->competitors[$j]->second = $this->teams[$i];
        }
        else
        {
            // fill first half
            for($i = 0; $i<($this->getTeamSize() / 2); $i++)
            {
                $this->competitors[$i] = new Competitors_t();
                $this->competitors[$i]->first = $this->teams[$i];
            }
            
            // fill second half just flip around
            for($i = $this->getTeamSize() - 1, $j = 0; $i>=($this->getTeamSize() / 2); $i--, $j++)
                $this->competitors[$j]->second = $this->teams[$i];
        }
    }
    
    /*
    @ This function goes through the circle method to find out who matches next
    */
    private function CircleRoation()
    {
        if($this->is_odd())
        {
            $tmpClone = array();
            
            foreach ($this->competitors as $val => $newVal)
            $tmpClone[$val] = clone $newVal;
            
            $firstLast =  $tmpClone[$this->getCompetitorSize() - 2]->first;
            $secondLast = $tmpClone[0]->second;
            
            // Move secondLast
            $this->competitors[$this->getCompetitorSize() - 1]->second = $firstLast;
            
            // Move firstLast
            $this->competitors[0]->first = $secondLast;
            
            //  Move first row
            for($i = 0; $i<$this->getCompetitorSize() - 2; $i++)
            $this->competitors[$i + 1]->first = $tmpClone[$i]->first;
            
            // Move second row
            for($i = $this->getCompetitorSize() - 1; $i > 0; $i--)
            $this->competitors[$i - 1]->second = $tmpClone[$i]->second;
        }
        else
        {
            $tmpClone = array();
            
            foreach ($this->competitors as $val => $newVal)
            $tmpClone[$val] = clone $newVal;
            
            $firstLast =  $tmpClone[$this->getCompetitorSize() - 1]->first;
            $secondLast = $tmpClone[0]->second;
            
            // Move secondLast
            $this->competitors[$this->getCompetitorSize() - 1]->second = $firstLast;
            
            // Move firstLast
            $this->competitors[1]->first = $secondLast;
            
            //  Move first row
            for($i = 1; $i<$this->getCompetitorSize() - 1; $i++)
            $this->competitors[$i + 1]->first = $tmpClone[$i]->first;
            
            // Move second row
            for($i = $this->getCompetitorSize() - 1; $i > 0; $i--)
            $this->competitors[$i - 1]->second = $tmpClone[$i]->second;
        }
    }
    
    /*
    @ This function calculates the total rounds, concurrently rounds and if the match is odd or even
    */
    private function CalculateRounds()
    {
        // (n / 2) * (n-1)
        $this->totalRounds = ($this->getTeamSize() / 2 * ($this->getTeamSize() - 1));
        if($this->getTeamSize() % 2 == 0) // is even
        $this->concurrentlyRounds = $this->getTeamSize() / 2;
        else // is odd - one competitor having no game
        {
            $this->concurrentlyRounds = (($this->getTeamSize() - 1) / 2);
            $this->odd = true;
        }
    }
    
    /*
    @ This function is used to swap first and second with each other in competitors array
    */
    private function swapCompetitors()
    {
        $tmpClone = array();
        
        foreach ($this->competitors as $val => $newVal)
        $tmpClone[$val] = clone $newVal;
        
        if($this->is_odd())
        {
            for($i = 0; $i<$this->getCompetitorSize(); $i++)
            {
                if($this->competitors[$i]->first)
                {
                    $this->competitors[$i]->first = $tmpClone[$i]->second;
                    $this->competitors[$i]->second = $tmpClone[$i]->first;
                }
            }
        }
        else
        {
            for($i = 0; $i<$this->getCompetitorSize(); $i++)
            {
                $this->competitors[$i]->first = $tmpClone[$i]->second;
                $this->competitors[$i]->second = $tmpClone[$i]->first;
            }
        }
    }
    
    /*
    @ This function is calculating the days of playing
    */
    private function CalculateDays()
    {
        // Create a clone of our teams array
        $clone = array();
        for($i = 0; $i<$this->getTeamSize(); $i++)
        array_push($clone, $this->teams[$i]);
        
        // move array until first and last do compare each other, each move demonstrates a day of playing
        $first = $this->competitors[0]->first;
        $second = $this->competitors[0]->second;
        
        $seek_finished = false;
        do
        {
            $this->matches_data[$this->totalDays] = new MatchData(($this->totalDays + 1), $this->competitors);

            // we have to rotate if we have more than 2 teams fighting each other
            if(count($this->competitors) > 1)
                $this->CircleRoation();

            $this->totalDays++;
            
            $tmpFirst = $this->competitors[0]->first;
            $tmpSecond = $this->competitors[0]->second;
            
            if($first == $tmpFirst && $second == $tmpSecond)
            $seek_finished = true;
            
        } while(!$seek_finished);
        
        // On double robin caluclate twice just backward
        if($this->is_double_robin())
        {
            $this->swapCompetitors();
            
            $first = $this->competitors[0]->first;
            $second = $this->competitors[0]->second;
            
            $seek_finished = false;
            do
            {
                $this->matches_data[$this->totalDays ] = new MatchData(($this->totalDays + 1), $this->competitors);
                
                // we have to rotate if we have more than 2 teams fighting each other
                if(count($this->competitors) > 1)
                $this->CircleRoation();

                $this->totalDays++;
                
                $tmpFirst = $this->competitors[0]->first;
                $tmpSecond = $this->competitors[0]->second;
                
                if($first == $tmpFirst && $second == $tmpSecond)
                $seek_finished = true;
                
            } while(!$seek_finished);
            
            // swap back so we can process data on the proper way
            $this->swapCompetitors();
        }
    }
    
    public function SetMatchResultData($data)
    {
        if(!$this->is_inited())
        return;

        $this->matchesResults = $data;
    }
    
    public function getTeamSize()
    {
        return count($this->teams);
    }
    
    public function getCompetitorSize()
    {
        return count($this->competitors);
    }
    
    public function getConcurrentlyRounds()
    {
        return $this->concurrentlyRounds;
    }
    
    public function is_inited()
    {
        return $this->inited;
    }
    
    /*
    @ Return Values
    0 = not played yet
    1 = loss
    2 = won
    3 = tie
    */
    private function has_won($team, $day)
    {
        if(!$team)
        return 0;
        
        $curday = ($day -1);

        if(!$this->matchesResults || !array_key_exists($curday, $this->matchesResults))
        return 0;

        if(!$this->matches_data || !array_key_exists($curday, $this->matches_data))
        return 0;

        for($j = 0; $j<count($this->matches_data[$curday]->getCompetitors()); $j++)
        {
            if(!$this->matches_data[$curday]->getCompetitors()[$j]->first)
            return 0;

            if(!array_key_exists($j, $this->matches_data[$curday]->getCompetitors()))
                continue;
            
            if($this->matches_data[$curday]->getCompetitors()[$j]->first == $team)
            {
                if(!array_key_exists($j, $this->matchesResults[$curday]))
                return 0;

                $competitors = $this->matchesResults[$curday][$j];
                if($competitors->getScoreFirst() > $competitors->getScoreSecond())
                return 2;
                else if($competitors->getScoreSecond() > $competitors->getScoreFirst())
                return 1;
                else
                return 3;
            }
            else if($this->matches_data[$curday]->getCompetitors()[$j]->second == $team)
            {
                if(!array_key_exists($j, $this->matchesResults[$curday]))
                return 0;

                $competitors = $this->matchesResults[$curday][$j];
                if($competitors->getScoreFirst() > $competitors->getScoreSecond())
                return 1;
                else if($competitors->getScoreSecond() > $competitors->getScoreFirst())
                return 2;
                else
                return 3;
            }
        }
        
        return 1;
    }
    
    /*
    @ Return Values
    has opponent = true
    none opponent = false
    */
    private function has_opponent($team, $day)
    {
        if(!$team)
        return false;
        
        $day--;
        if(!$this->matches_data[$day])
        return false;
        
        for($j = 0; $j<count($this->matches_data[$day]->getCompetitors()); $j++)
        {
            if(!$this->matches_data[$day]->getCompetitors()[$j]->first && $this->matches_data[$day]->getCompetitors()[$j]->second == $team)
            return false;
        }
        
        return true;
    }
    
    /*
    @ Return Value
    -1 = Error
    score of the match
    */
    private function getScore($team, $day)
    {
        if(!$team)
        return -1;
        
        $day--;
        if(!$this->matchesResults || !array_key_exists($day, $this->matchesResults))
        return -1;
        
        if(!array_key_exists($day, $this->matches_data))
        return -1;
        
        for($j = 0; $j<count($this->matches_data[$day]->getCompetitors()); $j++)
        {
            if(!array_key_exists($j, $this->matches_data[$day]->getCompetitors()))
            continue;
            
            if($this->matches_data[$day]->getCompetitors()[$j]->first == $team)
            {
                if(!array_key_exists($j, $this->matchesResults[$day]))
                    continue;

                $competitors = $this->matchesResults[$day][$j];
                return $competitors->getScoreFirst();
            }
            else if($this->matches_data[$day]->getCompetitors()[$j]->second == $team)
            {
                if(!array_key_exists($j, $this->matchesResults[$day]))
                continue;

                $competitors = $this->matchesResults[$day][$j];
                return $competitors->getScoreSecond();
            }
        }
        
        return -1;
    }
    
    /*
    // Ganz ehrlich, nicht die beste Lösung
    @ Return Value
    0 = having no winner
    1 = having a winner
    2 = having multiplie winners (same score)
    */
    private function has_winner()
    {
        // Check if we have enought match results otherwise we are not done with playing yet
        for($i = 0; $i<$this->totalDays; $i++)
        {   
            if(!$this->matchesResults || !array_key_exists($i, $this->matchesResults))
            return 0;
        }
        
        for($i = 0; $i<$this->totalDays; $i++)
        {   
            for($j = 0; $j < $this->getCompetitorSize(); $j++)
            {
                $first = $this->competitors[$j]->first;
                if(!$first)
                continue;
                
                $score = $this->getScore($first, ($i+1));
                if($score == -1)
                continue;
                
                $first->totalScore += $score;
            }
            
            for($j = 0; $j < $this->getCompetitorSize(); $j++)
            {
                $second = $this->competitors[$j]->second;
                $score = $this->getScore($second, ($i+1));
                if($score == -1)
                continue;
                
                $second->totalScore += $score;
            }
        }

        $AllTotalScore = array();
        for($i = 0; $i < $this->getCompetitorSize(); $i++)
        {
            $first = $this->competitors[$i]->first;
            if($first)
            array_push($AllTotalScore, new WinnerDat_t($first->getName(), $first->totalScore));
        }
        
        for($i = ($this->getCompetitorSize() - 1); $i >= 0; $i--)
        {
            $second = $this->competitors[$i]->second;
            array_push($AllTotalScore, new WinnerDat_t($second->getName(), $second->totalScore));
        }
        
        // Check for multiplie winners
        $MutliplieWinners = array();
        for($i = 0; $i < count($AllTotalScore); $i++)
        {
            for($j = $i; $j< count($AllTotalScore); $j++)
            {  
                if($AllTotalScore[$i]->totalScore == $AllTotalScore[$j]->totalScore)
                    return 2;
            }
        }
    
        $mostScore = NULL;
        for($i = 0; $i < count($AllTotalScore); $i++)
        {
            if(!$mostScore)
            {
                $mostScore = $AllTotalScore[$i];
                continue;
            }
          
            if($AllTotalScore[$i] > $mostScore)
            $mostScore = $AllTotalScore[$i];
        }

        if($mostScore != NULL)
            return 1;
        
        return 0;
    }
    
    /*
    // Ganz ehrlich, nicht die beste Lösung
    @ Return Value
    NULL = failure
    WinnerDat_t = winner data
    array of WinnerDat_t = mulitplie winners
    */
    private function get_winner()
    {
        $AllTotalScore = array();
        for($i = 0; $i < $this->getCompetitorSize(); $i++)
        {
            $first = $this->competitors[$i]->first;
            if($first)
            array_push($AllTotalScore, new WinnerDat_t($first->getName(), $first->totalScore));
        }
        
        for($i = ($this->getCompetitorSize() - 1); $i >= 0; $i--)
        {
            $second = $this->competitors[$i]->second;
            array_push($AllTotalScore, new WinnerDat_t($second->getName(), $second->totalScore));
        }
        
        // Check for multiplie winners
        $MutliplieWinners = array();
        for($i = 0; $i < count($AllTotalScore); $i++)
        {
            for($j = 0; $j< count($AllTotalScore); $j++)
            {  
                if($i == $j)
                continue;

                if($AllTotalScore[$i]->totalScore == $AllTotalScore[$j]->totalScore)
                {
                    array_push($MutliplieWinners, $AllTotalScore[$i]);
                    break;
                }
            }
        }
        
        if(count($MutliplieWinners) > 0)
        return $MutliplieWinners;
        
        $mostScore = new WinnerDat_t(NULL, NULL);
        for($i = 0; $i < count($AllTotalScore); $i++)
        {
            if(!$mostScore)
            {
                $mostScore = $AllTotalScore[$i];
                continue;
            }
            
            if($AllTotalScore[$i]->totalScore > $mostScore->totalScore)
            $mostScore = $AllTotalScore[$i];
        }
        
        if(count($mostScore) > 0)
        return $mostScore;
        
        return NULL;
    }
    
    /*
    @ This function goes through the algorithm to build the needed data for an output
    */
    public function ProcessResult()
    {
        if(!$this->is_inited())
        return;
        
        echo '<br><br><div class="Box">' . 'Insgesamte Runden: ' . $this->totalRounds . ' | gleichzeitig spielbare Runden: ' . $this->concurrentlyRounds . ' | Insgesamte Spieltage ' . $this->totalDays . '</div><br>';
        
        if($this->is_double_robin())
        echo '<br><br><div class="Box">Hinspiel</div><br><br>';
        
        $this->ProcessOutput();
        
        $res = $this->has_winner();
        if($res)
        $this->ProcessWinnerOutput($this->get_winner());
    }
    
    /*
    @ This function processes all data and build an output
    */
    private function ProcessOutput()
    {
        for($i = 0; $i<count($this->matches_data); $i++)
        {
            if($this->is_double_robin() && ($this->matches_data[$i]->getDay() - 1) == ($this->totalDays / 2))
            echo '<br><br><div class="Box">Rückspiel</div><br><br>';
            
            $output = '<table id="round-table"><tr><th>Spieltag :' . $this->matches_data[$i]->getDay() . '</th>';
            $output .= '<tr></tr>';
            
            for($j = 0; $j<count($this->matches_data[$i]->getCompetitors()); $j++)
            {
                $first = $this->matches_data[$i]->getCompetitors()[$j]->first;
                if(!$first)
                $output .= '<td>Empty</td>';
                else
                {
                    $res = $this->has_won($first, $this->matches_data[$i]->getDay());
                    if($res > 0)
                    {
                        $score  = $this->getScore($first, $this->matches_data[$i]->getDay());
                        if($score > -1)
                        {
                            if($res == 2)
                            $output .= '<td class="green">'. $first->getName() . ' [Score: '.$score.']</td>';
                            else if($res == 1)
                            $output .= '<td class="red">'. $first->getName() . ' [Score: '.$score.']</td>';
                            else if($res == 3)
                            $output .= '<td class="gray">'. $first->getName() . ' [Score: '.$score.']</td>';
                        }
                        else
                        {
                            $res = $this->has_won($first, $this->matches_data[$i]->getDay());
                            if($res == 2)
                            $output .= '<td class="green">'. $first->getName() . '</td>';
                            else if($res == 1)
                            $output .= '<td class="red">'. $first->getName() . '</td>';
                            else if($res == 3)
                            $output .= '<td class="gray">'. $first->getName() . '</td>';
                        }
                    }
                    else
                    $output .= '<td>'. $first->getName() . '</td>';
                }
            }
            
            $output .= '<tr></tr>';
            
            for($j = 0; $j<count($this->matches_data[$i]->getCompetitors()); $j++)
            {
                $second = $this->matches_data[$i]->getCompetitors()[$j]->second;
                
                $opponent = $this->has_opponent($second, $this->matches_data[$i]->getDay());
                if(!$opponent)
                $output .= '<td>'. $second->getName() . '</td>';
                else
                {
                    $res = $this->has_won($second, $this->matches_data[$i]->getDay());
                    if($res > 0)
                    {
                        $score  = $this->getScore($second, $this->matches_data[$i]->getDay());
                        if($score > -1)
                        {
                            $res = $this->has_won($second, $this->matches_data[$i]->getDay());
                            if($res == 2)
                            $output .= '<td class="green">'. $second->getName() . ' [Score: '.$score.']</td>';
                            else if($res == 1)
                            $output .= '<td class="red">'. $second->getName() . ' [Score: '.$score.']</td>';
                            else if($res == 3)
                            $output .= '<td class="gray">'. $second->getName() . ' [Score: '.$score.']</td>';
                        }
                        else
                        {
                            $res = $this->has_won($second, $this->matches_data[$i]->getDay());
                            if($res == 2)
                            $output .= '<td class="green">'. $second->getName() . '</td>';
                            else if($res == 1)
                            $output .= '<td class="red">'. $second->getName() . '</td>';
                            else if($res == 3)
                            $output .= '<td class="gray">'. $second->getName() . '</td>';
                        }
                    }
                    else
                    $output .= '<td>'. $second->getName() . '</td>';
                }
            }
            
            $output .= '</tr></table><br>';
            echo $output;
        }
        echo '<br>';
    }
    
    private function ProcessWinnerOutput($data)
    {
        if(!$data)
        return;
        
        if(is_array($data))
        {
            $output = '<div class="Box">All additionally first place holders are:<br>';
            for($i = 0; $i<count($data); $i++)
            {
                $singleData = $data[$i];
                $output .= 'Team: ' . $singleData->name . ' with a total score of ' . $singleData->totalScore . '<br>';
            }
            
            $output .= '</div>';
            echo $output;
        }
        else
        {
            $output = '<div class="Box">';
            $output .= 'Team: ' . $data->name . ' has won the tournament with a total score of ' . $data->totalScore;
            $output .= '</div>';
            echo $output;
        }
    }
}

?>