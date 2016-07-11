<?php
/**
 * CacheBreaker
 * CacheBreaker Hooks
 *
 * @author		Alexia E. Smith, Tim Aldridge
 * @copyright	(c) 2014 Curse Inc.
 * @license		GPL v3.0
 * @package		CacheBreaker
 * @link		https://github.com/HydraWiki/CacheBreaker
 *
 **/

class cacheBreakerHooks {
	/**
	 * Handles modifying thumbnail URL links.
	 *
	 * @access	public
	 * @param	object	ThumbnailImage Object
	 * @param	array	Attributes to be set on the IMG 
	 * @param	array	Attributes to be set on the A HREF tag.
	 * @return	boolean	true
	 */
	static public function onThumbnailBeforeProduceHTML(ThumbnailImage $thumbnailImage, &$attributes, &$linkAttributes) {
		$cacheHash = 'version='.md5($thumbnailImage->getFile()->getRepo()->getFileTimestamp($thumbnailImage->getStoragePath()).$thumbnailImage->getWidth().$thumbnailImage->getHeight().$thumbnailImage->getUrl());

		if (!empty($attributes['src'])) {
			$attributes['src'] = self::appendHash($attributes['src'], $cacheHash);
		}
		if (!empty($linkAttributes['href']) && stripos($linkAttributes['href'], $attributes['alt']) !== false) {
			$linkAttributes['href'] = self::appendHash($linkAttributes['href'], $cacheHash);
		}
		return true;
	}

	/**
	 * Handles appending hashes to URLs.
	 *
	 * @access	private
	 * @param	string	URL to Modify
	 * @param	string	Attribute to append in a GET parameter format.  Example: 'version=abcd1234'
	 * @return	void
	 */
	static private function appendHash($url, $cacheHash) {
		if (!strlen($url)) {
			return $url;
		}

		$questionPos = strpos($url, '?');
		$ampPos = strrpos($url, '&');
		$anchorPos = strpos($url, '#');
		if ($questionPos !== false) {
			if ($anchorPos != $questionPos + 1) {
				if (($ampPos === false && $questionPos != strlen($url) - 1) || ($ampPos !== false && $ampPos != $questionPos + 1 && $ampPos > $questionPos)) {
					$cacheHash .= '&';
				}
			}
			$url = str_replace('?', '?'.$cacheHash, $url);
		} else {
			if ($anchorPos !== false) {
				$url = str_replace('#', '?'.$cacheHash.'#', $url);
			} else {
				$url .= '?'.$cacheHash;
			}
		}

		return $url;
	}

	/**
	 * Handles adding cache breaker to $wgLogo.
	 *
	 * @access	public
	 * @param	object	SkinTemplate Object
	 * @param	object	Initialized SkinTemplate Object
	 * @return	boolean True
	 */
	static public function onSkinTemplateOutputPageBeforeExec(&$te, &$tpl) {
		if (!isset($tpl->data)) {
			return true;
		}
		if (isset($tpl->data['logopath'])) {
			$pathInfo = explode('/', $tpl->data['logopath']);
			$file = array_pop($pathInfo);
			$fileMD5 = md5($file);
			$md5_2 = array_pop($pathInfo);
			$md5_1 = array_pop($pathInfo);

			if (substr($fileMD5, 0, 1) == $md5_1 && substr($fileMD5, 0, 2) == $md5_2) {
				$_file = trim($file);
				$_file = wfLocalFile(Title::newFromText('File:'.$_file));

				if ($_file !== null && $_file->exists()) {
					$cacheHash = 'version='.md5($_file->getTimestamp().$_file->getWidth().$_file->getHeight());

					$tpl->set(
						'logopath',
						self::appendHash($tpl->data['logopath'], $cacheHash)
					);
				}
			}
		}

		return true;
	}
}
?>