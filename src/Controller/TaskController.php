<?php

namespace Controller;

use controllers\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

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
            $data = base64_encode(json_encode(array_merge(
                $job,
                array(
                    "timestamp" => $timestamp
                )
            ), true));

            $jobs[] = array(
                "print" => print_r($job, true),
                "removeurl" => $this->generate("taskmanager_deleteJob", array("data" => $data, "noci" => 1))
            );
        }

        return $this->render(
            'Resque/Default/scheduled_timestamp.html.twig',
            array(
                'timestamp' => $timestamp,
                'jobs' => $jobs
            )
        );
    }

    public function removeJob()
    {
        $request = $this->getRequest();

        $data = json_decode(base64_decode($request->query->get("data")), true);

        $redis = \Resque::redis();

        $redis->del("delayed:".$data["timestamp"]);
        $redis->zrem('delayed_queue_schedule', $data["timestamp"]);

        //\ResqueScheduler::removeDelayed($data["queue"], $data["class"], $data["args"]);

        return new RedirectResponse($this->getRequest()->headers->get('referer'));

        //
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
