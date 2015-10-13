<?php

namespace MemtechEvents\Console\Commands;

use Illuminate\Console\Command;
use MemtechEvents\Event;
use Carbon\Carbon;
use MemtechEvents\Group;
use MemtechEvents\Tweet;

class ScheduleTweets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'memvents:schedule
                            {--debug : no API calls, no DB writes}
                            ';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Schedule tweets based on known events.';

    protected $event;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->event = \App::make('MemtechEvents\Event');
        $this->tweet = \App::make('MemtechEvents\Tweet');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $eventsIn3Days = $this->event->getEventsWithin3Days();

        foreach ($eventsIn3Days as $event)
        {
            $time = $this->getEventTime($event->time);
            $this->info('Event: ' . $event['name'] . ' at ' . $time->format('jS \o\f M \a\t h:ia'));

            $content = $event['name'] . ' at ' . $time->format('jS \o\f M \a\t h:ia') . ' #memtech ' . $event['event_url'];
            $tweet = $this->reformatStatus($content, $event, $time->format('jS \o\f M \a\t h:ia'));
            $tweet_at = $time->subDays(3)->subHours(10)->setTimezone('America/Chicago');

            // check if we already have a pending tweet

            $existing = Tweet::where('event_id', $event['event_id'])
                ->where('days_before', 3)
                ->first();

            if (is_null($existing)) {
                $this->tweet->create([
                    'content' => $tweet,
                    'tweet_at' => $tweet_at,
                    'sent' => false,
                    'event_id' => $event['event_id'],
                    'days_before' => 3,
                ]);
            }
        }

        $eventsIn24hours = $this->event->getEventsWithin24Hours();

        foreach ($eventsIn24hours as $event) {
            $time = $this->getEventTime($event->time);
            $this->info('Event: ' . $event['name'] . ' at ' . $time->format('jS \o\f M \a\t h:ia'));

            $content = $event['name'] . ' at ' . $time->format('jS \o\f M \a\t h:ia') . ' #memtech ' . $event['event_url'];
            $tweet = $this->reformatStatus($content, $event, $time->format('jS \o\f M \a\t h:ia'));
            $tweet_at = $time->subHours(10)->setTimezone('America/Chicago');

            // check if we already have a pending tweet

            $existing = Tweet::where('event_id', $event['event_id'])
                ->where('days_before', 0)
                ->first();

            if (is_null($existing)) {
                $this->tweet->create([
                    'content' => $tweet,
                    'tweet_at' => $tweet_at,
                    'sent' => false,
                    'event_id' => $event['event_id'],
                    'days_before' => 0,
                ]);
            }
        }
    }

    public function reformatStatus($content, $event, $time_string)
    {
        if (strlen($content) > 140)
        {
            $tweet = '';
            $event['name'] = substr($event['name'], 0, (140 - 60));
            $event['name'] = preg_replace('/ [^ ]*$/', '...', $event['name']);
            $tweet = $event['name'] . ' ' . $time_string . ' #memtech' . $event['event_url'];

        }
        else
        {
            $tweet = $content;
        }

        return $tweet;
    }

    public function getEventTime($time)
    {
        $event_time = Carbon::createFromTimestamp($time / 1000);
        $event_time->setTimezone('America/Chicago');

        return $event_time;
    }
}
