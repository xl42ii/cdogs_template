<!doctype HTML public "-//W3C//DTD HTML 4.01 Transitional//EN">
<?php

function fix_tex($name) {
    // turns "\\alpha" to "\alpha", and "\\'" to "\'" 
    $phpstuff = array("\\\\","\'","\\\"");
    $texstuff = array("\\","'","\"");
    $name = str_replace($phpstuff, $texstuff,$name);
    return $name;
}

function read_markup($name) { // converts the markup language to fields for reading text
  $dir = '../.submissions/';
  $fname = $dir.$name.".txt";
  $pdfname = $name.".pdf";
  error_reporting(~E_WARNING); // don't pass error message to screen if file doesn't exist
  $handle = fopen($fname,'r') or die("Click on a speaker to see their abstract</td></tr></table></body></html>");
  $abstract = fread($handle, filesize($fname));

  // =================================================================
  // Parse  file
  $rb = preg_quote('> ','>');
  $lb = preg_quote('< ','<');
  $slash = preg_quote('/','/');

  // ----------------------------------------------------------------
  // TITLE OF TALK
  // ----------------------------------------------------------------
  $foo = preg_split('/\<title\>/',$abstract);
  $foo = preg_split('/\<\/title\>/',$foo[1]);
  $title = tex2html($foo[0]);
  // ----------------------------------------------------------------

  // ----------------------------------------------------------------
  // SPEAKER OF TALK, AND RELEVANT INFO
  // ----------------------------------------------------------------
  $foo =  preg_split('/\<speaker\>/',$abstract);
  $foo = preg_split('/\<\/speaker\>/',$foo[1]);
  $speaker = $foo[0];

  // get stuff out of speaker like ...

  // ...name 
  
  $foo =  preg_split('/\<name\>/',$speaker);
  $foo = preg_split('/\<\/name\>/',$foo[1]);
  $speak_name = tex2html($foo[0]);

  // ..affiliations

  $foo =  preg_split('/\<affil_numbers\>/',$speaker);
  $foo = preg_split('/\<\/affil_numbers\>/',$foo[1]);
  $speak_num = $foo[0];

  // ..email & email post locations

  $foo =  preg_split('/\<email\>/',$speaker);
  $foo = preg_split('/\<\/email\>/',$foo[1]);
  $email = $foo[0];

  $foo =  preg_split('/\<address\>/',$email);
  $foo = preg_split('/\<\/address\>/',$foo[1]);
  $address = $foo[0];

  $foo =  preg_split('/\<post_web\>/',$email);
  $foo = preg_split('/\<\/post_web\>/',$foo[1]);
  $post = $foo[0];

  if(preg_match('/yes/',$post)) {
    $email = "Speaker can be reached at ".$address;
  }
  else {$email = "";}
  // ----------------------------------------------------------------

  // ----------------------------------------------------------------
  // CO-AUTHORS AND RELEVANT INFO
  // ----------------------------------------------------------------
  $foobar = preg_split('/\<coauthor\>/',$abstract);
  $j = 1;
  $coauth_flag = 0;

  while($j < count($foobar)) {
    $i = $j - 1;
    $foo = preg_split('/\<\/coauthor\>/',$foobar[$j]);
    $coauthor = $foo[0];

    // ...name 
  
    $foo =  preg_split('/\<name\>/',$coauthor);
    $foo = preg_split('/\<\/name\>/',$foo[1]);
    $coauth_name[$i] = ", ".tex2html($foo[0]);

    // ..affiliations

    $foo =  preg_split('/\<affil_numbers\>/',$coauthor);
    $foo = preg_split('/\<\/affil_numbers\>/',$foo[1]);
    $coauth_num[$i] = $foo[0];

    $j++;
    $coauth_flag = 1;
  }
  // ----------------------------------------------------------------

  // ----------------------------------------------------------------
  // AFFILIATIONS
  // ----------------------------------------------------------------
  $foobar = preg_split('/\<affiliation\>/',$abstract);
  $j = 1;
  while($j < count($foobar)) {
    $i = $j - 1;
    $foo = preg_split('/\<\/affiliation\>/',$foobar[$j]);
    $affil = $foo[0];

    // ...name 
    $foo = preg_split('/\<name\>/',$affil);
    $foo = preg_split('/\<\/name\>/',$foo[1]);
    $affil_name[$i] = tex2html($foo[0]);

    // ..affiliations

    $foo =  preg_split('/\<affil_number\>/',$affil);
    $foo = preg_split('/\<\/affil_number\>/',$foo[1]);
    $affil_num[$i] = $foo[0];

    $j++;
  }
  // ----------------------------------------------------------------

  // ----------------------------------------------------------------
  // TEXT OF ABSTRACT
  // ----------------------------------------------------------------

  $foo = preg_split('/\<text\>/',$abstract);
  $foo = preg_split('/\<\/text\>/',$foo[1]);
  $text = tex2html($foo[0]);
  // ---------------------------------------------------------------
  print '<p><a class=plain target="main" href="' .$pdfname .'">View PDF</a></p>';

  print "<center><p class=title>".$title."</p>";
  print "<p clas=author><u>".$speak_name."</u><sup>".$speak_num."</sup>";
  if ($coauth_flag) {
    $i = 0;
    while ($i < count($coauth_name)) {
      print $coauth_name[$i]."<sup>".$coauth_num[$i]."</sup>";
      ++$i;
    }
    print "</p>";
  }
  $i = 0;
  print "<p class=author>";
  while ($i < count($affil_name)) {
     print "<sup>".$affil_num[$i]."</sup>".$affil_name[$i]."<br>";
     ++$i;
  }
  print "</p>";

  if (count(split('.',$email)) > 1) {
    print "<p class=em>".$email."</p>";
  }
  print "</center>";

  print "<div class=abstract>".$text."</p>";

}

function tex2html($s) { // Takes the input string, $s, and converts it to html

// Items to cover
// * paragraphs
$s = preg_replace("/(\\n)/",'</p><p>',$s);

// * accents
$slash = preg_quote('\\','/');
$fslash = preg_quote('/','/');
$squig_open = preg_quote('{','/');
$squig_close = preg_quote('}','/');

$and = preg_quote('&','/');

$s = preg_replace("/\\'{(.)}/", '$1acute;', $s);
$s = preg_replace("/\\`{(.)}/", '$1grave;', $s);
$s = preg_replace("/\\^{(.)}/", '$1circ;', $s);
$s = preg_replace("/\\\"{(.)}/", '$1uml;', $s);
$s = preg_replace("/c{(.)}/", '$1cedil;', $s);

// * superscripts
$s = preg_replace("/\^\{([^\}]+)\}/",'<sup>$1</sup>',$s);
$s = preg_replace("/\^(.)/",'<sup>$1</sup>',$s);

// * subscripts
$s = preg_replace("/\_\{([^\}]+)\}/",'<sub>$1</sub>',$s);
$s = preg_replace("/\_(.)/",'<sub>$1</sub>',$s);

// * math
$dol = preg_quote('$','/');
$s = preg_replace("/".$dol."([^".$dol."]+)".$dol."/",'<em>$1</em>', $s);

// * greek symbols

$s = preg_replace("/".$slash."/",$and,$s);

// * \sl and \rm pairs

$s = preg_replace("/".$and."sl/","<i>",$s);
$s = preg_replace("/".$and."rm/","</i>",$s);

// * percent

$s = preg_replace("/".$and."%/","%",$s);

return $s;
}

?>

<html lang="en">
<head>
<META http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>C-DOGS</title>
<link type="text/css" rel="stylesheet" href="../cdogs.css">
</head>
<body>
<table border="0" cellpadding="3" cellspacing="5" width ="100%">
<tr>
<td colspan=3 style="background-color:rgb(222,222,222);color:black">
<br><center><p class="titlehead">C-DOGS YYYY Abstracts<br>(note: some symbols/accents may look strange)</p> 
</center><br>
</td>
</tr>
</table>

<table border="0" cellpadding="3" cellspacing="5" width ="100%"> 
<tr style="background-color:rgb(244,255,255);color:0066ff"> 
<td width="25%" valign=top>list of speakers<br>
<?php readfile('speaker_list.html'); ?>

<!--
<br><br>:: <a class="plain" href="../schedule/schedule.php" target="main">Schedule</a>
-->

</td> 
<td width="75%" valign=top> 
<?php $abs = $_GET["abs"];
      read_markup($abs); ?>
</td>
</tr></table>
</body>
</html>
