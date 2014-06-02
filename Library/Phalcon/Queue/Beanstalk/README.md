Phalcon\Queue\Beanstalk
=======================

Extended Phalcon\Queue\Beanstalk class that supports tubes prefixes, pcntl-workers and tubes stats.

Allows to use same banstalkd server to multiple projects.

First add some tasks to the queues:

```php
<?php
use Phalcon\Queue\Beanstalk\Extended as BeanstalkExtended;

class IndexController extends Controller
{

    /**
     * Large video upload form.
     */
    public function uploadVideoAction()
    {
        if ($this->request->isPost()) {
            // Connect to the queue
            $beanstalk = new BeanstalkExtended(array(
                'host'   => '192.168.0.21',
                'prefix' => 'project-name',
            ));

            // Save the video info in database and send it to post-process
            $beanstalk->putInTube('processVideo', 4871);
        }
    }

    /**
     * Deletes the video.
     */
    public function deleteVideoAction()
    {
        // Connect to the queue
        $beanstalk = new BeanstalkExtended(array(
            'host'   => '192.168.0.21',
            'prefix' => 'project-name',
        ));

        // Send the command to physical unlink to the queue
        $beanstalk->putInTube('deleteVideo', 4871);
    }

}
```

Now handle the queues in console script (e.g.: php app/bin/video.php):

```php
#!/usr/bin/env php
<?php
/**
 * Workers that handles queues related to the videos.
 */
use Phalcon\Queue\Beanstalk\Extended as BeanstalkExtended;
use Phalcon\Queue\Beanstalk\Job;

$beanstalk = new BeanstalkExtended(array(
    'host'   => '192.168.0.21',
    'prefix' => 'project-name',
));

$beanstalk->addWorker('processVideo', function (Job $job) {
    // Here we should collect the meta information, make the screenshots, convert the video to the FLV etc.
    $videoId = $job->getBody();

    // It's very important to send the right exit code!
    exit(0);
});

$beanstalk->addWorker('deleteVideo', function (Job $job) {
    // Here we should collect the meta information, make the screenshots, convert the video to the FLV etc.
    $videoId = $job->getBody();

    unlink('/var/www/data/' . $videoId . '.flv');

    exit(0);
});

// Start processing queues
$beanstalk->doWork();
```

Simple console script that outputs tubes stats:

```php
#!/usr/bin/env php
<?php
use Phalcon\Queue\Beanstalk\Extended as BeanstalkExtended;
use Phalcon\Queue\Beanstalk\Job;

$prefix   = 'project-name';
beanstalk = new BeanstalkExtended(array(
    'host'   => '192.168.0.21',
    'prefix' => $prefix,
));

foreach ($beanstalk->getTubes() as $tube) {
    if (0 === strpos($tube, $prefix)) {
        try {
            $stats = $beanstalk->getTubeStats($tube);
            printf(
                "%s:\n\tready: %d\n\treserved: %d\n",
                $tube,
                $stats['current-jobs-ready'],
                $stats['current-jobs-reserved']
            );
        } catch (\Exception $e) {
        }
    }
}
```
