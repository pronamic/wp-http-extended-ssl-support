# Additional SSL Options for WordPress HTTP API

This library extends the WordPress HTTP API with additional SSL options. It provides the option to pass the cURL options `CURLOPT_SSLCERT`, `CURLOPT_SSLKEY` and `CURLOPT_SSLKEYPASSWD` to requests.

## Usage

```php
\wp_remote_get(
	$url,
	[
		'ssl_certificate'  => \ABSPATH . '/../private/your-certificate.pem',
		'ssl_key'          => \ABSPATH . '/../private/your-key.pem',
		'ssl_key_password' => ' your-password', 
	]
);
```

```php
\wp_remote_get(
	$url,
	[
		'ssl_certificate_blob' => '-----BEGIN CERTIFICATE-----', // Must be a full SSL certificate string.
		'ssl_key_blob'         => '-----BEGIN ENCRYPTED PRIVATE KEY-----', // Must be a full SSL key string.
		'ssl_key_password'     => ' your-password', 
	]
);
```

## Links

- https://github.com/WordPress/Requests/issues/377
- https://core.trac.wordpress.org/ticket/34883
- https://curl.se/libcurl/c/tls-options.html
