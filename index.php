<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>Catalog Markup Documentation</title>
	<style type='text/css'>
		body {
			width: 700px;
		}
	</style>
</head>
<body>

<h1>Catalog Markup Format</h1>

<h2>Format Description</h2>
<p>Course and section descriptions in the catalog should be primarily plain text. If a second paragraph is needed, use two new lines. Descriptions should not have any other text-formatting other than bold and italics as described below.</p>

<p>This text format is chosen to allow good legibility when displayed as plain text in Banner as well as allow for needed formatting when displaying on the web. This format is also simple and easy to type.</p>

<p>Bold formatting can be applied to text by surrounding a word or phrase with asterisks. Similarly, surrounding a word or phrase with forward-slashes will apply italic formatting.</p>

<ul>
	<li>Surround italic text with forward-slashes: <code>/Italic text/</code></li>
	<li>Surround bold text with asterisks: <code>*Bold text*</code></li>
	<li>There should not be a space between the asterisks or slashes and the word or phrase. If a space exists, no formatting will be applied.
		<br/>Correct: &nbsp; <code>/italic text/</code>
		<br/>Incorrect: <code>/ italic text /</code>
	</li>
	<li>Punctuation can be inside or outside of the asterisks or slashes.</li>
	<li>Slashes or asterisks surrounded by letters or numbers will not cause formatting changes: <code>normal/text normal*text</code></li>
	<li>Use two new-lines to separate paragraphs</li>
</ul>

<h2>Test your text</h2>
<h3>Input:</h3>
<?php
if (isset($_POST['sample_text']) && strlen($_POST['sample_text'])) {
	$sampleText = $_POST['sample_text'];
} else {
	$sampleText = "This is some text. Shakespeare wrote /The Merchant of Venice/ as well as /Macbeth/. Words can have slashes in them such as AC/DC, but this does not indicate italics. 

Spaces around slashes such as this / don't cause italics either. Quotes may be /\"used inside slashes\",/ or \"/outside of them/\". *Bold Text* should have asterisk characters around it. Like slashes, * can be used surrounded by spaces, or surrounded by letters or numbers and not cause bold formatting: 4*5 = 20 or 4 * 5 = 20. Numbers as well as text can be bold *42* or italic /85/";
}

$sampleText = htmlspecialchars($sampleText);
?>

<form action='index.php' method='post'>
	<textarea rows='10' cols='80' name='sample_text'><?php echo $sampleText; ?></textarea>
	<br/><input type='submit' value='Submit'/> <a href='index.php'><button>Reset to example</button></a>
</form>

<h3>Output:</h3>
<p>
<?php

error_reporting(E_WARNING);
include_once(dirname(__FILE__)."/fsmparser/fsmparserclass.inc.php");
$parser=new FSMParser();

//---------Programming the FSM:

/*********************************************************
 * Normal state
 *********************************************************/

// Enter from unknown into normal state if the first character is not a slash or bold.
$parser->FSM('/[^\/\*]/s','echo $STRING;','CDATA','UNKNOWN');

//In normal state, catch all other data
$parser->FSM('/./s','echo $STRING;','CDATA','CDATA');

/*********************************************************
 * Italic
 *********************************************************/
// Enter into Italic if at the begining of the line.
$parser->FSM(
	'/^\/\w/',
	'preg_match("/^\/(\w)/", $STRING, $m); echo "<em>".$m[1];',
	'ITALIC',
	'UNKNOWN');

//In normal state, catch italic start
$parser->FSM(
	'/[^\w.:\/]\/\w/',
	'preg_match("/([^\w.])\/(\w)/", $STRING, $m); echo $m[1]."<em>".$m[2];',
	'ITALIC',
	'CDATA');

// Close out of italic state back to normal
$parser->FSM(
	'/\w\/\W/',
	'preg_match("/(\w)\/(\W)/", $STRING, $m); echo $m[1]."</em>".$m[2];',
	'CDATA',
	'ITALIC');

//In normal state, catch italic start for whitespace+non-word
$parser->FSM(
	'/\s\/[^\s]/',
	'preg_match("/(\s)\/([^\s])/", $STRING, $m); echo $m[1]."<em>".$m[2];',
	'ITALIC',
	'CDATA');

// Close out of italic state back to normal for whitespace+non-word
$parser->FSM(
	'/[^\s]\/\s/',
	'preg_match("/([^\s])\/(\s)/", $STRING, $m); echo $m[1]."</em>".$m[2];',
	'CDATA',
	'ITALIC');

// Close out of italic state back to normal if italic at the very end
$parser->FSM(
	'/\w\/$/',
	'preg_match("/(\w)\/$/", $STRING, $m); echo $m[1]."</em>";',
	'CDATA',
	'ITALIC');
	
// Close out of italic state back to normal if there is no closing mark.
$parser->FSM(
	'/.$/',
	'preg_match("/(\w)$/", $STRING, $m); echo $m[1]."</em>";',
	'CDATA',
	'ITALIC');

//In italic state, catch all other data
$parser->FSM('/./s','echo $STRING;','ITALIC','ITALIC');


/*********************************************************
 * Bold
 *********************************************************/

// Enter into Bold if at the begining of the line.
$parser->FSM(
	'/^\*\w/',
	'preg_match("/^\*(\w)/", $STRING, $m); echo "<strong>".$m[1];',
	'BOLD',
	'UNKNOWN');

//In normal state, catch bold start
$parser->FSM(
	'/[^\w.]\*\w/',
	'preg_match("/(\W)\*(\w)/", $STRING, $m); echo $m[1]."<strong>".$m[2];',
	'BOLD',
	'CDATA');
	
// Close out of bold state back to normal
$parser->FSM(
	'/\w\*\W/',
	'preg_match("/(\w)\*(\W)/", $STRING, $m); echo $m[1]."</strong>".$m[2];',
	'CDATA',
	'BOLD');

//In normal state, catch bold start for whitespace+non-word
$parser->FSM(
	'/\s\*[^\s]/',
	'preg_match("/(\s)\*([^\s])/", $STRING, $m); echo $m[1]."<strong>".$m[2];',
	'BOLD',
	'CDATA');

// Close out of bold state back to normal for whitespace+non-word
$parser->FSM(
	'/[^\s]\*\s/',
	'preg_match("/([^\s])\*(\s)/", $STRING, $m); echo $m[1]."</strong>".$m[2];',
	'CDATA',
	'BOLD');
	
// Close out of bold state back to normal if bold at the very end
$parser->FSM(
	'/\w\*$/',
	'preg_match("/(\w)\*$/", $STRING, $m); echo $m[1]."</strong>";',
	'CDATA',
	'BOLD');
	
// Close out of bold state back to normal if there is no closing mark.
$parser->FSM(
	'/.$/',
	'preg_match("/(.)$/", $STRING, $m); echo $m[1]."</strong>";',
	'CDATA',
	'BOLD');
	
//In bold state, catch all other data
$parser->FSM('/./s','echo $STRING;','BOLD','BOLD');

//---------Run the parser
ob_start();
$parser->Parse($sampleText,"UNKNOWN");
$output = ob_get_clean();

$urlRegex = '{
  \\b
  # Match the leading part (proto://hostname, or just hostname)
  (
    # http://, or https:// leading part
    (https?)://[-\\w]+(\\.\\w[-\\w]*)+
  |
    # or, try to find a hostname with more specific sub-expression
    (?i: [a-z0-9] (?:[-a-z0-9]*[a-z0-9])? \\. )+ # sub domains
    # Now ending .com, etc. For these, require lowercase
    (?-i: com\\b
        | edu\\b
        | biz\\b
        | gov\\b
        | in(?:t|fo)\\b # .int or .info
        | mil\\b
        | net\\b
        | org\\b
        | [a-z][a-z]\\.[a-z][a-z]\\b # two-letter country code
    )
  )

  # Allow an optional port number
  ( : \\d+ )?

  # The rest of the URL is optional, and begins with /
  (
    /
    # The rest are heuristics for what seems to work well
    [^.!,?;"\\\'<>()\[\]\{\}\s\x7F-\\xFF]*
    (
      [.!,?]+ [^.!,?;"\\\'<>()\\[\\]\{\\}\s\\x7F-\\xFF]+
    )*
  )?
}ix
';
$output = preg_replace($urlRegex, '<a href="$0">$0</a>', $output);

print nl2br($output);

?>

</p>

<br/><br/>
<hr/>

<p>This is other text that shouldn't be affected by bold or italics in the paragraph above.</p>

</body>
</html>
