## Not meant for production

This is currently meant only for testing Mautic's auth plugins.

## Plugin Setup

1. Copy MauticSSOBundle into `/plugins` of Mautic.  
2. Delete the cache `/app/cache/dev`
3. Browse to `/s/plugins` and click the `Install/Upgrade Plugins` button
4. Configure a SSO provider (note the callback url for the SSO Provider Setup

## SSO Provider Setup

### Github

1. Register a [new application](https://github.com/settings/applications/new)
2. Use the client ID and secret when configuring the plugin's integration

### Google

1. Login to [Google's developer console](https://console.developers.google.com/)
2. Browse to APIs & Auth -> Credentials
3. Click `Add Credentials` then `OAuth 2.0 Client ID`
4. Configure the credentials
5. Use the client ID and secret when configuring the plugin's integration

## Local authorized Users

For demonstration of authorizing a login via the form, add the following to `/app/config/local.php` and fill in the details.  Note that the password must be encrypted with bcrypt (easiest way is to copy in a password from Mautic's users table).

```
	'authorized_users' => array(
		'custom_username' => array(
			'firstname' => '',
			'lastname'  => '',
			'password'  => '',
			'email'     => ''
		)
	)
```

Delete the cache then try to login with the above. The user will be created in Mautic's Users table and given role ID#1.  