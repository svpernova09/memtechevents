<?php

namespace MemtechEvents\Console\Commands;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Console\Command;
use MemtechEvents\Event;
use MemtechEvents\Tweet;
use TwitterAPIExchange;
use Carbon\Carbon;

class ProcessTweets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'memvents:process
                            {--debug : no API calls, no DB writes}
                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process Tweets.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $tweets = Tweet::where('tweet_at', '<', Carbon::now()->toDateTimeString())
            ->where('sent', false)
            ->get();

        if (count($tweets) > 0)
        {
            foreach ($tweets as $tweet)
            {
                try
                {
                    $event = Event::where('event_id', $tweet->event_id)->firstOrFail();
                }
                catch (ModelNotFoundException $e)
                {
                    $this->info('Event with event_id ' . $tweet->event_id . ' not found');
                }

                if (!$event->isCancelled($tweet->event_id))
                {
                    $this->postTweet($tweet->content);
                    $this->setTweetAsSent($tweet->id);
                }
            }
        }
    }

    public function getSettings()
    {
        $settings = array(
            'oauth_access_token' => env('oauth_access_token', ''),
            'oauth_access_token_secret' => env('oauth_access_token_secret', ''),
            'consumer_key' => env('consumer_key', ''),
            'consumer_secret' => env('consumer_secret', ''),
        );

        return $settings;
    }

    public function postTweet($status)
    {
        $url = 'https://api.twitter.com/1.1/statuses/update.json';
        $postFields['status'] = $status;

        $tweet = new TwitterAPIExchange($this->getSettings());

        if (!$this->option('debug'))
        {
            $response = $tweet->setPostfields($postFields)
                ->buildOauth($url, 'POST')
                ->performRequest();

            $this->info(var_dump($response));
        }

        if ($this->option('debug'))
        {
            $this->info('We should have tweeted: ' . $status);
        }
    }

    public function setTweetAsSent($id)
    {
        $tweet = Tweet::find($id);
        $tweet->sent = true;

        if (!$this->option('debug'))
        {
            $tweet->save();
        }

        if ($this->option('debug'))
        {
            $this->info('We should have set tweet->id ' . $tweet->id . ' to sent');
        }
    }

}
