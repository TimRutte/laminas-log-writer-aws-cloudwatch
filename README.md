# laminas-log-writer-cloudwatch

## Installation

Run the following to install this library:

```bash
$ composer require timrutte/laminas-log-writer-cloudwatch
```

## Usage

```php
$writer = new \TimRutte\Laminas\Log\Writer\Cloudwatch();
$writer->configureAws(
    'AWS_ACCESS_KEY', 
    'AWS_SECRET_KEY', 
    'eu-west-1', 
    'groupName', 
    'streamName'
);
$formatter = new \Laminas\Log\Formatter\Json();
$writer->setFormatter($formatter);
$logger = new \Laminas\Log\Logger();
$logger->addWriter($writer);
```

## Support

- [Issues](https://github.com/timrutte/laminas-log-writer-cloudwach/issues/)

