<?php

class dableAdminController extends dable {
	function procDableAdminSaveSetting()
	{
		$oModuleController = getController('module');

		$vars = Context::getRequestVars();
		$config = $this->getConfig();

		$config->boards = $vars->boards;
		$config->use_content_wrapper = $vars->use_content_wrapper === 'Y' ? 'Y' : 'N';
		$config->use_meta_tags = $vars->use_meta_tags === 'Y' ? 'Y' : 'N';
		$config->thumbnail_size = $vars->thumbnail_size;
		$config->script_type = $vars->script_type;
			
		$positions = array('article_bottom', 'article_top_left', 'article_top_right');
		$platforms = array('', 'pc', 'mobile');

		foreach($positions as $pos) {
			foreach($platforms as $platform) {
				$key = $platform ? $platform . '_' . $pos : $pos;
				$code = $key . '_code';

				$config->{$key} = $vars->{$key} === 'Y' ? 'Y' : 'N';
				$config->{$code} = trim($vars->{$code});
			}
		}

		$oModuleController->updateModuleConfig('dable', $config);
		$this->moduleUpdate();

		$this->setMessage('success_updated');
		if (Context::get('success_return_url')) {
			$this->setRedirectUrl(Context::get('success_return_url'));
		}
	}
}
