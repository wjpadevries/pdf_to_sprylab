
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html lang="en">
<head>
    <title>Progress Bar</title>
</head>
<body>

<?php
require_once __DIR__ . '/classes/progressBar.class.php';
$pg = New progressBar;

print "Some other info as startup<br>";
print "<hr>";

$pg->insertDiv();
// Total processes
$pg->totalCount = 4;

// Loop through process
for($i=1; $i<=$pg->totalCount; $i++){
    $pg->detail = "processing file xxx$i";
    $pg->actualCount = $i;
    $pg->pbUpdate();
    
    // Sleep one second so we can see the delay
    sleep(1);
}

// Tell user that the process is completed
$pg->pbFinished();

//$pg->reset();
?>

And show some more text that will be shown when ready.
</body>
</html>
