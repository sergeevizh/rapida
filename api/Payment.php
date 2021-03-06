<?php

/**
 * Simpla CMS
 *
 * @copyright	2011 Denis Pikusov
 * @link		http://simplacms.ru
 * @author		Denis Pikusov
 *
 */

require_once ('Simpla.php');

class Payment extends Simpla
{
	public function get_payment_methods($filter = array())
	{
		$delivery_filter = '';
		if (!empty($filter['delivery_id']))
			$delivery_filter = $this->db->placehold('AND id in (SELECT payment_method_id FROM __delivery_payment dp WHERE dp.delivery_id=?)', intval($filter['delivery_id']));

		$enabled_filter = '';
		if (!empty($filter['enabled']))
			$enabled_filter = $this->db->placehold('AND enabled=?', intval($filter['enabled']));

		$query = "SELECT *
					FROM __payment_methods WHERE 1 $delivery_filter $enabled_filter ORDER BY pos";

		$this->db->query($query);
		return $this->db->results_array();
	}

	function get_payment_method($id)
	{
		$query = $this->db->placehold("SELECT * FROM __payment_methods WHERE id=? LIMIT 1", intval($id));
		$this->db->query($query);
		$payment_method = $this->db->result_array();
		return $payment_method;
	}

	function get_payment_settings($method_id)
	{
		$query = $this->db->placehold("SELECT settings FROM __payment_methods WHERE id=? LIMIT 1", intval($method_id));
		$this->db->query($query);
		$settings = $this->db->result_array('settings');

		$settings = unserialize($settings);
		return $settings;
	}

	function get_payment_modules()
	{
		$modules_dir = $this->config->root_dir . 'payment/';

		$modules = array();
		$handler = opendir($modules_dir);
		while ($dir = readdir($handler)){
			$dir = preg_replace("/[^A-Za-z0-9]+/", "", $dir);
			if (!empty($dir) && $dir != "." && $dir != ".." && is_dir($modules_dir . $dir))
				{

				if (is_readable($modules_dir . $dir . '/settings.xml') 
				&& $xml = simplexml_load_file($modules_dir . $dir . '/settings.xml'))
					{
					$module = array();

					$module['name'] = (string)$xml->name;
					$module['settings'] = array();

					foreach ($xml->settings as $s){
						$module['settings'][(string)$s->variable] = array();
						$module['settings'][(string)$s->variable]['name'] = (string)$s->name;
						$module['settings'][(string)$s->variable]['variable'] = (string)$s->variable;
						$module['settings'][(string)$s->variable]['options'] = array();
						foreach ($s->options as $o){
							$module['settings'][(string)$s->variable]['options'][(string)$o->value] = array();
							$module['settings'][(string)$s->variable]['options'][(string)$o->value]['name'] = (string)$o->name;
							$module['settings'][(string)$s->variable]['options'][(string)$o->value]['value'] = (string)$o->value;
						}
					}
					$modules[$dir] = $module;
				}

			}
		}
		closedir($handler);
		return $modules;

	}

	public function get_payment_deliveries($id)
	{
		$query = $this->db->placehold("SELECT delivery_id FROM __delivery_payment WHERE payment_method_id=?", intval($id));
		$this->db->query($query);
		return $this->db->results_array('delivery_id');
	}

	public function update_payment_method($id, $payment_method)
	{
		$query = $this->db->placehold("UPDATE __payment_methods SET ?% WHERE id in(?@)", $payment_method, (array)$id);
		$this->db->query($query);
		return $id;
	}

	public function update_payment_settings($method_id, $settings)
	{
		if (!is_string($settings))
			{
			$settings = serialize($settings);
		}
		$query = $this->db->placehold("UPDATE __payment_methods SET settings=? WHERE id in(?@) LIMIT 1", $settings, (array)$method_id);
		$this->db->query($query);
		return $method_id;
	}

	public function update_payment_deliveries($id, $deliveries_ids)
	{
		$query = $this->db->placehold("DELETE FROM __delivery_payment WHERE payment_method_id=?", intval($id));
		$this->db->query($query);
		if (is_array($deliveries_ids))
			foreach ($deliveries_ids as $d_id)
			$this->db->query("INSERT INTO __delivery_payment SET payment_method_id=?, delivery_id=?", $id, $d_id);
	}

	public function add_payment_method($pm)
	{
		if (is_object($pm)) {
			$pm = (array)$pm;
		}
		//удалим id, если он сюда закрался, при создании id быть не должно
		if (isset($pm['id'])) {
			unset($pm['id']);
		}

		foreach ($pm as $k => $e) {
			if (empty_($e)) {
				unset($pm[$k]);
			}
		}

		$query = $this->db->placehold(
			'INSERT INTO __payment_methods
		SET ?%',
			$pm
		);

		if (!$this->db->query($query))
			return false;

		$id = $this->db->insert_id();
		$this->db->query("UPDATE __payment_methods SET pos=id WHERE id=?", $id);
		return $id;
	}

	public function delete_payment_method($id)
	{
		// Удаляем связь метода оплаты с достаками
		$query = $this->db->placehold("DELETE FROM __delivery_payment WHERE payment_method_id=?", intval($id));
		$this->db->query($query);

		if (!empty($id))
			{
			$query = $this->db->placehold("DELETE FROM __payment_methods WHERE id=? LIMIT 1", intval($id));
			$this->db->query($query);
		}
	}


}
