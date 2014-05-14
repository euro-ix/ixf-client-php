# IXF Database - PHP Client

PHP RESTful interface for the IXF database,

## Installation

This library is best used with Composer. Just add the following to
your `composer.json`:

```json
"require": {
    "euro-ix/ixf-client-php": "dev-master"
}
```

If you are not using composer, see the [demo file](https://github.com/euro-ix/ixf-client-php/blob/master/demo/demo.php)
for an example list of includes.

## Usage

First, prime the API with the endpoint (official end point to be provided when live) and your credentials:

```php
IXF\IXF::setApiBase( 'https://www.example.com/api' );
IXF\IXF::setApiUser( 'guest' );
IXF\IXF::setApiPass( 'guest' );
```

You can get an array of all IXPs and print their name, short name and latitude / longitude,
as follows:

```php
$ixps = IXF\ApiResource::all( 'IXP' );

foreach( $ixps as $o )
    echo $o->full_name . '[' . $o->short_name . '] => ' . $o->lat . ', ' . $o->lon . "\n";
```

You can use skip and limit options as follows:

```php
$ixps = IXF\ApiResource::all( 'IXP', [ 'skip' => 10, 'limit' => 5 ] );
```

The default ordering is by ID.

You can fetch a specific IXP by id and edit it as follows:

```php
$ixp = IXF\ApiResource::retrieve( 'IXP', $id );
$ixp->short_name = 'NEW_SHORTNAME';
$ixp->save();
```

You can also delete that IXP via:

```php
$ixp->delete();
```

Finally, create an IXP via:

```php
$newObjId = IXF\ApiResource::create( 'IXP',
    [
        "full_name" => "Test IXP",
        "short_name" => "TIXP"
    ]
);

$newIXP = IXF\ApiResource::retrieve( 'IXP', $newObjId );
```

Complete documentation can be found in the [wiki](https://github.com/euro-ix/ixf-client-php/wiki).


## Documentation and Tests

See the [tests/](https://github.com/euro-ix/ixf-client-php/blob/master/tests)
for sample test suites which will show how the code works.

We'll link API documentation when it's uploaded.

## Source / Origin Story

Written by [Barry O'Donovan](https://github.com/barryo) of [INEX](https://www.inex.ie/) during
the May 2014 euro-ix hackaton in Sheffield, UK.

Issues and pull requests are welcomed.

[Stripe](https://stripe.com/) created  a beautiful [API](https://stripe.com/docs/api/php)
for their payment gateway. They open sourced the PHP library under the
MIT license. This library is based on that.

*(a few hours later)* Hmmmm... beautiful API interface, horrendously coded
API library. It was a mistake to use it but that's a bed I've made for
myself now. I've stripped 70% of the cruft and it's slightly more palatable.
