<div class="UserLoginModal">
	<h1><?= wfMsg('userlogin-login-heading') ?></h1>
<?php
	$form = array(
		'inputs' => array(
			array(
				'type' => 'hidden',
				'name' => 'loginToken',
				'value' => $loginToken
			),
			array(
				'type' => 'hidden',
				'name' => 'returnto',
				'value' => ''
			),
			array(
				'type' => 'text',
				'name' => 'username',
				'isRequired' => true,
				'label' => wfMsg('yourname')
			),
			array(
				'type' => 'password',
				'name' => 'password',
				'class' => 'password-input',
				'isRequired' => true,
				'label' => wfMsg('yourpassword')
			),
			array(
				'type' => 'custom',
				'output' => '<a href="#" class="forgot-password">'.wfMsg('userlogin-forgot-password').'</a>',
			),
			array(
				'type' => 'checkbox',
				'name' => 'keeploggedin',
				'value' => '1',
				'label' => wfMsg('userlogin-remembermypassword'),
				'class' => 'keep-logged-in',
			),
			array(
				'type' => 'submit',
				'value' => wfMsg('login'),
				'class' => 'big'
			),
			array(
				'type' => 'custom',
				'output' => wfMsgExt('userlogin-get-account', 'parseinline'),
				'class' => 'get-account'
			)
		),
		'method' => 'post',
		'action' => $formPostAction,
	);

	//$form['isInvalid'] = true;
	//$form['errorMsg'] = '';

	echo $app->renderView('WikiaStyleGuideForm', 'index', array('form' => $form));
	echo $app->renderView('UserLoginSpecial', 'Providers');
?>
</div>
