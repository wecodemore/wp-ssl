# Is SSL?

Utility class for WordPress to check if a (local/on the current host) URL is accessible via SSL (or not).

## Methods

To setup the class, you need just a relative path. This path gets added to `get_site_url()`.
The second and third arguments are optional and can get omitted.

    $ssl_utils = new \WCM\SSL\Check( $path );

    $ssl_utils = new \WCM\SSL\Check( 'some/relative/path', 443, 12 );

The SSL `$port` defaults to `443`, but can get set to something totally different. It can also
get set later on by using the setter.

    $ssl_utils->setPort( 12345 );

The class can as well try to figure it out for you:

    $guessed_port = $ssl_utils->getPort();

To find out if the current host is running on ssl, you can simply check

    $is_ssl = $ssl_utils->isSSL();

And finally retrieving the SSL URl is as easy as just calling

    $ssl_url = $ssl_utils->getURl();

To use the class on a different site than the current one in a **Multisite** install, you
can explicitly set the `$blog_id` either during instantiating the class or with the setter.

    $ssl_url = $ssl_utils->setBlogID( 12 );


All mentioned setter methods are chainable:

    $ssl_utils = new \WCM\SSL\Check( 'example/path' );
    $ssl_utils
        ->setPort( 443 )
        ->setBlogID( 12 );

## Install

Best served via Composer:

    "wecodemore/wp-ssl" : "^1.0"