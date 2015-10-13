<?php

namespace MemtechEvents\Console\Commands;

use DMS\Service\Meetup\MeetupKeyAuthClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use MemtechEvents\Event;
use MemtechEvents\Group;

class UpdateEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'memvents:update
                            {--debug : no API calls, no DB writes}
                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Event Info From meetup.com.';

    protected $event;

    /**
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        parent::__construct();
        $this->memvent = $event;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
//        \DB::listen(function($sql, $bindings, $time) {
//            var_dump($sql);
//            var_dump($bindings);
//            var_dump($time);
//        });
        if ($this->option('debug'))
        {
            $this->info('Running in debug mode, no API calls or DB writes');
        }
        // Get Events
        if (!$this->option('debug')) {
            $client = MeetupKeyAuthClient::factory( array( 'key' => env( 'meetup_api' ) ) );
            $events = $client->getEvents( [ 'group_urlname' => 'memphis-technology-user-groups' ] );
        }
        if ($this->option('debug'))
        {
            // no Api calls, fake the response
            $response = $this->memvent->getSampleEvents();
            $events_array = json_decode($response, true);
            $events = $events_array['results'];
        }

        $this->output->progressStart(count($events));

        foreach ($events as $event)
        {

            $group = $this->matchGroup($event);

            $meetup = [];
            $meetup['name'] = $event['name'];
            $meetup['event_id'] = $event['id'];
            $meetup['time'] = $event['time'];
            $meetup['status'] = $event['status'];
            $meetup['event_url'] = $event['event_url'];
            $meetup['created'] = $event['created'];

            if (is_object($group))
            {
                $meetup['group_id'] = $group->id;
            }

            if ($this->findEvent($event['created'], $event['id']))
            {
                $this->info('Updating: ' . $meetup['name']);
                $meetup['id'] = $this->findEvent($event['created'], $event['id'])->id;
                // find meetup in our db
                $meet_up = $this->memvent->find($meetup['id']);
                // Update values
                $meet_up->name = $meetup['name'];
                $meet_up->event_id = $meetup['event_id'];
                $meet_up->time = $meetup['time'];
                $meet_up->status = $meetup['status'];
                $meet_up->event_url = $meetup['event_url'];
                $meet_up->created = $meetup['created'];

                if (is_object($group))
                {
                    $meet_up->group_id = $group->id;
                }

                if (!$this->option('debug'))
                {
                    $meet_up->save();
                }
                if ($this->option('debug'))
                {
                    $this->info('Should have updated: ' . $meetup['name']);
                }
            }
            else
            {
                $this->info('Creating: ' . $meetup['name']);
                if (!$this->option('debug'))
                {
                    $this->memvent->create($meetup);
                }
                if ($this->option('debug'))
                {
                    $this->info('Should have created: ' . $meetup['name']);
                }
            }
            $this->output->progressAdvance();
        }
        $this->info('All Done!');
    }

    public function findEvent($created, $event_id)
    {

        return $this->memvent->where('created', $created)
                             ->where('event_id', $event_id)
                             ->first();
    }

    public function matchGroup($event)
    {
        $query = $this->buildMatchQuery($event['name']);

        if ($query)
        {
            $group = $query->first();

            return $group;
        }

        return false;
    }

    public function buildMatchQuery($name)
    {
        $parts = explode(" ", $name);
        $query = DB::table('groups');

        if (count($parts) > 0) {
            foreach ($parts as $part)
            {
                $part = strtolower($part);

                if ($part == 'memphis')
                {
                    $query->where('name', 'LIKE', '%'. $part .'%');
                }
                else
                {
                    $query->orWhere('name', 'LIKE', '%'. $part .'%');
                }
            }

            return $query;
        }

        return false;
    }

}
