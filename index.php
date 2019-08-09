<?php
include("header.php");
require("functions.php");
?>
<body>
    <div id="header" 
        style="height: 20px; width: 100%; background-color: #0000FF;">
    </div>
    <div id="content" 
style="height: 20px; width: 100%; background-color: #00FFFF; font-size: large; font-weight: 100;">
		<br />
        <p><?php
        echo "hotfix";
        ?></p>
		<br />			
		
    </div>
	<form method="post">
	<input type="radio" name="answer" value="a1">Enable Standart debug<Br>
	<input type="radio" name="answer" value="a2">Enable Standart debug + SQL<Br>
	<input type="radio" name="answer" value="a3">Disable<Br>
    <input type="submit" name="test" id="test" value="RUN" /><br/>

<?php
	
	$file = "/var/www/vhosts/example.com/httpdocs/panel.ini";
		
	$ini_file = init_array($file);		
		
	if(array_key_exists('answer',$_POST) && $_POST['answer']=='a1'){
		enable("std");
		write_file($file);
		status($file);
		#php_uname();
	}
	elseif(array_key_exists('answer',$_POST) && $_POST['answer']=='a2'){
		enable("full");
		write_file($file);
		status($file);
		#php_uname();
	}
	elseif(array_key_exists('answer',$_POST) && $_POST['answer']=='a3'){
		disable("debug");
		write_file($file);
		status($file);
		#php_uname();
	}
?>	
		</form>
    <div id="footer" 
        style="height: 20px; width: 100%; background-color: #008000;">
    </div>
</body>
</html>