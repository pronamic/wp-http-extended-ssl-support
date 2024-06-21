# Additional SSL Options for WordPress HTTP API

This library extends the WordPress HTTP API with additional SSL options. It provides the option to pass the cURL options `CURLOPT_SSLCERT`, `CURLOPT_SSLKEY` and `CURLOPT_SSLKEYPASSWD` to requests.

## Usage

```php
\wp_remote_get(
	$url,
	[
		'ssl_certificate'  => \ABSPATH . '/../private/your-certificate.pem',
		'ssl_key'          => \ABSPATH . '/../private/your-key.pem',
		'ssl_key_password' => 'your-password', 
	]
);
```

```php
\wp_remote_get(
	$url,
	[
		'ssl_certificate_blob' => '-----BEGIN CERTIFICATE-----', // Must be a full SSL certificate string.
		'ssl_key_blob'         => '-----BEGIN ENCRYPTED PRIVATE KEY-----', // Must be a full SSL key string.
		'ssl_key_password'     => 'your-password', 
	]
);
```

## TLS backends and cURL

It is good to realize that PHP and cURL can work with different TLS backends. Not all TLS backends have support for all cURL SSL options. An overview of all cURL options and their support in the various TLS backends can be found at https://curl.se/libcurl/c/tls-options.html, below a part of this table from June 11, 2024:

> libcurl can use different TLS backends, selected at both build-time and run-time. This table shows all TLS related options and details the set of TLS backends that work with it.
> 
> The **OpenSSL** column also covers BoringSSL, libressl, quictls, AWS-LC and AmiSSL.
> 
> option | BearSSL | GnuTLS | mbedTLS | OpenSSL | rustls | Schannel | Secure Transport | wolfSSL
> -- | -- | -- | -- | -- | -- | -- | -- | --
> `CURLOPT_KEYPASSWD` |   |   | ✔ | ✔ |   | ✔ |   | ✔
> `CURLOPT_SSLCERT` |   | ✔ | ✔ | ✔ |   | ✔ | ✔ | ✔
> `CURLOPT_SSLCERTTYPE` |   | ✔ | ✔ | ✔ |   | ✔ | ✔ | ✔
> `CURLOPT_SSLCERT_BLOB` |   |   | ✔ | ✔ |   | ✔ | ✔ |  
> `CURLOPT_SSLENGINE` |   |   |   | ✔ |   |   |   |  
> `CURLOPT_SSLENGINE_DEFAULT` |   |   |   | ✔ |   |   |   |  
> `CURLOPT_SSLKEY` |   |   | ✔ | ✔ |   | ✔ |   | ✔
> `CURLOPT_SSLKEYTYPE` | ✔ |   |   | ✔ |   |   |   | ✔
> `CURLOPT_SSLKEY_BLOB` |   |   |   | ✔ |   |   |   |  
> `CURLOPT_SSLVERSION`  | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ | ✔

## Links

- https://github.com/WordPress/Requests/issues/377
- https://core.trac.wordpress.org/ticket/34883
- https://curl.se/libcurl/c/tls-options.html
