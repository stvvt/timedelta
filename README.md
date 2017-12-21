# About

This tiny PHP library helps you generate human language snippets for a time interval. For example

* now
* a minute ago
* 3 months ago
* ... etc

# Example

```php
<?php

$eventTime = $time() - 30; // 30 seconds back in time

$inWords = new \stvvt\TimeDeltaFormatter($eventTime);
echo $inWords;
```

# Install

```bash
$ composer require stvvt/timedelta
```

# API

@TODO