<?php
class dableAdminView extends dable
{
	function init() {
		$this->setTemplatePath($this->module_path . 'tpl');
	}

	function dispDableAdminDashboard()
	{
		$oModuleModel = getModel('module');
	}

	function dispDableAdminSetting()
	{
		$this->setTemplateFile('admin.setting');

		$vars = Context::getRequestVars();

		// Configuration
		$config = $this->getConfig();
		Context::set('config', $config);

		// Get bbs list
		$args = new stdClass;
		$result = executeQueryArray('board.getBoardList', $args);
		$board_list = $result->data;
		ModuleModel::syncModuleToSite($board_list);
		foreach($board_list as $board) {
			$board->selected = in_array($board->mid, $config->boards) !== false;
		}
		Context::set('board_list', $board_list);

		$security = new Security();
		$security->encodeHTML('config');
		$security->encodeHTML('lang..content_wrapper_desc');
		$security->encodeHTML('board_list..browser_title');

		$platforms = array('', 'pc', 'mobile');
		$positions = array('bottom', 'top_left', 'top_right');

		foreach ($platforms as $platform) {
			foreach($positions as $position) {
				$key = ( $platform ? $platform . '_' : '' ) . 'article_' . $position . '_code';
				$security->encodeHTML('config..' . $key);
			}
		}
	}
}
/* !End of file */
