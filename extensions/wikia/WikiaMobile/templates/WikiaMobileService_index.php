<?
/**
 * @var $languageCode String
 * @var $languageDirection String
 * @var $appCacheManifestPath String
 * @var $pageTitle String
 * @var $allowRobots String
 * @var $mimeType String
 * @var $charSet String
 * @var $headLinks String
 * @var $cssLinks String
 * @var $topScripts String
 * @var $globalVariablesScript String
 * @var $jsHeadFiles String
 * @var $headItems String
 * @var $bodyClasses String[]
 * @var $trackingCode String
 * @var $advertisement String
 * @var $wikiaNavigation String
 * @var $pageContent String
 * @var $wikiaFooter String
 * @var $jsBodyFiles String
 */
?>
<!DOCTYPE html>
<html lang=<?= $languageCode ;?> dir=<?= $languageDirection ;?><? if ( !empty( $appCacheManifestPath ) ) :?> manifest=<?= $appCacheManifestPath ;?><? endif ;?>>
<head>
	<meta http-equiv=Content-Type content="<?= "{$mimeType};charset={$charSet}" ;?>">
	<title><?= htmlspecialchars( $pageTitle ) ;?></title>
	<?= $cssLinks ;?>
	<? if( !$allowRobots ): ?>
	<meta name=robots content='noindex, nofollow'>
	<?endif; ?>
	<meta name=HandheldFriendly content=true>
	<meta name=MobileOptimized content=width>
	<meta name=viewport content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
	<meta name=apple-mobile-web-app-capable content=yes>
	<?= $headLinks ;?>
	<?= $globalVariablesScript ;?>
	<?= $jsHeadFiles ;?>
	<?= $headItems ;?>
</head>
<body class="<?= implode(' ', $bodyClasses) ?>">
	<?= $trackingCode ;?>
	<?= $wikiaNavigation ;?>
	<?= $pageContent ;?>
	<?= $wikiaFooter ;?>
	<div id=wkCurtain>&nbsp;</div>
	<?= $jsBodyFiles ;?>
	<?= $advertisement ;?>
</body>
</html>