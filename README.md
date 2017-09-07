[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Wabel/certain-api-wrapper/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Wabel/certain-api-wrapper/?branch=1.0)
[![Build Status](https://travis-ci.org/Wabel/certain-api-wrapper.svg?branch=master)](https://travis-ci.org/Wabel/certain-api-wrapper)
[![Coverage Status](https://coveralls.io/repos/Wabel/certain-api-wrapper/badge.svg?branch=master&service=github)](https://coveralls.io/github/Wabel/certain-api-wrapper?branch=master)

# Certain Api v2 PHP wrapper 

PHP Wrapper to use certain API v2.0 and integrate Events with Your Other Business Applications.

It provided by [Wabel]. 

### Tech

It uses :
* [Guzzle] http client library

### Installation

The recommended way to install Guzzle is through Composer.

- ##### Install Composer
curl -sS https://getcomposer.org/installer | php

Next, run the Composer command to install the latest stable version of Guzzle:
- ##### Install this package
composer.phar require wabel/certain-api-wrapper

After installing, you need to require Composer's autoloader:

require 'vendor/autoload.php';

You can then later update it using composer:

composer.phar update

### Detect changes about appointments (Update and Delete)
#### - DetectAppointmentsChangingsService
```php
$certainApiClient =  new \Wabel\CertainAPI\CertainApiClient(null,'username','password','accountCode');
$certainApiService  = new \Wabel\CertainAPI\CertainApiService($certainApiClient);
$appointmentsCertain = new \Wabel\CertainAPI\Ressources\AppointmentsCertain($certainApiService);
$detectService = new \Wabel\CertainAPI\Services\DetectAppointmentsChangingsService($appointmentsCertain);
$detectService->detectAppointmentsChangings($appointmentsOld,$appointmentsNew)
```

### Todos

 - Write Tests Ressources
 
 About Certain
 ----------------
 [Certain] Personalizing your event starts by showcasing your brand and collecting valuable attendee information. Registration is the critical moment when an attendee commits to your event, and a smooth process facilitated by Certain’s registration capabilities will set the right tone.

About Wabel
----------------
[Wabel] is the online marketplace for the european food industry. In our effort to integrate our web platform to more and more web services, we (Wabel's dev team!) are happy to share our work with Certain community.

[Guzzle]: <https://github.com/guzzle/guzzle>
[Wabel]: <http://www.wabel.com>
[Certain]: <http://www.certain.com>


