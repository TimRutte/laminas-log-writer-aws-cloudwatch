<?php

namespace TimRutte\Laminas\Log\Writer;

use Aws\CloudWatchLogs\CloudWatchLogsClient;
use Laminas\Log\Writer\AbstractWriter;

class Cloudwatch extends AbstractWriter
{
    /** @var string $awsKey AWS access key */
    private $awsKey;

    /** @var string $awsSecret AWS secret key */
    private $awsSecret;

    /** @var string $awsRegion AWS region */
    private $awsRegion;

    /** @var string $groupName Cloudwatch group name */
    private $groupName;

    /** @var string $streamName Cloudwatch stream name */
    private $streamName;

    /** @var string $version AWS API version */
    private $version;

    /**
     * Set AWS credentials and Cloudwatch settings
     *
     * @param $awsKey
     * @param $awsSecret
     * @param $awsRegion
     * @param $groupName
     * @param $streamName
     * @param $version
     * @return void
     */
    public function configureAws($awsKey, $awsSecret, $awsRegion, $groupName, $streamName, $version = 'latest')
    {
        $this->awsKey = $awsKey;
        $this->awsSecret = $awsSecret;
        $this->awsRegion = $awsRegion;
        $this->groupName = $groupName;
        $this->streamName = $streamName;
        $this->version = $version;
    }

    public function doWrite(array $event)
    {
        $timestamp = time();

        $cloudWatchClient = new CloudWatchLogsClient(
            [
                'version' => $this->version,
                'region' => $this->awsRegion,
                'stream_name' => $this->streamName,
                'retention' => 14, // in days
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