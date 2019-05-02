<?php
/* Copyright (C) Dable <http://dable.io> */

class dable extends ModuleObject
{
	private $triggers = array(
		array('display', 'dable', 'controller', 'triggerBeforeDisplay', 'before'),
	);

	public function getConfig() {
		$oModuleModel = getModel('module');
		$config = $oModuleModel->getModuleConfig('dable');

		if (empty($config)) $config = new stdClass;
		$config = $this->assign($config, array(
			'use_content_wrapper' => 'Y',
			'use_meta_tags' => 'Y',
			'boards' => array(),
		));

		return $config;
	}

	protected function assign($obj, $source) {
		$arr = (array)$source;

		foreach($arr as $key => $value) {
			if (!isset($obj->{$key})) {
				$obj->{$key} = $value;
			}
		}

		return $obj;
	}

	function moduleInstall() {
		// Register action forward (to use in administrator mode)
		$oModuleController = getController('module');
		
		// Save the default settings for attachments
		$config = new stdClass;
		$config->use_content_wrapper = 'Y';
		$config->use_meta_tags = 'Y';
		$config->script_type = 'responsive';
		$oModuleController->insertModuleConfig('dable', $config);

		return new BaseObject(0, 'success');
	}

	function checkUpdate()
	{
		$oModuleModel = getModel('module');
		$config = $this->getConfig();

		foreach ($this->triggers as $trigger) {
			if (!$oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4])) return true;
		}

		return false;
	}

	function moduleUpdate()
	{
		$oModuleModel = getModel('module');
		$oModuleController = getController('module');

		foreach ($this->triggers as $trigger) {
			if (!$oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4])) {
				$oModuleController->insertTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
			}
		}

		return new BaseObject(0, 'success_updated');
	}

	function moduleUninstall()
	{
		$oModuleController = getController('module');

		foreach ($this->triggers as $trigger) {
			$oModuleController->deleteTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
		}

		return $this->makeObject();
	}
}
