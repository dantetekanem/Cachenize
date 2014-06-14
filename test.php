<?PHP

	error_reporting(E_ALL);
	ini_set('display_errors', 1);

	require "cachenize.class.php";

	cachenize('test', function() {
		for($i = 1; $i <= 10; ++$i) {
			echo $i."<br />\n";
		}
	}, 10);

	echo '<br /><br />';

	$files	= cad('test.array', array('nome', 'leonardo', 'pereira', rand(0, 100)), 100);
	print_r($files);

	echo '<br /><br />';

?>
<?php cachenize('html.block', function(){ ?>

<pre>
	<?php for($i = 1; $i < 100; ++$i): ?>
	-
	<?php endfor; ?>
</pre>

<?php }, 10); ?>