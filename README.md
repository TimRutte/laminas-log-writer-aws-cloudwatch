# laminas-log-writer-aws-cloudwatch

Log writer to send all logs to AWS Cloudwatch.

## Installation

Run the following to install this library:

```bash
$ composer require timrutte/laminas-log-writer-cloudwatch
```

## Usage

```php
$writer = new \TimRutte\Laminas\Log\Writer\Cloudwatch();
$writer->configureAws(
    '<AWS_ACCESS_KEY>', 
    '<AWS_SECRET_KEY>', 
    '<AWS_REGION>', 
    '<CLOUDWATCH_GROUP_NAME>', 
    '<CLOUDWATCH_STREAM_NAME>'
);
$formatter = new \Laminas\Log\Formatter\Json();
$writer->setFormatter($formatter);
$logger = new \Laminas\Log\Logger();
$logger->addWriter($writer);
```

## Support

- [Issues](https://github.com/timrutte/laminas-log-writer-aws-cloudwach/issues/)

