<?php

class dableController extends dable {
	function init() {
	}

	function triggerBeforeDisplay(&$output) {
		if (Context::getResponseMethod() != 'HTML') return;
		if (Context::get('module') == 'admin') return;

		$config = $this->getConfig();
		$document_srl = Context::get('document_srl');
		$current_module_info = Context::get('current_module_info');

		if (empty($document_srl)) return;
		if (!in_array($current_module_info->mid, $config->boards)) return;

		$oDocument = Context::get('oDocument');

		// Content wrapper
		if ($config->use_content_wrapper === 'Y' && $index !== false) {
			$needle = sprintf('<!--BeforeDocument(%d,%d)--><div ', $oDocument->get('document_srl'), $oDocument->get('member_srl'));
			$index = strpos($output, $needle);

			if ($index !== false) {
				$index += strlen($needle);
				$output = substr($output, 0, $index) . 'itemprop="articleBody" ' . substr($output, $index);
				$output = $this->addWidgetScript($output, $index);
			}
		}

		// Is SEO module activated?
		$oModuleModel = getModel('module');
		$seo_config = $oModuleModel->getModuleConfig('seo');
		$seo_enabled = !empty($seo_config) && $seo_config->enable === 'Y';

		// Dable meta tags
		if ($config->use_meta_tags === 'Y') {
			$this->addMetaTag('dable:item_id', $document_srl);
			$this->addMetaTag('article:section', htmlspecialchars($current_module_info->browser_title));
			if (!$seo_enabled) {
				$this->printMetaTagsForSEO();
			}
		}
	}

	function addWidgetScript($output, $offset) {
		$config = $this->getConfig();
		$oDocument = Context::get('oDocument');
		$platform = $config->script_type === 'responsive' ? '' : (Mobile::isFromMobilePhone() ? 'mobile_' : 'pc_' );

		// top_left, top_right
		$key_tl = $platform . 'article_top_left';
		$key_tr = $platform . 'article_top_right';

		if ($config->{$key_tl} === 'Y' || $config->{$key_tr} === 'Y') {
			$index = strpos($output, '>', $offset);
			if ($index !== false) {
				$code = '';
				if ($config->{$key_tl} === 'Y' && !empty($config->{$key_tl . '_code'})) {
					$code .= $config->{$key_tl . '_code'};
				}
				if ($config->{$key_tr} === 'Y' && !empty($config->{$key_tr . '_code'})) {
					$code .= $config->{$key_tr . '_code'};
				}

				$output = substr($output, 0, $index + 1) . $code . substr($output, $index + 1);
				$offset = $index + strlen($code);
			}
		}

		// bottom
		$key_bt = $platform . 'article_bottom';
		if ($config->{$key_bt} === 'Y' && $config->{$key_bt . '_code'}) {
			$needle = sprintf('</div><!--AfterDocument(%d,%d)-->', $oDocument->get('document_srl'), $oDocument->get('member_srl'));
			$index = strpos($output, $needle, $offset);
			if ($index !== false) {
				$output = substr($output, 0, $index) . $config->{$key_bt . '_code'} . substr($output, $index);
			}
		}

		return $output;
	}

	function addMetaTag($property, $content) {
		Context::addHtmlHeader("<meta property=\"{$property}\" content=\"{$content}\" />");
	}

	function printMetaTagsForSEO() {
		$oDocument = Context::get('oDocument');

		$excerpt = trim(str_replace('&nbsp;', ' ', $oDocument->getContentText(400)));

		$this->addMetaTag('og:url', $oDocument->getPermanentUrl());
		$this->addMetaTag('og:title', htmlspecialchars($oDocument->getTitleText()));
		$this->addMetaTag('og:description', htmlspecialchars($excerpt));
		$this->addMetaTag('article:published_time', $oDocument->getRegdate('c'));
		$this->addMetaTag('article:modified_time', $oDocument->getUpdate('c'));
	}
}
