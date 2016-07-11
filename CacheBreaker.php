<?php
/**
 * CacheBreaker
 * CacheBreaker Mediawiki Settings
 *
 * @author		Alexia E. Smith
 * @copyright	(c) 2014 Curse Inc.
 * @license		GPL v3.0
 * @package		CacheBreaker
 * @link		https://github.com/HydraWiki/CacheBreaker
 *
 **/

/******************************************/
/* Credits								  */
/******************************************/
$credits = array(
	'path'				=> __FILE__,
	'name'				=> 'CacheBreaker',
	'author'			=> 'Alexia E. Smith',
	'descriptionmsg'	=> 'cachebreaker_description',
	'version'			=> '1.0',
	'license-name'		=> 'GPL-3.0',
	'url'				=> 'https://github.com/HydraWiki/CacheBreaker'
);
$wgExtensionCredits['other'][] = $credits;


/******************************************/
/* Language Strings, Page Aliases, Hooks  */
/******************************************/
$extDir = __DIR__;

$wgExtensionMessagesFiles['CacheBreaker']		= "{$extDir}/CacheBreaker.i18n.php";

$wgAutoloadClasses['cacheBreakerHooks']			= "{$extDir}/CacheBreaker.hooks.php";

$wgHooks['ThumbnailBeforeProduceHTML'][]		= 'cacheBreakerHooks::onThumbnailBeforeProduceHTML';
$wgHooks['SkinTemplateOutputPageBeforeExec'][]	= 'cacheBreakerHooks::onSkinTemplateOutputPageBeforeExec';


$wgMessagesDirs['CacheBreaker']					= "{$extDir}/i18n";
?>
