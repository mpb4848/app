<?php
/**
 * Groups all Wikia-specific hook handlers called by Resource Loader
 *
 * @package Wikia
 * @author Wladyslaw Bodzek
 */
class ResourceLoaderHooks {

	/**
	 * Configure Wikia-specific settings in ResourceLoader
	 *
	 * @static
	 * @param ResourceLoader $resourceLoader Object to configure
	 * @return bool true because it's a hook
	 */
	static public function onResourceLoaderRegisterModules( ResourceLoader $resourceLoader ) {
		global $wgScriptPath, $wgScriptExtension, $wgMedusaHostPrefix, $wgCdnRootUrl, $wgDevelEnvironment,
			   $wgStagingEnvironment, $wgCityId;

		// staff and internal special case
		if ( $wgCityId === null ) {
			$sources = $resourceLoader->getSources();
			$resourceLoader->addSource('common',$sources['local']);
			return true;
		}

		// Determine the shared domain name
		if ( empty($wgDevelEnvironment) && empty($wgStagingEnvironment) ) {
			$host = 'http://' . (empty($wgMedusaHostPrefix) ? 'community.' : $wgMedusaHostPrefix) . 'wikia.com';
		} else {
			$host = $wgCdnRootUrl;
		}

		// Feed RL with the "common" source
		$scriptUri = "$host{$wgScriptPath}/load{$wgScriptExtension}";
		$apiUri = "$host{$wgScriptPath}/api{$wgScriptExtension}";
		$resourceLoader->addSource('common',array(
			'loadScript' => $scriptUri,
			'apiScript' => $apiUri,
		));

		// Rebase all modules except startup to "common" source
//        $resourceLoader->rebaseModules(true,'common');
//        $resourceLoader->rebaseModules('startup','local');

		return true;
	}

	/**
	 * Filter out user options which will be emitted as inline <script> tag
	 * by ResourceLoader (BugId:33294)
	 *
	 * @author macbre
	 *
	 * @static
	 * @param ResourceLoaderContext $context ResourceLoader context
	 * @param array $options user options to be filtered out
	 * @return bool true because it's a hook
	 */
	static public function onResourceLoaderUserOptionsModuleGetOptions( ResourceLoaderContext $context, array &$options ) {
		wfProfileIn(__METHOD__);
		#wfDebug(__METHOD__ . 'user options count (before): ' . count($options) . "\n");

		$whitelist = User::getDefaultOptions();

		// returns an array containing all the entries of $options which have keys that are present in $whitelist
		$options = array_intersect_key($options, $whitelist);

		#wfDebug(__METHOD__ . 'user options count (after): ' . count($options) . "\n");
		wfProfileOut(__METHOD__);
		return true;
	}

	static public function onResourceLoaderFileModuleConcatenateScripts( &$script ) {
		$script = preg_replace('#^.*@Packager\\.RemoveLine.*$#m', '', $script);

		return true;
	}

	public static function onResourceLoaderSiteModuleGetPages( $module, $context, &$pages ) {
		global $wgResourceLoaderAssetsSkinMapping;

		// handle skin name changes
		$skinName = $context->getSkin();
		if ( isset( $wgResourceLoaderAssetsSkinMapping[$skinName] ) ) {
			$mappedName = $wgResourceLoaderAssetsSkinMapping[$skinName];
			$mapping = array(
				'MediaWiki:' . ucfirst( $skinName ) . '.js'
				=> 'MediaWiki:' . ucfirst( $mappedName ) . '.js',
				'MediaWiki:' . ucfirst( $skinName ) . '.css'
				=> 'MediaWiki:' . ucfirst( $mappedName ) . '.css'
			);
			$pages = Wikia::renameArrayKeys($pages,$mapping);
		}

		// Wikia doesn't include Mediawiki:Common.css in Oasis
		// lower-case skin name is returned by getSkin()
		if ( $skinName == 'oasis' ) {
			unset($pages['MediaWiki:Common.css']);
		}

		// todo: add user-defined site scripts here

		return true;
	}

	public static function onResourceLoaderUserModuleGetPages( $module, $context, $userpage, &$pages ) {
		global $wgResourceLoaderAssetsSkinMapping;

		// handle skin name changes
		$skinName = $context->getSkin();
		if ( isset( $wgResourceLoaderAssetsSkinMapping[$skinName] ) ) {
			$mappedName = $wgResourceLoaderAssetsSkinMapping[$skinName];
			$mapping = array(
				"$userpage/" . $skinName . '.js'
				=> "$userpage/" . $mappedName . '.js',
				"$userpage/" . $skinName . '.css'
				=> "$userpage/" . $mappedName . '.css'
			);
			$pages = Wikia::renameArrayKeys($pages,$mapping);
		}

		// todo: add user-defined user scripts here

		return true;
	}

	public static function onResourceLoaderCacheControlHeaders( $context, $maxage, $smaxage, $exp ) {
		header( "X-Pass-Cache-Control: public, max-age=$maxage, s-maxage=$smaxage" );
		header( 'X-Pass-Expires: ' . wfTimestamp( TS_RFC2822, $exp + time() ) );

		return true;
	}

	public static function onAlternateResourceLoaderURL( &$loadScript, &$query, &$url, $modules ) {
		global $wgEnableResourceLoaderRewrites, $wgCdnRootUrl;

		static $resourceLoaderInstance;
		if ( empty($resourceLoaderInstance) ) $resourceLoaderInstance = new ResourceLoader();

		$source = false;
		foreach ($modules as $moduleName) {
			$moduleInfo = $resourceLoaderInstance->getModuleInfo($moduleName);

			$moduleSource = 'local';
			// the module definition may explicitly define the source
			if ( !empty($moduleInfo['source']) ) {
				$moduleSource = $moduleInfo['source'];
			}

			if ($source === false) $source = $moduleSource;
			elseif ($source !== $moduleSource) {
				$source = 'local';
				break;
			}
		}
		if ( empty($source) ) {
			$source = 'local';
		}

		if ( !empty($wgEnableResourceLoaderRewrites) ) {
			$loadScript = str_replace('/load.php','/__load/',$loadScript);
			$domain = "-";
			if ( $source != 'local' ) {
				$loadScript = $wgCdnRootUrl . '/__load/';
			}

			$loadQuery = $query;
			$modules = $loadQuery['modules'];
			unset($loadQuery['modules']);

			$params = urlencode(http_build_query($loadQuery));
			$url = $loadScript . "$domain/$params/$modules";
			$url = wfExpandUrl( $url, PROTO_RELATIVE );

			return false;
		} else {
			$sources = $resourceLoaderInstance->getSources();
			$loadScript = $sources[$source]['loadScript'];
		}

		return true;
	}

}