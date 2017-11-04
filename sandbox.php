<?php
session_start();
if (isset($_SESSION['admin'])) {
	require_once (dirname(__FILE__) . '/api/Simpla.php');
	$simpla = new Simpla();
	
	//корневая директория системы 
	$dir = dirname(__FILE__) . '/';

	//имя создаваемого архива
	$filename = $dir . 'distro/rapida_' . $simpla->config->version . '.zip';

	$dbfile = 'rapida.sql';


	function glob_recurse($glob)
	{
		$res = array();
		foreach (glob($glob) as $g) {
			$g = realpath($g);
			if (is_file($g)) {
				$res[] = $g;
				print "\n$g";
			}
			else {
				$res = array_merge($res, glob_recurse($glob . '/*'));
			}
		}
		return $res;
	}

	function filesize_remote($url)
	{
		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, TRUE);
		curl_setopt($ch, CURLOPT_NOBODY, TRUE);

		$data = curl_exec($ch);
		$size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

		curl_close($ch);
		return $size;
	}

	function copy_remote($url, $dest)
	{
		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, TRUE);
		curl_setopt($ch, CURLOPT_NOBODY, TRUE);

		$data = curl_exec($ch);
		$size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

		curl_close($ch);
		return $size;
	}

	//**********************************
	print "<pre>";
	print "HELLO!\n";
	//**********************************



	//~ $simpla->db->query("SELECT id, val FROM __options_uniq");
	
	//~ while($row = $simpla->db->result_array()){
		//~ $val = trim($row['val']);
		//~ $trans = translit_url($val);
		//~ $md4 = hash('md4', $trans);
		//~ $id = $row['id'];
		//~ $simpla->db2->query("UPDATE __options_uniq SET `val` = '$val', `trans` = '$trans', `md4` = 0x$md4  WHERE 1 AND id = $id ");
	//~ }
	
	//~ $simpla->db->query("SELECT id, name as val FROM __features");
	
	//~ while($row = $simpla->db->result_array()){
		//~ $trans = translit_url($row['val']);
		//~ $id = $row['id'];
		//~ $simpla->db2->query("UPDATE __features SET `trans` = '$trans' WHERE 1 AND id = $id ");
	//~ }

	$options = $simpla->features->get_options();
	$features = $simpla->features->get_features_trans();
	print_r($options);

	print "</PRE>";
	dtimer::show();
}
