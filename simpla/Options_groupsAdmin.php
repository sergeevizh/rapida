<?PHP 

require_once ('api/Simpla.php');

########################################
class Options_groupsAdmin extends Simpla
{

	public function fetch()
	{
		//заново инициализируем опции
		$this->features->init_features(true);

		// Группы свойств
		$ogroups = $this->features->get_options_groups();
		$this->design->assign('ogroups', $ogroups);
		
		// Текущее меню
		$gid = isset($_GET['gid']) ? $_GET['gid'] : 0;
		$ogroup = $this->features->get_option_group($gid);
		
		$this->design->assign('ogroup', $ogroup);
		// Обработка действий
		if ($this->request->method('post'))
		{
		// Сортировка
			$poss = $_POST['poss'];
			foreach ($poss as $pos => $id){
				$this->features->update_feature($id, array('pos' => $pos) ); 
			}
		
		// Действия с выбранными
			$ids = $this->request->post('check');
			if (is_array($ids)){
				switch ($this->request->post('action'))
				{
				case 'delete' :
					{
						foreach ($ids as $id){
							$this->features->delete_feature($id);
						}
						break;
					}
				}
			}
		}

  

		// Отображение
		$opts = $this->features->get_features(array('gid' => $gid, "force_no_cache"=> 1));
		
		
		$this->design->assign('opts', $opts);
		return $this->design->fetch('options_groups.tpl');
	}
}
