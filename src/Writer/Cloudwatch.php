<?php

namespace TimRutte\Laminas\Log\Writer;

use Aws\CloudWatchLogs\CloudWatchLogsClient;
use Laminas\Log\Exception\InvalidArgumentException;
use Laminas\Log\Writer\AbstractWriter;

class Cloudwatch extends AbstractWriter
{
    /** @var string $awsKey AWS access key */
    private string $awsKey;

    /** @var string $awsSecret AWS secret key */
    private string $awsSecret;

    /** @var string $awsRegion AWS region */
    private string $awsRegion;

    /** @var string $groupName Cloudwatch group name */
    private string $groupName;

    /** @var string $streamName Cloudwatch stream name */
    private string $streamName;

    /** @var integer $retentionInDays Log retention in days */
    private int $retentionInDays;
    
    /** @var string $version AWS API version */
    private string $version;

    /**
     * Set AWS credentials and Cloudwatch settings
     *
     * @param string $awsKey
     * @param string $awsSecret
     * @param string $awsRegion
     * @param string $groupName
     * @param string $streamName
     * @param int $retentionInDays
     * @param string $version
     * @return void
     */
    public function configureAws(
        string $awsKey, 
        string $awsSecret, 
        string $awsRegion, 
        string $groupName, 
        string $streamName, 
        int $retentionInDays = 14, 
        string $version = 'latest'
    ): void
    {
        $this->awsKey = $awsKey;
        $this->awsSecret = $awsSecret;
        $this->awsRegion = $awsRegion;
        $this->groupName = $groupName;
        $this->streamName = $streamName;
        $this->retentionInDays = $retentionInDays;
        $this->version = $version;
    }

    private function validateAwsConfiguration()
    {
        if (empty($this->awsKey)) {
            throw new InvalidArgumentException('Missing AWS access key');
        }

        if (empty($this->awsSecret)) {
            throw new InvalidArgumentException('Missing AWS secret key');
        }

        if (empty($this->awsRegion)) {
            throw new InvalidArgumentException('Missing AWS region');
        }

        if (empty($this->groupName)) {
            throw new InvalidArgumentException('Missing cloudwatch group name');
        }

        if (empty($this->streamName)) {
            throw new InvalidArgumentException('Missing cloudwatch stream name');
        }

        if (!is_numeric($this->retentionInDays)) {
            throw new InvalidArgumentException('Unknown retention. Integer is required.');
        }
    }
    
    public function doWrite(array $event)
    {
        $this->validateAwsConfiguration();
        
        $timestamp = time();

        $cloudWatchClient = new CloudWatchLogsClient(
            [
                'version' => $this->version,
                'region' => $this->awsRegion,
                'stream_name' => $this->streamName,
                'retention' => $this->retentionInDays,
                'group_name' => $this->groupName,
                'credentials' => [
                    'key' => $this->awsKey,
                    'secret' => $this->awsSecret,
                ]
            ]
        );

        $eventData = [
            'logEvents' => [
                [
                    'message' => json_encode($event),
                    'timestamp' => ($timestamp * 1000)
                ]
            ],
            'logGroupName' => $this->groupName,
            'logStreamName' => $this->streamName
        ];

        $existingStreams = $cloudWatchClient->describeLogStreams(
            [
                'logGroupName' => $this->groupName,
                'logStreamNamePrefix' => $this->streamName
            ]
        )->get('logStreams');

        foreach ($existingStreams as $stream) {
            if ($stream['logStreamName'] === $this->streamName && isset($stream['uploadSequenceToken'])) {
                $eventData['sequenceToken'] = $stream['uploadSequenceToken'];
                break;
            }
        }

        $cloudWatchClient->putLogEvents($eventData);
    }
}