<?php
/**
 * Wall
 *
 * User Message Wall for MediaWiki
 *
 * @author Sean Colombo <sean@wikia-inc.com>, Christian Williams <christian@wikia-inc.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * @package MediaWiki
 *
 */

$wgExtensionCredits['specialpage'][] = array(
	'name' => 'User Wall - disabled',
	'author' => array( 'Tomek Odrobny', 'Andrzej Łukaszewski', 'Piotr Bablok' ),
	'url' => 'http://www.wikia.com',
	'descriptionmsg' => 'wall-disabled-desc',
);

$dir = dirname(__FILE__);

$app->registerExtensionMessageFile('Wall', $dir . '/Wall.i18n.php');
$app->registerClass('WallDisabledHooksHelper', $dir . '/WallDisabledHooksHelper.class.php');

include($dir . '/notification/WallNotifications.setup.php');

//don't let others edit wall messages after turning wall on and off
$app->registerHook('AfterEditPermissionErrors', 'WallDisabledHooksHelper', 'onAfterEditPermissionErrors');
include($dir . '/WallNamespaces.php');