yii2 APNs-Gcm through RabbitMQ
==========================
yii2 APNs-Gcm through RabbitMQ extension

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist gbksoft/yii2-apnsgcm-through-rabbitmq "*"
```

or add

```
"gbksoft/yii2-apnsgcm-through-rabbitmq": "*"
```

to the require section of your `composer.json` file.


Usage
-----

First of all, you need to configure the following extensions to work with RebbitMQ:
  - https://github.com/webtoucher/yii2-amqp

in your main.php your configuration would look like this

```php
'components' => [
	'apns' => [
		'class' => 'gbksoft\apnsGcm\Apns',
		'environment' => \gbksoft\apnsGcm\Apns::ENVIRONMENT_SANDBOX,
		'pemFile' => dirname(__FILE__).'/apnssert/apns-dev.pem',
		// 'retryTimes' => 3,
		'options' => [
			'sendRetryTimes' => 5
		]
	],
	'gcm' => [
		'class' => 'gbksoft\apnsGcm\Gcm',
		'apiKey' => 'your_api_key',
	],
	// using both gcm and apns, make sure you have 'gcm' and 'apns' in your component
	'apnsGcm' => [
		'class' => 'gbksoft\apnsGcm\ApnsGcm',
		// custom name for the component, by default we will use 'gcm' and 'apns'
		//'gcm' => 'gcm',
		//'apns' => 'apns',
	],
]
```

Online Tester
-------------
Please visit the link for online tester [http://apns-gcm.bryantan.info](http://apns-gcm.bryantan.info)

Usage
-----

**Usage using APNS only**

```php
/* @var $apnsGcm \gbksoft\apnsGcm\Apns */
$apns = Yii::$app->apns;
$apns->send($push_tokens, $message,
  [
    'customProperty_1' => 'Hello',
    'customProperty_2' => 'World'
  ],
  [
    'sound' => 'default',
    'badge' => 1
  ]
);
```

**Usage using GCM only**

```php
/* @var $apnsGcm \gbksoft\apnsGcm\Gcm */
$gcm = Yii::$app->gcm;
$gcm->send($push_tokens, $message,
  [
    'customerProperty' => 1,
  ],
  [
    'timeToLive' => 3
  ],
);
```

### Usage using APNS and GCM Together

**Send using Google Cloud Messaging**

```php
/* @var $apnsGcm \gbksoft\apnsGcm\ApnsGcm */
$apnsGcm = Yii::$app->apnsGcm;
$apnsGcm->send(\gbksoft\apnsGcm\ApnsGcm::TYPE_GCM, $push_tokens, $message,
  [
    'customerProperty' => 1
  ],
  [
    'timeToLive' => 3
  ],
)
```

**Send using Apple push notification service**

```php
/* @var $apnsGcm \gbksoft\apnsGcm\ApnsGcm */
$apnsGcm = Yii::$app->apnsGcm;
$apnsGcm->send(\bryglen\apnsgcm\ApnsGcm::TYPE_APNS, $push_tokens, $message,
  [
    'customerProperty' => 1
  ],
  [
    'sound' => 'default',
  	'badge' => 1
  ],
)
```

**Add to RebbitMQ queue a push notification**

```php
/* @var $apnsGcm \gbksoft\apnsGcm\ApnsGcm */
$apnsGcm = Yii::$app->apnsGcm;
$apnsGcm->addToQueue(\gbksoft\apnsGcm\ApnsGcm::TYPE_APNS, $push_tokens, $message,
  [
    'customerProperty' => 1
  ],
  [
    'sound' => 'default',
  	'badge' => 1
  ],
)
```

**You need to add console command to config like this**

```php
'controllerMap' => [
    'apnsGcm' => [
        'class' => 'gbksoft\apnsGcm\console\ApnsGcmController',
    ],
],
```

This extension is fork of https://github.com/bryglen/yii2-apns-gcm