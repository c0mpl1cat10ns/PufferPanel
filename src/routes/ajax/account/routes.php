<?php
/*
	PufferPanel - A Minecraft Server Management Panel
	Copyright (c) 2013 Dane Everitt

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see http://www.gnu.org/licenses/.
*/
namespace PufferPanel\Core;
use \ORM, \Otp\GoogleAuthenticator;


$klein->respond('POST', '/ajax/account/totp', function($request, $response) use ($core) {

	/*
	* Generate the TOTP Token
	*/
	$secret = GoogleAuthenticator::generateRandom();

	$account = ORM::forTable('users')->findOne($core->user->getData('id'));
	$account->totp_secret = $secret;
	$account->save();

	/*
	* Generate QR Code
	*/

	$response->body('<div class="row" id="notice_box_totp" style="display:none;"></div>
	<div class="row">
		<div class="col-md-6">
			<center><img src="'.GoogleAuthenticator::getQrCodeUrl('totp', $core->user->getData('email'), $secret).'" /><br /><br /><code>'.$secret.'</code></center>
		</div>
		<div class="col-md-6">
			<div class="alert alert-info">Please verify your TOTP settings by scanning the QR Code to the right with your phone\'s authenticator application, and then enter the 6 number code generated by the application in the box below. Press the enter key when finished.</div>
			<form action="#" method="post" id="totp_token_verify">
				<div class="form-group">
					<label class="control-label" for="totp_token">TOTP Token</label>
					<input class="form-control input-lg" type="text" id="totp_token" style="font-size:30px;" />
				</div>
				'.$core->auth->XSRF().'
			</form>
		</div>
	</div>')->send();

});

$klein->respond('POST', '/ajax/account/totp/verify', function($request, $response) use ($core) {

	// Responding with body rather than a flash since this is an AJAX request.
	if(!$core->auth->XSRF($request->param('xsrf'))) {
		$response->body('<div class="alert alert-danger">Unable to verify XSRF token. Please reload the page and try again.</div>')->send();
		return;
	}

	if(!$core->auth->validateTOTP($request->param('token'), $core->user->getData('totp_secret'))){
		$response->body('<div class="alert alert-danger">Unable to verify your TOTP token. Please try again.</div>')->send();
		return;
	}

	$account = ORM::forTable('users')->findOne($core->user->getData('id'));
	$account->set('use_totp', 1);
	$account->save();

	$response->body('<div class="alert alert-success">Your account has been enabled with TOTP verification. Please click the close button on this box to finish.</div>')->send();

});