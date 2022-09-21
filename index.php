<!-- 
Author: Tobias Staack (MYLIFEISMYHOME)
Libraries: JQUERY
Design: W3Schools
-->
<html>
<head>
<!-- Tabellen Design, sowie inputs und buttons stammen von W3Schools, aufgrund dessen das meine Designing Skills miserabel sind. -->
<link href="css/main.css" rel="stylesheet">
<script src="js/jquery.js"></script>
</head>
<body>
<div id="main">
<div id="input">
<p>Team 1: <input class="input" type="text" value="1"></p>
<p>Team 2: <input class="input" type="text" value="2"></p>
<p>Team 3: <input class="input" type="text" value="3"></p>
<p>Team 4: <input class="input" type="text" value="4"></p>
<p>Team 5: <input class="input" type="text" value="5"></p>
<p>Team 6: <input class="input" type="text" value="6"></p>
<p>Team 7: <input class="input" type="text" value="7"></p>
</div>
<button class="button" onclick="addTeam()">Team hinzufügen</button>
<button class="button" onclick="removeTeam()">Team entfernen</button>
<p>Hin- und zurück (Double Robin): <input type="checkbox" id="doubleRobin" checked></p>
<hr>
<div id="matchResult">
<p>Match Resultat [Spiel 1]</p>
<input class="input inline" type="text" placeholder="Score of Team 1"><input class="input inline" type="text" placeholder="Score of Team 2">
</div>
<button class="button" onclick="Process()">Daten verarbeiten</button>
<button class="button" onclick="addMatchResult()">Match Resultat hinzufügen</button>
<button class="button" onclick="removeMatchResult()">Match Resultat entfernen</button>
</div>

<div id="data">
</div>
<script>
function addTeam()
{
    var i = 1;
    $('#input').find('input:text').each(function() {
        i++;
    });
    
    $('#input').append('<p>Team ' + i +': <input class="input" type="text" value="'+i+'"></p>');
}

function removeTeam()
{
    var i = 1;
    $('#input').find('p').each(function() {
        i++;
    });
    
    var i2 = 1;
    $('#input').find('p').each(function() {
        i2++;
        
        if(i == i2)
        $(this).remove();
    });
}

function addMatchResult()
{
    var i = 1;
    $('#matchResult').find('p').each(function() {
        i++;
    });
    
    $('#matchResult').append('<p>Match Resultat [Spiel '+i+']</p><input class="input inline" type="text" placeholder="Score of Team 1"><input class="input inline" type="text" placeholder="Score of Team 2">');
}

// Dreckige Lösung, jedoch funktioniert es, naja frontend halt
function removeMatchResult()
{
    var i = 1;
    $('#matchResult').find('p').each(function() {
        i++;
    });
    
    var i2 = 1;
    $('#matchResult').find('p').each(function() {
        i2++;
        
        if(i == i2)
        $(this).remove();
    });
    
    for(var j = 0; j<2; j++)
    {
        i = 1;
        $('#matchResult').find('input:text').each(function() {
            i++;
        });
        
        i2 = 1;
        $('#matchResult').find('input:text').each(function() {
            i2++;
            
            if(i == i2)
            $(this).remove();
        });
    }
}

function Process()
{
    var teams = [];
    $('#input').find('input:text').each(function() {
        teams.push($(this).val());
    });

    var AllResults = [];
    $('#matchResult').find('input:text').each(function() {
        AllResults.push($(this).val());
    });

    var Match_Results = [];
    for(var i = 0; i<AllResults.length; i+=2)
        Match_Results.push(Array(AllResults[i], AllResults[(i+1)]));

    
    var jsonObj = {"double_robin": $('#doubleRobin').prop("checked"), "Teams": teams, "MatchResults": Match_Results};
    var json = JSON.stringify(jsonObj);
    console.log(json);
    
    $.ajax({
        url: 'process_input.php',
        data: {'data': json},
        type: 'post',
        success: function(data) {
            jQuery('#main').remove();
            jQuery('#data').append('<div class="legend"><p class="inline">Legende: </p><p class="inline">Gewonnen: </p><div class="small green"></div> | <p class="inline">Verloren: </p><div class="small red"></div> | <p class="inline">Unentschieden: </p><div class="small gray"></div></div>');
            jQuery('#data').append(data);
        },
        error: function() {
            alert('Failure on calling process_input.php');
        }
    });
}
</script>
</body>
</html>