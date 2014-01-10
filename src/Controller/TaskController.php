<?php

namespace Controller;

class TaskController extends AbstractController
{
    public function __construct()
    {

    }

    public function indexAction()
    {
        $this->onlyAdmin();

        return $this->render(
            'Resque/Default/index.html.twig',
            array(
                'resque' => $this->getResque(),
            )
        );
    }

    public function showQueueAction($queue)
    {
        $this->onlyAdmin();

        list($start, $count, $showingAll) = $this->getShowParameters();

        $queue = $this->getResque()->getQueue($queue);
        $jobs = $queue->getJobs($start, $count);

        if (!$showingAll) {
            $jobs = array_reverse($jobs);
        }

        return $this->render(
            'Resque/Default/queue_show.html.twig',
            array(
                'queue' => $queue,
                'jobs' => $jobs,
                'showingAll' => $showingAll
            )
        );
    }

    public function listFailedAction()
    {
        $this->onlyAdmin();

        list($start, $count, $showingAll) = $this->getShowParameters();

        $jobs = $this->getResque()->getFailedJobs($start, $count);

        if (!$showingAll) {
            $jobs = array_reverse($jobs);
        }

        return $this->render(
            'Resque/Default/failed_list.html.twig',
            array(
                'jobs' => $jobs,
                'showingAll' => $showingAll,
            )
        );
    }

    public function listScheduledAction()
    {
        $this->onlyAdmin();

        return $this->render(
            'Resque/Default/scheduled_list.html.twig',
            array(
                'timestamps' => $this->getResque()->getDelayedJobTimestamps()
            )
        );
    }

    public function showTimestampAction($timestamp)
    {
        $this->onlyAdmin();

        $timestamp = $this->getRequest()->query->get("timestamp");

        $jobs = array();

        // we don't want to enable the twig debug extension for this...
        foreach ($this->getResque()->getJobsForTimestamp($timestamp) as $job) {
            $jobs[] = print_r($job, true);
        }

        return $this->render(
            'Resque/Default/scheduled_timestamp.html.twig',
            array(
                'timestamp' => $timestamp,
                'jobs' => $jobs
            )
        );
    }

    /**
     * @return \service\Resque
     */
    protected function getResque()
    {
        return $this->get('resque');
    }

    /**
     * decide which parts of a job queue to show
     *
     * @return array
     */
    private function getShowParameters()
    {
        $this->onlyAdmin();

        $showingAll = false;
        $start = -100;
        $count = -1;

        if ($this->getRequest()->query->has('all')) {
            $start = 0;
            $count = -1;
            $showingAll = true;
        }

        return array($start, $count, $showingAll);
    }
}