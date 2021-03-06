<?
	#
	# $Id$
	#

	include("include/init.php");

	if (! $GLOBALS['cfg']['enable_feature_password_retrieval']){
		error_404();
	}

	if ($GLOBALS['cfg']['user']['id']){

		header("location: /");
		exit();
	}

	$reset_code = post_str('reset');

	if (! $reset_code){
		$reset_code = get_str('reset');
	}

	if (! $reset_code){

		# seriously, go away...

		header("location: /");
		exit();
	}

	$user = users_get_by_password_reset_code($reset_code);

	if (! $user){

		$GLOBALS['error']['nouser'] = 1;		
		$GLOBALS['smarty']->display('page_reset.txt');
		exit();	
	}

	$new_reset_code = users_generate_password_reset_code($user);

	$GLOBALS['smarty']->assign('reset_code', $new_reset_code);

	if (post_str('reset')){

		$new_password1 = post_str('new_password1');
		$new_password2 = post_str('new_password2');

		if ((! $new_password1) || (! $new_password2)){

			$GLOBALS['error']['missing_password'] = 1;
			$GLOBALS['smarty']->display('page_reset.txt');
			exit();	
		}

		if ($new_password1 !== $new_password2){

			$GLOBALS['error']['password_mismatch'] = 1;
			$GLOBALS['smarty']->display('page_reset.txt');
			exit();	
		}

		$rsp = users_update_password($user, $new_password1);

		if (! $rsp['ok']){

			$GLOBALS['error']['update_failed'] = 1;
			$GLOBALS['smarty']->display('page_reset.txt');
			exit();	
		}

		users_purge_password_reset_codes($user);
		users_reload_user($user);

		login_do_login($user);
		exit();
	}


	#
	# output
	#

	$smarty->display('page_reset.txt');
	exit();
?>