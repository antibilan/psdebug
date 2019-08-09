<!DOCTYPE html>
<html>
<head>
    <title>Блочная вёрстка</title>
<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<div id="container">
	<?php 
	
	$dockroot = "C:/xampp/htdocs/test";
	include ($dockroot . "/header.php");
	include ($dockroot . "/navigation.php");
	include ($dockroot . "/sidebar.php");
	require ($dockroot . "/functions.php");?>
	
    <div id="content" >
        <?php 
		echo "hotfix";
        ?>				
    
	<form method="post">
		<input type="radio" name="answer" value="a1"/>Enable Standart debug<Br>
		<input type="radio" name="answer" value="a2"/>Enable Standart debug + SQL<Br>
		<input type="radio" name="answer" value="a3"/>Disable<Br>
		<input type="submit" name="test" id="test" value="RUN" /><br/>

		<?php
		
		#$file = "/var/www/vhosts/example.com/httpdocs/panel.ini";
		$file = $dockroot . "/panel.ini";
		
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
		
	<?php 
	cont_load();
	?>
		
</div>
<?php include ("footer.php");?>
</div>
</body>
</html>