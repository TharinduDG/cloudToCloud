
<div id="providers">
	<img id="openid" class="login_with"
		src="<?php echo OC_Helper::imagePath('cloud_to_cloud', 'openid.jpg') ?>"
		alt="openid" /> <img id="google" class="login_with"
		src="<?php echo OC_Helper::imagePath('cloud_to_cloud', 'google.jpg') ?>"
		alt="google" /> <img id="yahoo" class="login_with"
		src="<?php echo OC_Helper::imagePath('cloud_to_cloud', 'yahoo.jpg') ?>"
		alt="yahoo" />
</div>
<form id="provider_form" method="post"
	action="<?php echo OC_Helper::linkTo('cloud_to_cloud','viewShared.php')?>">
	<div class="login_window" provider="openid">
		OpenID: <input size="80" type="text" id="openid_url"
			name="openid_identifier" />
		<button id="provider_openid" type="submit">Submit</button>
		<br />
		<p id="openid_example">eg: http://myblog.domain.com</p>
	</div>

	<div class="login_window" provider="google">
		<input id="provider_google" class="provider_logo" type="image"
			src="<?php echo OC_Helper::imagePath('cloud_to_cloud', 'sign_in_with_gmail.jpg') ?>"
			width="120px" height="120px" value="" />
	</div>

	<div class="login_window" provider="yahoo">
		<input id="provider_yahoo" class="provider_logo" type="image"
			src="<?php echo OC_Helper::imagePath('cloud_to_cloud', 'sign_in_with_yahoo.jpg') ?>"
			width="120px" height="120px" value="" />
	</div>
</form>
