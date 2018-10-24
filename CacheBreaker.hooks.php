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

class CacheBreakerHooks {
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
		global $wgUploadPath;

		$cacheHash = 'version='.md5($thumbnailImage->getFile()->getRepo()->getFileTimestamp($thumbnailImage->getFile()->getPath()).$thumbnailImage->getWidth().$thumbnailImage->getHeight().$thumbnailImage->getUrl());

		if (!empty($attributes['src'])) {
			$attributes['src'] = self::appendHash($attributes['src'], $cacheHash);
		}
		if (!empty($attributes['srcset'])) {
			$sets = explode(', ', $attributes['srcset']);
			if (!empty($sets)) {
				foreach ($sets as $key => $set) {
					list($url, $zoom) = explode(' ', $set);
					$url = self::appendHash($url, $cacheHash);
					$sets[$key] = $url.' '.$zoom;
				}
				$attributes['srcset'] = implode(', ', $sets);
			}
		}
		if (!empty($linkAttributes['href']) && strpos($linkAttributes['href'], $wgUploadPath) === 0) {
			$linkAttributes['href'] = self::appendHash($linkAttributes['href'], $cacheHash);
		}

		return true;
	}

	/**
	 * Append the cache hash to [[Media:...]] links.
	 *
	 * @param	object	Title
	 * @param	object	File
	 * @param	string	HTML inside the anchor tag.
	 * @param	array	Array of tag attributes.
	 * @param	string	The raw return: "Html::rawElement( 'a', $attribs, $html );"
	 * @return	boolean	False - Returning false is required to trigger the MediaWiki to know that &$return was modified.
	 */
	static public function onLinkerMakeMediaLinkFile($title, $file, &$html, &$attributes, &$return) {
		if (empty($attributes['href']) || !$file) {
			return true;
		}

		$cacheHash = 'version='.md5($file->getRepo()->getFileTimestamp($file->getPath()).$file->getWidth().$file->getHeight().$file->getUrl());

		$attributes['href'] = self::appendHash($attributes['href'], $cacheHash);

		$return = Html::rawElement('a', $attributes, $html);
		return false;
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
	 * @param	object	[Unused] Title
	 * @param	object	[Unused] Article
	 * @param	object	[Unused] Output
	 * @param	object	[Unused] User
	 * @param	object	[Unused] WebRequest
	 * @param	object	[Unused] Mediawiki
	 * @return	boolean	True
	 */
	static public function onBeforeInitialize(&$title = null, &$article = null, &$output = null, &$user = null, $request = null, $mediaWiki = null) {
		global $wgLogo, $wgLogoHD, $wgFavicon;

		$wgLogo = self::appendHashToUrl($wgLogo);
		if (is_array($wgLogoHD)) {
			foreach ($wgLogoHD as $key => $url) {
				$wgLogoHD[$key] = self::appendHashToUrl($url);
			}
		}
		$wgFavicon = self::appendHashToUrl($wgFavicon);

		return true;
	}

	/**
	 * Append the cache hash to the URL based on the file name.
	 *
	 * @access	private
	 * @param	string	Original URL
	 * @return	string	Modified URL or the original URL on error.
	 */
	static private function appendHashToUrl($url) {
		$pathInfo = explode('/', $url);
		$file = array_pop($pathInfo);
		$fileMD5 = md5($file);
		$md5_2 = array_pop($pathInfo);
		$md5_1 = array_pop($pathInfo);

		if (substr($fileMD5, 0, 1) == $md5_1 && substr($fileMD5, 0, 2) == $md5_2) {
			$_file = trim($file);
			$_file = wfLocalFile(Title::newFromText('File:'.$_file));

			if ($_file !== null && $_file->exists()) {
				$cacheHash = 'version='.md5($_file->getTimestamp().$_file->getWidth().$_file->getHeight());

				return self::appendHash($url, $cacheHash);
			}
		}
		return $url;
	}

	/**
	 * Handles adding cache breaker to $wgLogo.
	 *
	 * @access	public
	 * @param	object	Title Object
	 * @param	array	URLs to be purged.
	 * @return	boolean True
	 */
	static public function onTitleSquidURLs(Title $title, array &$urls) {
		foreach ($urls as $url) {
			if (strpos($url, 'https://') === 0) {
				$urls[] = str_replace('https://', 'http://', $url);
			}
			if (strpos($url, 'http://') === 0) {
				$urls[] = str_replace('http://', 'https://', $url);
			}
		}
		$urls = array_unique($urls);

		return true;
	}
}
?>